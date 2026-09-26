<?php
/**
 * CPT "Formation" + taxonomie "Faculté" — rend le contenu des formations
 * modifiable depuis le tableau de bord WordPress (menu "Formations"),
 * remplace le tableau statique en dur dans le code (voir
 * docs/06-storyboard.md "CPT Formation depuis le dashboard"). Le rewrite
 * natif du CPT (`/formations/{slug}/`) remplace la règle de réécriture
 * manuelle utilisée avant cette version.
 *
 * Champs du CPT :
 * - Titre           → nom de la formation.
 * - Slug             → utilisé tel quel dans l'URL /formations/{slug}/.
 * - Extrait          → description courte (modale + page de détail).
 * - Contenu          → "Programme" sur la page de détail (vide = "à venir").
 * - Image mise en avant → photo (deux tailles dédiées générées : desktop
 *                      et mobile, voir up2a_formations_register_image_sizes()).
 * - Faculté (taxonomie) → nom court (ex. "SJPA") + un champ "Nom complet"
 *                      ajouté sur l'écran de gestion des termes.
 * - Métabox "Détails" → icône (liste fermée) et débouchés (un par ligne).
 * - Ordre (page-attributes) → contrôle l'ordre d'affichage des cartes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function up2a_formations_register_cpt(): void {
	register_post_type(
		'up2a_formation',
		array(
			'labels'        => array(
				'name'               => __( 'Formations', 'up2a-formations' ),
				'singular_name'      => __( 'Formation', 'up2a-formations' ),
				'add_new_item'       => __( 'Ajouter une formation', 'up2a-formations' ),
				'edit_item'          => __( 'Modifier la formation', 'up2a-formations' ),
				'all_items'          => __( 'Toutes les formations', 'up2a-formations' ),
				'excerpt_meta_box'   => __( 'Description courte (modale + page de détail)', 'up2a-formations' ),
				'featured_image'     => __( 'Photo de la formation', 'up2a-formations' ),
				'set_featured_image' => __( 'Définir la photo', 'up2a-formations' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'show_in_menu'  => true,
			'menu_position' => 24,
			'menu_icon'     => 'dashicons-welcome-learn-more',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'formations', 'with_front' => false ),
			'show_in_rest'  => true,
		)
	);

	register_taxonomy(
		'up2a_faculte',
		'up2a_formation',
		array(
			'labels'            => array(
				'name'          => __( 'Facultés', 'up2a-formations' ),
				'singular_name' => __( 'Faculté', 'up2a-formations' ),
			),
			'hierarchical'      => false,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		)
	);
}
add_action( 'init', 'up2a_formations_register_cpt' );

/**
 * Flush automatique si la version du plugin a changé depuis le dernier
 * chargement : filet de sécurité pour le cas fréquent où les fichiers sont
 * remplacés sans passer par une (dés)activation WordPress (voir
 * wordpress/README.md "Dépannage"). Important en particulier pour la
 * migration 1.0.0 → 1.1.0 (règle de réécriture manuelle → rewrite natif
 * du CPT) : sans ce flush, les anciennes règles mises en cache pointent
 * encore vers l'ancien mécanisme et les pages /formations/{slug}/ ne se
 * chargent plus correctement jusqu'au prochain flush.
 */
add_action(
	'init',
	function (): void {
		if ( get_option( 'up2a_formations_rewrite_version' ) !== UP2A_FORMATIONS_VERSION ) {
			flush_rewrite_rules();
			update_option( 'up2a_formations_rewrite_version', UP2A_FORMATIONS_VERSION );
		}
	},
	20
);

/**
 * Tailles d'image dédiées, pour garder des cadrages cohérents avec le
 * design (cartes/modale/bannière) sans dépendre des tailles WordPress
 * génériques ('large'/'medium', non recadrées par défaut).
 */
function up2a_formations_register_image_sizes(): void {
	add_image_size( 'up2a_formations_desktop', 900, 675, true );
	add_image_size( 'up2a_formations_mobile', 600, 450, true );
}
add_action( 'after_setup_theme', 'up2a_formations_register_image_sizes' );

/**
 * Champ "Nom complet" sur les termes de la taxonomie Faculté (ex. "SJPA"
 * → "Sciences Juridiques, Politiques et de l'Administration (SJPA)") —
 * WordPress ne propose pas ce champ nativement sur les taxonomies.
 */
