<?php
/**
 * Rendu de l'en-tête — lit l'option `up2a_header_option` (voir
 * inc/settings.php, menu wp-admin "En-tête"). Extrait de `up2a-core`
 * (voir docs/06-storyboard.md "Extraction En-tête/Pied de page/Slider/
 * Actualités", 2026-09-28).
 *
 * Dépendance obligatoire : `up2a-core` (icônes `up2a_core_content_icon()`
 * — voir up2a-header.php "Requires Plugins").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lien de géolocalisation (Google Maps) vers l'adresse renseignée dans les
 * réglages. On s'appuie sur une recherche par adresse plutôt que des
 * coordonnées GPS figées : aucune coordonnée précise n'a été fournie par
 * le client (voir docs/05-contenus.md).
 */
function up2a_header_maps_url(): string {
	return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( up2a_header_get_option()['adresse'] );
}

/**
 * Variante "embed" de l'URL ci-dessus (carte intégrée en <iframe> dans la
 * section Contact d'up2a-core) — ne nécessite pas de clé API Google Maps.
 */
function up2a_header_maps_embed_url(): string {
	return 'https://www.google.com/maps?q=' . rawurlencode( up2a_header_get_option()['adresse'] ) . '&output=embed';
}

/**
 * URL du bouton "Espace étudiant". Ancre neutre (pas de lien mort) tant
 * que l'URL n'a pas été renseignée dans les réglages.
 */
function up2a_header_espace_etudiant_url(): string {
	$url = up2a_header_get_option()['espace_etudiant_url'];
	return '' !== $url ? $url : '#up2a-espace-etudiant';
}

function up2a_header_render(): void {
	$o          = up2a_header_get_option();
	$phone_href = 'tel:' . preg_replace( '/[^0-9+]/', '', $o['telephone'] );
	?>
	<header id="up2a-header" class="up2a-header">
		<div class="up2a-topbar">
			<div class="up2a-topbar__marquee" aria-hidden="true">
				<div class="up2a-topbar__marquee-track js-up2a-marquee">
					<span class="up2a-topbar__marquee-item"><?php echo esc_html( $o['marquee'] ); ?></span>
					<span class="up2a-topbar__marquee-item"><?php echo esc_html( $o['marquee'] ); ?></span>
				</div>
			</div>
			<div class="up2a-topbar__contacts">
				<a class="up2a-topbar__contact" href="<?php echo esc_attr( $phone_href ); ?>">
					<?php echo up2a_core_content_icon( 'phone' ); ?>
					<span><?php echo esc_html( $o['telephone'] ); ?></span>
				</a>
				<a
					class="up2a-topbar__contact"
					href="<?php echo esc_url( up2a_header_maps_url() ); ?>"
					target="_blank"
					rel="noopener"
				>
					<?php echo up2a_core_content_icon( 'pin' ); ?>
					<span><?php echo esc_html( $o['localisation'] ); ?></span>
				</a>
			</div>
		</div>

		<div class="up2a-mainbar">
			<button
				type="button"
				class="up2a-mainbar__toggle js-up2a-menu-toggle"
				aria-expanded="false"
				aria-controls="up2a-nav"
			>
				<span class="up2a-mainbar__toggle-icon up2a-mainbar__toggle-icon--open"><?php echo up2a_core_content_icon( 'menu' ); ?></span>
				<span class="up2a-mainbar__toggle-icon up2a-mainbar__toggle-icon--close"><?php echo up2a_core_content_icon( 'close' ); ?></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'up2a-header' ); ?></span>
			</button>

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="up2a-mainbar__logo">
				<?php if ( $o['logo_id'] ) : ?>
					<?php
					echo wp_get_attachment_image(
						$o['logo_id'],
						'full',
						false,
						array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'alt'           => get_bloginfo( 'name' ),
						)
					);
					?>
				<?php endif; ?>
			</a>

			<a href="<?php echo esc_url( up2a_header_espace_etudiant_url() ); ?>" class="up2a-mainbar__cta">
				<span><?php esc_html_e( 'ESPACE ÉTUDIANT', 'up2a-header' ); ?></span>
				<?php echo up2a_core_content_icon( 'cap' ); ?>
			</a>
		</div>

		<nav id="up2a-nav" class="up2a-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'up2a-header' ); ?>">
			<?php
			if ( has_nav_menu( 'up2a-primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'up2a-primary',
						'container'      => false,
						'menu_class'     => 'up2a-nav__list',
						'fallback_cb'    => false,
					)
				);
			} else {
				echo '<p class="up2a-nav__empty">' . esc_html__( 'Menu à configurer : Apparence → Menus → assigner l\'emplacement "Menu principal UP-2A".', 'up2a-header' ) . '</p>';
			}
			?>
		</nav>
	</header>
	<?php
}
add_action( 'wp_body_open', 'up2a_header_render' );

/**
 * Shortcode `[up2a_header]` — pour cohérence avec les autres modules
 * (Formations, Galerie...) et au cas où l'en-tête devrait être inséré
 * manuellement ailleurs ; l'usage normal reste automatique via
 * `wp_body_open` ci-dessus (l'en-tête doit s'afficher sur tout le site,
 * pas seulement où le shortcode serait posé).
 */
add_shortcode(
	'up2a_header',
	function (): string {
		ob_start();
		up2a_header_render();
		return (string) ob_get_clean();
	}
);

/**
 * Emplacement de menu géré depuis wp-admin (Apparence → Menus), pour que
 * les liens du menu restent éditables sans toucher au code.
 */
function up2a_header_register_menu(): void {
	register_nav_menu( 'up2a-primary', __( 'Menu principal UP-2A', 'up2a-header' ) );
}
add_action( 'after_setup_theme', 'up2a_header_register_menu' );
