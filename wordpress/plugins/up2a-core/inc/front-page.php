<?php
/**
 * Template de page "Accueil UP-2A (onepage)" — voir docs/06-storyboard.md.
 *
 * Pourquoi un template de page plutôt qu'un montage Elementor : les scènes
 * animées (Hero, etc.) ont besoin d'un DOM stable et prévisible pour GSAP/
 * ScrollTrigger, plus simple à garantir dans du code versionné que dans un
 * montage de widgets Elementor. Les pages "simples" (formations en détail,
 * contact...) restent prévues en Elementor (docs/03-roadmap.md phase 4+).
 *
 * Sections volontairement absentes pour l'instant : "chiffres clés" (pas
 * de chiffres validés par le client — mieux vaut ne rien afficher que des
 * chiffres inventés) et "actualités" (pas encore de CPT ni d'articles).
 * Voir docs/06-storyboard.md pour le détail.
 *
 * Images : aucune photo n'est utilisée (aucune n'a été fournie — voir
 * CLAUDE.md §3). Les sections qui en attendraient une utilisent soit un
 * dégradé de secours, soit une illustration vectorielle de marque
 * (assets/img/motif-communaute.svg), jamais une fausse photo. Chaque
 * emplacement est prévu pour recevoir une vraie image via une constante
 * `UP2A_*_IMAGE_URL` dès qu'elle sera disponible (voir wordpress/README.md).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Déclare le template auprès de WordPress (sélectionnable dans
 * Pages → Attributs de page → Modèle).
 */
add_filter(
	'theme_page_templates',
	function ( array $templates ): array {
		$templates['up2a-core-onepage.php'] = __( 'Accueil UP-2A (onepage)', 'up2a-core' );
		return $templates;
	}
);

add_filter(
	'template_include',
	function ( string $template ): string {
		if ( is_singular( 'page' ) && get_page_template_slug( get_the_ID() ) === 'up2a-core-onepage.php' ) {
			$custom = UP2A_CORE_PATH . 'templates/front-page-onepage.php';
			if ( file_exists( $custom ) ) {
				return $custom;
			}
		}
		return $template;
	}
);

/**
 * Charge les styles/scripts propres à ce template, uniquement quand il
 * est utilisé (pas sur le reste du site).
 */
add_action(
	'wp_enqueue_scripts',
	function (): void {
		if ( ! is_singular( 'page' ) || get_page_template_slug( get_the_ID() ) !== 'up2a-core-onepage.php' ) {
			return;
		}

		wp_enqueue_style(
			'up2a-front-page',
			UP2A_CORE_URL . 'assets/css/up2a-front-page.css',
			array( 'up2a-tokens' ),
			UP2A_CORE_VERSION
		);

		wp_enqueue_script(
			'up2a-front-page',
			UP2A_CORE_URL . 'assets/js/up2a-front-page.js',
			array( 'up2a-core' ),
			UP2A_CORE_VERSION,
			true
		);
	}
);

/**
 * Diapositives du slider Hero. Par défaut : les deux photos de campus
 * fournies par le client (assets/img/hero-slide-*.webp — versions
 * desktop + mobile déjà optimisées). Remplaçable entièrement via le
 * filtre `up2a_core_hero_slides` (ex. depuis wp-config.php/un mu-plugin)
 * si de nouvelles photos arrivent, sans toucher au code du plugin.
 * Chaque diapositive : ['desktop' => url, 'mobile' => url].
 */
function up2a_core_hero_slides(): array {
	$defaults = array(
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/hero-slide-1.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/hero-slide-1-mobile.webp',
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/hero-slide-2.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/hero-slide-2-mobile.webp',
		),
	);

	return apply_filters( 'up2a_core_hero_slides', $defaults );
}

/**
 * Photo de campus pour la section "Pourquoi choisir l'UP-2A" (bâtiment +
 * étudiants, fournie par le client). Retourne un tableau
 * ['desktop' => url, 'mobile' => url] ou un tableau vide pour revenir à
 * l'illustration de marque (assets/img/motif-communaute.svg) — jamais un
 * espace vide.
 */
function up2a_core_life_image(): array {
	if ( defined( 'UP2A_LIFE_IMAGE_URL' ) ) {
		return array(
			'desktop' => UP2A_LIFE_IMAGE_URL,
			'mobile'  => defined( 'UP2A_LIFE_IMAGE_MOBILE_URL' ) ? UP2A_LIFE_IMAGE_MOBILE_URL : UP2A_LIFE_IMAGE_URL,
		);
	}

	$default = array(
		'desktop' => UP2A_CORE_URL . 'assets/img/pourquoi-photo.webp',
		'mobile'  => UP2A_CORE_URL . 'assets/img/pourquoi-photo-mobile.webp',
	);

	return apply_filters( 'up2a_core_life_image', $default );
}

/**
 * Seconde photo (plus petite, superposée à la première) pour le collage
 * de la section "Pourquoi choisir l'UP-2A". Même logique de secours que
 * up2a_core_life_image() : toujours une vraie photo, jamais un vide.
 */
function up2a_core_life_image_accent(): array {
	if ( defined( 'UP2A_LIFE_IMAGE_ACCENT_URL' ) ) {
		return array(
			'desktop' => UP2A_LIFE_IMAGE_ACCENT_URL,
			'mobile'  => defined( 'UP2A_LIFE_IMAGE_ACCENT_MOBILE_URL' ) ? UP2A_LIFE_IMAGE_ACCENT_MOBILE_URL : UP2A_LIFE_IMAGE_ACCENT_URL,
		);
	}

	$default = array(
		'desktop' => UP2A_CORE_URL . 'assets/img/gallery-campus-facade.webp',
		'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-campus-facade-mobile.webp',
	);

	return apply_filters( 'up2a_core_life_image_accent', $default );
}

/**
 * Date de rentrée académique, affichée en compte à rebours dans le Hero.
 * Par défaut 5 octobre (année courante avancée automatiquement si la date
 * est déjà passée), remplaçable via la constante `UP2A_RENTREE_DATE`
 * (format `Y-m-d` ou `Y-m-d H:i:s`) ou le filtre `up2a_core_rentree_date`.
 */