add_action(
	'up2a_faculte_add_form_fields',
	function (): void {
		?>
		<div class="form-field">
			<label for="up2a-faculte-nom-complet"><?php esc_html_e( 'Nom complet', 'up2a-formations' ); ?></label>
			<input type="text" name="up2a_faculte_nom_complet" id="up2a-faculte-nom-complet" value="">
			<p><?php esc_html_e( 'Ex. "Sciences Économiques et de Gestion (SEG)" pour le terme "SEG".', 'up2a-formations' ); ?></p>
		</div>
		<?php
	}
);

add_action(
	'up2a_faculte_edit_form_fields',
	function ( \WP_Term $term ): void {
		$valeur = get_term_meta( $term->term_id, 'nom_complet', true );
		?>
		<tr class="form-field">
			<th scope="row"><label for="up2a-faculte-nom-complet"><?php esc_html_e( 'Nom complet', 'up2a-formations' ); ?></label></th>
			<td><input type="text" name="up2a_faculte_nom_complet" id="up2a-faculte-nom-complet" value="<?php echo esc_attr( $valeur ); ?>"></td>
		</tr>
		<?php
	}
);

function up2a_formations_save_faculte_meta( int $term_id ): void {
	if ( isset( $_POST['up2a_faculte_nom_complet'] ) ) {
		update_term_meta( $term_id, 'nom_complet', sanitize_text_field( wp_unslash( $_POST['up2a_faculte_nom_complet'] ) ) );
	}
}
add_action( 'created_up2a_faculte', 'up2a_formations_save_faculte_meta' );
add_action( 'edited_up2a_faculte', 'up2a_formations_save_faculte_meta' );

/**
 * Métabox "Détails de la formation" : icône (parmi celles déjà dessinées
 * dans up2a_core_content_icon()/up2a_core_icon() — un texte libre créerait
 * un risque de faute de frappe silencieuse, une liste fermée l'évite) et
 * débouchés (une ligne par débouché, pas de champ répétable pour rester
 * sans dépendance à un plugin de champs personnalisés).
 */
