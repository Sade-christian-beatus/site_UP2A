<?php
/**
 * Page de réglages "Slider" (menu wp-admin) — un seul écran : diapositives
 * (répéteur d'images), titre/sous-titre, libellés des deux boutons, date
 * de rentrée. Stocké dans une unique option `up2a_slider_option`. Même
 * logique que les écrans Formations/Galerie/En-tête/Pied de page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function up2a_slider_register_image_sizes(): void {
	add_image_size( 'up2a_slider_desktop', 1920, 1080, true );
	add_image_size( 'up2a_slider_mobile', 900, 1200, true );
}
add_action( 'after_setup_theme', 'up2a_slider_register_image_sizes' );

add_action(
	'admin_menu',
	function (): void {
		add_menu_page(
			__( 'Slider', 'up2a-slider' ),
			__( 'Slider', 'up2a-slider' ),
			'manage_options',
			'up2a-slider',
			'up2a_slider_render_settings_page',
			'dashicons-images-alt2',
			24
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( UP2A_SLIDER_FILE ),
	function ( array $links ): array {
		$url = admin_url( 'admin.php?page=up2a-slider' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Réglages', 'up2a-slider' ) . '</a>' );
		return $links;
	}
);

function up2a_slider_sideload_image( string $chemin_absolu, string $alt = '' ): int {
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

function up2a_slider_seed_default_option(): array {
	$fichiers = array( 'hero-slide-1.webp', 'hero-slide-2.webp' );
	$slides   = array();
	foreach ( $fichiers as $fichier ) {
		$image_id = defined( 'UP2A_CORE_PATH' ) ? up2a_slider_sideload_image( UP2A_CORE_PATH . 'assets/img/' . $fichier ) : 0;
		if ( $image_id ) {
			$slides[] = array( 'image_id' => $image_id );
		}
	}

	$defaut = array(
		'slides'        => $slides,
		'titre_ligne1'  => "Former aujourd'hui",
		'titre_ligne2'  => 'les élites de demain !',
		'sous_titre'    => 'Une formation de qualité pour construire les compétences, développer les ambitions et préparer les professionnels de demain.',
		'cta1_label'    => 'Découvrir nos formations',
		'cta2_label'    => "S'inscrire maintenant",
		'rentree_date'  => '',
	);

	add_option( 'up2a_slider_option', $defaut );
	return $defaut;
}

function up2a_slider_get_option(): array {
	$stored = get_option( 'up2a_slider_option', false );
	if ( false === $stored || ! is_array( $stored ) ) {
		return up2a_slider_seed_default_option();
	}
	return $stored;
}

function up2a_slider_save_settings( array $post ): void {
	$slides_in = isset( $post['slides'] ) && is_array( $post['slides'] ) ? $post['slides'] : array();
	$slides    = array();
	foreach ( $slides_in as $slide ) {
		$image_id = absint( $slide['image_id'] ?? 0 );
		if ( $image_id ) {
			$slides[] = array( 'image_id' => $image_id );
		}
	}

	$actuel                = up2a_slider_get_option();
	$actuel['slides']      = $slides;
	$actuel['titre_ligne1'] = sanitize_text_field( wp_unslash( $post['titre_ligne1'] ?? '' ) );
	$actuel['titre_ligne2'] = sanitize_text_field( wp_unslash( $post['titre_ligne2'] ?? '' ) );
	$actuel['sous_titre']   = sanitize_textarea_field( wp_unslash( $post['sous_titre'] ?? '' ) );
	$actuel['cta1_label']   = sanitize_text_field( wp_unslash( $post['cta1_label'] ?? '' ) );
	$actuel['cta2_label']   = sanitize_text_field( wp_unslash( $post['cta2_label'] ?? '' ) );
	$actuel['rentree_date'] = sanitize_text_field( wp_unslash( $post['rentree_date'] ?? '' ) );

	update_option( 'up2a_slider_option', $actuel );
}

function up2a_slider_render_slide_row( string $index, array $slide ): void {
	$image_id = (int) ( $slide['image_id'] ?? 0 );
	$name     = 'slides[' . $index . ']';
	?>
	<div class="up2a-slider-row">
		<span class="up2a-slider-row__thumb">
			<?php if ( $image_id ) : ?>
				<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
			<?php endif; ?>
		</span>
		<input type="hidden" class="up2a-slider-image-id" name="<?php echo esc_attr( $name ); ?>[image_id]" value="<?php echo esc_attr( $image_id ); ?>">
		<button type="button" class="button up2a-slider-pick-image"><?php esc_html_e( 'Choisir une image', 'up2a-slider' ); ?></button>
		<button type="button" class="button up2a-slider-remove-image" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Retirer', 'up2a-slider' ); ?></button>
		<button type="button" class="button up2a-slider-move-up">↑</button>
		<button type="button" class="button up2a-slider-move-down">↓</button>
		<button type="button" class="button-link-delete up2a-slider-remove-row"><?php esc_html_e( 'Supprimer', 'up2a-slider' ); ?></button>
	</div>
	<?php
}

function up2a_slider_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['up2a_slider_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['up2a_slider_nonce'] ) ), 'up2a_slider_save' ) ) {
		up2a_slider_save_settings( wp_unslash( $_POST ) );
		$message = __( 'Slider enregistré.', 'up2a-slider' );
	}

	$o = up2a_slider_get_option();

	wp_enqueue_media();
	wp_enqueue_style( 'up2a-slider-admin', UP2A_SLIDER_URL . 'assets/css/admin-settings.css', array(), UP2A_SLIDER_VERSION );
	wp_enqueue_script( 'up2a-slider-admin', UP2A_SLIDER_URL . 'assets/js/admin-settings.js', array( 'media-editor' ), UP2A_SLIDER_VERSION, true );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Slider (Hero)', 'up2a-slider' ); ?></h1>
		<p><?php esc_html_e( "Réglages de la scène d'accueil (diaporama, titre, boutons, compte à rebours de la rentrée), affichée en haut de la home via le shortcode [up2a_slider].", 'up2a-slider' ); ?></p>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<form method="post" class="up2a-slider-form">
			<?php wp_nonce_field( 'up2a_slider_save', 'up2a_slider_nonce' ); ?>

			<p><strong><?php esc_html_e( 'Diapositives', 'up2a-slider' ); ?></strong></p>
			<div id="up2a-slider-rows">
				<?php foreach ( $o['slides'] as $i => $slide ) : ?>
					<?php up2a_slider_render_slide_row( (string) $i, $slide ); ?>
				<?php endforeach; ?>
			</div>
			<template id="up2a-slider-row-template">
				<?php up2a_slider_render_slide_row( '__INDEX__', array() ); ?>
			</template>
			<p><button type="button" class="button" id="up2a-slider-add"><?php esc_html_e( '+ Ajouter une diapositive', 'up2a-slider' ); ?></button></p>

			<p>
				<label for="up2a-slider-titre1"><strong><?php esc_html_e( 'Titre — première ligne', 'up2a-slider' ); ?></strong></label><br>
				<input type="text" id="up2a-slider-titre1" name="titre_ligne1" class="regular-text" value="<?php echo esc_attr( $o['titre_ligne1'] ); ?>">
			</p>
			<p>
				<label for="up2a-slider-titre2"><strong><?php esc_html_e( 'Titre — seconde ligne (mise en avant)', 'up2a-slider' ); ?></strong></label><br>
				<input type="text" id="up2a-slider-titre2" name="titre_ligne2" class="regular-text" value="<?php echo esc_attr( $o['titre_ligne2'] ); ?>">
			</p>
			<p>
				<label for="up2a-slider-sous-titre"><strong><?php esc_html_e( 'Sous-titre', 'up2a-slider' ); ?></strong></label><br>
				<textarea id="up2a-slider-sous-titre" name="sous_titre" rows="2" class="large-text"><?php echo esc_textarea( $o['sous_titre'] ); ?></textarea>
			</p>
			<p>
				<label for="up2a-slider-cta1"><strong><?php esc_html_e( 'Bouton 1 (vers "Nos formations")', 'up2a-slider' ); ?></strong></label><br>
				<input type="text" id="up2a-slider-cta1" name="cta1_label" class="regular-text" value="<?php echo esc_attr( $o['cta1_label'] ); ?>">
			</p>
			<p>
				<label for="up2a-slider-cta2"><strong><?php esc_html_e( 'Bouton 2 (vers la préinscription)', 'up2a-slider' ); ?></strong></label><br>
				<input type="text" id="up2a-slider-cta2" name="cta2_label" class="regular-text" value="<?php echo esc_attr( $o['cta2_label'] ); ?>">
			</p>
			<p>
				<label for="up2a-slider-rentree"><strong><?php esc_html_e( 'Date de rentrée académique', 'up2a-slider' ); ?></strong></label><br>
				<input type="date" id="up2a-slider-rentree" name="rentree_date" value="<?php echo esc_attr( $o['rentree_date'] ); ?>">
				<span class="description"><?php esc_html_e( 'Laisser vide pour le 5 octobre par défaut (année suivante calculée automatiquement une fois la date passée).', 'up2a-slider' ); ?></span>
			</p>

			<?php submit_button( __( 'Enregistrer les modifications', 'up2a-slider' ) ); ?>
		</form>
	</div>
	<?php
}
