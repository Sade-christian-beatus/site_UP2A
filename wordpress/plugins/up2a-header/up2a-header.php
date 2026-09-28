<?php
/**
 * Plugin Name:       UP-2A — En-tête
 * Description:       En-tête du site (bandeau défilant, coordonnées, logo,
 *                     bouton "Espace étudiant", menu) — extrait de
 *                     `up2a-core` (voir docs/06-storyboard.md). Contenu
 *                     géré depuis le tableau de bord (menu "En-tête") —
 *                     voir inc/settings.php. Shortcode `[up2a_header]`.
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Requires Plugins:  up2a-core
 * Text Domain:        up2a-header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_HEADER_VERSION', '1.0.0' );
define( 'UP2A_HEADER_FILE', __FILE__ );
define( 'UP2A_HEADER_PATH', plugin_dir_path( __FILE__ ) );
define( 'UP2A_HEADER_URL', plugin_dir_url( __FILE__ ) );

require_once UP2A_HEADER_PATH . 'inc/settings.php';
require_once UP2A_HEADER_PATH . 'inc/header.php';

/**
 * Charge le CSS/JS de l'en-tête sur tout le site (l'en-tête s'affiche
 * partout, contrairement aux modules de la home). Dépend de
 * `up2a-tokens` (design system, toujours fourni par up2a-core).
 */
function up2a_header_enqueue_assets(): void {
	if ( is_admin() ) {
		return;
	}

	wp_enqueue_style(
		'up2a-header',
		UP2A_HEADER_URL . 'assets/css/up2a-header.css',
		array( 'up2a-tokens' ),
		UP2A_HEADER_VERSION
	);

	wp_enqueue_script(
		'up2a-header',
		UP2A_HEADER_URL . 'assets/js/up2a-header.js',
		array(),
		UP2A_HEADER_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'up2a_header_enqueue_assets' );