function up2a_formations_register_meta_box(): void {
	add_meta_box(
		'up2a_formations_details',
		__( 'Détails de la formation', 'up2a-formations' ),
		'up2a_formations_render_meta_box',
		'up2a_formation',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'up2a_formations_register_meta_box' );

/**
 * Icônes disponibles pour ce champ : sous-ensemble de
 * up2a_core_content_icon() qui a un sens pour une formation (métier,
 * secteur) — pas toutes les icônes du site (ex. "mail", "clock" ne sont
 * pas pertinentes ici).
 */
function up2a_formations_icon_choices(): array {
	return array(
		'scale'     => __( 'Balance (droit)', 'up2a-formations' ),
		'users'     => __( 'Personnes', 'up2a-formations' ),
		'truck'     => __( 'Camion (logistique)', 'up2a-formations' ),
		'megaphone' => __( 'Mégaphone (communication)', 'up2a-formations' ),
		'briefcase' => __( 'Mallette', 'up2a-formations' ),
		'cpu'       => __( 'Processeur (informatique)', 'up2a-formations' ),
		'globe'     => __( 'Globe', 'up2a-formations' ),
		'book'      => __( 'Livre', 'up2a-formations' ),
		'star'      => __( 'Étoile', 'up2a-formations' ),
		'shield'    => __( 'Bouclier', 'up2a-formations' ),
	);
}

function up2a_formations_render_meta_box( \WP_Post $post ): void {
	wp_nonce_field( 'up2a_formations_save_meta', 'up2a_formations_meta_nonce' );
	$icone     = get_post_meta( $post->ID, 'up2a_icone', true );
	$debouches = get_post_meta( $post->ID, 'up2a_debouches', true );
	?>
	<p>
		<label for="up2a-icone"><strong><?php esc_html_e( 'Icône', 'up2a-formations' ); ?></strong></label><br>
		<select name="up2a_icone" id="up2a-icone">
			<?php foreach ( up2a_formations_icon_choices() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $icone, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="up2a-debouches"><strong><?php esc_html_e( 'Débouchés (un par ligne)', 'up2a-formations' ); ?></strong></label><br>
		<textarea name="up2a_debouches" id="up2a-debouches" rows="4" class="large-text" placeholder="<?php esc_attr_e( "Ex.\nAdministration publique\nFonction publique", 'up2a-formations' ); ?>"><?php echo esc_textarea( $debouches ); ?></textarea>
	</p>
	<p class="description">
		<?php esc_html_e( 'Le champ "Extrait" ci-dessous sert de description courte (modale + page de détail). Le contenu principal sert de "Programme" sur la page de détail — laissez-le vide pour afficher "bientôt disponible".', 'up2a-formations' ); ?>
	</p>
	<?php
}

function up2a_formations_save_meta( int $post_id ): void {
	if ( ! isset( $_POST['up2a_formations_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['up2a_formations_meta_nonce'] ), 'up2a_formations_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['up2a_icone'] ) ) {
		update_post_meta( $post_id, 'up2a_icone', sanitize_key( wp_unslash( $_POST['up2a_icone'] ) ) );
	}
	if ( isset( $_POST['up2a_debouches'] ) ) {
		update_post_meta( $post_id, 'up2a_debouches', sanitize_textarea_field( wp_unslash( $_POST['up2a_debouches'] ) ) );
	}
}
add_action( 'save_post_up2a_formation', 'up2a_formations_save_meta' );

/**
 * Copie un fichier image déjà présent sur le serveur (bundle du plugin)
 * dans la médiathèque WordPress, pour pouvoir le définir comme image mise
 * en avant d'un post créé par le code (voir up2a_formations_seed_defaults()
 * ci-dessous). Utilisé uniquement pour l'amorçage : reprend les mêmes
 * photos que l'ancienne version "tableau statique" du plugin, pour que la
 * mise à jour ne fasse pas disparaître les images déjà en ligne sur le
 * site (voir docs/06-storyboard.md "CPT Formation depuis le dashboard").
 * Retourne 0 si le fichier n'existe pas ou en cas d'échec — l'appelant
 * doit s'en accommoder sans casser le reste de l'amorçage.
 */
function up2a_formations_sideload_image( string $chemin_absolu, string $alt = '' ): int {
	if ( ! file_exists( $chemin_absolu ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$contenu = file_get_contents( $chemin_absolu ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( false === $contenu ) {
		return 0;
	}

	$upload = wp_upload_bits( wp_basename( $chemin_absolu ), null, $contenu );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => wp_check_filetype( $upload['file'] )['type'],
			'post_title'     => sanitize_file_name( wp_basename( $chemin_absolu ) ),
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	if ( '' !== $alt ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	}

	return $attachment_id;
}

/**
 * Amorçage : crée les 2 facultés et les 4 licences par défaut si le CPT
 * est vide (première activation, ou mise à jour depuis une version qui
 * n'avait pas encore ce CPT), en reprenant les mêmes photos que l'ancienne
 * version du plugin — pour que la mise à jour ne fasse pas régresser
 * visuellement un site déjà en ligne. Ne s'exécute qu'une fois (option
 * verrou) — même filet de sécurité que le flush des règles de réécriture
 * plus haut, pour tourner même si WordPress ne déclenche pas le hook
 * d'activation (remplacement de fichiers via FTP/zip sans désactivation
 * explicite).
 *
 * Pour une formation ajoutée plus tard depuis wp-admin (pas par cet
 * amorçage), l'administrateur définit sa photo lui-même depuis
 * Formations → [formation] → "Photo de la formation" — tant qu'aucune
 * photo n'est définie, la carte s'affiche sans image plutôt qu'avec une
 * image cassée (voir up2a_formations_formations() dans formations.php).
 */
function up2a_formations_seed_defaults(): void {
	$defauts = array(
		array(
			'nom'          => __( 'Licence en Droit Public', 'up2a-formations' ),
			'slug'         => 'licence-droit-public',
			'icone'        => 'scale',
			'faculte'      => 'SJPA',
			'faculte_full' => __( "Sciences Juridiques, Politiques et de l'Administration (SJPA)", 'up2a-formations' ),
			'intro'        => __( 'Droit constitutionnel, administratif et institutions publiques.', 'up2a-formations' ),
			'debouches'    => array(
				__( 'Administration publique', 'up2a-formations' ),
				__( 'Fonction publique', 'up2a-formations' ),
				__( 'Collectivités territoriales', 'up2a-formations' ),
			),
			'image'        => 'hero-slide-1.webp',
		),
		array(
			'nom'          => __( 'Licence en Droit Privé', 'up2a-formations' ),
			'slug'         => 'licence-droit-prive',
			'icone'        => 'users',
			'faculte'      => 'SJPA',
			'faculte_full' => __( "Sciences Juridiques, Politiques et de l'Administration (SJPA)", 'up2a-formations' ),
			'intro'        => __( 'Droit civil, des affaires et des contrats.', 'up2a-formations' ),
			'debouches'    => array(
				__( "Droit d'entreprise", 'up2a-formations' ),
				__( 'Conseil juridique', 'up2a-formations' ),
				__( 'Professions judiciaires', 'up2a-formations' ),
			),
			'image'        => 'gallery-etudiants-batiment.webp',
		),
		array(
			'nom'          => __( 'Licence en Logistique Internationale', 'up2a-formations' ),
			'slug'         => 'licence-logistique-internationale',
			'icone'        => 'truck',
			'faculte'      => 'SEG',
			'faculte_full' => __( 'Sciences Économiques et de Gestion (SEG)', 'up2a-formations' ),
			'intro'        => __( "Transport, chaîne d'approvisionnement et commerce international.", 'up2a-formations' ),
			'debouches'    => array(
				__( 'Logistique & transport', 'up2a-formations' ),
				__( "Chaîne d'approvisionnement", 'up2a-formations' ),
				__( 'Commerce international', 'up2a-formations' ),
			),
			'image'        => 'hero-slide-2.webp',
		),
		array(
			'nom'          => __( 'Licence en Marketing Communication', 'up2a-formations' ),
			'slug'         => 'licence-marketing-communication',
			'icone'        => 'megaphone',
			'faculte'      => 'SEG',
			'faculte_full' => __( 'Sciences Économiques et de Gestion (SEG)', 'up2a-formations' ),
			'intro'        => __( 'Stratégie de marque, communication et marketing digital.', 'up2a-formations' ),
			'debouches'    => array(
				__( 'Marketing', 'up2a-formations' ),
				__( "Communication d'entreprise", 'up2a-formations' ),
				__( 'Stratégie de marque', 'up2a-formations' ),
			),
			'image'        => 'gallery-campus-facade.webp',
		),
	);

	$facultes_full = array();
	foreach ( $defauts as $f ) {
		$facultes_full[ $f['faculte'] ] = $f['faculte_full'];
	}
	foreach ( $facultes_full as $code => $nom_complet ) {
		$terme = term_exists( $code, 'up2a_faculte' );
		if ( ! $terme ) {
			$terme = wp_insert_term( $code, 'up2a_faculte' );
		}
		$term_id = is_wp_error( $terme ) ? 0 : ( is_array( $terme ) ? $terme['term_id'] : $terme );
		if ( $term_id ) {
			update_term_meta( $term_id, 'nom_complet', $nom_complet );
		}
	}

	foreach ( $defauts as $ordre => $f ) {
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'up2a_formation',
				'post_status'  => 'publish',
				'post_title'   => $f['nom'],
				'post_name'    => $f['slug'],
				'post_excerpt' => $f['intro'],
				'menu_order'   => $ordre,
			)
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}
		update_post_meta( $post_id, 'up2a_icone', $f['icone'] );
		update_post_meta( $post_id, 'up2a_debouches', implode( "\n", $f['debouches'] ) );
		wp_set_object_terms( $post_id, $f['faculte'], 'up2a_faculte' );

		if ( defined( 'UP2A_CORE_PATH' ) ) {
			$attachment_id = up2a_formations_sideload_image( UP2A_CORE_PATH . 'assets/img/' . $f['image'], $f['nom'] );
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}
		}
	}
}

/**
 * Déclenche l'amorçage une seule fois. Vérifié sur `init` (après
 * l'enregistrement du CPT) plutôt que seulement à l'activation, pour le
 * cas fréquent où les fichiers du plugin sont remplacés sans passer par
 * une désactivation/réactivation explicite dans wp-admin (même motif que
 * le filet de sécurité de flush des règles de réécriture, voir
 * formations.php).
 */
add_action(
	'init',
	function (): void {
		if ( get_option( 'up2a_formations_seeded' ) ) {
			return;
		}
		$existe_deja = get_posts(
			array(
				'post_type'      => 'up2a_formation',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( empty( $existe_deja ) ) {
			up2a_formations_seed_defaults();
		}
		update_option( 'up2a_formations_seeded', 1 );
	},
	15
);
