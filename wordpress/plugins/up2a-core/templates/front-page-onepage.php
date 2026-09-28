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
	// Hero, Formations, Galerie et Actualités sont des plugins séparés
	// (up2a-slider, up2a-formations, up2a-galerie, up2a-actualites — voir
	// docs/06-storyboard.md "Extraction En-tête/Pied de page/Slider/
	// Actualités" et "Scission Formations/Galerie"), affichés via leurs
	// shortcodes plutôt qu'un appel direct : ça permet de replacer chaque
	// section ailleurs (Elementor, éditeur de blocs...) depuis wp-admin
	// sans toucher au code. Chaque section ne s'affiche que si son plugin
	// est actif, plutôt que de faire échouer toute la home s'il ne l'est
	// pas.
	if ( shortcode_exists( 'up2a_slider' ) ) {
		echo do_shortcode( '[up2a_slider]' );
	}

	up2a_core_render_pourquoi();
	up2a_core_render_valeurs();

	if ( shortcode_exists( 'up2a_formations' ) ) {
		echo do_shortcode( '[up2a_formations]' );
	}
	if ( shortcode_exists( 'up2a_galerie' ) ) {
		echo do_shortcode( '[up2a_galerie]' );
	}
	if ( shortcode_exists( 'up2a_actualites' ) ) {
		echo do_shortcode( '[up2a_actualites]' );
	}

	up2a_core_render_admissions();
	up2a_core_render_contact();
	?>
</main>

<?php
get_footer();
