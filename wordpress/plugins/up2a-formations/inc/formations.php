<?php
/**
 * Lecture et rendu du module "Formations" — le contenu vit désormais dans
 * le CPT `up2a_formation` (voir inc/cpt.php), modifiable depuis le tableau
 * de bord WordPress (menu "Formations"). Ce fichier ne fait plus que
 * lire ce CPT et le présenter avec la même forme de données qu'avant
 * (tableau slug/icone/nom/faculte/faculte_full/intro/debouches/image) —
 * voir docs/06-storyboard.md "CPT Formation depuis le dashboard" pour le
 * détail de cette migration (2026-09-26).
 *
 * Dépendance obligatoire : `up2a-core` (icônes `up2a_core_content_icon()`/
 * `up2a_core_icon()`, décor `up2a_core_decor()`, URL de préinscription
 * `up2a_core_preinscription_url()` — voir up2a-formations.php
 * "Requires Plugins").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// --------------------------------------------------------------------
// Données — lues depuis le CPT up2a_formation (inc/cpt.php)
// --------------------------------------------------------------------

/**
 * Formate un post `up2a_formation` dans la forme de données attendue par
 * le reste du plugin (inchangée depuis la version "tableau statique", pour
 * ne pas avoir à toucher au rendu/JS/CSS lors de cette migration).
 */
function up2a_formations_format_post( \WP_Post $post ): array {
	$termes        = get_the_terms( $post->ID, 'up2a_faculte' );
	$terme         = ( is_array( $termes ) && ! empty( $termes ) ) ? $termes[0] : null;
	$faculte       = $terme ? $terme->name : '';
	$faculte_full  = $terme ? ( get_term_meta( $terme->term_id, 'nom_complet', true ) ?: $terme->name ) : '';
	$debouches_raw = (string) get_post_meta( $post->ID, 'up2a_debouches', true );
	$debouches     = array_values( array_filter( array_map( 'trim', explode( "\n", $debouches_raw ) ) ) );

	$thumb_id = get_post_thumbnail_id( $post );
	$image    = array( 'desktop' => '', 'mobile' => '' );
	if ( $thumb_id ) {
		$desktop = wp_get_attachment_image_url( $thumb_id, 'up2a_formations_desktop' );
		$mobile  = wp_get_attachment_image_url( $thumb_id, 'up2a_formations_mobile' );
		$image   = array(
			'desktop' => $desktop ?: '',
			'mobile'  => $mobile ?: $desktop ?: '',
		);
	}

	return array(
		'id'           => $post->ID,
		'slug'         => $post->post_name,
		'icone'        => (string) get_post_meta( $post->ID, 'up2a_icone', true ) ?: 'book',
		'nom'          => get_the_title( $post ),
		'faculte'      => $faculte,
		'faculte_full' => $faculte_full,
		'intro'        => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
		'programme'    => trim( (string) $post->post_content ) ? apply_filters( 'the_content', $post->post_content ) : '',
		'debouches'    => $debouches,
		'image'        => $image,
	);
}

/**
 * Liste des formations publiées, triées par ordre (Attributs de page →
 * Ordre, dans l'écran d'édition du CPT). Mise en cache pour la durée de
 * la requête : cette fonction est appelée plusieurs fois par page (rendu
 * home, pied de page d'up2a-core, page de détail, synchronisation
 * up2a-preinscription...).
 */
