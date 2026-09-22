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
		<div class="up2a-hero__wave" aria-hidden="true">
			<svg viewBox="0 0 220 100" preserveAspectRatio="none"><path d="M0,100 L0,55 C35,20 70,0 110,15 C150,30 175,10 220,0 L220,100 Z" fill="var(--up2a-color-accent)" fill-opacity="0.9"/><path d="M0,100 L0,75 C45,55 85,45 130,60 C165,72 195,55 220,40 L220,100 Z" fill="var(--up2a-color-primary-dark)" fill-opacity="0.55"/></svg>
		</div>

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
			<ul class="up2a-hero__badges">
				<li><?php echo up2a_core_content_icon( 'check' ); ?> <?php esc_html_e( 'Préinscription 100% en ligne', 'up2a-core' ); ?></li>
				<li><?php echo up2a_core_content_icon( 'check' ); ?> <?php esc_html_e( 'Aucun frais de dossier', 'up2a-core' ); ?></li>
				<li><?php echo up2a_core_content_icon( 'check' ); ?> <?php esc_html_e( '4 licences, Ouagadougou', 'up2a-core' ); ?></li>
			</ul>
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
}

// --------------------------------------------------------------------
// Scène 1 bis — Pourquoi choisir l'UP-2A
// --------------------------------------------------------------------

function up2a_core_render_pourquoi(): void {
	$image  = up2a_core_life_image();
	$points = array(
		__( 'Encadrement pédagogique de proximité, en petits effectifs', 'up2a-core' ),
		__( 'Formations connectées aux besoins concrets du marché du travail', 'up2a-core' ),
		__( 'Préinscription entièrement en ligne, sans frais de dossier', 'up2a-core' ),
		__( 'Une communauté étudiante ouverte sur l\'Afrique et le monde', 'up2a-core' ),
	);
	?>
	<section class="up2a-pourquoi js-pourquoi">
		<div class="up2a-section-inner up2a-pourquoi__grid">
			<div class="up2a-pourquoi__text">
				<h2 class="up2a-section-title up2a-section-title--left"><?php esc_html_e( 'Pourquoi choisir l\'UP-2A', 'up2a-core' ); ?></h2>
				<p><?php esc_html_e( 'Fondée par une association engagée pour l\'éducation, l\'Université Privée An-Nahdah d\'Afrique accompagne chaque étudiant vers l\'excellence académique et professionnelle, dans un cadre exigeant et bienveillant.', 'up2a-core' ); ?></p>
				<p><?php esc_html_e( 'À Ouagadougou, au cœur du Burkina Faso, nous formons une nouvelle génération de diplômés capables de répondre aux défis économiques, sociaux et technologiques de l\'Afrique de demain.', 'up2a-core' ); ?></p>
				<ul class="up2a-pourquoi__points">
					<?php foreach ( $points as $point ) : ?>
						<li><?php echo up2a_core_content_icon( 'check' ); ?> <span><?php echo esc_html( $point ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="up2a-pourquoi__media">
				<?php if ( ! empty( $image['desktop'] ) ) : ?>
					<picture>
						<source srcset="<?php echo esc_url( $image['mobile'] ); ?>" media="(max-width: 640px)">
						<img
							src="<?php echo esc_url( $image['desktop'] ); ?>"
							alt="<?php esc_attr_e( "Étudiants de l'UP-2A devant le campus", 'up2a-core' ); ?>"
							loading="lazy"
							width="972"
							height="642"
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
			'icone' => 'star',
			'titre' => __( 'Excellence', 'up2a-core' ),
			'texte' => __( 'Une exigence académique constante, portée par un corps enseignant qualifié et des méthodes pédagogiques rigoureuses.', 'up2a-core' ),
		),
		array(
			'icone' => 'book',
			'titre' => __( 'Savoir', 'up2a-core' ),
			'texte' => __( 'Une formation ancrée dans les savoirs fondamentaux et les compétences pratiques attendues par le monde professionnel.', 'up2a-core' ),
		),
		array(
			'icone' => 'shield',
			'titre' => __( 'Intégrité', 'up2a-core' ),
			'texte' => __( 'Une éducation qui forme des femmes et des hommes responsables, honnêtes et engagés envers leur communauté.', 'up2a-core' ),
		),
		array(
			'icone' => 'globe',
			'titre' => __( 'Ouverture', 'up2a-core' ),
			'texte' => __( 'Une université tournée vers l\'Afrique et le monde, accueillante pour tous les profils d\'étudiants.', 'up2a-core' ),
		),
	);
	?>
	<section class="up2a-values js-values">
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nos valeurs', 'up2a-core' ); ?></h2>
			<div class="up2a-values__grid">
				<?php foreach ( $valeurs as $v ) : ?>
					<div class="up2a-values__card js-values-card">
						<div class="up2a-values__icon"><?php echo up2a_core_content_icon( $v['icone'] ); ?></div>
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

