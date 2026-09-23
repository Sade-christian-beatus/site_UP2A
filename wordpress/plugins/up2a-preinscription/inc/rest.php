<?php
/**
 * Route REST `up2a/v1/preinscription` — reçoit la soumission finale du
 * formulaire, valide TOUT côté serveur (jamais confiance dans la
 * validation JS, qui n'est qu'un confort pour le visiteur — voir
 * docs/03-roadmap.md phase 5), puis appelle Supabase avec la clé
 * `service_role` (uniquement depuis ce PHP serveur, jamais transmise au
 * navigateur — voir CLAUDE.md §3/§8).
 *
 * Flux (voir docs/01-architecture.md "Flux 1") :
 *   1. resolve formation_id (par slug) et annee_academique_id (année
 *      courante) via l'API REST Supabase (clé service_role, select
 *      seul — ces tables sont aussi lisibles en anon mais on reste
 *      cohérent en utilisant service_role pour tout l'appel serveur) ;
 *   2. upload des pièces jointes vers Supabase Storage (bucket
 *      `candidatures`) ;
 *   3. insertion de la ligne dans `public.candidatures` (statut
 *      `nouvelle` par défaut, jamais de champ paiement) ;
 *   4. e-mail de confirmation au candidat via wp_mail() (SMTP WordPress —
 *      choix retenu plutôt qu'une fonction Supabase Edge, pour rester
 *      simple : voir docs/01-architecture.md "Flux 1").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'up2a/v1',
			'/preinscription',
			array(
				'methods'             => 'POST',
				'callback'            => 'up2a_preinscription_handle_submit',
				'permission_callback' => '__return_true',
			)
		);
	}
);

/**
 * Vérifie le nonce REST standard WordPress (créé via wp_create_nonce
 * ('wp_rest') côté shortcode, voir up2a-preinscription.php). Fonctionne
 * pour un visiteur anonyme, ce n'est pas une authentification mais un
 * garde-fou contre les soumissions automatisées naïves — combiné au
 * honeypot et à la limite de fréquence ci-dessous.
 */
function up2a_preinscription_verify_nonce( WP_REST_Request $request ): bool {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	return is_string( $nonce ) && wp_verify_nonce( $nonce, 'wp_rest' );
}

/**
 * Limite très simple par IP (5 soumissions / heure) pour réduire le bruit
 * de bots sans exiger de CAPTCHA (pas demandé, pas nécessaire pour un
 * MVP — à revoir si abus constaté).
 */
function up2a_preinscription_rate_limited( string $ip ): bool {
	$key   = 'up2a_preinsc_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= 5 ) {
		return true;
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	return false;
}

/**
 * Requête générique vers l'API REST Supabase (PostgREST) avec la clé
 * service_role. Retourne le corps décodé (array) ou un WP_Error.
 */
