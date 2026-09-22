<?php
/**
 * Plugin Name:       UP-2A — Core
 * Description:       Couche d'animation (GSAP + ScrollTrigger + SplitText + Lenis),
 *                     variables du design system et en-tête du site (structure/style,
 *                     pas le contenu éditorial des pages) pour UP-2A. Cible des classes
 *                     CSS `js-*` posées dans Elementor pour l'animation des pages (voir
 *                     docs/04-conventions.md et docs/06-storyboard.md).
 * Version:           0.5.0
 * Requires PHP:      8.0
 * Text Domain:        up2a-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_CORE_VERSION', '0.5.0' );
define( 'UP2A_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'UP2A_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once UP2A_CORE_PATH . 'inc/header.php';
require_once UP2A_CORE_PATH . 'inc/front-page.php';

/**
 * Charge les tokens du design system (docs/02-design-system.md) et la
 * couche d'animation sur le front-end public.
 *
 * GSAP est chargé depuis un CDN pour ce scaffold ; à self-hoster en
 * production (voir docs/CLAUDE.md §7 — pas de multi-CDN en prod).
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
		'https://cdn.jsdelivr.net/npm/gsap@3/dist/gsap.min.js',
		array(),
		null,
		true
	);

	wp_enqueue_script(
		'gsap-scrolltrigger',
		'https://cdn.jsdelivr.net/npm/gsap@3/dist/ScrollTrigger.min.js',
		array( 'gsap' ),
		null,
		true
	);

	wp_enqueue_script(
		'gsap-splittext',
		'https://cdn.jsdelivr.net/npm/gsap@3/dist/SplitText.min.js',
		array( 'gsap' ),
		null,
		true
	);

	wp_enqueue_script(
		'lenis',
		'https://cdn.jsdelivr.net/npm/lenis@1/dist/lenis.min.js',
		array(),
		null,
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