function up2a_core_render_formations(): void {
	$formations = array(
		array(
			'icone'   => 'scale',
			'nom'     => __( 'Licence en Droit Public', 'up2a-core' ),
			'faculte' => __( "Sciences Juridiques, Politiques et de l'Administration (SJPA)", 'up2a-core' ),
			'texte'   => __( "Droit constitutionnel, administratif et institutions publiques — pour les métiers de l'administration, de la fonction publique et des collectivités.", 'up2a-core' ),
		),
		array(
			'icone'   => 'users',
			'nom'     => __( 'Licence en Droit Privé', 'up2a-core' ),
			'faculte' => __( "Sciences Juridiques, Politiques et de l'Administration (SJPA)", 'up2a-core' ),
			'texte'   => __( 'Droit civil, des affaires et des contrats — pour les métiers du droit, du conseil juridique et des professions judiciaires.', 'up2a-core' ),
		),
		array(
			'icone'   => 'truck',
			'nom'     => __( 'Licence en Logistique Internationale', 'up2a-core' ),
			'faculte' => __( 'Sciences Économiques et de Gestion (SEG)', 'up2a-core' ),
			'texte'   => __( "Transport, chaîne d'approvisionnement et commerce international — pour les métiers de la logistique et des échanges.", 'up2a-core' ),
		),
		array(
			'icone'   => 'megaphone',
			'nom'     => __( 'Licence en Marketing Communication', 'up2a-core' ),
			'faculte' => __( 'Sciences Économiques et de Gestion (SEG)', 'up2a-core' ),
			'texte'   => __( 'Stratégie de marque, communication et marketing digital — pour les métiers du marketing et de la communication d\'entreprise.', 'up2a-core' ),
		),
	);
	?>
	<section id="up2a-formations" class="up2a-formations js-formations">
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nos formations', 'up2a-core' ); ?></h2>
			<p class="up2a-formations__hint"><?php esc_html_e( 'Survolez (ou touchez) une carte pour en savoir plus', 'up2a-core' ); ?></p>
			<div class="up2a-formations__grid">
				<?php foreach ( $formations as $f ) : ?>
					<div class="up2a-formations__card js-formations-card">
						<div class="up2a-formations__flip">
							<div class="up2a-formations__face up2a-formations__face--front">
								<div class="up2a-formations__icon"><?php echo up2a_core_content_icon( $f['icone'] ); ?></div>
								<p class="up2a-formations__faculte"><?php echo esc_html( $f['faculte'] ); ?></p>
								<h3><?php echo esc_html( $f['nom'] ); ?></h3>
							</div>
							<div class="up2a-formations__face up2a-formations__face--back">
								<h3><?php echo esc_html( $f['nom'] ); ?></h3>
								<p class="up2a-formations__texte"><?php echo esc_html( $f['texte'] ); ?></p>
								<a href="#up2a-admissions" class="up2a-formations__link"><?php esc_html_e( 'Faire ma préinscription', 'up2a-core' ); ?> →</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
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
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/hero-slide-2.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/hero-slide-2-mobile.webp',
			'alt'     => __( "Le bâtiment de l'UP-2A", 'up2a-core' ),
		),
		array(
			'desktop' => UP2A_CORE_URL . 'assets/img/pourquoi-photo.webp',
			'mobile'  => UP2A_CORE_URL . 'assets/img/pourquoi-photo-mobile.webp',
			'alt'     => __( "Étudiants devant le campus de l'UP-2A", 'up2a-core' ),
		),
	);

	return apply_filters( 'up2a_core_gallery_images', $defaults );
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
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Galerie', 'up2a-core' ); ?></h2>
			<p class="up2a-galerie__hint"><?php esc_html_e( "Un aperçu du campus et de la vie étudiante à l'UP-2A", 'up2a-core' ); ?></p>
			<div class="up2a-galerie__grid">
				<?php foreach ( $images as $i => $img ) : ?>
					<button
						type="button"
						class="up2a-galerie__item js-galerie-item"
						data-index="<?php echo (int) $i; ?>"
						data-full="<?php echo esc_url( $img['desktop'] ); ?>"
						data-alt="<?php echo esc_attr( $img['alt'] ); ?>"
					>
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
						<span class="up2a-galerie__zoom" aria-hidden="true"><?php echo up2a_core_content_icon( 'globe' ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="up2a-galerie__lightbox js-galerie-lightbox" aria-hidden="true">
			<button type="button" class="up2a-galerie__lightbox-close js-galerie-close" aria-label="<?php esc_attr_e( 'Fermer', 'up2a-core' ); ?>"><?php echo up2a_core_icon( 'close' ); ?></button>
			<button type="button" class="up2a-galerie__lightbox-nav up2a-galerie__lightbox-nav--prev js-galerie-prev" aria-label="<?php esc_attr_e( 'Image précédente', 'up2a-core' ); ?>">‹</button>
			<img class="up2a-galerie__lightbox-img js-galerie-lightbox-img" src="" alt="">
			<button type="button" class="up2a-galerie__lightbox-nav up2a-galerie__lightbox-nav--next js-galerie-next" aria-label="<?php esc_attr_e( 'Image suivante', 'up2a-core' ); ?>">›</button>
		</div>
	</section>
	<?php
}

