<?php
/**
 * Plugin Name:       UP-2A — Préinscription
 * Description:       Formulaire de préinscription multi-étapes → Supabase, SANS
 *                     paiement (contrainte absolue, voir CLAUDE.md §3). Squelette
 *                     uniquement à ce stade : construction complète en phase 5 de
 *                     docs/03-roadmap.md (formulaire, upload des pièces, e-mail de
 *                     confirmation).
 * Version:           0.1.0
 * Requires PHP:      8.0
 * Text Domain:        up2a-preinscription
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'UP2A_PREINSCRIPTION_VERSION', '0.1.0' );
define( 'UP2A_PREINSCRIPTION_PATH', plugin_dir_path( __FILE__ ) );

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
 * TODO (phase 5, voir docs/03-roadmap.md) :
 * - Enregistrer le shortcode / bloc Elementor du formulaire multi-étapes.
 * - Enregistrer une route REST WordPress (namespace `up2a/v1`) qui reçoit
 *   la soumission finale, valide/sanitize côté serveur, upload les pièces
 *   jointes vers le bucket Supabase Storage `candidatures`, puis insère
 *   la ligne dans `public.candidatures` via l'API REST Supabase avec
 *   UP2A_SUPABASE_SERVICE_ROLE_KEY (jamais exposée au navigateur).
 * - Envoyer l'e-mail de confirmation.
 * - AUCUNE étape de paiement, à aucun moment (contrainte absolue).
 */
