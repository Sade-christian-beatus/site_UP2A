<?php
/**
 * Page de réglages "En-tête" (menu wp-admin) — un seul écran avec tous les
 * réglages de l'en-tête (logo, coordonnées, bandeau défilant, bouton
 * "Espace étudiant"), stockés dans une unique option `up2a_header_option`.
 * Même logique que les écrans de réglages Formations/Galerie (voir
 * up2a-formations/inc/settings.php) — pas de répéteur ici, un formulaire
 * de champs simples suffit (une seule instance d'en-tête sur le site).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function (): void {
		add_menu_page(
			__( 'En-tête', 'up2a-header' ),
			__( 'En-tête', 'up2a-header' ),
			'manage_options',
			'up2a-header',
			'up2a_header_render_settings_page',
			'dashicons-align-center',
			22
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( UP2A_HEADER_FILE ),
	function ( array $links ): array {
		$url = admin_url( 'admin.php?page=up2a-header' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Réglages', 'up2a-header' ) . '</a>' );
		return $links;
	}
);

/**
 * Copie les deux logos existants (bundle up2a-core) dans la médiathèque au
 * premier chargement, pour qu'une mise à jour ne fasse rien disparaître
 * d'un site déjà en ligne — même raisonnement que les seeds Formations/
 * Galerie (voir up2a-formations/inc/settings.php).
 */
function up2a_header_sideload_image( string $chemin_absolu, string $alt = '' ): int {
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

function up2a_header_seed_default_option(): array {
	$logo_id = defined( 'UP2A_CORE_PATH' )
		? up2a_header_sideload_image( UP2A_CORE_PATH . 'assets/img/logo-up2a.webp', __( "UP-2A — Université Privée An-Nahdah d'Afrique", 'up2a-header' ) )
		: 0;

	$defaut = array(
		'logo_id'              => $logo_id,
		'telephone'            => '+226 50 63 85 54',
		'localisation'         => 'Ouagadougou, Burkina Faso',
		'adresse'              => 'Ouagadougou - Balkuy, Burkina Faso',
		'marquee'              => "Rejoignez dès aujourd'hui l'UNIVERSITÉ PRIVÉE AN-NAHDAH D'AFRIQUE pour bâtir une carrière à la hauteur de vos ambitions",
		'espace_etudiant_url'  => defined( 'UP2A_ESPACE_ETUDIANT_URL' ) ? UP2A_ESPACE_ETUDIANT_URL : '',
	);

	add_option( 'up2a_header_option', $defaut );
	return $defaut;
}

function up2a_header_get_option(): array {
	$stored = get_option( 'up2a_header_option', false );
	if ( false === $stored || ! is_array( $stored ) ) {
		return up2a_header_seed_default_option();
	}
	return $stored;
}

function up2a_header_save_settings( array $post ): void {
	$actuel = up2a_header_get_option();

	$actuel['telephone']           = sanitize_text_field( wp_unslash( $post['telephone'] ?? '' ) );
	$actuel['localisation']        = sanitize_text_field( wp_unslash( $post['localisation'] ?? '' ) );
	$actuel['adresse']             = sanitize_text_field( wp_unslash( $post['adresse'] ?? '' ) );
	$actuel['marquee']             = sanitize_text_field( wp_unslash( $post['marquee'] ?? '' ) );
	$actuel['espace_etudiant_url'] = sanitize_text_field( wp_unslash( $post['espace_etudiant_url'] ?? '' ) );
	$actuel['logo_id']             = absint( $post['logo_id'] ?? 0 );

	update_option( 'up2a_header_option', $actuel );
}

function up2a_header_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['up2a_header_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['up2a_header_nonce'] ) ), 'up2a_header_save' ) ) {
		up2a_header_save_settings( wp_unslash( $_POST ) );
		$message = __( 'En-tête enregistré.', 'up2a-header' );
	}

	$o = up2a_header_get_option();

	wp_enqueue_media();
	wp_enqueue_style( 'up2a-header-admin', UP2A_HEADER_URL . 'assets/css/admin-settings.css', array(), UP2A_HEADER_VERSION );
	wp_enqueue_script( 'up2a-header-admin', UP2A_HEADER_URL . 'assets/js/admin-settings.js', array( 'media-editor' ), UP2A_HEADER_VERSION, true );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'En-tête', 'up2a-header' ); ?></h1>
		<p><?php esc_html_e( "Réglages de l'en-tête affiché sur toutes les pages du site. Le menu de navigation reste géré depuis Apparence → Menus.", 'up2a-header' ); ?></p>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<form method="post" class="up2a-header-form">
			<?php wp_nonce_field( 'up2a_header_save', 'up2a_header_nonce' ); ?>

			<p class="up2a-header-row__image">
				<label><strong><?php esc_html_e( 'Logo', 'up2a-header' ); ?></strong></label><br>
				<span class="up2a-header-thumb">
					<?php if ( $o['logo_id'] ) : ?>
						<?php echo wp_get_attachment_image( $o['logo_id'], 'medium' ); ?>
					<?php endif; ?>
				</span>
				<input type="hidden" class="up2a-header-image-id" name="logo_id" value="<?php echo esc_attr( $o['logo_id'] ); ?>">
				<button type="button" class="button up2a-header-pick-image"><?php esc_html_e( 'Choisir le logo', 'up2a-header' ); ?></button>
				<button type="button" class="button up2a-header-remove-image" <?php echo $o['logo_id'] ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Retirer', 'up2a-header' ); ?></button>
			</p>

			<p>
				<label for="up2a-header-telephone"><strong><?php esc_html_e( 'Téléphone', 'up2a-header' ); ?></strong></label><br>
				<input type="text" id="up2a-header-telephone" name="telephone" class="regular-text" value="<?php echo esc_attr( $o['telephone'] ); ?>">
			</p>
			<p>
				<label for="up2a-header-localisation"><strong><?php esc_html_e( 'Localisation courte (bandeau supérieur)', 'up2a-header' ); ?></strong></label><br>
				<input type="text" id="up2a-header-localisation" name="localisation" class="regular-text" value="<?php echo esc_attr( $o['localisation'] ); ?>">
			</p>
			<p>
				<label for="up2a-header-adresse"><strong><?php esc_html_e( 'Adresse complète (section Contact, pied de page)', 'up2a-header' ); ?></strong></label><br>
				<input type="text" id="up2a-header-adresse" name="adresse" class="regular-text" value="<?php echo esc_attr( $o['adresse'] ); ?>">
			</p>
			<p>
				<label for="up2a-header-marquee"><strong><?php esc_html_e( 'Texte du bandeau défilant', 'up2a-header' ); ?></strong></label><br>
				<textarea id="up2a-header-marquee" name="marquee" rows="2" class="large-text"><?php echo esc_textarea( $o['marquee'] ); ?></textarea>
			</p>
			<p>
				<label for="up2a-header-espace"><strong><?php esc_html_e( "URL de l'espace étudiant", 'up2a-header' ); ?></strong></label><br>
				<input type="text" id="up2a-header-espace" name="espace_etudiant_url" class="regular-text" placeholder="https://espace.bdo-burkina.com/" value="<?php echo esc_attr( $o['espace_etudiant_url'] ); ?>">
				<span class="description"><?php esc_html_e( 'Laisser vide tant que le sous-domaine n\'est pas prêt : le bouton pointera vers une ancre neutre plutôt qu\'un lien mort.', 'up2a-header' ); ?></span>
			</p>

			<?php submit_button( __( 'Enregistrer les modifications', 'up2a-header' ) ); ?>
		</form>
	</div>
	<?php
}