// --------------------------------------------------------------------
// Scène 5 — Admissions
// --------------------------------------------------------------------

function up2a_core_render_admissions(): void {
	$etapes = array(
		__( 'Je consulte les formations disponibles.', 'up2a-core' ),
		__( "Je remplis le formulaire de préinscription en ligne (pièces d'identité et diplômes à joindre).", 'up2a-core' ),
		__( 'Je reçois une confirmation par e-mail.', 'up2a-core' ),
		__( "L'université étudie mon dossier et me contacte pour la suite.", 'up2a-core' ),
	);
	?>
	<section id="up2a-admissions" class="up2a-admissions js-admissions">
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Comment candidater', 'up2a-core' ); ?></h2>
			<p class="up2a-admissions__note">
				<?php esc_html_e( 'Préinscription 100% en ligne, sans aucun frais à régler.', 'up2a-core' ); ?>
			</p>
			<ol class="up2a-admissions__steps">
				<?php foreach ( $etapes as $i => $etape ) : ?>
					<li class="js-admissions-step">
						<span class="up2a-admissions__step-number"><?php echo esc_html( $i + 1 ); ?></span>
						<span><?php echo esc_html( $etape ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
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
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nous contacter', 'up2a-core' ); ?></h2>
			<div class="up2a-contact__grid">
				<div class="up2a-contact__card">
					<div class="up2a-contact__icon"><?php echo up2a_core_icon( 'phone' ); ?></div>
					<h3><?php esc_html_e( 'Téléphone', 'up2a-core' ); ?></h3>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', up2a_core_header_phone() ) ); ?>"><?php echo esc_html( up2a_core_header_phone() ); ?></a>
				</div>
				<div class="up2a-contact__card">
					<div class="up2a-contact__icon"><?php echo up2a_core_icon( 'pin' ); ?></div>
					<h3><?php esc_html_e( 'Adresse', 'up2a-core' ); ?></h3>
					<a href="<?php echo esc_url( up2a_core_header_maps_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( up2a_core_header_address() ); ?></a>
				</div>
				<div class="up2a-contact__card">
					<div class="up2a-contact__icon"><?php echo up2a_core_content_icon( 'clock' ); ?></div>
					<h3><?php esc_html_e( 'Horaires', 'up2a-core' ); ?></h3>
					<p><?php esc_html_e( 'Lundi – Vendredi, 8h – 17h', 'up2a-core' ); ?></p>
				</div>
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
		'#up2a-formations' => __( 'Formations', 'up2a-core' ),
		'#up2a-galerie'    => __( 'Galerie', 'up2a-core' ),
		'#up2a-admissions' => __( 'Admissions', 'up2a-core' ),
		'#up2a-contact'    => __( 'Contact', 'up2a-core' ),
	);
	$formations_liens = array(
		__( 'Licence en Droit Public', 'up2a-core' ),
		__( 'Licence en Droit Privé', 'up2a-core' ),
		__( 'Licence en Logistique Internationale', 'up2a-core' ),
		__( 'Licence en Marketing Communication', 'up2a-core' ),
	);
	?>
	<footer class="up2a-footer">
		<div class="up2a-section-inner up2a-footer__grid">
			<div class="up2a-footer__col up2a-footer__col--brand">
				<p class="up2a-footer__brand">Université Privée<br>An-Nahdah d'Afrique</p>
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
					<?php foreach ( $formations_liens as $label ) : ?>
						<li><a href="#up2a-formations"><?php echo esc_html( $label ); ?></a></li>
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
