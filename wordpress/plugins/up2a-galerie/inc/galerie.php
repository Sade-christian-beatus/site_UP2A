<?php
/**
 * Lecture et rendu du module "Galerie" — le contenu vit désormais dans le
 * CPT `up2a_photo` (voir inc/cpt.php), modifiable depuis le tableau de
 * bord WordPress (menu "Galerie"). Ce fichier ne fait plus que lire ce
 * CPT et le présenter avec la même forme de données qu'avant
 * (tableau desktop/mobile/alt/legende) — voir docs/06-storyboard.md "CPT
 * Formation depuis le dashboard" (2026-09-26).
 *
 * Dépendance obligatoire : `up2a-core` (icônes `up2a_core_content_icon()`/
 * `up2a_core_icon()`, décor `up2a_core_decor()` — voir up2a-galerie.php
 * "Requires Plugins").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Liste des photos publiées, triées par ordre (Attributs de page → Ordre,
 * dans l'écran d'édition du CPT), avec image définie (une entrée sans
 * photo est ignorée plutôt que d'afficher un cadre cassé). Mise en cache
 * pour la durée de la requête : cette fonction est appelée plusieurs fois
 * par page (rendu galerie + modale Formations pour les vignettes).
 */
function up2a_galerie_images(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$posts = get_posts(
		array(
			'post_type'      => 'up2a_photo',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	$images = array();
	foreach ( $posts as $post ) {
		$thumb_id = get_post_thumbnail_id( $post );
		if ( ! $thumb_id ) {
			continue;
		}
		$desktop = wp_get_attachment_image_url( $thumb_id, 'up2a_galerie_desktop' );
		$mobile  = wp_get_attachment_image_url( $thumb_id, 'up2a_galerie_mobile' );
		if ( ! $desktop ) {
			continue;
		}
		$images[] = array(
			'desktop' => $desktop,
			'mobile'  => $mobile ?: $desktop,
			'alt'     => (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ),
			'legende' => get_the_title( $post ),
		);
	}

	$cache = $images;
	return $cache;
}

/**
 * Galerie photo (grille + lightbox tactile). N'affiche rien si aucune
 * image n'est disponible (jamais de cadre vide) — voir
 * up2a_galerie_images() pour ajouter des photos depuis le tableau de bord.
 */
function up2a_galerie_render(): void {
	$images = up2a_galerie_images();
	if ( empty( $images ) ) {
		return;
	}
	?>
	<section id="up2a-galerie" class="up2a-galerie js-galerie">
		<?php echo up2a_core_decor( 'galerie' ); ?>
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Galerie', 'up2a-galerie' ); ?></h2>
			<p class="up2a-galerie__hint"><?php esc_html_e( "Un aperçu du campus et de la vie étudiante à l'UP-2A", 'up2a-galerie' ); ?></p>
			<div class="up2a-galerie__grid">
				<?php foreach ( $images as $i => $img ) : ?>
					<button
						type="button"
						class="up2a-galerie__item js-galerie-item<?php echo 0 === $i ? ' up2a-galerie__item--featured js-galerie-featured' : ''; ?>"
						data-index="<?php echo (int) $i; ?>"
						data-full="<?php echo esc_url( $img['desktop'] ); ?>"
						data-alt="<?php echo esc_attr( $img['alt'] ); ?>"
						data-legende="<?php echo esc_attr( $img['legende'] ?? '' ); ?>"
					>
						<?php if ( 0 === $i ) : ?>
							<?php
							// Seuls 2 calques image sont rendus (au lieu d'une par
							// photo) : le JS fait défiler le contenu du calque
							// caché avant chaque fondu enchaîné, pour ne charger
							// qu'une photo à l'avance plutôt que les 8 d'un coup
							// (voir CLAUDE.md §7 — mobile/4G).
							$featured_photos = array_map(
								function ( $p ) {
									return array(
										'src'     => $p['desktop'],
										'alt'     => $p['alt'],
										'legende' => $p['legende'] ?? '',
									);
								},
								$images
							);
							$second = $images[1] ?? $images[0];
							?>
							<div class="up2a-galerie__featured-stack js-galerie-featured-stack" data-photos="<?php echo esc_attr( wp_json_encode( $featured_photos ) ); ?>">
								<img
									class="up2a-galerie__featured-slide is-active"
									src="<?php echo esc_url( $img['desktop'] ); ?>"
									alt="<?php echo esc_attr( $img['alt'] ); ?>"
									loading="eager"
									decoding="async"
								>
								<img
									class="up2a-galerie__featured-slide"
									src="<?php echo esc_url( $second['desktop'] ); ?>"
									alt=""
									loading="lazy"
									decoding="async"
								>
							</div>
						<?php else : ?>
							<picture>
								<source srcset="<?php echo esc_url( $img['mobile'] ); ?>" media="(max-width: 640px)">
								<img
									src="<?php echo esc_url( $img['desktop'] ); ?>"
									alt="<?php echo esc_attr( $img['alt'] ); ?>"
									loading="lazy"
									width="600"
									height="450"
								>
							</picture>
						<?php endif; ?>
						<span class="up2a-galerie__caption">
							<span class="up2a-galerie__caption-text js-galerie-caption-text"><?php echo esc_html( $img['legende'] ?? '' ); ?></span>
							<span class="up2a-galerie__zoom" aria-hidden="true"><?php echo up2a_core_content_icon( 'globe' ); ?></span>
						</span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="up2a-galerie__lightbox js-galerie-lightbox" aria-hidden="true">
			<button type="button" class="up2a-galerie__lightbox-close js-galerie-close" aria-label="<?php esc_attr_e( 'Fermer', 'up2a-galerie' ); ?>"><?php echo up2a_core_icon( 'close' ); ?></button>
			<button type="button" class="up2a-galerie__lightbox-nav up2a-galerie__lightbox-nav--prev js-galerie-prev" aria-label="<?php esc_attr_e( 'Image précédente', 'up2a-galerie' ); ?>">‹</button>
			<figure class="up2a-galerie__lightbox-figure">
				<img class="up2a-galerie__lightbox-img js-galerie-lightbox-img" src="" alt="">
				<figcaption class="up2a-galerie__lightbox-caption js-galerie-lightbox-caption"></figcaption>
			</figure>
			<button type="button" class="up2a-galerie__lightbox-nav up2a-galerie__lightbox-nav--next js-galerie-next" aria-label="<?php esc_attr_e( 'Image suivante', 'up2a-galerie' ); ?>">›</button>
			<p class="up2a-galerie__lightbox-count js-galerie-lightbox-count" aria-hidden="true"></p>
		</div>
	</section>
	<?php
}

/**
 * Shortcode `[up2a_galerie]` — permet d'insérer la section depuis
 * n'importe quelle page (Elementor "Shortcode", éditeur de blocs...) sans
 * toucher au code. Utilisé par défaut par la home onepage d'up2a-core
 * (voir templates/front-page-onepage.php).
 */
add_shortcode(
	'up2a_galerie',
	function (): string {
		ob_start();
		up2a_galerie_render();
		return (string) ob_get_clean();
	}
);
