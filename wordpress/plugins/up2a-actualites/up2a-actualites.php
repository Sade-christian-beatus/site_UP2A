<?php
/**
 * Plugin Name:       UP-2A — Actualités
 * Description:       Grille d'actualités (annonces, événements) pour la
 *                     home UP-2A — voir docs/06-storyboard.md. Contenu
 *                     géré depuis le tableau de bord (menu "Actualités")
 *                     — voir inc/settings.php. Shortcode
 *                     `[up2a_actualites]`. Section absente jusqu'ici
 *                     (docs/00-brief.md : "pas encore de CPT ni
 *                     d'articles") — aucune actualité n'est préremplie,
 *                     contrairement aux autres modules qui reprennent du
 *                     contenu existant : il n'y a ici aucun contenu réel
 *                     à préserver (voir CLAUDE.md §3, jamais de contenu
 *                     inventé).
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Requires Plugins:  up2a-core
 * Text Domain:        up2a-actualites
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_ACTUALITES_VERSION', '1.0.0' );
define( 'UP2A_ACTUALITES_FILE', __FILE__ );
define( 'UP2A_ACTUALITES_PATH', plugin_dir_path( __FILE__ ) );
define( 'UP2A_ACTUALITES_URL', plugin_dir_url( __FILE__ ) );

require_once UP2A_ACTUALITES_PATH . 'inc/settings.php';
require_once UP2A_ACTUALITES_PATH . 'inc/actualites.php';

/**
 * Charge le CSS/JS propres au module Actualités, uniquement sur la home
 * onepage (seule page qui l'affiche à ce jour). Dépend de
 * `up2a-front-page` (styles/utilitaires partagés — toujours fourni par
 * up2a-core).
 */
function up2a_actualites_enqueue_assets(): void {
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
		'up2a-actualites',
		UP2A_ACTUALITES_URL . 'assets/css/up2a-actualites.css',
		array( 'up2a-front-page' ),
		UP2A_ACTUALITES_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'up2a_actualites_enqueue_assets' );
