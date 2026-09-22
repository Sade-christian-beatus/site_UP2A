<?php
/**
 * En-tête du site (barre supérieure + barre principale), voir docs/06-storyboard.md.
 * Structure/CSS/JS versionnés ici ; seuls le texte du menu (wp_nav_menu) et
 * les actualités sont éditables depuis wp-admin sans toucher au code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Réglages de l'en-tête. Valeurs par défaut = informations réelles connues
 * à ce jour (voir docs/05-contenus.md). Modifiables sans toucher au reste
 * du fichier, ou via les filtres `up2a_core_*` ci-dessous depuis un thème
 * enfant / mu-plugin si besoin de les changer sans éditer ce plugin.
 */
function up2a_core_header_phone(): string {
	return apply_filters( 'up2a_core_header_phone', '+226 50 63 85 54' );
}

function up2a_core_header_location(): string {
	return apply_filters( 'up2a_core_header_location', 'Ouagadougou, Burkina Faso' );
}

/**
 * Adresse complète (quartier inclus), utilisée dans la section Contact et
 * le pied de page — plus précise que up2a_core_header_location() qui
 * reste courte pour la barre supérieure.
 */
function up2a_core_header_address(): string {
	return apply_filters( 'up2a_core_header_address', 'Ouagadougou - Balkuy, Burkina Faso' );
}

/**
 * Lien de géolocalisation (Google Maps) vers l'adresse ci-dessus. On
 * s'appuie sur une recherche par adresse plutôt que des coordonnées GPS
 * figées dans le code : aucune coordonnée précise n'a été fournie par le
 * client, et un lien de recherche reste correct même si l'adresse est
 * affinée plus tard (voir docs/05-contenus.md, "adresse précise ... à
 * confirmer"). Remplaçable par un lien à coordonnées exactes via le
 * filtre si besoin.
 */
function up2a_core_header_maps_url(): string {
	$url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( up2a_core_header_address() );
	return apply_filters( 'up2a_core_header_maps_url', $url );
}

/**
 * Variante "embed" de l'URL ci-dessus (carte intégrée en <iframe> dans la
 * section Contact) — ne nécessite pas de clé API Google Maps, juste une
 * recherche publique par adresse. Remplaçable par des coordonnées GPS
 * exactes via le même filtre que up2a_core_header_maps_url() une fois
 * connues (voir wordpress/README.md).
 */
function up2a_core_header_maps_embed_url(): string {
	$url = 'https://www.google.com/maps?q=' . rawurlencode( up2a_core_header_address() ) . '&output=embed';
	return apply_filters( 'up2a_core_header_maps_embed_url', $url );
}

function up2a_core_header_marquee_text(): string {
	return apply_filters(
		'up2a_core_header_marquee_text',
		"Rejoignez dès aujourd'hui l'UNIVERSITÉ PRIVÉE AN-NAHDAH D'AFRIQUE pour bâtir une carrière à la hauteur de vos ambitions"
	);
}

/**
 * URL du bouton "Espace étudiant". À remplacer par l'URL réelle du
 * sous-domaine app-etudiant une fois déployé (ex. https://espace.up2a.bf).
 * `#up2a-espace-etudiant` en attendant, pour ne pas pointer vers un lien mort.
 */
function up2a_core_espace_etudiant_url(): string {
	if ( defined( 'UP2A_ESPACE_ETUDIANT_URL' ) ) {
		return UP2A_ESPACE_ETUDIANT_URL;
	}
	return apply_filters( 'up2a_core_espace_etudiant_url', '#up2a-espace-etudiant' );
}

/**
 * Petites icônes SVG en ligne (pas de police d'icônes externe — cohérent
 * avec la règle "self-host, pas de multi-CDN", docs/CLAUDE.md §7).
 */
function up2a_core_icon( string $name ): string {
	$icons = array(
		'phone'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
		'pin'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
		'menu'     => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
		'close'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
		'cap'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg>',
	);

	return $icons[ $name ] ?? '';
}

/**
 * Rendu de l'en-tête, accroché à wp_body_open (donc affiché sur tout le
 * site, indépendamment de ce que contient la page Elementor).
 */
function up2a_core_render_header(): void {
	$phone_raw = up2a_core_header_phone();
	$phone_href = 'tel:' . preg_replace( '/[^0-9+]/', '', $phone_raw );
	$marquee   = esc_html( up2a_core_header_marquee_text() );
	?>
	<header id="up2a-header" class="up2a-header">
		<div class="up2a-topbar">
			<div class="up2a-topbar__marquee" aria-hidden="true">
				<div class="up2a-topbar__marquee-track js-up2a-marquee">
					<span class="up2a-topbar__marquee-item"><?php echo $marquee; ?></span>
					<span class="up2a-topbar__marquee-item"><?php echo $marquee; ?></span>
				</div>
			</div>
			<div class="up2a-topbar__contacts">
				<a class="up2a-topbar__contact" href="<?php echo esc_attr( $phone_href ); ?>">
					<?php echo up2a_core_icon( 'phone' ); ?>
					<span><?php echo esc_html( $phone_raw ); ?></span>
				</a>
				<a
					class="up2a-topbar__contact"
					href="<?php echo esc_url( up2a_core_header_maps_url() ); ?>"
					target="_blank"
					rel="noopener"
				>
					<?php echo up2a_core_icon( 'pin' ); ?>
					<span><?php echo esc_html( up2a_core_header_location() ); ?></span>
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
				<span class="up2a-mainbar__toggle-icon up2a-mainbar__toggle-icon--open"><?php echo up2a_core_icon( 'menu' ); ?></span>
				<span class="up2a-mainbar__toggle-icon up2a-mainbar__toggle-icon--close"><?php echo up2a_core_icon( 'close' ); ?></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'up2a-core' ); ?></span>
			</button>

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="up2a-mainbar__logo">
				<picture>
					<source srcset="<?php echo esc_url( UP2A_CORE_URL . 'assets/img/logo-up2a.webp' ); ?>" type="image/webp">
					<img
						src="<?php echo esc_url( UP2A_CORE_URL . 'assets/img/logo-up2a.png' ); ?>"
						width="677"
						height="186"
						alt="<?php esc_attr_e( "UP-2A — Université Privée An-Nahdah d'Afrique", 'up2a-core' ); ?>"
						loading="eager"
						fetchpriority="high"
					>
				</picture>
			</a>

			<a href="<?php echo esc_url( up2a_core_espace_etudiant_url() ); ?>" class="up2a-mainbar__cta">
				<span><?php esc_html_e( 'ESPACE ÉTUDIANT', 'up2a-core' ); ?></span>
				<?php echo up2a_core_icon( 'cap' ); ?>
			</a>
		</div>

		<nav id="up2a-nav" class="up2a-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'up2a-core' ); ?>">
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
				echo '<p class="up2a-nav__empty">' . esc_html__( 'Menu à configurer : Apparence → Menus → assigner l\'emplacement "Menu principal UP-2A".', 'up2a-core' ) . '</p>';
			}
			?>
		</nav>
	</header>
	<?php
}
add_action( 'wp_body_open', 'up2a_core_render_header' );

/**
 * Emplacement de menu géré depuis wp-admin (Apparence → Menus), pour que
 * les liens du menu restent éditables sans toucher au code.
 */
function up2a_core_register_menu(): void {
	register_nav_menu( 'up2a-primary', __( 'Menu principal UP-2A', 'up2a-core' ) );
}
add_action( 'after_setup_theme', 'up2a_core_register_menu' );
