<?php
/**
 * Données et rendu du module "Galerie" — extrait de
 * `up2a-core/inc/front-page.php` (voir docs/06-storyboard.md et
 * docs/03-roadmap.md "Scission Formations/Galerie"). Photos disponibles à
 * ce jour (campus + vie étudiante, fournies par le client). D'autres
 * arriveront progressivement (voir CLAUDE.md §3) : le tableau ci-dessous
 * est filtrable via `up2a_galerie_images` pour en ajouter sans toucher au
 * code du plugin.
 *
 * Dépendance obligatoire : `up2a-core` (icônes `up2a_core_content_icon()`/
 * `up2a_core_icon()`, décor `up2a_core_decor()`, constante `UP2A_CORE_URL`
 * pour les photos — voir up2a-galerie.php "Requires Plugins"). Les photos
 * restent physiquement dans up2a-core/assets/img/ : ce sont les mêmes
 * visuels de campus déjà réutilisés par le Hero et les Formations, pas des
 * assets propres à ce module.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function up2a_galerie_images(): array {
	$defaults = array(
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/hero-slide-1.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/hero-slide-1-mobile.webp',
			'alt'     => __( "Le campus de l'UP-2A", 'up2a-galerie' ),
			'legende' => __( 'Le campus', 'up2a-galerie' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-etudiants-batiment.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-etudiants-batiment-mobile.webp',
			'alt'     => __( "Étudiants de l'UP-2A devant le bâtiment", 'up2a-galerie' ),
			'legende' => __( 'Vie étudiante', 'up2a-galerie' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/hero-slide-2.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/hero-slide-2-mobile.webp',
			'alt'     => __( "Le bâtiment de l'UP-2A", 'up2a-galerie' ),
			'legende' => __( 'Le bâtiment', 'up2a-galerie' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-campus-facade.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-campus-facade-mobile.webp',
			'alt'     => __( "Façade principale du campus de l'UP-2A", 'up2a-galerie' ),
			'legende' => __( 'La façade principale', 'up2a-galerie' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/pourquoi-photo.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/pourquoi-photo-mobile.webp',
			'alt'     => __( "Étudiants devant le campus de l'UP-2A", 'up2a-galerie' ),
			'legende' => __( 'Nos étudiants', 'up2a-galerie' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-campus-angle.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-campus-angle-mobile.webp',
			'alt'     => __( "Vue d'angle du campus de l'UP-2A", 'up2a-galerie' ),
			'legende' => __( 'Le campus, vue latérale', 'up2a-galerie' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-campus-perspective.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-campus-perspective-mobile.webp',
			'alt'     => __( "Architecture du bâtiment de l'UP-2A", 'up2a-galerie' ),
			'legende' => __( "L'architecture", 'up2a-galerie' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-vie-etudiante-groupe.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-vie-etudiante-groupe-mobile.webp',
			'alt'     => __( "Groupe d'étudiants de l'UP-2A devant le campus", 'up2a-galerie' ),
			'legende' => __( 'Nos étudiants sur le campus', 'up2a-galerie' ),
		),
	);

	$images = apply_filters( 'up2a_galerie_images', $defaults );

	// Garde-fou : un filtre externe qui renverrait une liste vide ne doit
	// jamais vider la galerie.
	if ( ! is_array( $images ) || empty( $images ) ) {
		return $defaults;
	}

	return $images;
}

/**
 * Galerie photo (grille + lightbox tactile). N'affiche rien si aucune
 * image n'est disponible (jamais de cadre vide) — voir
 * up2a_galerie_images() pour ajouter des photos sans coder.
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