function up2a_formations_formations(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$posts = get_posts(
		array(
			'post_type'      => 'up2a_formation',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	$cache = array_map( 'up2a_formations_format_post', $posts );
	return $cache;
}

/**
 * Retrouve une formation par son slug, pour la page de détail (voir
 * up2a_formations_template_include() plus bas) — évite de dupliquer la
 * boucle de recherche à chaque usage.
 */
function up2a_formations_find( string $slug ): ?array {
	foreach ( up2a_formations_formations() as $formation ) {
		if ( $formation['slug'] === $slug ) {
			return $formation;
		}
	}
	return null;
}

// --------------------------------------------------------------------
// Rendu — cartes + modale (home onepage)
// --------------------------------------------------------------------

function up2a_formations_render(): void {
	$formations = up2a_formations_formations();
	if ( empty( $formations ) ) {
		return;
	}
	// Le module Galerie fournit les vignettes de la modale (choix de
	// présentation, pas une dépendance technique) : si le plugin
	// up2a-galerie n'est pas actif, la modale s'affiche simplement sans
	// bandeau de vignettes plutôt que d'échouer.
	$photos = function_exists( 'up2a_galerie_images' ) ? up2a_galerie_images() : array();
	?>
	<section id="up2a-formations" class="up2a-formations js-formations">
		<?php echo up2a_core_decor( 'formations' ); ?>
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nos formations', 'up2a-formations' ); ?></h2>
			<p class="up2a-formations__hint"><?php esc_html_e( 'Cliquez sur une formation pour en savoir plus', 'up2a-formations' ); ?></p>
			<div class="up2a-formations__grid">
				<?php foreach ( $formations as $f ) : ?>
					<button
						type="button"
						class="up2a-formations__card js-formations-card"
						data-formation="<?php echo esc_attr( $f['slug'] ); ?>"
						data-image="<?php echo esc_url( $f['image']['desktop'] ); ?>"
						data-image-alt="<?php echo esc_attr( $f['nom'] ); ?>"
					>
						<?php if ( ! empty( $f['image']['desktop'] ) ) : ?>
							<picture class="up2a-formations__card-media">
								<source srcset="<?php echo esc_url( $f['image']['mobile'] ); ?>" media="(max-width: 640px)">
								<img
									src="<?php echo esc_url( $f['image']['desktop'] ); ?>"
									alt=""
									loading="lazy"
									decoding="async"
									width="600"
									height="450"
								>
							</picture>
						<?php else : ?>
							<div class="up2a-formations__card-media up2a-formations__card-media--placeholder" aria-hidden="true"></div>
						<?php endif; ?>
						<span class="up2a-formations__badge up2a-formations__badge--<?php echo esc_attr( strtolower( $f['faculte'] ) ); ?>"><?php echo esc_html( $f['faculte'] ); ?></span>
						<span class="up2a-formations__hover-link"><?php esc_html_e( 'Voir la formation', 'up2a-formations' ); ?> <?php echo up2a_core_content_icon( 'arrow' ); ?></span>
						<span class="up2a-formations__card-footer">
							<span class="up2a-formations__card-title"><?php echo esc_html( $f['nom'] ); ?></span>
							<span class="up2a-formations__card-meta"><?php echo up2a_core_content_icon( $f['icone'] ); ?> <?php esc_html_e( 'Licence · 3 ans', 'up2a-formations' ); ?></span>
						</span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<?php foreach ( $formations as $f ) : ?>
			<template class="js-formation-template" data-formation="<?php echo esc_attr( $f['slug'] ); ?>">
				<span class="up2a-formation-modal__badge up2a-formation-modal__badge--<?php echo esc_attr( strtolower( $f['faculte'] ) ); ?>"><?php echo esc_html( $f['faculte'] ); ?></span>
				<h3><?php echo esc_html( $f['nom'] ); ?></h3>
				<p class="up2a-formation-modal__highlight"><?php echo up2a_core_content_icon( $f['icone'] ); ?> <?php esc_html_e( 'Licence · 3 ans', 'up2a-formations' ); ?></p>
				<p class="up2a-formation-modal__faculte"><?php echo up2a_core_icon( 'cap' ); ?> <?php echo esc_html( $f['faculte_full'] ); ?></p>
				<hr class="up2a-formation-modal__divider">
				<?php if ( ! empty( $f['intro'] ) ) : ?>
					<p class="up2a-formation-modal__label"><?php esc_html_e( 'Description', 'up2a-formations' ); ?></p>
					<p class="up2a-formation-modal__intro"><?php echo esc_html( $f['intro'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $f['debouches'] ) ) : ?>
					<p class="up2a-formation-modal__label"><?php esc_html_e( 'Débouchés', 'up2a-formations' ); ?></p>
					<ul class="up2a-formation-modal__debouches">
						<?php foreach ( $f['debouches'] as $debouche ) : ?>
							<li><?php echo up2a_core_content_icon( 'check' ); ?> <span><?php echo esc_html( $debouche ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<div class="up2a-formation-modal__actions">
					<a href="<?php echo esc_url( up2a_core_preinscription_url( $f['slug'] ) ); ?>" class="up2a-hero__cta up2a-hero__cta--accent js-formation-modal-cta"><?php esc_html_e( 'Faire ma préinscription', 'up2a-formations' ); ?> <?php echo up2a_core_content_icon( 'arrow' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/formations/' . $f['slug'] . '/' ) ); ?>" class="up2a-hero__cta up2a-hero__cta--outline"><?php esc_html_e( 'Voir la fiche complète', 'up2a-formations' ); ?></a>
					<button type="button" class="up2a-formation-modal__back js-formation-modal-close"><?php esc_html_e( 'Retour aux formations', 'up2a-formations' ); ?></button>
				</div>
			</template>
		<?php endforeach; ?>

		<div class="up2a-formation-modal js-formation-modal" aria-hidden="true">
			<div class="up2a-formation-modal__backdrop js-formation-modal-close"></div>
			<div class="up2a-formation-modal__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Détail de la formation', 'up2a-formations' ); ?>">
				<button type="button" class="up2a-formation-modal__close js-formation-modal-close" aria-label="<?php esc_attr_e( 'Fermer', 'up2a-formations' ); ?>"><?php echo up2a_core_icon( 'close' ); ?></button>
				<div class="up2a-formation-modal__gallery">
					<div class="up2a-formation-modal__main">
						<img class="js-formation-modal-mainimg" src="" alt="">
						<button type="button" class="up2a-formation-modal__nav up2a-formation-modal__nav--prev js-formation-modal-prev" aria-label="<?php esc_attr_e( 'Photo précédente', 'up2a-formations' ); ?>">‹</button>
						<button type="button" class="up2a-formation-modal__nav up2a-formation-modal__nav--next js-formation-modal-next" aria-label="<?php esc_attr_e( 'Photo suivante', 'up2a-formations' ); ?>">›</button>
					</div>
					<div class="up2a-formation-modal__thumbs js-formation-modal-thumbs">
						<?php foreach ( $photos as $pi => $photo ) : ?>
							<button
								type="button"
								class="up2a-formation-modal__thumb js-formation-modal-thumb"
								data-src="<?php echo esc_url( $photo['desktop'] ); ?>"
								data-alt="<?php echo esc_attr( $photo['alt'] ); ?>"
							>
								<img src="<?php echo esc_url( $photo['mobile'] ); ?>" alt="" loading="lazy" decoding="async">
							</button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="up2a-formation-modal__content js-formation-modal-content"></div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Shortcode `[up2a_formations]` — permet d'insérer la section depuis
 * n'importe quelle page (Elementor "Shortcode", éditeur de blocs...) sans
 * toucher au code. Utilisé par défaut par la home onepage d'up2a-core
 * (voir templates/front-page-onepage.php).
 */
add_shortcode(
	'up2a_formations',
	function (): string {
		ob_start();
		up2a_formations_render();
		return (string) ob_get_clean();
	}
);

// --------------------------------------------------------------------
// Pages de détail — /formations/{slug}/
// --------------------------------------------------------------------
// Le rewrite est désormais géré nativement par le CPT `up2a_formation`
// (voir inc/cpt.php, `'rewrite' => array('slug' => 'formations')`) —
// WordPress gère lui-même l'URL, le 404 et le flush des règles ; il ne
// reste plus qu'à brancher le template de détail sur ce type de contenu.

add_filter(
	'template_include',
	function ( string $template ): string {
		if ( ! is_singular( 'up2a_formation' ) ) {
			return $template;
		}
		$custom = UP2A_FORMATIONS_PATH . 'templates/formation-detail.php';
		return file_exists( $custom ) ? $custom : $template;
	}
);

/**
 * Styles/scripts propres au module Formations, chargés sur la home
 * onepage (cartes + modale) et sur les pages de détail. `up2a-front-page`
 * (styles/utilitaires partagés : boutons, titres de section, décor) est
 * toujours fourni par up2a-core, mais son propre hook d'enregistrement ne
 * tourne que sur la home — on l'enregistre donc aussi ici, pour que les
 * pages de détail (hors template home) l'aient bien en dépendance.
 */
add_action(
	'wp_enqueue_scripts',
	function (): void {
		$is_home   = is_singular( 'page' ) && get_page_template_slug( get_the_ID() ) === 'up2a-core-onepage.php';
		$is_detail = is_singular( 'up2a_formation' );

		if ( ! $is_home && ! $is_detail ) {
			return;
		}

		wp_enqueue_style(
			'up2a-front-page',
			UP2A_CORE_URL . 'assets/css/up2a-front-page.css',
			array( 'up2a-tokens' ),
			UP2A_CORE_VERSION
		);

		up2a_formations_enqueue_shared_assets();

		if ( $is_detail ) {
			wp_enqueue_style(
				'up2a-formation-detail',
				UP2A_FORMATIONS_URL . 'assets/css/up2a-formation-detail.css',
				array( 'up2a-formations' ),
				UP2A_FORMATIONS_VERSION
			);
		}
	}
);
