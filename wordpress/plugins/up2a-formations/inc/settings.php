<?php
/**
 * Page de réglages "Formations" (menu wp-admin) — remplace l'écran par
 * post (CPT) d'une version précédente : tout le contenu (les 2 facultés,
 * les 4 licences par défaut) se gère sur **un seul écran**, avec un
 * formulaire répéteur (ajouter/supprimer/réordonner une formation,
 * choisir sa photo depuis la médiathèque). Stocké dans une unique option
 * `up2a_formations_option` (tableau de formations), pas un CPT.
 *
 * Pourquoi ce choix plutôt qu'un CPT : demande explicite de gérer chaque
 * module "à travers réglage de l'extension depuis le tableau de bord",
 * càd un écran de type Réglages plutôt qu'une liste d'articles à ouvrir
 * un par un (voir docs/06-storyboard.md "Réglages Formations/Galerie").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// --------------------------------------------------------------------
// Tailles d'image dédiées (mêmes cadrages qu'avant, voir formations.php)
// --------------------------------------------------------------------

function up2a_formations_register_image_sizes(): void {
	add_image_size( 'up2a_formations_desktop', 900, 675, true );
	add_image_size( 'up2a_formations_mobile', 600, 450, true );
}
add_action( 'after_setup_theme', 'up2a_formations_register_image_sizes' );

// --------------------------------------------------------------------
// Menu + lien "Réglages" depuis la liste des extensions
// --------------------------------------------------------------------

add_action(
	'admin_menu',
	function (): void {
		add_menu_page(
			__( 'Formations', 'up2a-formations' ),
			__( 'Formations', 'up2a-formations' ),
			'manage_options',
			'up2a-formations',
			'up2a_formations_render_settings_page',
			'dashicons-welcome-learn-more',
			24
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( UP2A_FORMATIONS_FILE ),
	function ( array $links ): array {
		$url = admin_url( 'admin.php?page=up2a-formations' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Réglages', 'up2a-formations' ) . '</a>' );
		return $links;
	}
);

// --------------------------------------------------------------------
// Icônes disponibles pour le champ "Icône" (sous-ensemble de
// up2a_core_content_icon() qui a un sens pour une formation).
// --------------------------------------------------------------------

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

// --------------------------------------------------------------------
// Lecture / écriture de la liste
// --------------------------------------------------------------------

/**
 * Copie un fichier image déjà présent sur le serveur (bundle du plugin)
 * dans la médiathèque WordPress — utilisé uniquement pour préremplir les
 * 4 formations par défaut avec leurs photos d'origine la première fois
 * (voir up2a_formations_seed_default_option() plus bas), pour qu'un site
 * déjà en ligne ne perde pas ses images en installant cette mise à jour.
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
 * Construit et enregistre la liste par défaut (les 4 licences connues),
 * avec leurs photos d'origine importées dans la médiathèque — appelée
 * une seule fois, quand l'option n'existe pas encore (première
 * activation). Une formation ajoutée ensuite depuis l'écran de réglages
 * n'a pas cette étape : son administrateur choisit la photo lui-même.
 */
