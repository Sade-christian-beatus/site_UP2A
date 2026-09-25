<?php
/**
 * Plugin Name:       UP-2A — Core
 * Description:       Couche d'animation (GSAP + ScrollTrigger + SplitText + Lenis),
 *                     variables du design system et en-tête du site (structure/style,
 *                     pas le contenu éditorial des pages) pour UP-2A. Cible des classes
 *                     CSS `js-*` posées dans Elementor pour l'animation des pages (voir
 *                     docs/04-conventions.md et docs/06-storyboard.md).
 * Version:           0.14.0
 * Requires PHP:      8.0
 * Text Domain:        up2a-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_CORE_VERSION', '0.14.0' );
define( 'UP2A_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'UP2A_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once UP2A_CORE_PATH . 'inc/header.php';
require_once UP2A_CORE_PATH . 'inc/front-page.php';
require_once UP2A_CORE_PATH . 'inc/formation-detail.php';

/**
 * Enregistre la règle de réécriture des pages formation puis force un
 * flush au moment de l'activation, pour qu'elle fonctionne immédiatement
 * sans dépendre du filet de sécurité par version (voir
 * inc/formation-detail.php).
 */
register_activation_hook(
	__FILE__,
	function (): void {
		up2a_core_register_formation_rewrite();
		flush_rewrite_rules();
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Charge les tokens du design system (docs/02-design-system.md) et la
 * couche d'animation sur le front-end public.
 *
 * GSAP/ScrollTrigger/SplitText/Lenis sont self-hostés depuis
 * assets/js/vendor/ (voir CLAUDE.md §7 — pas de multi-CDN en production).
 * Fichiers copiés tels quels depuis les paquets npm officiels (gsap@3,
 * lenis@1) ; voir assets/js/vendor/VERSIONS.md pour les versions exactes
 * et la procédure de mise à jour.
 */
function up2a_core_enqueue_assets(): void {
	if ( is_admin() ) {
		return;
	}

	wp_enqueue_style(
		'up2a-tokens',
		UP2A_CORE_URL . 'assets/css/up2a-tokens.css',
		array(),
		UP2A_CORE_VERSION
	);

	wp_enqueue_style(
		'up2a-header',
		UP2A_CORE_URL . 'assets/css/up2a-header.css',
		array( 'up2a-tokens' ),
		UP2A_CORE_VERSION
	);

	wp_enqueue_script(
		'up2a-header',
		UP2A_CORE_URL . 'assets/js/up2a-header.js',
		array(),
		UP2A_CORE_VERSION,
		true
	);

	wp_enqueue_script(
		'gsap',
		UP2A_CORE_URL . 'assets/js/vendor/gsap.min.js',
		array(),
		UP2A_CORE_VERSION,
		true
	);

	wp_enqueue_script(
		'gsap-scrolltrigger',
		UP2A_CORE_URL . 'assets/js/vendor/ScrollTrigger.min.js',
		array( 'gsap' ),
		UP2A_CORE_VERSION,
		true
	);

	wp_enqueue_script(
		'gsap-splittext',
		UP2A_CORE_URL . 'assets/js/vendor/SplitText.min.js',
		array( 'gsap' ),
		UP2A_CORE_VERSION,
		true
	);

	wp_enqueue_script(
		'lenis',
		UP2A_CORE_URL . 'assets/js/vendor/lenis.min.js',
		array(),
		UP2A_CORE_VERSION,
		true
	);

	wp_enqueue_script(
		'up2a-core',
		UP2A_CORE_URL . 'assets/js/up2a-core.js',
		array( 'gsap', 'gsap-scrolltrigger', 'gsap-splittext', 'lenis' ),
		UP2A_CORE_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'up2a_core_enqueue_assets' );
