<?php
/**
 * Page de réglages "Galerie" (menu wp-admin) — remplace l'écran par post
 * (CPT) d'une version précédente : toutes les photos se gèrent sur **un
 * seul écran**, avec un formulaire répéteur (ajouter/supprimer/réordonner
 * une photo, choisir l'image depuis la médiathèque). Stocké dans une
 * unique option `up2a_galerie_option` (tableau de photos), pas un CPT —
 * voir up2a-formations/inc/settings.php pour le même choix côté
 * Formations et docs/06-storyboard.md "Réglages Formations/Galerie".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// --------------------------------------------------------------------
// Tailles d'image dédiées (mêmes cadrages qu'avant, voir galerie.php)
// --------------------------------------------------------------------

function up2a_galerie_register_image_sizes(): void {
	add_image_size( 'up2a_galerie_desktop', 900, 675, true );
	add_image_size( 'up2a_galerie_mobile', 600, 450, true );
}
add_action( 'after_setup_theme', 'up2a_galerie_register_image_sizes' );

// --------------------------------------------------------------------
// Menu + lien "Réglages" depuis la liste des extensions
// --------------------------------------------------------------------

add_action(
	'admin_menu',
	function (): void {
		add_menu_page(
			__( 'Galerie', 'up2a-galerie' ),
			__( 'Galerie', 'up2a-galerie' ),
			'manage_options',
			'up2a-galerie',
			'up2a_galerie_render_settings_page',
			'dashicons-format-gallery',
			25
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( UP2A_GALERIE_FILE ),
	function ( array $links ): array {
		$url = admin_url( 'admin.php?page=up2a-galerie' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Réglages', 'up2a-galerie' ) . '</a>' );
		return $links;
	}
);

// --------------------------------------------------------------------
// Lecture / écriture de la liste
// --------------------------------------------------------------------

/**
 * Copie un fichier image déjà présent sur le serveur (bundle d'up2a-core)
 * dans la médiathèque WordPress — même helper que côté up2a-formations
 * (voir up2a-formations/inc/settings.php pour le détail du
 * raisonnement, dupliqué ici plutôt que partagé entre plugins pour
 * rester indépendant d'up2a-formations).
 */
