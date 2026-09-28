<?php
/**
 * Plugin Name:       UP-2A — Slider (Hero)
 * Description:       Scène d'accueil "Hero" (diaporama de photos, titre,
 *                     sous-titre, boutons d'action, compte à rebours de
 *                     la rentrée) — extrait de `up2a-core` (voir
 *                     docs/06-storyboard.md). Contenu géré depuis le
 *                     tableau de bord (menu "Slider") — voir
 *                     inc/settings.php. Shortcode `[up2a_slider]`.
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Requires Plugins:  up2a-core
 * Text Domain:        up2a-slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_SLIDER_VERSION', '1.0.0' );
define( 'UP2A_SLIDER_FILE', __FILE__ );
define( 'UP2A_SLIDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'UP2A_SLIDER_URL', plugin_dir_url( __FILE__ ) );

require_once UP2A_SLIDER_PATH . 'inc/settings.php';
require_once UP2A_SLIDER_PATH . 'inc/slider.php';

/**
 * Charge le CSS/JS du Hero, uniquement sur la home onepage (seule page où
 * il s'affiche à ce jour). Dépend de `up2a-front-page` (styles/utilitaires
 * partagés — toujours fourni par up2a-core) et de `up2a-core` (JS, pour
 * `window.UP2A` et GSAP/ScrollTrigger déjà chargés en dépendance de ce
 * handle).
 */
function up2a_slider_enqueue_assets(): void {
	if ( ! is_singular( 'page' ) || get_page_template_slug( get_the_ID() ) !== 'up2a-core-onepage.php' ) {
		return;
	}

	wp_enqueue_style(
		'up2a-front-page',
		UP2A_CORE_URL . 'assets/css/up2a-front-page.css',
		array( 'up2a-tokens' ),
		UP2A_CORE_VERSION
	);

	wp_enqueue_style(
		'up2a-slider',
		UP2A_SLIDER_URL . 'assets/css/up2a-slider.css',
		array( 'up2a-front-page' ),
		UP2A_SLIDER_VERSION
	);

	wp_enqueue_script(
		'up2a-slider',
		UP2A_SLIDER_URL . 'assets/js/up2a-slider.js',
		array( 'up2a-core' ),
		UP2A_SLIDER_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'up2a_slider_enqueue_assets' );