function up2a_formations_seed_default_option(): array {
	$defauts = array(
		array(
			'nom'          => __( 'Licence en Droit Public', 'up2a-formations' ),
			'slug'         => 'licence-droit-public',
			'icone'        => 'scale',
			'faculte'      => 'SJPA',
			'faculte_full' => __( "Sciences Juridiques, Politiques et de l'Administration (SJPA)", 'up2a-formations' ),
			'intro'        => __( 'Droit constitutionnel, administratif et institutions publiques.', 'up2a-formations' ),
			'programme'    => '',
			'debouches'    => implode(
				"\n",
				array(
					__( 'Administration publique', 'up2a-formations' ),
					__( 'Fonction publique', 'up2a-formations' ),
					__( 'Collectivités territoriales', 'up2a-formations' ),
				)
			),
			'fichier'      => 'hero-slide-1.webp',
		),
		array(
			'nom'          => __( 'Licence en Droit Privé', 'up2a-formations' ),
			'slug'         => 'licence-droit-prive',
			'icone'        => 'users',
			'faculte'      => 'SJPA',
			'faculte_full' => __( "Sciences Juridiques, Politiques et de l'Administration (SJPA)", 'up2a-formations' ),
			'intro'        => __( 'Droit civil, des affaires et des contrats.', 'up2a-formations' ),
			'programme'    => '',
			'debouches'    => implode(
				"\n",
				array(
					__( "Droit d'entreprise", 'up2a-formations' ),
					__( 'Conseil juridique', 'up2a-formations' ),
					__( 'Professions judiciaires', 'up2a-formations' ),
				)
			),
			'fichier'      => 'gallery-etudiants-batiment.webp',
		),
		array(
			'nom'          => __( 'Licence en Logistique Internationale', 'up2a-formations' ),
			'slug'         => 'licence-logistique-internationale',
			'icone'        => 'truck',
			'faculte'      => 'SEG',
			'faculte_full' => __( 'Sciences Économiques et de Gestion (SEG)', 'up2a-formations' ),
			'intro'        => __( "Transport, chaîne d'approvisionnement et commerce international.", 'up2a-formations' ),
			'programme'    => '',
			'debouches'    => implode(
				"\n",
				array(
					__( 'Logistique & transport', 'up2a-formations' ),
					__( "Chaîne d'approvisionnement", 'up2a-formations' ),
					__( 'Commerce international', 'up2a-formations' ),
				)
			),
			'fichier'      => 'hero-slide-2.webp',
		),
		array(
			'nom'          => __( 'Licence en Marketing Communication', 'up2a-formations' ),
			'slug'         => 'licence-marketing-communication',
			'icone'        => 'megaphone',
			'faculte'      => 'SEG',
			'faculte_full' => __( 'Sciences Économiques et de Gestion (SEG)', 'up2a-formations' ),
			'intro'        => __( 'Stratégie de marque, communication et marketing digital.', 'up2a-formations' ),
			'programme'    => '',
			'debouches'    => implode(
				"\n",
				array(
					__( 'Marketing', 'up2a-formations' ),
					__( "Communication d'entreprise", 'up2a-formations' ),
					__( 'Stratégie de marque', 'up2a-formations' ),
				)
			),
			'fichier'      => 'gallery-campus-facade.webp',
		),
	);

	$liste = array();
	foreach ( $defauts as $f ) {
		$image_id = 0;
		if ( defined( 'UP2A_CORE_PATH' ) ) {
			$image_id = up2a_formations_sideload_image( UP2A_CORE_PATH . 'assets/img/' . $f['fichier'], $f['nom'] );
		}
		unset( $f['fichier'] );
		$f['image_id'] = $image_id;
		$liste[]       = $f;
	}

	add_option( 'up2a_formations_option', $liste );
	return $liste;
}

/**
 * Liste brute (telle que stockée/éditée), sans mise en forme pour
 * l'affichage front — utilisée par l'écran de réglages et par
 * up2a_formations_formations() ci-dessous.
 */
function up2a_formations_get_raw_list(): array {
	$stored = get_option( 'up2a_formations_option', false );
	if ( false === $stored ) {
		return up2a_formations_seed_default_option();
	}
	return is_array( $stored ) ? $stored : array();
}

/**
 * Sauvegarde la liste soumise par le formulaire de réglages. Les lignes
 * sans nom (ligne ajoutée puis non remplie) sont ignorées plutôt
 * qu'enregistrées vides. Les slugs en collision sont désambiguïsés
 * (`-2`, `-3`...) pour que chaque formation garde une URL de détail qui
 * lui est propre.
 */