function up2a_preinscription_supabase_request( string $method, string $path, ?array $body = null, array $extra_headers = array() ) {
	if ( ! defined( 'UP2A_SUPABASE_URL' ) || ! defined( 'UP2A_SUPABASE_SERVICE_ROLE_KEY' ) ) {
		return new WP_Error( 'up2a_config', __( 'Configuration Supabase manquante.', 'up2a-preinscription' ) );
	}

	$url = rtrim( UP2A_SUPABASE_URL, '/' ) . $path;

	$headers = array_merge(
		array(
			'apikey'        => UP2A_SUPABASE_SERVICE_ROLE_KEY,
			'Authorization' => 'Bearer ' . UP2A_SUPABASE_SERVICE_ROLE_KEY,
		),
		$extra_headers
	);

	$args = array(
		'method'  => $method,
		'headers' => $headers,
		'timeout' => 20,
	);

	if ( null !== $body ) {
		$args['headers']['Content-Type'] = 'application/json';
		$args['body']                    = wp_json_encode( $body );
	}

	$response = wp_remote_request( $url, $args );

	if ( is_wp_error( $response ) ) {
		error_log( 'up2a-preinscription Supabase error: ' . $response->get_error_message() );
		return new WP_Error( 'up2a_supabase', __( 'Le service est momentanément indisponible, merci de réessayer.', 'up2a-preinscription' ) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$raw  = wp_remote_retrieve_body( $response );
	$data = json_decode( $raw, true );

	if ( $code < 200 || $code >= 300 ) {
		error_log( 'up2a-preinscription Supabase HTTP ' . $code . ': ' . $raw );
		return new WP_Error( 'up2a_supabase', __( 'Le service est momentanément indisponible, merci de réessayer.', 'up2a-preinscription' ) );
	}

	return is_array( $data ) ? $data : array();
}

/**
 * Résout le slug de formation (choisi dans le formulaire) en UUID réel
 * de `public.formations` sur Supabase.
 */
function up2a_preinscription_resolve_formation_id( string $slug ) {
	$result = up2a_preinscription_supabase_request(
		'GET',
		'/rest/v1/formations?slug=eq.' . rawurlencode( $slug ) . '&select=id&limit=1'
	);
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	if ( empty( $result[0]['id'] ) ) {
		return new WP_Error( 'up2a_formation_introuvable', __( 'Formation introuvable.', 'up2a-preinscription' ) );
	}
	return $result[0]['id'];
}

/**
 * Résout l'année académique courante (est_courante = true).
 */
function up2a_preinscription_resolve_annee_academique_id() {
	$result = up2a_preinscription_supabase_request(
		'GET',
		'/rest/v1/annees_academiques?est_courante=eq.true&select=id&limit=1'
	);
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	if ( empty( $result[0]['id'] ) ) {
		return new WP_Error( 'up2a_annee_introuvable', __( "Aucune année académique courante n'est configurée.", 'up2a-preinscription' ) );
	}
	return $result[0]['id'];
}

/**
 * Upload un fichier vers le bucket Storage `candidatures`. Retourne le
 * chemin relatif stocké (à conserver dans `pieces_jointes`) ou un
 * WP_Error.
 */
function up2a_preinscription_upload_piece( array $file, string $label ) {
	if ( ! defined( 'UP2A_SUPABASE_URL' ) || ! defined( 'UP2A_SUPABASE_SERVICE_ROLE_KEY' ) ) {
		return new WP_Error( 'up2a_config', __( 'Configuration Supabase manquante.', 'up2a-preinscription' ) );
	}

	if ( ! empty( $file['error'] ) && UPLOAD_ERR_OK !== $file['error'] ) {
		return new WP_Error( 'up2a_upload', __( "Échec de l'envoi du fichier.", 'up2a-preinscription' ) );
	}

	if ( $file['size'] > up2a_preinscription_max_file_size() ) {
		return new WP_Error( 'up2a_upload_taille', __( 'Fichier trop volumineux (5 Mo maximum).', 'up2a-preinscription' ) );
	}

	$checked = wp_check_filetype( $file['name'], up2a_preinscription_allowed_mimes() );
	if ( empty( $checked['type'] ) ) {
		return new WP_Error( 'up2a_upload_type', __( 'Type de fichier non accepté (JPG, PNG ou PDF uniquement).', 'up2a-preinscription' ) );
	}

	$bytes = file_get_contents( $file['tmp_name'] );
	if ( false === $bytes ) {
		return new WP_Error( 'up2a_upload', __( "Échec de l'envoi du fichier.", 'up2a-preinscription' ) );
	}

	$ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
	$path = gmdate( 'Y/m' ) . '/' . wp_generate_uuid4() . '-' . $label . '.' . $ext;

	$url = rtrim( UP2A_SUPABASE_URL, '/' ) . '/storage/v1/object/candidatures/' . $path;

	$response = wp_remote_request(
		$url,
		array(
			'method'  => 'POST',
			'headers' => array(
				'apikey'        => UP2A_SUPABASE_SERVICE_ROLE_KEY,
				'Authorization' => 'Bearer ' . UP2A_SUPABASE_SERVICE_ROLE_KEY,
				'Content-Type'  => $checked['type'],
			),
			'body'    => $bytes,
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( 'up2a-preinscription upload error: ' . $response->get_error_message() );
		return new WP_Error( 'up2a_upload', __( "Échec de l'envoi du fichier, merci de réessayer.", 'up2a-preinscription' ) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		error_log( 'up2a-preinscription upload HTTP ' . $code . ': ' . wp_remote_retrieve_body( $response ) );
		return new WP_Error( 'up2a_upload', __( "Échec de l'envoi du fichier, merci de réessayer.", 'up2a-preinscription' ) );
	}

	return $path;
}

/**
 * Valide et nettoie les champs texte du formulaire. Retourne le tableau
 * validé ou un WP_Error listant le premier problème rencontré.
 */
function up2a_preinscription_validate_fields( WP_REST_Request $request ) {
	$nom      = sanitize_text_field( (string) $request->get_param( 'nom' ) );
	$prenom   = sanitize_text_field( (string) $request->get_param( 'prenom' ) );
	$email    = sanitize_email( (string) $request->get_param( 'email' ) );
	$tel      = sanitize_text_field( (string) $request->get_param( 'telephone' ) );
	$naissance = sanitize_text_field( (string) $request->get_param( 'date_naissance' ) );
	$sexe     = sanitize_text_field( (string) $request->get_param( 'sexe' ) );
	$formation_slug = sanitize_title( (string) $request->get_param( 'formation' ) );

	if ( '' === $nom || '' === $prenom ) {
		return new WP_Error( 'up2a_validation', __( 'Nom et prénom sont obligatoires.', 'up2a-preinscription' ) );
	}
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'up2a_validation', __( 'Adresse e-mail invalide.', 'up2a-preinscription' ) );
	}
	if ( '' === $tel ) {
		return new WP_Error( 'up2a_validation', __( 'Le numéro de téléphone est obligatoire.', 'up2a-preinscription' ) );
	}
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $naissance ) ) {
		return new WP_Error( 'up2a_validation', __( 'Date de naissance invalide.', 'up2a-preinscription' ) );
	}
	if ( ! in_array( $sexe, array( 'M', 'F' ), true ) ) {
		return new WP_Error( 'up2a_validation', __( 'Sexe invalide.', 'up2a-preinscription' ) );
	}
	if ( ! up2a_preinscription_is_valid_formation_slug( $formation_slug ) ) {
		return new WP_Error( 'up2a_validation', __( 'Formation invalide.', 'up2a-preinscription' ) );
	}

	return array(
		'nom'                   => $nom,
		'prenom'                => $prenom,
		'email'                 => $email,
		'telephone'             => $tel,
		'date_naissance'        => $naissance,
		'sexe'                  => $sexe,
		'formation_slug'        => $formation_slug,
		'adresse'               => sanitize_text_field( (string) $request->get_param( 'adresse' ) ),
		'diplome_obtenu'        => sanitize_text_field( (string) $request->get_param( 'diplome_obtenu' ) ),
		'etablissement_origine' => sanitize_text_field( (string) $request->get_param( 'etablissement_origine' ) ),
	);
}