function up2a_core_rentree_date(): string {
	if ( defined( 'UP2A_RENTREE_DATE' ) ) {
		return UP2A_RENTREE_DATE;
	}

	$year    = (int) gmdate( 'Y' );
	$default = $year . '-10-05 08:00:00';
	if ( strtotime( $default ) < time() ) {
		$default = ( $year + 1 ) . '-10-05 08:00:00';
	}

	return apply_filters( 'up2a_core_rentree_date', $default );
}

/**
 * Formate une date en français sans dépendre de la locale du serveur
 * (`gmdate()` ne traduit pas les noms de mois) — utilisé pour l'étiquette
 * du compte à rebours.
 */
function up2a_core_format_date_fr( int $timestamp ): string {
	$mois = array(
		1 => 'janvier',
		'février',
		'mars',
		'avril',
		'mai',
		'juin',
		'juillet',
		'août',
		'septembre',
		'octobre',
		'novembre',
		'décembre',
	);

	return (int) gmdate( 'j', $timestamp ) . ' ' . $mois[ (int) gmdate( 'n', $timestamp ) ] . ' ' . gmdate( 'Y', $timestamp );
}

/**
 * Petites icônes de contenu (mêmes conventions que up2a_core_icon() dans
 * inc/header.php, mais pour les sections de la home — dessinées à la main,
 * pas de police d'icônes ni de librairie externe).
 */
function up2a_core_content_icon( string $name ): string {
	$icons = array(
		'star'       => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 15 9 22 9.5 16.5 14 18 21 12 17.5 6 21 7.5 14 2 9.5 9 9 12 2Z"/></svg>',
		'book'       => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 6a2 2 0 0 1 2-2h5a3 3 0 0 1 3 3 3 3 0 0 1 3-3h5a2 2 0 0 1 2 2v12a1 1 0 0 1-1 1h-6a2 2 0 0 0-2 2 2 2 0 0 0-2-2H3a1 1 0 0 1-1-1Z"/></svg>',
		'shield'     => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V5Z"/></svg>',
		'globe'      => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18"/><path d="M12 3a15 15 0 0 0 0 18"/></svg>',
		'briefcase'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/></svg>',
		'scale'      => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18"/><path d="M5 7h6M13 7h6"/><path d="M5 7 2 13a3 3 0 0 0 6 0Z"/><path d="M19 7l-3 6a3 3 0 0 0 6 0Z"/><path d="M8 21h8"/></svg>',
		'cpu'        => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg>',
		'megaphone'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9v6h4l6 4V5L7 9Z"/><path d="M16 9a3 3 0 0 1 0 6"/><path d="M19 6a7 7 0 0 1 0 12"/></svg>',
		'clock'      => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>',
		'mail'       => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 6 8 7 8-7"/></svg>',
		'check'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>',
		'file'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg>',
		'arrow'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
		'map-pin-lg' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
		'users'      => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="9" r="3"/><circle cx="16" cy="9" r="3"/><path d="M2 21c0-3.5 2.5-6 6-6s6 2.5 6 6"/><path d="M14 21c0-2.5 1-4.5 3-5.5"/></svg>',
		'truck'      => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="7" width="13" height="10" rx="1"/><path d="M14 10h4l3 3v4h-7z"/><circle cx="5.5" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/></svg>',
	);

	return $icons[ $name ] ?? '';
}

/**
 * Décor de fond par section : formes discrètes (cercles/anneaux) en
 * dégradé de la palette de marque, purement décoratives (aria-hidden),
 * derrière le contenu (voir `.up2a-decor` dans up2a-front-page.css).
 * Un léger parallaxe au scroll leur est appliqué en JS, desktop
 * uniquement (voir up2a-front-page.js et CLAUDE.md §7 : pas d'effet
 * lourd sur mobile).
 */
function up2a_core_decor( string $variant ): string {
	return '<div class="up2a-decor up2a-decor--' . esc_attr( $variant ) . ' js-decor" aria-hidden="true">'
		. '<span class="up2a-decor__shape up2a-decor__shape--blob js-decor-shape"></span>'
		. '<span class="up2a-decor__shape up2a-decor__shape--ring js-decor-shape"></span>'
		. '</div>';
}

// --------------------------------------------------------------------
// Scène 1 — Hero
// --------------------------------------------------------------------

