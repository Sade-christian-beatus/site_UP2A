<?php
/**
 * Page de détail par formation, servie sur `/formations/{slug}/` — voir
 * docs/03-roadmap.md phase 4. Approche retenue : une règle de réécriture +
 * un template codé (même logique que la home, voir inc/front-page.php),
 * pas un CPT ni un montage Elementor : le contenu réel des 4 licences est
 * déjà centralisé dans up2a_core_formations() (synchronisé avec
 * supabase/migrations/0003_seed.sql), un CPT dupliquerait cette source de
 * vérité sans rien apporter pour seulement 4 pages fixes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Déclare la règle `/formations/{slug}/` → `index.php?up2a_formation={slug}`.
 * Appelée sur `init` (cas normal) et directement à l'activation du plugin
 * (voir up2a-core.php) pour que la règle existe avant le premier flush.
 */
function up2a_core_register_formation_rewrite(): void {
	add_rewrite_tag( '%up2a_formation%', '([^&/]+)' );
	add_rewrite_rule( '^formations/([^/]+)/?$', 'index.php?up2a_formation=$matches[1]', 'top' );
}
add_action( 'init', 'up2a_core_register_formation_rewrite' );

/**
 * Flush automatique si la version du plugin a changé depuis le dernier
 * chargement : filet de sécurité pour le cas fréquent où les fichiers sont
 * remplacés sans passer par une (dés)activation WordPress (voir
 * wordpress/README.md "Dépannage" — même symptôme que le 404 sur
 * /preinscription/ déjà rencontré). Reste sans effet si rien n'a changé.
 */
add_action(
	'init',
	function (): void {
		if ( get_option( 'up2a_core_rewrite_version' ) !== UP2A_CORE_VERSION ) {
			flush_rewrite_rules();
			update_option( 'up2a_core_rewrite_version', UP2A_CORE_VERSION );
		}
	},
	20
);

/**
 * Empêche WordPress de traiter la requête comme un 404 : aucune règle de
 * réécriture ne correspond à un contenu WP réel (page/article), donc la
 * requête principale ne trouve rien par défaut. On rétablit un statut 200
 * dès que le slug demandé correspond à une formation connue.
 */
add_action(
	'wp',
	function (): void {
		$slug = get_query_var( 'up2a_formation' );
		if ( '' === $slug || null === $slug ) {
			return;
		}
		if ( null === up2a_core_find_formation( $slug ) ) {
			return; // Slug inconnu : on laisse WordPress rendre son vrai 404.
		}
		global $wp_query;
		$wp_query->is_404 = false;
		status_header( 200 );
	}
);

/**
 * Sert le template de détail dès que le slug demandé correspond à une
 * formation connue.
 */
add_filter(
	'template_include',
	function ( string $template ): string {
		$slug = get_query_var( 'up2a_formation' );
		if ( '' === $slug || null === $slug ) {
			return $template;
		}
		if ( null === up2a_core_find_formation( $slug ) ) {
			return $template;
		}
		$custom = UP2A_CORE_PATH . 'templates/formation-detail.php';
		return file_exists( $custom ) ? $custom : $template;
	}
);

/**
 * Styles/scripts propres à la page de détail, chargés uniquement sur ces
 * pages (même logique que inc/front-page.php pour la home).
 */
add_action(
	'wp_enqueue_scripts',
	function (): void {
		$slug = get_query_var( 'up2a_formation' );
		if ( '' === $slug || null === $slug || null === up2a_core_find_formation( $slug ) ) {
			return;
		}

		wp_enqueue_style(
			'up2a-front-page',
			UP2A_CORE_URL . 'assets/css/up2a-front-page.css',
			array( 'up2a-tokens' ),
			UP2A_CORE_VERSION
		);

		wp_enqueue_style(
			'up2a-formation-detail',
			UP2A_CORE_URL . 'assets/css/up2a-formation-detail.css',
			array( 'up2a-front-page' ),
			UP2A_CORE_VERSION
		);
	}
);
