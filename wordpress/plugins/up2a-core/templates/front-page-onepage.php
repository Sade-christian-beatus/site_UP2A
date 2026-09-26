<?php
/**
 * Template Name: Accueil UP-2A (onepage)
 * Description: Home cinématique UP-2A (docs/06-storyboard.md). Assigner ce
 *              modèle à la page choisie comme accueil (Pages → Attributs
 *              de page → Modèle), puis Réglages → Lecture → "Une page
 *              statique" pour la définir comme page d'accueil du site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="up2a-onepage">
	<?php
	up2a_core_render_hero();
	up2a_core_render_pourquoi();
	up2a_core_render_valeurs();

	// Formations et Galerie sont des plugins séparés (up2a-formations,
	// up2a-galerie — voir docs/06-storyboard.md "Scission Formations/
	// Galerie"), affichés via leurs shortcodes `[up2a_formations]` /
	// `[up2a_galerie]` plutôt qu'un appel direct : ça permet de replacer
	// chaque section ailleurs (Elementor, éditeur de blocs...) depuis
	// wp-admin sans toucher au code. Chaque section ne s'affiche que si
	// son plugin est actif, plutôt que de faire échouer toute la home
	// s'il ne l'est pas.
	if ( shortcode_exists( 'up2a_formations' ) ) {
		echo do_shortcode( '[up2a_formations]' );
	}
	if ( shortcode_exists( 'up2a_galerie' ) ) {
		echo do_shortcode( '[up2a_galerie]' );
	}

	up2a_core_render_admissions();
	up2a_core_render_contact();
	up2a_core_render_footer();
	?>
</main>

<?php
get_footer();