function up2a_core_render_hero(): void {
	$slides = up2a_core_hero_slides();
	?>
	<section class="up2a-hero js-hero" id="up2a-hero">
		<?php if ( $slides ) : ?>
			<style>
				<?php foreach ( $slides as $i => $slide ) : ?>
					.up2a-hero__slide[data-slide="<?php echo (int) $i; ?>"] { background-image: url('<?php echo esc_url_raw( $slide['desktop'] ); ?>'); }
					@media (max-width: 640px) {
						.up2a-hero__slide[data-slide="<?php echo (int) $i; ?>"] { background-image: url('<?php echo esc_url_raw( $slide['mobile'] ); ?>'); }
					}
				<?php endforeach; ?>
			</style>
			<div class="up2a-hero__slides js-hero-bg" aria-hidden="true">
				<?php foreach ( $slides as $i => $slide ) : ?>
					<div class="up2a-hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide="<?php echo (int) $i; ?>"></div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="up2a-hero__slides up2a-hero__slides--fallback js-hero-bg" aria-hidden="true"></div>
		<?php endif; ?>

		<div class="up2a-hero__scrim" aria-hidden="true"></div>

		<div class="up2a-hero__content">
			<p class="up2a-hero__eyebrow"><span></span> UP-2A</p>
			<h1 class="up2a-hero__title js-hero-title">
				<?php esc_html_e( "Former aujourd'hui", 'up2a-core' ); ?><br>
				<span><?php esc_html_e( 'les élites de demain !', 'up2a-core' ); ?></span>
			</h1>
			<p class="up2a-hero__subtitle">
				<?php esc_html_e( 'Une formation de qualité pour construire les compétences, développer les ambitions et préparer les professionnels de demain.', 'up2a-core' ); ?>
			</p>
			<div class="up2a-hero__actions">
				<a href="#up2a-formations" class="up2a-hero__cta up2a-hero__cta--accent js-hero-cta">
					<?php echo up2a_core_icon( 'cap' ); ?>
					<?php esc_html_e( 'Découvrir nos formations', 'up2a-core' ); ?>
					<?php echo up2a_core_content_icon( 'arrow' ); ?>
				</a>
				<a href="#up2a-admissions" class="up2a-hero__cta up2a-hero__cta--outline js-hero-cta">
					<?php echo up2a_core_content_icon( 'file' ); ?>
					<?php esc_html_e( "S'inscrire maintenant", 'up2a-core' ); ?>
					<?php echo up2a_core_content_icon( 'arrow' ); ?>
				</a>
			</div>
		</div>

		<?php if ( count( $slides ) > 1 ) : ?>
			<div class="up2a-hero__dots js-hero-dots">
				<?php foreach ( $slides as $i => $slide ) : ?>
					<button type="button" class="<?php echo 0 === $i ? 'is-active' : ''; ?>" data-slide="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Image %d', 'up2a-core' ), $i + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<?php
	$rentree    = up2a_core_rentree_date();
	$rentree_ts = strtotime( $rentree );
	?>
	<div class="up2a-hero__countdown-wrap">
		<div class="up2a-hero__countdown js-hero-countdown" data-target="<?php echo esc_attr( gmdate( 'c', $rentree_ts ) ); ?>">
			<div class="up2a-hero__countdown-icon" aria-hidden="true"><?php echo up2a_core_content_icon( 'clock' ); ?></div>
			<div class="up2a-hero__countdown-label">
				<span class="up2a-hero__countdown-eyebrow"><?php esc_html_e( 'Rentrée académique', 'up2a-core' ); ?></span>
				<span class="up2a-hero__countdown-date"><?php echo esc_html( up2a_core_format_date_fr( $rentree_ts ) ); ?></span>
			</div>
			<div class="up2a-hero__countdown-stats">
				<div class="up2a-hero__countdown-stat"><span class="js-countdown-days">00</span><small><?php esc_html_e( 'Jours', 'up2a-core' ); ?></small></div>
				<div class="up2a-hero__countdown-stat"><span class="js-countdown-hours">00</span><small><?php esc_html_e( 'Heures', 'up2a-core' ); ?></small></div>
				<div class="up2a-hero__countdown-stat"><span class="js-countdown-minutes">00</span><small><?php esc_html_e( 'Min', 'up2a-core' ); ?></small></div>
				<div class="up2a-hero__countdown-stat"><span class="js-countdown-seconds">00</span><small><?php esc_html_e( 'Sec', 'up2a-core' ); ?></small></div>
			</div>
		</div>
	</div>
	<?php
}

// --------------------------------------------------------------------
// Scène 1 bis — Pourquoi choisir l'UP-2A
// --------------------------------------------------------------------

function up2a_core_render_pourquoi(): void {
	$image  = up2a_core_life_image();
	$accent = up2a_core_life_image_accent();
	$points = array(
		__( 'Encadrement pédagogique de proximité, en petits effectifs', 'up2a-core' ),
		__( 'Formations connectées aux besoins concrets du marché du travail', 'up2a-core' ),
		__( 'Préinscription entièrement en ligne, sans frais de dossier', 'up2a-core' ),
		__( 'Une communauté étudiante ouverte sur l\'Afrique et le monde', 'up2a-core' ),
	);
	?>
	<section id="up2a-pourquoi" class="up2a-pourquoi js-pourquoi">
		<?php echo up2a_core_decor( 'pourquoi' ); ?>
		<div class="up2a-section-inner up2a-pourquoi__grid">
			<div class="up2a-pourquoi__media">
				<div class="up2a-pourquoi__collage">
					<div class="up2a-pourquoi__collage-main">
						<?php if ( ! empty( $image['desktop'] ) ) : ?>
							<picture>
								<source srcset="<?php echo esc_url( $image['mobile'] ); ?>" media="(max-width: 640px)">
								<img
									src="<?php echo esc_url( $image['desktop'] ); ?>"
									alt="<?php esc_attr_e( "Étudiants de l'UP-2A devant le campus", 'up2a-core' ); ?>"
									loading="lazy"
									width="700"
									height="620"
								>
							</picture>
						<?php else : ?>
							<img
								src="<?php echo esc_url( UP2A_CORE_URL . 'assets/img/motif-communaute.svg' ); ?>"
								alt="<?php esc_attr_e( 'Illustration de la communauté étudiante UP-2A', 'up2a-core' ); ?>"
								loading="lazy"
								width="580"
								height="190"
							>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $accent['desktop'] ) ) : ?>
						<div class="up2a-pourquoi__collage-accent">
							<picture>
								<source srcset="<?php echo esc_url( $accent['mobile'] ); ?>" media="(max-width: 640px)">
								<img
									src="<?php echo esc_url( $accent['desktop'] ); ?>"
									alt="<?php esc_attr_e( "Le campus de l'UP-2A", 'up2a-core' ); ?>"
									loading="lazy"
									width="320"
									height="280"
								>
							</picture>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="up2a-pourquoi__text">
				<h2 class="up2a-section-title up2a-section-title--left"><?php esc_html_e( 'Pourquoi choisir l\'UP-2A', 'up2a-core' ); ?></h2>
				<p><?php esc_html_e( 'Fondée par une association engagée pour l\'éducation, l\'Université Privée An-Nahdah d\'Afrique accompagne chaque étudiant vers l\'excellence académique et professionnelle, dans un cadre exigeant et bienveillant.', 'up2a-core' ); ?></p>
				<p><?php esc_html_e( 'À Ouagadougou, au cœur du Burkina Faso, nous formons une nouvelle génération de diplômés capables de répondre aux défis économiques, sociaux et technologiques de l\'Afrique de demain.', 'up2a-core' ); ?></p>
				<ul class="up2a-pourquoi__points">
					<?php foreach ( $points as $point ) : ?>
						<li><span class="up2a-pourquoi__points-icon"><?php echo up2a_core_content_icon( 'check' ); ?></span> <span><?php echo esc_html( $point ); ?></span></li>
					<?php endforeach; ?>
				</ul>
				<a href="#up2a-formations" class="up2a-hero__cta up2a-hero__cta--accent">
					<?php esc_html_e( 'Voir nos formations', 'up2a-core' ); ?>
					<?php echo up2a_core_content_icon( 'arrow' ); ?>
				</a>
			</div>
		</div>
	</section>
	<?php
}

