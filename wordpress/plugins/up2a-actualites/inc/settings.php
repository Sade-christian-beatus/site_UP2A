<?php
/**
 * Page de réglages "Actualités" (menu wp-admin) — répéteur d'actualités
 * (titre, date, extrait, image, lien optionnel), stocké dans une unique
 * option `up2a_actualites_option`. Même logique que les autres écrans
 * (voir up2a-formations/inc/settings.php).
 *
 * Contrairement à Formations/Galerie/En-tête/Pied de page/Slider, aucun
 * amorçage automatique ici : il n'existe aucune actualité réelle à
 * préserver (le module n'existait pas avant), donc l'option démarre
 * vide plutôt que d'inventer un contenu de démonstration (voir
 * CLAUDE.md §3).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function up2a_actualites_register_image_sizes(): void {
	add_image_size( 'up2a_actualites_desktop', 800, 600, true );
	add_image_size( 'up2a_actualites_mobile', 600, 450, true );
}
add_action( 'after_setup_theme', 'up2a_actualites_register_image_sizes' );

add_action(
	'admin_menu',
	function (): void {
		add_menu_page(
			__( 'Actualités', 'up2a-actualites' ),
			__( 'Actualités', 'up2a-actualites' ),
			'manage_options',
			'up2a-actualites',
			'up2a_actualites_render_settings_page',
			'dashicons-megaphone',
			26
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( UP2A_ACTUALITES_FILE ),
	function ( array $links ): array {
		$url = admin_url( 'admin.php?page=up2a-actualites' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Réglages', 'up2a-actualites' ) . '</a>' );
		return $links;
	}
);

function up2a_actualites_get_raw_list(): array {
	$stored = get_option( 'up2a_actualites_option', array() );
	return is_array( $stored ) ? $stored : array();
}

function up2a_actualites_save_settings( array $post ): void {
	$rows_in = isset( $post['actualites'] ) && is_array( $post['actualites'] ) ? $post['actualites'] : array();

	$clean = array();
	foreach ( $rows_in as $row ) {
		$titre = sanitize_text_field( wp_unslash( $row['titre'] ?? '' ) );
		if ( '' === $titre ) {
			continue;
		}
		$clean[] = array(
			'titre'    => $titre,
			'date'     => sanitize_text_field( wp_unslash( $row['date'] ?? '' ) ),
			'extrait'  => sanitize_textarea_field( wp_unslash( $row['extrait'] ?? '' ) ),
			'lien'     => sanitize_text_field( wp_unslash( $row['lien'] ?? '' ) ),
			'image_id' => absint( $row['image_id'] ?? 0 ),
		);
	}

	update_option( 'up2a_actualites_option', $clean );
}

function up2a_actualites_render_row( string $index, array $row ): void {
	$titre    = $row['titre'] ?? '';
	$date     = $row['date'] ?? '';
	$extrait  = $row['extrait'] ?? '';
	$lien     = $row['lien'] ?? '';
	$image_id = (int) ( $row['image_id'] ?? 0 );
	$name     = 'actualites[' . $index . ']';
	?>
	<div class="up2a-actualites-row">
		<p>
			<label><strong><?php esc_html_e( 'Titre', 'up2a-actualites' ); ?></strong></label><br>
			<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[titre]" value="<?php echo esc_attr( $titre ); ?>">
		</p>
		<p>
			<label><?php esc_html_e( 'Date', 'up2a-actualites' ); ?></label><br>
			<input type="date" name="<?php echo esc_attr( $name ); ?>[date]" value="<?php echo esc_attr( $date ); ?>">
		</p>
		<p>
			<label><?php esc_html_e( 'Extrait', 'up2a-actualites' ); ?></label><br>
			<textarea class="large-text" rows="2" name="<?php echo esc_attr( $name ); ?>[extrait]"><?php echo esc_textarea( $extrait ); ?></textarea>
		</p>
		<p>
			<label><?php esc_html_e( "Lien \"En savoir plus\" (optionnel — laisser vide pour une actualité sans lien)", 'up2a-actualites' ); ?></label><br>
			<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[lien]" placeholder="https://..." value="<?php echo esc_attr( $lien ); ?>">
		</p>
		<p class="up2a-actualites-row__image">
			<span class="up2a-actualites-row__thumb">
				<?php if ( $image_id ) : ?>
					<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
				<?php endif; ?>
			</span>
			<input type="hidden" class="up2a-actualites-image-id" name="<?php echo esc_attr( $name ); ?>[image_id]" value="<?php echo esc_attr( $image_id ); ?>">
			<button type="button" class="button up2a-actualites-pick-image"><?php esc_html_e( 'Choisir une image', 'up2a-actualites' ); ?></button>
			<button type="button" class="button up2a-actualites-remove-image" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Retirer', 'up2a-actualites' ); ?></button>
		</p>
		<p class="up2a-actualites-row__actions">
			<button type="button" class="button up2a-actualites-move-up">↑ <?php esc_html_e( 'Monter', 'up2a-actualites' ); ?></button>
			<button type="button" class="button up2a-actualites-move-down">↓ <?php esc_html_e( 'Descendre', 'up2a-actualites' ); ?></button>
			<button type="button" class="button-link-delete up2a-actualites-remove-row"><?php esc_html_e( 'Supprimer cette actualité', 'up2a-actualites' ); ?></button>
		</p>
	</div>
	<?php
}

function up2a_actualites_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['up2a_actualites_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['up2a_actualites_nonce'] ) ), 'up2a_actualites_save' ) ) {
		up2a_actualites_save_settings( wp_unslash( $_POST ) );
		$message = __( 'Actualités enregistrées.', 'up2a-actualites' );
	}

	$liste = up2a_actualites_get_raw_list();

	wp_enqueue_media();
	wp_enqueue_style( 'up2a-actualites-admin', UP2A_ACTUALITES_URL . 'assets/css/admin-settings.css', array(), UP2A_ACTUALITES_VERSION );
	wp_enqueue_script( 'up2a-actualites-admin', UP2A_ACTUALITES_URL . 'assets/js/admin-settings.js', array( 'media-editor' ), UP2A_ACTUALITES_VERSION, true );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Actualités', 'up2a-actualites' ); ?></h1>
		<p><?php esc_html_e( "Gérez ici les actualités affichées par le shortcode [up2a_actualites] sur la home. Tant qu'aucune actualité n'est enregistrée, la section reste absente de la page.", 'up2a-actualites' ); ?></p>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<?php if ( empty( $liste ) ) : ?>
			<div class="notice notice-info"><p><?php esc_html_e( 'Aucune actualité pour le moment — ajoutez-en une ci-dessous.', 'up2a-actualites' ); ?></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'up2a_actualites_save', 'up2a_actualites_nonce' ); ?>

			<div id="up2a-actualites-rows">
				<?php foreach ( $liste as $i => $row ) : ?>
					<?php up2a_actualites_render_row( (string) $i, $row ); ?>
				<?php endforeach; ?>
			</div>

			<template id="up2a-actualites-row-template">
				<?php up2a_actualites_render_row( '__INDEX__', array() ); ?>
			</template>

			<p>
				<button type="button" class="button" id="up2a-actualites-add"><?php esc_html_e( '+ Ajouter une actualité', 'up2a-actualites' ); ?></button>
			</p>

			<?php submit_button( __( 'Enregistrer les modifications', 'up2a-actualites' ) ); ?>
		</form>
	</div>
	<?php
}
