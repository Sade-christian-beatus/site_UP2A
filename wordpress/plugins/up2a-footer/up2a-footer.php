<?php
/**
 * Plugin Name:       UP-2A — Pied de page
 * Description:       Pied de page multi-colonnes (logo, slogan, liens
 *                     rapides, coordonnées, copyright) — extrait de
 *                     `up2a-core` (voir docs/06-storyboard.md). Contenu
 *                     géré depuis le tableau de bord (menu "Pied de
 *                     page") — voir inc/settings.php. Shortcode
 *                     `[up2a_footer]`.
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Requires Plugins:  up2a-core
 * Text Domain:        up2a-footer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_FOOTER_VERSION', '1.0.0' );
define( 'UP2A_FOOTER_FILE', __FILE__ );
define( 'UP2A_FOOTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'UP2A_FOOTER_URL', plugin_dir_url( __FILE__ ) );

require_once UP2A_FOOTER_PATH . 'inc/settings.php';
require_once UP2A_FOOTER_PATH . 'inc/footer.php';

/**
 * Charge le CSS du pied de page sur tout le site (affiché partout, comme
 * l'en-tête). Pas de JS : le pied de page est statique.
 */
function up2a_footer_enqueue_assets(): void {
	if ( is_admin() ) {
		return;
	}

	wp_enqueue_style(
		'up2a-footer',
		UP2A_FOOTER_URL . 'assets/css/up2a-footer.css',
		array( 'up2a-tokens' ),
		UP2A_FOOTER_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'up2a_footer_enqueue_assets' );