// --------------------------------------------------------------------
// Scène 2 — Valeurs
// --------------------------------------------------------------------

function up2a_core_render_valeurs(): void {
	$valeurs = array(
		array(
			'icone'   => 'star',
			'couleur' => 'accent',
			'titre'   => __( 'Excellence', 'up2a-core' ),
			'texte'   => __( 'Une exigence académique constante, portée par un corps enseignant qualifié et des méthodes pédagogiques rigoureuses.', 'up2a-core' ),
		),
		array(
			'icone'   => 'book',
			'couleur' => 'teal',
			'titre'   => __( 'Savoir', 'up2a-core' ),
			'texte'   => __( 'Une formation ancrée dans les savoirs fondamentaux et les compétences pratiques attendues par le monde professionnel.', 'up2a-core' ),
		),
		array(
			'icone'   => 'shield',
			'couleur' => 'primary',
			'titre'   => __( 'Intégrité', 'up2a-core' ),
			'texte'   => __( 'Une éducation qui forme des femmes et des hommes responsables, honnêtes et engagés envers leur communauté.', 'up2a-core' ),
		),
		array(
			'icone'   => 'globe',
			'couleur' => 'red',
			'titre'   => __( 'Ouverture', 'up2a-core' ),
			'texte'   => __( 'Une université tournée vers l\'Afrique et le monde, accueillante pour tous les profils d\'étudiants.', 'up2a-core' ),
		),
	);
	?>
	<section id="up2a-valeurs" class="up2a-values js-values">
		<?php echo up2a_core_decor( 'valeurs' ); ?>
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nos valeurs', 'up2a-core' ); ?></h2>
			<div class="up2a-values__grid">
				<?php foreach ( $valeurs as $v ) : ?>
					<div class="up2a-values__card js-values-card">
						<div class="up2a-values__icon up2a-values__icon--<?php echo esc_attr( $v['couleur'] ); ?>"><?php echo up2a_core_content_icon( $v['icone'] ); ?></div>
						<h3><?php echo esc_html( $v['titre'] ); ?></h3>
						<p><?php echo esc_html( $v['texte'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

// --------------------------------------------------------------------
// Scène 3 — Formations en un coup d'œil (cartes flip)
// --------------------------------------------------------------------
// Intitulés confirmés par le client : 2 facultés, 4 licences (voir
// docs/05-contenus.md). Repris à l'identique dans
// supabase/migrations/0003_seed.sql. À terme (docs/03-roadmap.md
// phase 4), ce bloc lira un CPT "Formation" plutôt que ce tableau statique.

function up2a_core_formations(): array {
	$defaults = array(
		array(
			'slug'         => 'droit-public',
			'icone'        => 'scale',
			'nom'          => __( 'Licence en Droit Public', 'up2a-core' ),
			'faculte'      => 'SJPA',
			'faculte_full' => __( "Sciences Juridiques, Politiques et de l'Administration (SJPA)", 'up2a-core' ),
			'intro'        => __( 'Droit constitutionnel, administratif et institutions publiques.', 'up2a-core' ),
			'debouches'    => array(
				__( 'Administration publique', 'up2a-core' ),
				__( 'Fonction publique', 'up2a-core' ),
				__( 'Collectivités territoriales', 'up2a-core' ),
			),
			'image'        => array(
				'desktop' => UP2A_CORE_URL . 'assets/img/hero-slide-1.webp',
				'mobile'  => UP2A_CORE_URL . 'assets/img/hero-slide-1-mobile.webp',
			),
		),
		array(
			'slug'         => 'droit-prive',
			'icone'        => 'users',
			'nom'          => __( 'Licence en Droit Privé', 'up2a-core' ),
			'faculte'      => 'SJPA',
			'faculte_full' => __( "Sciences Juridiques, Politiques et de l'Administration (SJPA)", 'up2a-core' ),
			'intro'        => __( 'Droit civil, des affaires et des contrats.', 'up2a-core' ),
			'debouches'    => array(
				__( "Droit d'entreprise", 'up2a-core' ),
				__( 'Conseil juridique', 'up2a-core' ),
				__( 'Professions judiciaires', 'up2a-core' ),
			),
			'image'        => array(
				'desktop' => UP2A_CORE_URL . 'assets/img/gallery-etudiants-batiment.webp',
				'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-etudiants-batiment-mobile.webp',
			),
		),
		array(
			'slug'         => 'logistique-internationale',
			'icone'        => 'truck',
			'nom'          => __( 'Licence en Logistique Internationale', 'up2a-core' ),
			'faculte'      => 'SEG',
			'faculte_full' => __( 'Sciences Économiques et de Gestion (SEG)', 'up2a-core' ),
			'intro'        => __( "Transport, chaîne d'approvisionnement et commerce international.", 'up2a-core' ),
			'debouches'    => array(
				__( 'Logistique & transport', 'up2a-core' ),
				__( "Chaîne d'approvisionnement", 'up2a-core' ),
				__( 'Commerce international', 'up2a-core' ),
			),
			'image'        => array(
				'desktop' => UP2A_CORE_URL . 'assets/img/hero-slide-2.webp',
				'mobile'  => UP2A_CORE_URL . 'assets/img/hero-slide-2-mobile.webp',
			),
		),
		array(
			'slug'         => 'marketing-communication',
			'icone'        => 'megaphone',
			'nom'          => __( 'Licence en Marketing Communication', 'up2a-core' ),
			'faculte'      => 'SEG',
			'faculte_full' => __( 'Sciences Économiques et de Gestion (SEG)', 'up2a-core' ),
			'intro'        => __( 'Stratégie de marque, communication et marketing digital.', 'up2a-core' ),
			'debouches'    => array(
				__( 'Marketing', 'up2a-core' ),
				__( "Communication d'entreprise", 'up2a-core' ),
				__( 'Stratégie de marque', 'up2a-core' ),
			),
			'image'        => array(
				'desktop' => UP2A_CORE_URL . 'assets/img/gallery-campus-facade.webp',
				'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-campus-facade-mobile.webp',
			),
		),
	);

	$formations = apply_filters( 'up2a_core_formations', $defaults );

	// Garde-fou : si un filtre externe (autre extension, code du thème)
	// renvoie une valeur vide ou invalide, on retombe sur les 4 licences
	// par défaut plutôt que d'afficher une section vide — voir
	// docs/06-storyboard.md "Corrections et ajouts".
	if ( ! is_array( $formations ) || empty( $formations ) ) {
		return $defaults;
	}

	return $formations;
}

function up2a_core_render_formations(): void {
	$formations = up2a_core_formations();
	$photos     = up2a_core_gallery_images();
	?>
	<section id="up2a-formations" class="up2a-formations js-formations">
		<?php echo up2a_core_decor( 'formations' ); ?>
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nos formations', 'up2a-core' ); ?></h2>
			<p class="up2a-formations__hint"><?php esc_html_e( 'Cliquez sur une formation pour en savoir plus', 'up2a-core' ); ?></p>
			<div class="up2a-formations__grid">
				<?php foreach ( $formations as $f ) : ?>
					<button
						type="button"
						class="up2a-formations__card js-formations-card"
						data-formation="<?php echo esc_attr( $f['slug'] ); ?>"
						data-image="<?php echo esc_url( $f['image']['desktop'] ); ?>"
						data-image-alt="<?php echo esc_attr( $f['nom'] ); ?>"
					>
						<picture class="up2a-formations__card-media">
							<source srcset="<?php echo esc_url( $f['image']['mobile'] ); ?>" media="(max-width: 640px)">
							<img
								src="<?php echo esc_url( $f['image']['desktop'] ); ?>"
								alt=""
								decoding="async"
								width="600"
								height="450"
							>
						</picture>
						<span class="up2a-formations__badge up2a-formations__badge--<?php echo esc_attr( strtolower( $f['faculte'] ) ); ?>"><?php echo esc_html( $f['faculte'] ); ?></span>
						<span class="up2a-formations__hover-link"><?php esc_html_e( 'Voir la formation', 'up2a-core' ); ?> <?php echo up2a_core_content_icon( 'arrow' ); ?></span>
						<span class="up2a-formations__card-footer">
							<span class="up2a-formations__card-title"><?php echo esc_html( $f['nom'] ); ?></span>
							<span class="up2a-formations__card-meta"><?php echo up2a_core_content_icon( $f['icone'] ); ?> <?php esc_html_e( 'Licence · 3 ans', 'up2a-core' ); ?></span>
						</span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<?php foreach ( $formations as $f ) : ?>
			<template class="js-formation-template" data-formation="<?php echo esc_attr( $f['slug'] ); ?>">
				<span class="up2a-formation-modal__badge up2a-formation-modal__badge--<?php echo esc_attr( strtolower( $f['faculte'] ) ); ?>"><?php echo esc_html( $f['faculte'] ); ?></span>
				<h3><?php echo esc_html( $f['nom'] ); ?></h3>
				<p class="up2a-formation-modal__highlight"><?php echo up2a_core_content_icon( $f['icone'] ); ?> <?php esc_html_e( 'Licence · 3 ans', 'up2a-core' ); ?></p>
				<p class="up2a-formation-modal__faculte"><?php echo up2a_core_icon( 'cap' ); ?> <?php echo esc_html( $f['faculte_full'] ); ?></p>
				<hr class="up2a-formation-modal__divider">
				<p class="up2a-formation-modal__label"><?php esc_html_e( 'Description', 'up2a-core' ); ?></p>
				<p class="up2a-formation-modal__intro"><?php echo esc_html( $f['intro'] ); ?></p>
				<p class="up2a-formation-modal__label"><?php esc_html_e( 'Débouchés', 'up2a-core' ); ?></p>
				<ul class="up2a-formation-modal__debouches">
					<?php foreach ( $f['debouches'] as $debouche ) : ?>
						<li><?php echo up2a_core_content_icon( 'check' ); ?> <span><?php echo esc_html( $debouche ); ?></span></li>
					<?php endforeach; ?>
				</ul>
				<div class="up2a-formation-modal__actions">
					<a href="#up2a-admissions" class="up2a-hero__cta up2a-hero__cta--accent js-formation-modal-cta"><?php esc_html_e( 'Faire ma préinscription', 'up2a-core' ); ?> <?php echo up2a_core_content_icon( 'arrow' ); ?></a>
					<button type="button" class="up2a-formation-modal__back js-formation-modal-close"><?php esc_html_e( 'Retour aux formations', 'up2a-core' ); ?></button>
				</div>
			</template>
		<?php endforeach; ?>

		<div class="up2a-formation-modal js-formation-modal" aria-hidden="true">
			<div class="up2a-formation-modal__backdrop js-formation-modal-close"></div>
			<div class="up2a-formation-modal__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Détail de la formation', 'up2a-core' ); ?>">
				<button type="button" class="up2a-formation-modal__close js-formation-modal-close" aria-label="<?php esc_attr_e( 'Fermer', 'up2a-core' ); ?>"><?php echo up2a_core_icon( 'close' ); ?></button>
				<div class="up2a-formation-modal__gallery">
					<div class="up2a-formation-modal__main">
						<img class="js-formation-modal-mainimg" src="" alt="">
						<button type="button" class="up2a-formation-modal__nav up2a-formation-modal__nav--prev js-formation-modal-prev" aria-label="<?php esc_attr_e( 'Photo précédente', 'up2a-core' ); ?>">‹</button>
						<button type="button" class="up2a-formation-modal__nav up2a-formation-modal__nav--next js-formation-modal-next" aria-label="<?php esc_attr_e( 'Photo suivante', 'up2a-core' ); ?>">›</button>
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

// --------------------------------------------------------------------
// Scène 3 bis — Galerie
// --------------------------------------------------------------------
// Photos disponibles à ce jour (campus + vie étudiante, fournies par le
// client). D'autres arriveront progressivement (voir CLAUDE.md §3) : ce
// tableau est filtrable via `up2a_core_gallery_images` pour en ajouter
// sans toucher au code du plugin.

function up2a_core_gallery_images(): array {
	$defaults = array(
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/hero-slide-1.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/hero-slide-1-mobile.webp',
			'alt'     => __( "Le campus de l'UP-2A", 'up2a-core' ),
			'legende' => __( 'Le campus', 'up2a-core' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-etudiants-batiment.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-etudiants-batiment-mobile.webp',
			'alt'     => __( "Étudiants de l'UP-2A devant le bâtiment", 'up2a-core' ),
			'legende' => __( 'Vie étudiante', 'up2a-core' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/hero-slide-2.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/hero-slide-2-mobile.webp',
			'alt'     => __( "Le bâtiment de l'UP-2A", 'up2a-core' ),
			'legende' => __( 'Le bâtiment', 'up2a-core' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-campus-facade.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-campus-facade-mobile.webp',
			'alt'     => __( "Façade principale du campus de l'UP-2A", 'up2a-core' ),
			'legende' => __( 'La façade principale', 'up2a-core' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/pourquoi-photo.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/pourquoi-photo-mobile.webp',
			'alt'     => __( "Étudiants devant le campus de l'UP-2A", 'up2a-core' ),
			'legende' => __( 'Nos étudiants', 'up2a-core' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-campus-angle.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-campus-angle-mobile.webp',
			'alt'     => __( "Vue d'angle du campus de l'UP-2A", 'up2a-core' ),
			'legende' => __( 'Le campus, vue latérale', 'up2a-core' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-campus-perspective.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-campus-perspective-mobile.webp',
			'alt'     => __( "Architecture du bâtiment de l'UP-2A", 'up2a-core' ),
			'legende' => __( "L'architecture", 'up2a-core' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/gallery-vie-etudiante-groupe.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/gallery-vie-etudiante-groupe-mobile.webp',
			'alt'     => __( "Groupe d'étudiants de l'UP-2A devant le campus", 'up2a-core' ),
			'legende' => __( 'Nos étudiants sur le campus', 'up2a-core' ),
		),
	);

	$images = apply_filters( 'up2a_core_gallery_images', $defaults );

	// Même garde-fou que up2a_core_formations() : un filtre externe qui
	// renverrait une liste vide ne doit jamais vider la galerie.
	if ( ! is_array( $images ) || empty( $images ) ) {
		return $defaults;
	}

	return $images;
}

/**
 * Galerie photo (grille + lightbox tactile). N'affiche rien si aucune
 * image n'est disponible (jamais de cadre vide) — voir
 * up2a_core_gallery_images() pour ajouter des photos sans coder.
 */
function up2a_core_render_galerie(): void {
	$images = up2a_core_gallery_images();
	if ( empty( $images ) ) {
		return;
	}
	?>
	<section id="up2a-galerie" class="up2a-galerie js-galerie">
		<?php echo up2a_core_decor( 'galerie' ); ?>
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Galerie', 'up2a-core' ); ?></h2>
			<p class="up2a-galerie__hint"><?php esc_html_e( "Un aperçu du campus et de la vie étudiante à l'UP-2A", 'up2a-core' ); ?></p>
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
							<div class="up2a-galerie__featured-stack js-galerie-featured-stack">
								<?php foreach ( $images as $si => $simg ) : ?>
									<img
										class="up2a-galerie__featured-slide<?php echo 0 === $si ? ' is-active' : ''; ?>"
										src="<?php echo esc_url( $simg['desktop'] ); ?>"
										alt="<?php echo esc_attr( $simg['alt'] ); ?>"
										data-full="<?php echo esc_url( $simg['desktop'] ); ?>"
										data-alt="<?php echo esc_attr( $simg['alt'] ); ?>"
										data-legende="<?php echo esc_attr( $simg['legende'] ?? '' ); ?>"
										loading="<?php echo 0 === $si ? 'eager' : 'lazy'; ?>"
										decoding="async"
									>
								<?php endforeach; ?>
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
			<button type="button" class="up2a-galerie__lightbox-close js-galerie-close" aria-label="<?php esc_attr_e( 'Fermer', 'up2a-core' ); ?>"><?php echo up2a_core_icon( 'close' ); ?></button>
			<button type="button" class="up2a-galerie__lightbox-nav up2a-galerie__lightbox-nav--prev js-galerie-prev" aria-label="<?php esc_attr_e( 'Image précédente', 'up2a-core' ); ?>">‹</button>
			<figure class="up2a-galerie__lightbox-figure">
				<img class="up2a-galerie__lightbox-img js-galerie-lightbox-img" src="" alt="">
				<figcaption class="up2a-galerie__lightbox-caption js-galerie-lightbox-caption"></figcaption>
			</figure>
			<button type="button" class="up2a-galerie__lightbox-nav up2a-galerie__lightbox-nav--next js-galerie-next" aria-label="<?php esc_attr_e( 'Image suivante', 'up2a-core' ); ?>">›</button>
			<p class="up2a-galerie__lightbox-count js-galerie-lightbox-count" aria-hidden="true"></p>
		</div>
	</section>
	<?php
}

// --------------------------------------------------------------------
// Scène 3 ter — Documents (brochure)
// --------------------------------------------------------------------
// Aucun PDF n'a été fourni à ce jour : la section reste en place (jamais
// bloquée sur l'asset manquant, voir CLAUDE.md §3) mais l'affiche
// honnêtement comme "bientôt disponible" plutôt que de pointer vers un
// lien mort. Brancher un vrai PDF via UP2A_BROCHURE_URL (wp-config.php)
// ou le filtre `up2a_core_brochure_url` dès qu'il existe.

function up2a_core_brochure_url(): string {
	if ( defined( 'UP2A_BROCHURE_URL' ) ) {
		return UP2A_BROCHURE_URL;
	}
	return apply_filters( 'up2a_core_brochure_url', '' );
}

function up2a_core_render_documents(): void {
	$url = up2a_core_brochure_url();
	?>
	<section id="up2a-documents" class="up2a-documents js-documents">
		<?php echo up2a_core_decor( 'documents' ); ?>
		<div class="up2a-section-inner up2a-documents__grid">
			<div class="up2a-documents__cover" aria-hidden="true">
				<div class="up2a-documents__cover-page up2a-documents__cover-page--back"></div>
				<div class="up2a-documents__cover-page up2a-documents__cover-page--front">
					<span class="up2a-documents__cover-icon"><?php echo up2a_core_content_icon( 'book' ); ?></span>
					<span class="up2a-documents__cover-title">Brochure<br>UP-2A</span>
					<span class="up2a-documents__cover-sub"><?php esc_html_e( 'Formations & admissions', 'up2a-core' ); ?></span>
				</div>
			</div>
			<div class="up2a-documents__text">
				<h2 class="up2a-section-title up2a-section-title--left"><?php esc_html_e( 'Télécharger nos documents', 'up2a-core' ); ?></h2>
				<p><?php esc_html_e( "Retrouvez l'ensemble de nos formations, nos conditions d'admission et les informations pratiques de l'université dans notre brochure officielle.", 'up2a-core' ); ?></p>
				<ul class="up2a-documents__list">
					<li><?php echo up2a_core_content_icon( 'check' ); ?> <span><?php esc_html_e( 'Présentation des 2 facultés et des 4 licences', 'up2a-core' ); ?></span></li>
					<li><?php echo up2a_core_content_icon( 'check' ); ?> <span><?php esc_html_e( "Conditions d'admission et pièces à fournir", 'up2a-core' ); ?></span></li>
					<li><?php echo up2a_core_content_icon( 'check' ); ?> <span><?php esc_html_e( 'Coordonnées et localisation du campus', 'up2a-core' ); ?></span></li>
				</ul>
				<?php if ( $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>" class="up2a-hero__cta up2a-hero__cta--accent" download>
						<?php echo up2a_core_content_icon( 'file' ); ?>
						<?php esc_html_e( 'Télécharger la brochure (PDF)', 'up2a-core' ); ?>
						<?php echo up2a_core_content_icon( 'arrow' ); ?>
					</a>
				<?php else : ?>
					<span class="up2a-documents__soon" aria-disabled="true">
						<?php echo up2a_core_content_icon( 'clock' ); ?>
						<?php esc_html_e( 'Brochure disponible prochainement', 'up2a-core' ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

// --------------------------------------------------------------------
// Scène 5 — Admissions
// --------------------------------------------------------------------

function up2a_core_render_admissions(): void {
	$etapes = array(
		array(
			'titre' => __( 'Consultez nos formations', 'up2a-core' ),
			'texte' => __( 'Explorez nos 4 licences réparties dans 2 facultés (SJPA et SEG) et trouvez celle qui correspond à votre projet.', 'up2a-core' ),
		),
		array(
			'titre' => __( 'Remplissez le formulaire', 'up2a-core' ),
			'texte' => __( "Complétez le formulaire de préinscription en ligne et joignez vos pièces d'identité et diplômes.", 'up2a-core' ),
		),
		array(
			'titre' => __( 'Recevez la confirmation', 'up2a-core' ),
			'texte' => __( 'Vous recevez une confirmation par e-mail dès que votre dossier est enregistré.', 'up2a-core' ),
		),
		array(
			'titre' => __( 'Suivi de votre dossier', 'up2a-core' ),
			'texte' => __( "L'université étudie votre dossier et vous contacte pour la suite du processus.", 'up2a-core' ),
		),
	);
	$premiere = array_shift( $etapes );
	?>
	<section id="up2a-admissions" class="up2a-admissions js-admissions">
		<?php echo up2a_core_decor( 'admissions' ); ?>
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Comment candidater', 'up2a-core' ); ?></h2>
			<p class="up2a-admissions__note">
				<?php esc_html_e( 'Préinscription 100% en ligne, sans aucun frais à régler.', 'up2a-core' ); ?>
			</p>

			<div class="up2a-admissions__feature js-admissions-step">
				<div class="up2a-admissions__feature-text">
					<h3><span class="up2a-admissions__number">1.</span> <?php echo esc_html( $premiere['titre'] ); ?></h3>
					<p><?php echo esc_html( $premiere['texte'] ); ?></p>
					<a href="#up2a-formations" class="up2a-hero__cta up2a-hero__cta--primary">
						<?php esc_html_e( 'Voir nos formations', 'up2a-core' ); ?>
						<?php echo up2a_core_content_icon( 'arrow' ); ?>
					</a>
				</div>
				<div class="up2a-admissions__feature-visual" aria-hidden="true">
					<?php echo up2a_core_content_icon( 'file' ); ?>
				</div>
			</div>

			<div class="up2a-admissions__grid">
				<?php foreach ( $etapes as $i => $etape ) : ?>
					<div class="up2a-admissions__card js-admissions-step">
						<h3><span class="up2a-admissions__number"><?php echo esc_html( $i + 2 ); ?>.</span> <?php echo esc_html( $etape['titre'] ); ?></h3>
						<p><?php echo esc_html( $etape['texte'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

// --------------------------------------------------------------------
// Scène 7 — CTA final
// --------------------------------------------------------------------

function up2a_core_render_cta_final(): void {
	?>
	<section class="up2a-cta-final js-cta-final">
		<?php echo up2a_core_decor( 'cta-final' ); ?>
		<div class="up2a-section-inner up2a-cta-final__inner">
			<h2><?php esc_html_e( 'Prêt·e à construire votre avenir ?', 'up2a-core' ); ?></h2>
			<a href="#up2a-admissions" class="up2a-hero__cta up2a-hero__cta--accent"><?php esc_html_e( 'Faire ma préinscription', 'up2a-core' ); ?></a>
		</div>
	</section>
	<?php
}

// --------------------------------------------------------------------
// Contact
// --------------------------------------------------------------------

function up2a_core_render_contact(): void {
	?>
	<section id="up2a-contact" class="up2a-contact js-contact">
		<?php echo up2a_core_decor( 'contact' ); ?>
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nous contacter', 'up2a-core' ); ?></h2>
			<p class="up2a-contact__hint"><?php esc_html_e( 'Contactez-nous dès aujourd\'hui pour toute question sur nos formations et admissions.', 'up2a-core' ); ?></p>
			<div class="up2a-contact__panel">
				<div class="up2a-contact__grid">
					<div class="up2a-contact__card">
						<div class="up2a-contact__icon"><?php echo up2a_core_icon( 'phone' ); ?></div>
						<h3><?php esc_html_e( 'Appelez-nous', 'up2a-core' ); ?></h3>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', up2a_core_header_phone() ) ); ?>"><?php echo esc_html( up2a_core_header_phone() ); ?></a>
					</div>
					<div class="up2a-contact__card">
						<div class="up2a-contact__icon"><?php echo up2a_core_content_icon( 'mail' ); ?></div>
						<h3><?php esc_html_e( 'Horaires', 'up2a-core' ); ?></h3>
						<p><?php esc_html_e( 'Lundi – Vendredi, 8h – 17h', 'up2a-core' ); ?></p>
					</div>
					<div class="up2a-contact__card">
						<div class="up2a-contact__icon"><?php echo up2a_core_icon( 'pin' ); ?></div>
						<h3><?php esc_html_e( 'Adresse', 'up2a-core' ); ?></h3>
						<a href="<?php echo esc_url( up2a_core_header_maps_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( up2a_core_header_address() ); ?></a>
					</div>
				</div>
			</div>
			<div class="up2a-contact__map">
				<iframe
					src="<?php echo esc_url( up2a_core_header_maps_embed_url() ); ?>"
					title="<?php esc_attr_e( "Localisation de l'UP-2A", 'up2a-core' ); ?>"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
				></iframe>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Footer de contenu, multi-colonnes (informations, pas le colophon
 * technique du thème — voir wordpress/README.md pour la nuance).
 */
function up2a_core_render_footer(): void {
	$liens = array(
		'#up2a-hero'       => __( 'Accueil', 'up2a-core' ),
		'#up2a-pourquoi'   => __( "Pourquoi l'UP-2A", 'up2a-core' ),
		'#up2a-valeurs'    => __( 'Nos valeurs', 'up2a-core' ),
		'#up2a-formations' => __( 'Formations', 'up2a-core' ),
		'#up2a-galerie'    => __( 'Galerie', 'up2a-core' ),
		'#up2a-admissions' => __( 'Admissions', 'up2a-core' ),
		'#up2a-contact'    => __( 'Contact', 'up2a-core' ),
	);
	$formations = up2a_core_formations();
	?>
	<footer class="up2a-footer">
		<div class="up2a-section-inner up2a-footer__grid">
			<div class="up2a-footer__col up2a-footer__col--brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="up2a-footer__logo">
					<picture>
						<source srcset="<?php echo esc_url( UP2A_CORE_URL . 'assets/img/logo-up2a-footer.webp' ); ?>" type="image/webp">
						<img
							src="<?php echo esc_url( UP2A_CORE_URL . 'assets/img/logo-up2a-footer.png' ); ?>"
							alt="<?php esc_attr_e( "UP-2A — Université Privée An-Nahdah d'Afrique", 'up2a-core' ); ?>"
							loading="lazy"
							width="480"
							height="134"
						>
					</picture>
				</a>
				<p class="up2a-footer__slogan"><?php esc_html_e( 'Former aujourd\'hui les élites de demain.', 'up2a-core' ); ?></p>
				<p class="up2a-footer__contact-line">
					<?php echo up2a_core_icon( 'pin' ); ?>
					<a href="<?php echo esc_url( up2a_core_header_maps_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( up2a_core_header_address() ); ?></a>
				</p>
				<p class="up2a-footer__contact-line">
					<?php echo up2a_core_icon( 'phone' ); ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', up2a_core_header_phone() ) ); ?>"><?php echo esc_html( up2a_core_header_phone() ); ?></a>
				</p>
			</div>
			<div class="up2a-footer__col">
				<p class="up2a-footer__title"><?php esc_html_e( 'Liens rapides', 'up2a-core' ); ?></p>
				<ul class="up2a-footer__list">
					<?php foreach ( $liens as $href => $label ) : ?>
						<li><a href="<?php echo esc_url( $href ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="up2a-footer__col">
				<p class="up2a-footer__title"><?php esc_html_e( 'Nos formations', 'up2a-core' ); ?></p>
				<ul class="up2a-footer__list">
					<?php foreach ( $formations as $f ) : ?>
						<li>
							<a
								href="#up2a-formations"
								class="js-formations-card"
								data-formation="<?php echo esc_attr( $f['slug'] ); ?>"
								data-image="<?php echo esc_url( $f['image']['desktop'] ); ?>"
							><?php echo esc_html( $f['nom'] ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="up2a-footer__bottom">
			<p>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( "Université Privée An-Nahdah d'Afrique (UP-2A)", 'up2a-core' ); ?>
				— <?php esc_html_e( 'Autorisation MESRI n°2026-001647', 'up2a-core' ); ?>
			</p>
		</div>
	</footer>
	<?php
}
