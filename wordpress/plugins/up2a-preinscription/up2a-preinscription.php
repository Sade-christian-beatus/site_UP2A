<?php
/**
 * Plugin Name:       UP-2A — Préinscription
 * Description:       Formulaire de préinscription multi-étapes → Supabase, SANS
 *                     paiement (contrainte absolue, voir CLAUDE.md §3). Shortcode
 *                     `[up2a_preinscription]` à poser sur une page dédiée (voir
 *                     wordpress/README.md).
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Text Domain:        up2a-preinscription
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_PREINSCRIPTION_VERSION', '1.0.0' );
define( 'UP2A_PREINSCRIPTION_PATH', plugin_dir_path( __FILE__ ) );
define( 'UP2A_PREINSCRIPTION_URL', plugin_dir_url( __FILE__ ) );

require_once UP2A_PREINSCRIPTION_PATH . 'inc/data.php';
require_once UP2A_PREINSCRIPTION_PATH . 'inc/shortcode.php';
require_once UP2A_PREINSCRIPTION_PATH . 'inc/rest.php';

/**
 * Vérifie à l'activation que les constantes Supabase sont bien définies
 * côté serveur (wp-config.php), jamais dans ce plugin ni dans le dépôt.
 * N'empêche pas l'activation — affiche juste un avertissement admin, pour
 * ne pas bloquer l'installation avant que les vraies clés soient
 * disponibles.
 */
function up2a_preinscription_check_config(): void {
	if ( ! defined( 'UP2A_SUPABASE_URL' ) || ! defined( 'UP2A_SUPABASE_SERVICE_ROLE_KEY' ) ) {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-warning"><p>';
				echo esc_html__(
					'up2a-preinscription : les constantes UP2A_SUPABASE_URL et UP2A_SUPABASE_SERVICE_ROLE_KEY ne sont pas définies dans wp-config.php. Le formulaire ne pourra pas écrire dans Supabase tant qu\'elles ne sont pas configurées.',
					'up2a-preinscription'
				);
				echo '</p></div>';
			}
		);
	}
}
add_action( 'admin_init', 'up2a_preinscription_check_config' );

/**
 * Charge les styles/scripts du formulaire uniquement sur les pages qui
 * contiennent le shortcode — jamais sur le reste du site (perf mobile,
 * voir CLAUDE.md §7).
 */
function up2a_preinscription_enqueue_assets(): void {
	if ( is_admin() || ! is_singular() ) {
		return;
	}

	$post = get_post();
	if ( ! $post || ! has_shortcode( $post->post_content, 'up2a_preinscription' ) ) {
		return;
	}

	$style_deps = wp_style_is( 'up2a-tokens', 'registered' ) ? array( 'up2a-tokens' ) : array();

	wp_enqueue_style(
		'up2a-preinscription',
		UP2A_PREINSCRIPTION_URL . 'assets/css/up2a-preinscription.css',
		$style_deps,
		UP2A_PREINSCRIPTION_VERSION
	);

	wp_enqueue_script(
		'up2a-preinscription',
		UP2A_PREINSCRIPTION_URL . 'assets/js/up2a-preinscription.js',
		array(),
		UP2A_PREINSCRIPTION_VERSION,
		true
	);

	wp_localize_script(
		'up2a-preinscription',
		'UP2A_PREINSCRIPTION_CONFIG',
		array(
			'endpoint' => esc_url_raw( rest_url( 'up2a/v1/preinscription' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'up2a_preinscription_enqueue_assets' );