function up2a_preinscription_handle_submit( WP_REST_Request $request ) {
	if ( ! up2a_preinscription_verify_nonce( $request ) ) {
		return new WP_Error( 'up2a_nonce', __( 'Session expirée, merci de recharger la page et réessayer.', 'up2a-preinscription' ), array( 'status' => 403 ) );
	}

	// Honeypot : un vrai visiteur ne remplit jamais ce champ (masqué en CSS).
	if ( '' !== (string) $request->get_param( 'site_web' ) ) {
		return new WP_REST_Response( array( 'success' => true ), 200 ); // Réponse neutre, ne pas indiquer au bot que c'est un piège.
	}

	$ip = $request->get_header( 'X-Forwarded-For' ) ?: ( $_SERVER['REMOTE_ADDR'] ?? '' );
	$ip = trim( explode( ',', (string) $ip )[0] );
	if ( $ip && up2a_preinscription_rate_limited( $ip ) ) {
		return new WP_Error( 'up2a_rate_limit', __( 'Trop de soumissions, merci de réessayer plus tard.', 'up2a-preinscription' ), array( 'status' => 429 ) );
	}

	$fields = up2a_preinscription_validate_fields( $request );
	if ( is_wp_error( $fields ) ) {
		$fields->add_data( array( 'status' => 400 ) );
		return $fields;
	}

	if ( ! (bool) $request->get_param( 'consentement' ) ) {
		return new WP_Error( 'up2a_validation', __( 'Merci de confirmer la déclaration avant de continuer.', 'up2a-preinscription' ), array( 'status' => 400 ) );
	}

	// Pièces jointes obligatoires : pièce d'identité + diplôme.
	$files = $request->get_file_params();
	foreach ( up2a_preinscription_pieces_attendues() as $key => $piece ) {
		if ( $piece['requis'] && empty( $files[ 'fichier_' . $key ] ) ) {
			return new WP_Error( 'up2a_validation', sprintf(
				/* translators: %s: nom de la pièce jointe attendue */
				__( 'Le document "%s" est obligatoire.', 'up2a-preinscription' ),
				$piece['label']
			), array( 'status' => 400 ) );
		}
	}

	$formation_id = up2a_preinscription_resolve_formation_id( $fields['formation_slug'] );
	if ( is_wp_error( $formation_id ) ) {
		$formation_id->add_data( array( 'status' => 502 ) );
		return $formation_id;
	}

	$annee_id = up2a_preinscription_resolve_annee_academique_id();
	if ( is_wp_error( $annee_id ) ) {
		$annee_id->add_data( array( 'status' => 502 ) );
		return $annee_id;
	}

	$pieces_jointes = array();
	foreach ( up2a_preinscription_pieces_attendues() as $key => $piece ) {
		if ( empty( $files[ 'fichier_' . $key ] ) ) {
			continue;
		}
		$path = up2a_preinscription_upload_piece( $files[ 'fichier_' . $key ], $key );
		if ( is_wp_error( $path ) ) {
			$path->add_data( array( 'status' => 502 ) );
			return $path;
		}
		$pieces_jointes[] = array(
			'type'  => $key,
			'label' => $piece['label'],
			'path'  => $path,
		);
	}

	$candidature = array(
		'nom'                   => $fields['nom'],
		'prenom'                => $fields['prenom'],
		'date_naissance'        => $fields['date_naissance'],
		'sexe'                  => $fields['sexe'],
		'email'                 => $fields['email'],
		'telephone'             => $fields['telephone'],
		'adresse'               => '' !== $fields['adresse'] ? $fields['adresse'] : null,
		'formation_id'          => $formation_id,
		'annee_academique_id'   => $annee_id,
		'diplome_obtenu'        => '' !== $fields['diplome_obtenu'] ? $fields['diplome_obtenu'] : null,
		'etablissement_origine' => '' !== $fields['etablissement_origine'] ? $fields['etablissement_origine'] : null,
		'pieces_jointes'        => $pieces_jointes,
	);

	$inserted = up2a_preinscription_supabase_request(
		'POST',
		'/rest/v1/candidatures',
		$candidature,
		array( 'Prefer' => 'return=minimal' )
	);
	if ( is_wp_error( $inserted ) ) {
		$inserted->add_data( array( 'status' => 502 ) );
		return $inserted;
	}

	up2a_preinscription_send_emails( $fields );

	return new WP_REST_Response( array( 'success' => true ), 200 );
}

