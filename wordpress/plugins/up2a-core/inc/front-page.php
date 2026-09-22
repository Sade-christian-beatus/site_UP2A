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
 * URL de l'image du bâtiment (seul visuel existant à ce jour — voir
 * CLAUDE.md §3). Tant qu'aucune image n'est fournie, le Hero utilise un
 * dégradé de secours (voir up2a-front-page.css) plutôt que de bloquer sur
 * un asset manquant.
 */
function up2a_core_hero_image_url(): string {
	if ( defined( 'UP2A_HERO_IMAGE_URL' ) ) {
		return UP2A_HERO_IMAGE_URL;
	}
	return apply_filters( 'up2a_core_hero_image_url', '' );
}

/**
 * URL d'une photo de vie étudiante pour la section "Pourquoi nous
 * choisir". Sans photo fournie, une illustration de marque est utilisée
 * à la place (assets/img/motif-communaute.svg) — jamais un espace vide.
 */
function up2a_core_life_image_url(): string {
	if ( defined( 'UP2A_LIFE_IMAGE_URL' ) ) {
		return UP2A_LIFE_IMAGE_URL;
	}
	return apply_filters( 'up2a_core_life_image_url', '' );
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
	);

	return $icons[ $name ] ?? '';
}

// --------------------------------------------------------------------
// Scène 1 — Hero
// --------------------------------------------------------------------

function up2a_core_render_hero(): void {
	$image = up2a_core_hero_image_url();
	?>
	<section class="up2a-hero js-hero" <?php echo $image ? 'style="--up2a-hero-image: url(' . esc_url( $image ) . ')"' : ''; ?>>
		<div class="up2a-hero__bg js-hero-bg" aria-hidden="true"></div>
		<div class="up2a-hero__content">
			<h1 class="up2a-hero__title js-hero-title">
				<?php esc_html_e( 'Former aujourd\'hui les élites de demain.', 'up2a-core' ); ?>
			</h1>
			<p class="up2a-hero__subtitle">
				<?php esc_html_e( "L'Université Privée An-Nahdah d'Afrique forme des diplômés compétents, intègres et prêts à servir leur pays et leur continent.", 'up2a-core' ); ?>
			</p>
			<a href="#up2a-admissions" class="up2a-hero__cta js-hero-cta">
				<?php esc_html_e( 'Faire ma préinscription', 'up2a-core' ); ?>
			</a>
			<ul class="up2a-hero__badges">
				<li><?php echo up2a_core_content_icon( 'check' ); ?> <?php esc_html_e( 'Préinscription 100% en ligne', 'up2a-core' ); ?></li>
				<li><?php echo up2a_core_content_icon( 'check' ); ?> <?php esc_html_e( 'Aucun frais de dossier', 'up2a-core' ); ?></li>
				<li><?php echo up2a_core_content_icon( 'check' ); ?> <?php esc_html_e( '4 facultés, Ouagadougou', 'up2a-core' ); ?></li>
			</ul>
		</div>
	</section>
	<?php
}

// --------------------------------------------------------------------
// Scène 1 bis — Pourquoi choisir l'UP-2A
// --------------------------------------------------------------------

function up2a_core_render_pourquoi(): void {
	$image  = up2a_core_life_image_url();
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
			<div class="up2a-pourquoi__media" <?php echo $image ? 'style="--up2a-life-image: url(' . esc_url( $image ) . ')"' : ''; ?>>
				<?php if ( ! $image ) : ?>
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
// Scène 3 — Formations en un coup d'œil
// --------------------------------------------------------------------
// ⚠️ Intitulés provisoires, identiques à docs/05-contenus.md et
// supabase/migrations/0003_seed.sql — à valider/remplacer par le client.
// À terme (docs/03-roadmap.md phase 4), ce bloc lira un CPT "Formation"
// plutôt que ce tableau statique.

function up2a_core_render_formations(): void {
	$formations = array(
		array(
			'icone'   => 'briefcase',
			'nom'     => __( 'Licence en Gestion des Entreprises', 'up2a-core' ),
			'faculte' => __( 'Faculté des Sciences Économiques et de Gestion', 'up2a-core' ),
			'texte'   => __( 'Fondamentaux de la gestion, de la comptabilité et du management, pour des diplômés opérationnels.', 'up2a-core' ),
		),
		array(
			'icone'   => 'scale',
			'nom'     => __( 'Licence en Droit', 'up2a-core' ),
			'faculte' => __( 'Faculté de Droit et Sciences Politiques', 'up2a-core' ),
			'texte'   => __( 'Formation juridique généraliste, préparant aux métiers du droit et de l\'administration.', 'up2a-core' ),
		),
		array(
			'icone'   => 'cpu',
			'nom'     => __( 'Licence en Informatique et Réseaux', 'up2a-core' ),
			'faculte' => __( 'Faculté des Sciences et Technologies', 'up2a-core' ),
			'texte'   => __( 'Développement, réseaux et systèmes, pour répondre aux besoins de la transformation digitale.', 'up2a-core' ),
		),
		array(
			'icone'   => 'megaphone',
			'nom'     => __( 'Licence en Communication et Journalisme', 'up2a-core' ),
			'faculte' => __( 'Faculté des Lettres, Langues et Sciences Humaines', 'up2a-core' ),
			'texte'   => __( 'Métiers de la communication, de l\'information et des médias, avec exigence et rigueur.', 'up2a-core' ),
		),
	);
	?>
	<section class="up2a-formations js-formations">
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nos formations', 'up2a-core' ); ?></h2>
			<div class="up2a-formations__grid">
				<?php foreach ( $formations as $f ) : ?>
					<div class="up2a-formations__card js-formations-card">
						<div class="up2a-formations__icon"><?php echo up2a_core_content_icon( $f['icone'] ); ?></div>
						<p class="up2a-formations__faculte"><?php echo esc_html( $f['faculte'] ); ?></p>
						<h3><?php echo esc_html( $f['nom'] ); ?></h3>
						<p class="up2a-formations__texte"><?php echo esc_html( $f['texte'] ); ?></p>
						<a href="#" class="up2a-formations__link"><?php esc_html_e( 'En savoir plus', 'up2a-core' ); ?> →</a>
					</div>
				<?php endforeach; ?>
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
			<a href="#up2a-admissions" class="up2a-hero__cta"><?php esc_html_e( 'Faire ma préinscription', 'up2a-core' ); ?></a>
		</div>
	</section>
	<?php
}

// --------------------------------------------------------------------
// Contact
// --------------------------------------------------------------------

function up2a_core_render_contact(): void {
	?>
	<section class="up2a-contact js-contact">
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
					<p><?php echo esc_html( up2a_core_header_location() ); ?></p>
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
 * Footer de contenu (informations, pas le colophon technique du thème —
 * voir wordpress/README.md pour la nuance).
 */
function up2a_core_render_footer(): void {
	?>
	<footer class="up2a-footer">
		<div class="up2a-section-inner up2a-footer__grid">
			<div>
				<p class="up2a-footer__title">Université Privée An-Nahdah d'Afrique (UP-2A)</p>
				<p><?php echo esc_html( up2a_core_header_location() ); ?></p>
				<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', up2a_core_header_phone() ) ); ?>"><?php echo esc_html( up2a_core_header_phone() ); ?></a></p>
			</div>
			<div>
				<p class="up2a-footer__title"><?php esc_html_e( 'Mentions légales', 'up2a-core' ); ?></p>
				<p><?php esc_html_e( 'Autorisation MESRI n°2026-001647', 'up2a-core' ); ?></p>
			</div>
		</div>
	</footer>
	<?php
}