function up2a_galerie_sideload_image( string $chemin_absolu, string $alt = '' ): int {
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
 * Construit et enregistre la liste par défaut (les 8 photos connues),
 * avec leurs photos d'origine importées dans la médiathèque — appelée
 * une seule fois, quand l'option n'existe pas encore (première
 * activation).
 */
function up2a_galerie_seed_default_option(): array {
	$defauts = array(
		array( 'legende' => __( 'Le campus', 'up2a-galerie' ), 'fichier' => 'hero-slide-1.webp' ),
		array( 'legende' => __( 'Vie étudiante', 'up2a-galerie' ), 'fichier' => 'gallery-etudiants-batiment.webp' ),
		array( 'legende' => __( 'Le bâtiment', 'up2a-galerie' ), 'fichier' => 'hero-slide-2.webp' ),
		array( 'legende' => __( 'La façade principale', 'up2a-galerie' ), 'fichier' => 'gallery-campus-facade.webp' ),
		array( 'legende' => __( 'Nos étudiants', 'up2a-galerie' ), 'fichier' => 'pourquoi-photo.webp' ),
		array( 'legende' => __( 'Le campus, vue latérale', 'up2a-galerie' ), 'fichier' => 'gallery-campus-angle.webp' ),
		array( 'legende' => __( "L'architecture", 'up2a-galerie' ), 'fichier' => 'gallery-campus-perspective.webp' ),
		array( 'legende' => __( 'Nos étudiants sur le campus', 'up2a-galerie' ), 'fichier' => 'gallery-vie-etudiante-groupe.webp' ),
	);

	$liste = array();
	foreach ( $defauts as $p ) {
		$image_id = 0;
		if ( defined( 'UP2A_CORE_PATH' ) ) {
			$image_id = up2a_galerie_sideload_image( UP2A_CORE_PATH . 'assets/img/' . $p['fichier'], $p['legende'] );
		}
		$liste[] = array(
			'legende'  => $p['legende'],
			'image_id' => $image_id,
		);
	}

	add_option( 'up2a_galerie_option', $liste );
	return $liste;
}

/**
 * Liste brute (telle que stockée/éditée), sans mise en forme pour
 * l'affichage front — utilisée par l'écran de réglages et par
 * up2a_galerie_images() (voir galerie.php).
 */
function up2a_galerie_get_raw_list(): array {
	$stored = get_option( 'up2a_galerie_option', false );
	if ( false === $stored ) {
		return up2a_galerie_seed_default_option();
	}
	return is_array( $stored ) ? $stored : array();
}

/**
 * Sauvegarde la liste soumise par le formulaire de réglages. Les lignes
 * sans photo ni légende (ligne ajoutée puis non remplie) sont ignorées
 * plutôt qu'enregistrées vides.
 */
function up2a_galerie_save_settings( array $post ): void {
	$rows_in = isset( $post['photos'] ) && is_array( $post['photos'] ) ? $post['photos'] : array();

	$clean = array();
	foreach ( $rows_in as $row ) {
		$legende  = sanitize_text_field( wp_unslash( $row['legende'] ?? '' ) );
		$image_id = absint( $row['image_id'] ?? 0 );
		if ( '' === $legende && ! $image_id ) {
			continue;
		}
		$clean[] = array(
			'legende'  => $legende,
			'image_id' => $image_id,
		);
	}

	update_option( 'up2a_galerie_option', $clean );
}

// --------------------------------------------------------------------
// Écran de réglages
// --------------------------------------------------------------------

function up2a_galerie_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['up2a_galerie_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['up2a_galerie_nonce'] ) ), 'up2a_galerie_save' ) ) {
		up2a_galerie_save_settings( wp_unslash( $_POST ) );
		$message = __( 'Galerie enregistrée.', 'up2a-galerie' );
	}

	$liste = up2a_galerie_get_raw_list();

	wp_enqueue_media();
	wp_enqueue_style( 'up2a-galerie-admin', UP2A_GALERIE_URL . 'assets/css/admin-settings.css', array(), UP2A_GALERIE_VERSION );
	wp_enqueue_script( 'up2a-galerie-admin', UP2A_GALERIE_URL . 'assets/js/admin-settings.js', array( 'media-editor' ), UP2A_GALERIE_VERSION, true );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Galerie', 'up2a-galerie' ); ?></h1>
		<p><?php esc_html_e( "Gérez ici les photos affichées par le shortcode [up2a_galerie] sur la home. La première photo de la liste devient la vignette \"en avant\" avec le défilement automatique. Chaque modification n'est prise en compte qu'après avoir cliqué sur \"Enregistrer les modifications\" en bas de page.", 'up2a-galerie' ); ?></p>
		<p class="description"><?php esc_html_e( 'Le texte alternatif (accessibilité) de chaque photo se règle depuis Médias → modifier l\'image → "Texte alternatif".', 'up2a-galerie' ); ?></p>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'up2a_galerie_save', 'up2a_galerie_nonce' ); ?>

			<div id="up2a-galerie-rows">
				<?php foreach ( $liste as $i => $row ) : ?>
					<?php up2a_galerie_render_row( (string) $i, $row ); ?>
				<?php endforeach; ?>
			</div>

			<template id="up2a-galerie-row-template">
				<?php up2a_galerie_render_row( '__INDEX__', array() ); ?>
			</template>

			<p>
				<button type="button" class="button" id="up2a-galerie-add"><?php esc_html_e( '+ Ajouter une photo', 'up2a-galerie' ); ?></button>
			</p>

			<?php submit_button( __( 'Enregistrer les modifications', 'up2a-galerie' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Une ligne du répéteur (une photo). `$index` est soit un entier (ligne
 * existante) soit le jeton `__INDEX__` (ligne modèle, remplacé côté JS
 * par un identifiant unique au moment de l'ajout — voir
 * assets/js/admin-settings.js).
 */
function up2a_galerie_render_row( string $index, array $row ): void {
	$legende  = $row['legende'] ?? '';
	$image_id = (int) ( $row['image_id'] ?? 0 );
	$name     = 'photos[' . $index . ']';
	?>
	<div class="up2a-galerie-row">
		<p>
			<label>
				<strong><?php esc_html_e( 'Légende', 'up2a-galerie' ); ?></strong><br>
				<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[legende]" value="<?php echo esc_attr( $legende ); ?>">
			</label>
		</p>
		<p class="up2a-galerie-row__image">
			<span class="up2a-galerie-row__thumb">
				<?php if ( $image_id ) : ?>
					<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
				<?php endif; ?>
			</span>
			<input type="hidden" class="up2a-galerie-image-id" name="<?php echo esc_attr( $name ); ?>[image_id]" value="<?php echo esc_attr( $image_id ); ?>">
			<button type="button" class="button up2a-galerie-pick-image"><?php esc_html_e( 'Choisir une photo', 'up2a-galerie' ); ?></button>
			<button type="button" class="button up2a-galerie-remove-image" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Retirer la photo', 'up2a-galerie' ); ?></button>
		</p>
		<p class="up2a-galerie-row__actions">
			<button type="button" class="button up2a-galerie-move-up">↑ <?php esc_html_e( 'Monter', 'up2a-galerie' ); ?></button>
			<button type="button" class="button up2a-galerie-move-down">↓ <?php esc_html_e( 'Descendre', 'up2a-galerie' ); ?></button>
			<button type="button" class="button-link-delete up2a-galerie-remove-row"><?php esc_html_e( 'Supprimer cette photo', 'up2a-galerie' ); ?></button>
		</p>
	</div>
	<?php
}
