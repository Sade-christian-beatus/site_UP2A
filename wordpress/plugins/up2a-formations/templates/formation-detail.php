<?php
/**
 * Page de détail d'une formation — servie sur /formations/{slug}/ (voir
 * inc/formations.php). Contenu strictement repris de
 * up2a_formations_formations() : aucune information (programme détaillé,
 * conditions spécifiques, effectifs...) n'est inventée pour cette page —
 * ce qui n'est pas encore confirmé par le client reste marqué comme
 * "à venir" plutôt que fabriqué (voir CLAUDE.md §3).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$formation = up2a_formations_find( (string) get_query_var( 'up2a_formation' ) );
if ( null === $formation ) {
	// Filet de sécurité : ne devrait pas arriver (déjà vérifié dans
	// inc/formations.php avant de servir ce template).
	get_header();
	echo '<main class="up2a-section-inner" style="padding:80px 24px;text-align:center;"><p>' . esc_html__( 'Formation introuvable.', 'up2a-formations' ) . '</p></main>';
	get_footer();
	return;
}

$autres = array_values(
	array_filter(
		up2a_formations_formations(),
		static function ( array $f ) use ( $formation ) {
			return $f['slug'] !== $formation['slug'];
		}
	)
);

get_header();
?>

<main id="up2a-formation-page" class="up2a-formation-page">

	<nav class="up2a-formation-page__breadcrumb" aria-label="<?php esc_attr_e( 'Fil d\'Ariane', 'up2a-formations' ); ?>">
		<div class="up2a-section-inner">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'up2a-formations' ); ?></a>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( home_url( '/#up2a-formations' ) ); ?>"><?php esc_html_e( 'Formations', 'up2a-formations' ); ?></a>
			<span aria-hidden="true">/</span>
			<span aria-current="page"><?php echo esc_html( $formation['nom'] ); ?></span>
		</div>
	</nav>

	<section class="up2a-formation-page__banner">
		<div class="up2a-section-inner up2a-formation-page__banner-grid">
			<div class="up2a-formation-page__banner-media">
				<picture>
					<source srcset="<?php echo esc_url( $formation['image']['mobile'] ); ?>" media="(max-width: 640px)">
					<img
						src="<?php echo esc_url( $formation['image']['desktop'] ); ?>"
						alt="<?php echo esc_attr( $formation['nom'] ); ?>"
						loading="eager"
						fetchpriority="high"
						width="700"
						height="500"
					>
				</picture>
			</div>
			<div class="up2a-formation-page__banner-text">
				<span class="up2a-formation-modal__badge up2a-formation-modal__badge--<?php echo esc_attr( strtolower( $formation['faculte'] ) ); ?>"><?php echo esc_html( $formation['faculte'] ); ?></span>
				<h1><?php echo esc_html( $formation['nom'] ); ?></h1>
				<p class="up2a-formation-modal__highlight"><?php echo up2a_core_content_icon( $formation['icone'] ); ?> <?php esc_html_e( 'Licence · 3 ans', 'up2a-formations' ); ?></p>
				<p class="up2a-formation-modal__faculte"><?php echo up2a_core_icon( 'cap' ); ?> <?php echo esc_html( $formation['faculte_full'] ); ?></p>
				<p class="up2a-formation-page__lead"><?php echo esc_html( $formation['intro'] ); ?></p>
				<a href="<?php echo esc_url( up2a_core_preinscription_url( $formation['slug'] ) ); ?>" class="up2a-hero__cta up2a-hero__cta--accent">
					<?php esc_html_e( 'Faire ma préinscription', 'up2a-formations' ); ?>
					<?php echo up2a_core_content_icon( 'arrow' ); ?>
				</a>
			</div>
		</div>
	</section>

	<section class="up2a-formation-page__body">
		<div class="up2a-section-inner up2a-formation-page__body-grid">
			<div class="up2a-formation-page__main">
				<h2 class="up2a-section-title up2a-section-title--left"><?php esc_html_e( 'Présentation', 'up2a-formations' ); ?></h2>
				<p class="up2a-formation-modal__intro"><?php echo esc_html( $formation['intro'] ); ?></p>

				<h2 class="up2a-section-title up2a-section-title--left"><?php esc_html_e( 'Débouchés', 'up2a-formations' ); ?></h2>
				<ul class="up2a-formation-modal__debouches">
					<?php foreach ( $formation['debouches'] as $debouche ) : ?>
						<li><?php echo up2a_core_content_icon( 'check' ); ?> <span><?php echo esc_html( $debouche ); ?></span></li>
					<?php endforeach; ?>
				</ul>

				<h2 class="up2a-section-title up2a-section-title--left"><?php esc_html_e( 'Programme', 'up2a-formations' ); ?></h2>
				<p class="up2a-formation-page__placeholder">
					<?php echo up2a_core_content_icon( 'clock' ); ?>
					<?php esc_html_e( 'Le détail du programme (matières et volumes horaires) sera publié prochainement.', 'up2a-formations' ); ?>
				</p>

				<h2 class="up2a-section-title up2a-section-title--left"><?php esc_html_e( "Conditions d'admission", 'up2a-formations' ); ?></h2>
				<ol class="up2a-formation-page__steps">
					<li><?php esc_html_e( 'Remplissez le formulaire de préinscription en ligne.', 'up2a-formations' ); ?></li>
					<li><?php esc_html_e( "Joignez vos pièces d'identité et diplômes.", 'up2a-formations' ); ?></li>
					<li><?php esc_html_e( 'Recevez une confirmation par e-mail dès l\'enregistrement de votre dossier.', 'up2a-formations' ); ?></li>
					<li><?php esc_html_e( "L'université étudie votre dossier et vous contacte pour la suite du processus.", 'up2a-formations' ); ?></li>
				</ol>
				<p class="up2a-formation-page__note"><?php esc_html_e( 'Aucun frais à régler à cette étape.', 'up2a-formations' ); ?></p>

				<a href="<?php echo esc_url( up2a_core_preinscription_url( $formation['slug'] ) ); ?>" class="up2a-hero__cta up2a-hero__cta--accent">
					<?php esc_html_e( 'Faire ma préinscription pour cette formation', 'up2a-formations' ); ?>
					<?php echo up2a_core_content_icon( 'arrow' ); ?>
				</a>
			</div>

			<aside class="up2a-formation-page__aside">
				<h2 class="up2a-formation-page__aside-title"><?php esc_html_e( 'Autres formations', 'up2a-formations' ); ?></h2>
				<ul class="up2a-formation-page__related">
					<?php foreach ( $autres as $f ) : ?>
						<li>
							<a href="<?php echo esc_url( home_url( '/formations/' . $f['slug'] . '/' ) ); ?>">
								<picture>
									<img src="<?php echo esc_url( $f['image']['mobile'] ); ?>" alt="" loading="lazy" decoding="async" width="80" height="80">
								</picture>
								<span>
									<strong><?php echo esc_html( $f['nom'] ); ?></strong>
									<small><?php echo esc_html( $f['faculte'] ); ?></small>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</aside>
		</div>
	</section>

	<?php up2a_core_render_footer(); ?>
</main>

<?php
get_footer();