function up2a_formations_save_settings( array $post ): void {
	$rows_in = isset( $post['formations'] ) && is_array( $post['formations'] ) ? $post['formations'] : array();

	$clean = array();
	foreach ( $rows_in as $row ) {
		$nom = sanitize_text_field( wp_unslash( $row['nom'] ?? '' ) );
		if ( '' === $nom ) {
			continue;
		}
		$slug_saisi = sanitize_title( wp_unslash( $row['slug'] ?? '' ) );
		$clean[]    = array(
			'nom'          => $nom,
			'slug'         => '' !== $slug_saisi ? $slug_saisi : sanitize_title( $nom ),
			'faculte'      => sanitize_text_field( wp_unslash( $row['faculte'] ?? '' ) ),
			'faculte_full' => sanitize_text_field( wp_unslash( $row['faculte_full'] ?? '' ) ),
			'icone'        => sanitize_key( wp_unslash( $row['icone'] ?? 'book' ) ),
			'intro'        => sanitize_textarea_field( wp_unslash( $row['intro'] ?? '' ) ),
			'programme'    => wp_kses_post( wp_unslash( $row['programme'] ?? '' ) ),
			'debouches'    => sanitize_textarea_field( wp_unslash( $row['debouches'] ?? '' ) ),
			'image_id'     => absint( $row['image_id'] ?? 0 ),
		);
	}

	$slugs_vus = array();
	foreach ( $clean as &$f ) {
		$base    = $f['slug'];
		$suffixe = 1;
		while ( in_array( $f['slug'], $slugs_vus, true ) ) {
			++$suffixe;
			$f['slug'] = $base . '-' . $suffixe;
		}
		$slugs_vus[] = $f['slug'];
	}
	unset( $f );

	update_option( 'up2a_formations_option', $clean );
}

// --------------------------------------------------------------------
// Écran de réglages
// --------------------------------------------------------------------