/**
 * E-mail de confirmation au candidat + notification interne. Utilise
 * wp_mail() (SMTP WordPress) — voir docs/01-architecture.md "Flux 1"
 * pour l'arbitrage vs. fonction Supabase Edge.
 */
function up2a_preinscription_send_emails( array $fields ): void {
	$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	$subject = __( 'Confirmation de votre préinscription — UP-2A', 'up2a-preinscription' );
	$body    = sprintf(
		/* translators: 1: prénom, 2: nom de l'université */
		__(
			"Bonjour %1\$s,\n\nNous avons bien reçu votre préinscription à l'%2\$s.\n\nVotre dossier va être étudié par nos équipes, qui vous recontacteront prochainement pour la suite du processus.\n\nAucun frais n'est à régler à ce stade.\n\nCordialement,\nL'équipe UP-2A",
			'up2a-preinscription'
		),
		$fields['prenom'],
		$site_name
	);

	wp_mail( $fields['email'], $subject, $body );

	$admin_email = apply_filters( 'up2a_preinscription_notify_email', get_option( 'admin_email' ) );
	if ( $admin_email ) {
		$admin_subject = __( 'Nouvelle préinscription reçue', 'up2a-preinscription' );
		$admin_body    = sprintf(
			"%s %s — %s — %s\n%s",
			$fields['prenom'],
			$fields['nom'],
			$fields['email'],
			$fields['telephone'],
			$fields['formation_slug']
		);
		wp_mail( $admin_email, $admin_subject, $admin_body );
	}
}
