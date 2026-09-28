<?php
/**
 * Rendu du pied de page — lit l'option `up2a_footer_option` (voir
 * inc/settings.php, menu wp-admin "Pied de page"). Extrait de
 * `up2a-core` (voir docs/06-storyboard.md "Extraction En-tête/Pied de
 * page/Slider/Actualités", 2026-09-28).
 *
 * Contrairement à la version précédente (appelée seulement depuis les
 * templates home/détail formation), ce pied de page s'affiche désormais
 * sur **tout le site** (`wp_footer`) — cohérent avec le fait que
 * l'en-tête (up2a-header) s'affiche lui aussi partout ; une page comme
 * /preinscription/ n'avait jusqu'ici aucun pied de page.
 *
 * Dépendances : `up2a-core` (obligatoire, icônes) ; `up2a-header`
 * (facultative, téléphone/adresse — dégradation propre via
 * `function_exists()` si absent) ; `up2a-formations` (facultative,
 * colonne "Nos formations").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function up2a_footer_render(): void {
	$o = up2a_footer_get_option();

	$telephone = function_exists( 'up2a_header_get_option' ) ? up2a_header_get_option()['telephone'] : '';
	$adresse   = function_exists( 'up2a_header_get_option' ) ? up2a_header_get_option()['adresse'] : '';

	// Le module Formations est un plugin séparé : la colonne "Nos
	// formations" ne s'affiche que s'il est actif.
	$formations = function_exists( 'up2a_formations_formations' ) ? up2a_formations_formations() : array();
	?>
	<footer class="up2a-footer">
		<div class="up2a-footer__grid">
			<div class="up2a-footer__col up2a-footer__col--brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="up2a-footer__logo">
					<?php if ( $o['logo_id'] ) : ?>
						<?php echo wp_get_attachment_image( $o['logo_id'], 'medium', false, array( 'loading' => 'lazy', 'alt' => get_bloginfo( 'name' ) ) ); ?>
					<?php endif; ?>
				</a>
				<?php if ( $o['slogan'] ) : ?>
					<p class="up2a-footer__slogan"><?php echo esc_html( $o['slogan'] ); ?></p>
				<?php endif; ?>
				<?php if ( $adresse ) : ?>
					<p class="up2a-footer__contact-line">
						<?php echo up2a_core_content_icon( 'pin' ); ?>
						<?php if ( function_exists( 'up2a_header_maps_url' ) ) : ?>
							<a href="<?php echo esc_url( up2a_header_maps_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $adresse ); ?></a>
						<?php else : ?>
							<span><?php echo esc_html( $adresse ); ?></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>
				<?php if ( $telephone ) : ?>
					<p class="up2a-footer__contact-line">
						<?php echo up2a_core_content_icon( 'phone' ); ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telephone ) ); ?>"><?php echo esc_html( $telephone ); ?></a>
					</p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $o['liens'] ) ) : ?>
				<div class="up2a-footer__col">
					<p class="up2a-footer__title"><?php esc_html_e( 'Liens rapides', 'up2a-footer' ); ?></p>
					<ul class="up2a-footer__list">
						<?php foreach ( $o['liens'] as $lien ) : ?>
							<?php if ( empty( $lien['label'] ) || empty( $lien['href'] ) ) : continue; endif; ?>
							<li><a href="<?php echo esc_url( $lien['href'] ); ?>"><?php echo esc_html( $lien['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $formations ) ) : ?>
				<div class="up2a-footer__col">
					<p class="up2a-footer__title"><?php esc_html_e( 'Nos formations', 'up2a-footer' ); ?></p>
					<ul class="up2a-footer__list">
						<?php foreach ( $formations as $f ) : ?>
							<li>
								<a
									href="<?php echo esc_url( home_url( '/formations/' . $f['slug'] . '/' ) ); ?>"
								><?php echo esc_html( $f['nom'] ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
		<div class="up2a-footer__bottom">
			<p><?php echo esc_html( up2a_footer_copyright_text() ); ?></p>
		</div>
	</footer>
	<?php
}
add_action( 'wp_footer', 'up2a_footer_render' );

/**
 * Copyright avec l'année courante substituée automatiquement (le champ de
 * réglage contient `{annee}`, remplacé ici — voir inc/settings.php).
 */
function up2a_footer_copyright_text(): string {
	$modele = up2a_footer_get_option()['copyright'];
	return str_replace( '{annee}', gmdate( 'Y' ), $modele );
}

/**
 * Shortcode `[up2a_footer]` — pour cohérence avec les autres modules et
 * au cas où le pied de page devrait être inséré manuellement ailleurs ;
 * l'usage normal reste automatique via `wp_footer` ci-dessus.
 */
add_shortcode(
	'up2a_footer',
	function (): string {
		ob_start();
		up2a_footer_render();
		return (string) ob_get_clean();
	}
);