function up2a_formations_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['up2a_formations_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['up2a_formations_nonce'] ) ), 'up2a_formations_save' ) ) {
		up2a_formations_save_settings( wp_unslash( $_POST ) );
		$message = __( 'Formations enregistrées.', 'up2a-formations' );
	}

	$liste = up2a_formations_get_raw_list();

	wp_enqueue_media();
	wp_enqueue_style( 'up2a-formations-admin', UP2A_FORMATIONS_URL . 'assets/css/admin-settings.css', array(), UP2A_FORMATIONS_VERSION );
	wp_enqueue_script( 'up2a-formations-admin', UP2A_FORMATIONS_URL . 'assets/js/admin-settings.js', array( 'media-editor' ), UP2A_FORMATIONS_VERSION, true );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Formations', 'up2a-formations' ); ?></h1>
		<p><?php esc_html_e( "Gérez ici les formations affichées par le shortcode [up2a_formations] sur la home. Chaque modification n'est prise en compte qu'après avoir cliqué sur \"Enregistrer les modifications\" en bas de page.", 'up2a-formations' ); ?></p>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'up2a_formations_save', 'up2a_formations_nonce' ); ?>

			<div id="up2a-formations-rows">
				<?php foreach ( $liste as $i => $row ) : ?>
					<?php up2a_formations_render_row( (string) $i, $row ); ?>
				<?php endforeach; ?>
			</div>

			<template id="up2a-formations-row-template">
				<?php up2a_formations_render_row( '__INDEX__', array() ); ?>
			</template>

			<p>
				<button type="button" class="button" id="up2a-formations-add"><?php esc_html_e( '+ Ajouter une formation', 'up2a-formations' ); ?></button>
			</p>

			<?php submit_button( __( 'Enregistrer les modifications', 'up2a-formations' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Une ligne du répéteur (une formation). `$index` est soit un entier
 * (ligne existante) soit le jeton `__INDEX__` (ligne modèle, remplacé
 * côté JS par un identifiant unique au moment de l'ajout — voir
 * assets/js/admin-settings.js).
 */
function up2a_formations_render_row( string $index, array $row ): void {
	$nom          = $row['nom'] ?? '';
	$slug         = $row['slug'] ?? '';
	$faculte      = $row['faculte'] ?? '';
	$faculte_full = $row['faculte_full'] ?? '';
	$icone        = $row['icone'] ?? 'book';
	$intro        = $row['intro'] ?? '';
	$programme    = $row['programme'] ?? '';
	$debouches    = $row['debouches'] ?? '';
	$image_id     = (int) ( $row['image_id'] ?? 0 );
	$name         = 'formations[' . $index . ']';
	?>
	<div class="up2a-formations-row">
		<p>
			<label>
				<strong><?php esc_html_e( 'Nom de la formation', 'up2a-formations' ); ?></strong><br>
				<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[nom]" value="<?php echo esc_attr( $nom ); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e( 'Slug (URL /formations/...)', 'up2a-formations' ); ?><br>
				<input type="text" name="<?php echo esc_attr( $name ); ?>[slug]" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'généré depuis le nom si laissé vide', 'up2a-formations' ); ?>">
			</label>
		</p>
		<p class="up2a-formations-row__cols">
			<label>
				<?php esc_html_e( 'Faculté (code court, ex. SJPA)', 'up2a-formations' ); ?><br>
				<input type="text" name="<?php echo esc_attr( $name ); ?>[faculte]" value="<?php echo esc_attr( $faculte ); ?>">
			</label>
			<label>
				<?php esc_html_e( 'Faculté (nom complet)', 'up2a-formations' ); ?><br>
				<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[faculte_full]" value="<?php echo esc_attr( $faculte_full ); ?>">
			</label>
			<label>
				<?php esc_html_e( 'Icône', 'up2a-formations' ); ?><br>
				<select name="<?php echo esc_attr( $name ); ?>[icone]">
					<?php foreach ( up2a_formations_icon_choices() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $icone, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e( 'Description courte (modale + page de détail)', 'up2a-formations' ); ?><br>
				<textarea class="large-text" rows="2" name="<?php echo esc_attr( $name ); ?>[intro]"><?php echo esc_textarea( $intro ); ?></textarea>
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e( 'Débouchés (un par ligne)', 'up2a-formations' ); ?><br>
				<textarea class="large-text" rows="3" name="<?php echo esc_attr( $name ); ?>[debouches]" placeholder="<?php esc_attr_e( "Ex.\nAdministration publique\nFonction publique", 'up2a-formations' ); ?>"><?php echo esc_textarea( $debouches ); ?></textarea>
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e( 'Programme (page de détail — laisser vide pour afficher "bientôt disponible")', 'up2a-formations' ); ?><br>
				<textarea class="large-text" rows="4" name="<?php echo esc_attr( $name ); ?>[programme]"><?php echo esc_textarea( $programme ); ?></textarea>
			</label>
		</p>
		<p class="up2a-formations-row__image">
			<span class="up2a-formations-row__thumb">
				<?php if ( $image_id ) : ?>
					<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
				<?php endif; ?>
			</span>
			<input type="hidden" class="up2a-formations-image-id" name="<?php echo esc_attr( $name ); ?>[image_id]" value="<?php echo esc_attr( $image_id ); ?>">
			<button type="button" class="button up2a-formations-pick-image"><?php esc_html_e( 'Choisir une photo', 'up2a-formations' ); ?></button>
			<button type="button" class="button up2a-formations-remove-image" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Retirer la photo', 'up2a-formations' ); ?></button>
		</p>
		<p class="up2a-formations-row__actions">
			<button type="button" class="button up2a-formations-move-up">↑ <?php esc_html_e( 'Monter', 'up2a-formations' ); ?></button>
			<button type="button" class="button up2a-formations-move-down">↓ <?php esc_html_e( 'Descendre', 'up2a-formations' ); ?></button>
			<button type="button" class="button-link-delete up2a-formations-remove-row"><?php esc_html_e( 'Supprimer cette formation', 'up2a-formations' ); ?></button>
		</p>
	</div>
	<?php
}
