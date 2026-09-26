<?php
/**
 * Plugin Name:       UP-2A — Formations
 * Description:       Module "Formations" (cartes, modale de détail, pages
 *                     /formations/{slug}/) de la home UP-2A — extrait de
 *                     `up2a-core` (voir docs/06-storyboard.md et
 *                     docs/03-roadmap.md). Contenu (images, titres, intros,
 *                     débouchés) modifiable dans inc/formations.php sans
 *                     toucher au reste du site.
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Requires Plugins:  up2a-core
 * Text Domain:        up2a-formations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_FORMATIONS_VERSION', '1.0.0' );
define( 'UP2A_FORMATIONS_PATH', plugin_dir_path( __FILE__ ) );
define( 'UP2A_FORMATIONS_URL', plugin_dir_url( __FILE__ ) );

require_once UP2A_FORMATIONS_PATH . 'inc/formations.php';

/**
 * Enregistre la règle de réécriture des pages formation puis force un
 * flush au moment de l'activation, pour qu'elle fonctionne immédiatement
 * sans dépendre du filet de sécurité par version (voir inc/formations.php).
 */
register_activation_hook(
	__FILE__,
	function (): void {
		up2a_formations_register_rewrite();
		flush_rewrite_rules();
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Charge le CSS/JS propres au module Formations, uniquement sur les pages
 * qui en affichent (home onepage + pages de détail) — voir
 * inc/formations.php pour les deux points d'accroche `wp_enqueue_scripts`.
 * Dépend de `up2a-front-page` (styles/utilitaires partagés : boutons,
 * titres de section, décor — toujours fournis par up2a-core, dépendance
 * obligatoire de ce plugin) et de `up2a-core` (JS, pour `window.UP2A` et
 * GSAP/ScrollTrigger déjà chargés en dépendance de ce handle).
 */
function up2a_formations_enqueue_shared_assets(): void {
	wp_enqueue_style(
		'up2a-formations',
		UP2A_FORMATIONS_URL . 'assets/css/up2a-formations.css',
		array( 'up2a-front-page' ),
		UP2A_FORMATIONS_VERSION
	);

	wp_enqueue_script(
		'up2a-formations',
		UP2A_FORMATIONS_URL . 'assets/js/up2a-formations.js',
		array( 'up2a-core' ),
		UP2A_FORMATIONS_VERSION,
		true
	);
}
