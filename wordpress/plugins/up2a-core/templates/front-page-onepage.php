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
	// Galerie") : chaque section ne s'affiche que si son plugin est actif,
	// plutôt que de faire échouer toute la home s'il ne l'est pas.
	if ( function_exists( 'up2a_formations_render' ) ) {
		up2a_formations_render();
	}
	if ( function_exists( 'up2a_galerie_render' ) ) {
		up2a_galerie_render();
	}

	up2a_core_render_admissions();
	up2a_core_render_contact();
	up2a_core_render_footer();
	?>
</main>

<?php
get_footer();
