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
			'titre'  => __( 'Excellence', 'up2a-core' ),
			'texte'  => __( 'Une exigence académique constante, portée par un corps enseignant qualifié et des méthodes pédagogiques rigoureuses.', 'up2a-core' ),
		),
		array(
			'titre'  => __( 'Savoir', 'up2a-core' ),
			'texte'  => __( 'Une formation ancrée dans les savoirs fondamentaux et les compétences pratiques attendues par le monde professionnel.', 'up2a-core' ),
		),
		array(
			'titre'  => __( 'Intégrité', 'up2a-core' ),
			'texte'  => __( 'Une éducation qui forme des femmes et des hommes responsables, honnêtes et engagés envers leur communauté.', 'up2a-core' ),
		),
		array(
			'titre'  => __( 'Ouverture', 'up2a-core' ),
			'texte'  => __( 'Une université tournée vers l\'Afrique et le monde, accueillante pour tous les profils d\'étudiants.', 'up2a-core' ),
		),
	);
	?>
	<section class="up2a-values js-values">
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nos valeurs', 'up2a-core' ); ?></h2>
			<div class="up2a-values__grid">
				<?php foreach ( $valeurs as $v ) : ?>
					<div class="up2a-values__card js-values-card">
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
			'nom'      => __( 'Licence en Gestion des Entreprises', 'up2a-core' ),
			'faculte'  => __( 'Faculté des Sciences Économiques et de Gestion', 'up2a-core' ),
		),
		array(
			'nom'      => __( 'Licence en Droit', 'up2a-core' ),
			'faculte'  => __( 'Faculté de Droit et Sciences Politiques', 'up2a-core' ),
		),
		array(
			'nom'      => __( 'Licence en Informatique et Réseaux', 'up2a-core' ),
			'faculte'  => __( 'Faculté des Sciences et Technologies', 'up2a-core' ),
		),
		array(
			'nom'      => __( 'Licence en Communication et Journalisme', 'up2a-core' ),
			'faculte'  => __( 'Faculté des Lettres, Langues et Sciences Humaines', 'up2a-core' ),
		),
	);
	?>
	<section class="up2a-formations js-formations">
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Nos formations', 'up2a-core' ); ?></h2>
			<div class="up2a-formations__grid">
				<?php foreach ( $formations as $f ) : ?>
					<div class="up2a-formations__card js-formations-card">
						<p class="up2a-formations__faculte"><?php echo esc_html( $f['faculte'] ); ?></p>
						<h3><?php echo esc_html( $f['nom'] ); ?></h3>
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
// Scène 7 — CTA final + contact
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
