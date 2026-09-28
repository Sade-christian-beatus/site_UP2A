<?php
/**
 * Rendu du Hero (diaporama + titre + boutons + compte à rebours) — lit
 * l'option `up2a_slider_option` (voir inc/settings.php, menu wp-admin
 * "Slider"). Extrait de `up2a-core` (voir docs/06-storyboard.md
 * "Extraction En-tête/Pied de page/Slider/Actualités", 2026-09-28).
 *
 * Dépendance obligatoire : `up2a-core` (icônes `up2a_core_content_icon()`,
 * URL de préinscription `up2a_core_preinscription_url()` — voir
 * up2a-slider.php "Requires Plugins").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date de rentrée académique, affichée en compte à rebours. Par défaut le
 * 5 octobre (année courante avancée automatiquement si déjà passée) si le
 * champ de réglage est laissé vide.
 */
function up2a_slider_rentree_date(): string {
	$saisie = up2a_slider_get_option()['rentree_date'];
	if ( '' !== $saisie ) {
		return $saisie . ' 08:00:00';
	}

	$year    = (int) gmdate( 'Y' );
	$default = $year . '-10-05 08:00:00';
	if ( strtotime( $default ) < time() ) {
		$default = ( $year + 1 ) . '-10-05 08:00:00';
	}
	return $default;
}

/**
 * Formate une date en français sans dépendre de la locale du serveur
 * (`gmdate()` ne traduit pas les noms de mois).
 */
function up2a_slider_format_date_fr( int $timestamp ): string {
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

function up2a_slider_render(): void {
	$o      = up2a_slider_get_option();
	$slides = array();
	foreach ( $o['slides'] as $slide ) {
		$image_id = (int) ( $slide['image_id'] ?? 0 );
		if ( ! $image_id ) {
			continue;
		}
		$desktop = wp_get_attachment_image_url( $image_id, 'up2a_slider_desktop' );
		$mobile  = wp_get_attachment_image_url( $image_id, 'up2a_slider_mobile' );
		if ( ! $desktop ) {
			continue;
		}
		$slides[] = array( 'desktop' => $desktop, 'mobile' => $mobile ?: $desktop );
	}
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
			<?php if ( $o['titre_ligne1'] || $o['titre_ligne2'] ) : ?>
				<h1 class="up2a-hero__title js-hero-title">
					<?php echo esc_html( $o['titre_ligne1'] ); ?><br>
					<span><?php echo esc_html( $o['titre_ligne2'] ); ?></span>
				</h1>
			<?php endif; ?>
			<?php if ( $o['sous_titre'] ) : ?>
				<p class="up2a-hero__subtitle"><?php echo esc_html( $o['sous_titre'] ); ?></p>
			<?php endif; ?>
			<div class="up2a-hero__actions">
				<a href="#up2a-formations" class="up2a-hero__cta up2a-hero__cta--accent js-hero-cta">
					<?php echo up2a_core_content_icon( 'cap' ); ?>
					<?php echo esc_html( $o['cta1_label'] ); ?>
					<?php echo up2a_core_content_icon( 'arrow' ); ?>
				</a>
				<a href="<?php echo esc_url( up2a_core_preinscription_url() ); ?>" class="up2a-hero__cta up2a-hero__cta--outline js-hero-cta">
					<?php echo up2a_core_content_icon( 'file' ); ?>
					<?php echo esc_html( $o['cta2_label'] ); ?>
					<?php echo up2a_core_content_icon( 'arrow' ); ?>
				</a>
			</div>
		</div>

		<?php if ( count( $slides ) > 1 ) : ?>
			<div class="up2a-hero__dots js-hero-dots">
				<?php foreach ( $slides as $i => $slide ) : ?>
					<button type="button" class="<?php echo 0 === $i ? 'is-active' : ''; ?>" data-slide="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Image %d', 'up2a-slider' ), $i + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<?php
	$rentree    = up2a_slider_rentree_date();
	$rentree_ts = strtotime( $rentree );
	?>
	<div class="up2a-hero__countdown-wrap">
		<div class="up2a-hero__countdown js-hero-countdown" data-target="<?php echo esc_attr( gmdate( 'c', $rentree_ts ) ); ?>">
			<div class="up2a-hero__countdown-icon" aria-hidden="true"><?php echo up2a_core_content_icon( 'clock' ); ?></div>
			<div class="up2a-hero__countdown-label">
				<span class="up2a-hero__countdown-eyebrow"><?php esc_html_e( 'Rentrée académique', 'up2a-slider' ); ?></span>
				<span class="up2a-hero__countdown-date"><?php echo esc_html( up2a_slider_format_date_fr( $rentree_ts ) ); ?></span>
			</div>
			<div class="up2a-hero__countdown-stats">
				<div class="up2a-hero__countdown-stat"><span class="js-countdown-days">00</span><small><?php esc_html_e( 'Jours', 'up2a-slider' ); ?></small></div>
				<div class="up2a-hero__countdown-stat"><span class="js-countdown-hours">00</span><small><?php esc_html_e( 'Heures', 'up2a-slider' ); ?></small></div>
				<div class="up2a-hero__countdown-stat"><span class="js-countdown-minutes">00</span><small><?php esc_html_e( 'Min', 'up2a-slider' ); ?></small></div>
				<div class="up2a-hero__countdown-stat"><span class="js-countdown-seconds">00</span><small><?php esc_html_e( 'Sec', 'up2a-slider' ); ?></small></div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Shortcode `[up2a_slider]` — permet d'insérer la scène d'accueil depuis
 * n'importe quelle page sans toucher au code. Utilisé par défaut par la
 * home onepage d'up2a-core (voir templates/front-page-onepage.php).
 */
add_shortcode(
	'up2a_slider',
	function (): string {
		ob_start();
		up2a_slider_render();
		return (string) ob_get_clean();
	}
);
