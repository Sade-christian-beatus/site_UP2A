<?php
/**
 * Page de réglages "Pied de page" (menu wp-admin) — un seul écran :
 * logo, slogan, liens rapides (répéteur label + URL), texte de
 * copyright. Stocké dans une unique option `up2a_footer_option`. Même
 * logique que les écrans Formations/Galerie/En-tête.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function (): void {
		add_menu_page(
			__( 'Pied de page', 'up2a-footer' ),
			__( 'Pied de page', 'up2a-footer' ),
			'manage_options',
			'up2a-footer',
			'up2a_footer_render_settings_page',
			'dashicons-align-center',
			23
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( UP2A_FOOTER_FILE ),
	function ( array $links ): array {
		$url = admin_url( 'admin.php?page=up2a-footer' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Réglages', 'up2a-footer' ) . '</a>' );
		return $links;
	}
);

function up2a_footer_sideload_image( string $chemin_absolu, string $alt = '' ): int {
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

function up2a_footer_seed_default_option(): array {
	$logo_id = defined( 'UP2A_CORE_PATH' )
		? up2a_footer_sideload_image( UP2A_CORE_PATH . 'assets/img/logo-up2a-footer.webp', __( "UP-2A — Université Privée An-Nahdah d'Afrique", 'up2a-footer' ) )
		: 0;

	$defaut = array(
		'logo_id'   => $logo_id,
		'slogan'    => "Former aujourd'hui les élites de demain.",
		'liens'     => array(
			array( 'label' => __( 'Accueil', 'up2a-footer' ), 'href' => '#up2a-hero' ),
			array( 'label' => __( "Pourquoi l'UP-2A", 'up2a-footer' ), 'href' => '#up2a-pourquoi' ),
			array( 'label' => __( 'Nos valeurs', 'up2a-footer' ), 'href' => '#up2a-valeurs' ),
			array( 'label' => __( 'Formations', 'up2a-footer' ), 'href' => '#up2a-formations' ),
			array( 'label' => __( 'Galerie', 'up2a-footer' ), 'href' => '#up2a-galerie' ),
			array( 'label' => __( 'Admissions', 'up2a-footer' ), 'href' => '#up2a-admissions' ),
			array( 'label' => __( 'Contact', 'up2a-footer' ), 'href' => '#up2a-contact' ),
		),
		'copyright' => "&copy; {annee} Université Privée An-Nahdah d'Afrique (UP-2A) — Autorisation MESRI n°2026-001647",
	);

	add_option( 'up2a_footer_option', $defaut );
	return $defaut;
}

function up2a_footer_get_option(): array {
	$stored = get_option( 'up2a_footer_option', false );
	if ( false === $stored || ! is_array( $stored ) ) {
		return up2a_footer_seed_default_option();
	}
	return $stored;
}

function up2a_footer_save_settings( array $post ): void {
	$liens_in = isset( $post['liens'] ) && is_array( $post['liens'] ) ? $post['liens'] : array();
	$liens    = array();
	foreach ( $liens_in as $lien ) {
		$label = sanitize_text_field( wp_unslash( $lien['label'] ?? '' ) );
		$href  = sanitize_text_field( wp_unslash( $lien['href'] ?? '' ) );
		if ( '' === $label && '' === $href ) {
			continue;
		}
		$liens[] = array( 'label' => $label, 'href' => $href );
	}

	$actuel             = up2a_footer_get_option();
	$actuel['logo_id']  = absint( $post['logo_id'] ?? 0 );
	$actuel['slogan']   = sanitize_text_field( wp_unslash( $post['slogan'] ?? '' ) );
	$actuel['liens']    = $liens;
	$actuel['copyright'] = wp_kses_post( wp_unslash( $post['copyright'] ?? '' ) );

	update_option( 'up2a_footer_option', $actuel );
}

function up2a_footer_render_lien_row( string $index, array $lien ): void {
	$label = $lien['label'] ?? '';
	$href  = $lien['href'] ?? '';
	$name  = 'liens[' . $index . ']';
	?>
	<div class="up2a-footer-lien-row">
		<input type="text" name="<?php echo esc_attr( $name ); ?>[label]" placeholder="<?php esc_attr_e( 'Libellé', 'up2a-footer' ); ?>" value="<?php echo esc_attr( $label ); ?>" class="regular-text">
		<input type="text" name="<?php echo esc_attr( $name ); ?>[href]" placeholder="<?php esc_attr_e( 'URL ou ancre (#up2a-contact)', 'up2a-footer' ); ?>" value="<?php echo esc_attr( $href ); ?>" class="regular-text">
		<button type="button" class="button-link-delete up2a-footer-remove-lien"><?php esc_html_e( 'Supprimer', 'up2a-footer' ); ?></button>
	</div>
	<?php
}

function up2a_footer_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['up2a_footer_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['up2a_footer_nonce'] ) ), 'up2a_footer_save' ) ) {
		up2a_footer_save_settings( wp_unslash( $_POST ) );
		$message = __( 'Pied de page enregistré.', 'up2a-footer' );
	}

	$o = up2a_footer_get_option();

	wp_enqueue_media();
	wp_enqueue_style( 'up2a-footer-admin', UP2A_FOOTER_URL . 'assets/css/admin-settings.css', array(), UP2A_FOOTER_VERSION );
	wp_enqueue_script( 'up2a-footer-admin', UP2A_FOOTER_URL . 'assets/js/admin-settings.js', array( 'media-editor' ), UP2A_FOOTER_VERSION, true );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Pied de page', 'up2a-footer' ); ?></h1>
		<p><?php esc_html_e( "Réglages du pied de page affiché sur toutes les pages du site.", 'up2a-footer' ); ?></p>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<form method="post" class="up2a-footer-form">
			<?php wp_nonce_field( 'up2a_footer_save', 'up2a_footer_nonce' ); ?>

			<p class="up2a-footer-row__image">
				<label><strong><?php esc_html_e( 'Logo', 'up2a-footer' ); ?></strong></label><br>
				<span class="up2a-footer-thumb">
					<?php if ( $o['logo_id'] ) : ?>
						<?php echo wp_get_attachment_image( $o['logo_id'], 'medium' ); ?>
					<?php endif; ?>
				</span>
				<input type="hidden" class="up2a-footer-image-id" name="logo_id" value="<?php echo esc_attr( $o['logo_id'] ); ?>">
				<button type="button" class="button up2a-footer-pick-image"><?php esc_html_e( 'Choisir le logo', 'up2a-footer' ); ?></button>
				<button type="button" class="button up2a-footer-remove-image" <?php echo $o['logo_id'] ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Retirer', 'up2a-footer' ); ?></button>
			</p>

			<p>
				<label for="up2a-footer-slogan"><strong><?php esc_html_e( 'Slogan', 'up2a-footer' ); ?></strong></label><br>
				<input type="text" id="up2a-footer-slogan" name="slogan" class="regular-text" value="<?php echo esc_attr( $o['slogan'] ); ?>">
			</p>

			<p><strong><?php esc_html_e( 'Liens rapides', 'up2a-footer' ); ?></strong></p>
			<div id="up2a-footer-liens">
				<?php foreach ( $o['liens'] as $i => $lien ) : ?>
					<?php up2a_footer_render_lien_row( (string) $i, $lien ); ?>
				<?php endforeach; ?>
			</div>
			<template id="up2a-footer-lien-template">
				<?php up2a_footer_render_lien_row( '__INDEX__', array() ); ?>
			</template>
			<p><button type="button" class="button" id="up2a-footer-add-lien"><?php esc_html_e( '+ Ajouter un lien', 'up2a-footer' ); ?></button></p>

			<p>
				<label for="up2a-footer-copyright"><strong><?php esc_html_e( 'Texte de copyright', 'up2a-footer' ); ?></strong></label><br>
				<input type="text" id="up2a-footer-copyright" name="copyright" class="large-text" value="<?php echo esc_attr( $o['copyright'] ); ?>">
				<span class="description"><?php esc_html_e( '{annee} est remplacé automatiquement par l\'année en cours.', 'up2a-footer' ); ?></span>
			</p>

			<?php submit_button( __( 'Enregistrer les modifications', 'up2a-footer' ) ); ?>
		</form>
	</div>
	<?php
}
