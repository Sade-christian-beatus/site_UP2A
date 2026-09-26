<?php
/**
 * Plugin Name:       UP-2A — Galerie
 * Description:       Module "Galerie" (grille + lightbox photo) de la home
 *                     UP-2A — extrait de `up2a-core` (voir
 *                     docs/06-storyboard.md et docs/03-roadmap.md). Photos
 *                     gérées depuis le tableau de bord (menu "Galerie",
 *                     CPT `up2a_photo`) — voir inc/cpt.php. Shortcode
 *                     `[up2a_galerie]`.
 * Version:           1.1.0
 * Requires PHP:      8.0
 * Requires Plugins:  up2a-core
 * Text Domain:        up2a-galerie
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_GALERIE_VERSION', '1.1.0' );
define( 'UP2A_GALERIE_PATH', plugin_dir_path( __FILE__ ) );
define( 'UP2A_GALERIE_URL', plugin_dir_url( __FILE__ ) );

require_once UP2A_GALERIE_PATH . 'inc/cpt.php';
require_once UP2A_GALERIE_PATH . 'inc/galerie.php';

/**
 * Charge le CSS/JS propres au module Galerie, uniquement sur la home
 * onepage (seule page qui affiche la galerie à ce jour). `up2a-front-page`
 * (styles/utilitaires partagés : boutons, titres de section, décor) est
 * toujours fourni par up2a-core — dépendance obligatoire de ce plugin.
 */
add_action(
	'wp_enqueue_scripts',
	function (): void {
		if ( ! is_singular( 'page' ) || get_page_template_slug( get_the_ID() ) !== 'up2a-core-onepage.php' ) {
			return;
		}

		wp_enqueue_style(
			'up2a-galerie',
			UP2A_GALERIE_URL . 'assets/css/up2a-galerie.css',
			array( 'up2a-front-page' ),
			UP2A_GALERIE_VERSION
		);

		wp_enqueue_script(
			'up2a-galerie',
			UP2A_GALERIE_URL . 'assets/js/up2a-galerie.js',
			array( 'up2a-core' ),
			UP2A_GALERIE_VERSION,
			true
		);
	}
);
