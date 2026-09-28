<?php
/**
 * Lecture et rendu du module "Actualités" — lit l'option
 * `up2a_actualites_option` (voir inc/settings.php, menu wp-admin
 * "Actualités"). Nouveau module (n'existait pas avant, voir
 * docs/06-storyboard.md "Extraction En-tête/Pied de page/Slider/
 * Actualités") : pas de contenu par défaut, la section reste absente de
 * la home jusqu'à ce qu'une actualité réelle soit ajoutée depuis le
 * tableau de bord — jamais de contenu inventé (CLAUDE.md §3).
 *
 * L'ordre d'affichage est celui choisi sur l'écran de réglages (boutons
 * Monter/Descendre), pas un tri automatique par date — même logique que
 * les cartes Formations/Galerie.
 *
 * Dépendance obligatoire : `up2a-core` (icônes `up2a_core_content_icon()`
 * — voir up2a-actualites.php "Requires Plugins").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function up2a_actualites_format_row( array $row ): array {
	$image    = array( 'desktop' => '', 'mobile' => '' );
	$image_id = (int) ( $row['image_id'] ?? 0 );
	if ( $image_id ) {
		$desktop = wp_get_attachment_image_url( $image_id, 'up2a_actualites_desktop' );
		$mobile  = wp_get_attachment_image_url( $image_id, 'up2a_actualites_mobile' );
		if ( $desktop ) {
			$image = array( 'desktop' => $desktop, 'mobile' => $mobile ?: $desktop );
		}
	}

	$date_brute = (string) ( $row['date'] ?? '' );
	$date_affichee = '';
	if ( '' !== $date_brute ) {
		$ts = strtotime( $date_brute );
		if ( false !== $ts ) {
			$date_affichee = date_i18n( 'j F Y', $ts );
		}
	}

	return array(
		'titre'    => (string) ( $row['titre'] ?? '' ),
		'date'     => $date_affichee,
		'extrait'  => (string) ( $row['extrait'] ?? '' ),
		'lien'     => (string) ( $row['lien'] ?? '' ),
		'image'    => $image,
	);
}

function up2a_actualites_liste(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array_map( 'up2a_actualites_format_row', up2a_actualites_get_raw_list() );
	return $cache;
}

/**
 * Grille d'actualités. N'affiche rien si la liste est vide (jamais de
 * section vide/factice) — voir up2a_actualites_get_raw_list() pour en
 * ajouter depuis le tableau de bord.
 */
function up2a_actualites_render(): void {
	$liste = up2a_actualites_liste();
	if ( empty( $liste ) ) {
		return;
	}
	?>
	<section id="up2a-actualites" class="up2a-actualites js-actualites">
		<?php echo up2a_core_decor( 'actualites' ); ?>
		<div class="up2a-section-inner">
			<h2 class="up2a-section-title"><?php esc_html_e( 'Actualités', 'up2a-actualites' ); ?></h2>
			<div class="up2a-actualites__grid">
				<?php foreach ( $liste as $a ) : ?>
					<?php $carte_tag = $a['lien'] ? 'a' : 'div'; ?>
					<<?php echo esc_attr( $carte_tag ); ?>
						class="up2a-actualites__card"
						<?php if ( $a['lien'] ) : ?>href="<?php echo esc_url( $a['lien'] ); ?>"<?php endif; ?>
					>
						<?php if ( ! empty( $a['image']['desktop'] ) ) : ?>
							<picture class="up2a-actualites__card-media">
								<source srcset="<?php echo esc_url( $a['image']['mobile'] ); ?>" media="(max-width: 640px)">
								<img
									src="<?php echo esc_url( $a['image']['desktop'] ); ?>"
									alt=""
									loading="lazy"
									decoding="async"
									width="600"
									height="450"
								>
							</picture>
						<?php else : ?>
							<div class="up2a-actualites__card-media up2a-actualites__card-media--placeholder" aria-hidden="true">
								<?php echo up2a_core_content_icon( 'megaphone' ); ?>
							</div>
						<?php endif; ?>
						<div class="up2a-actualites__card-body">
							<?php if ( $a['date'] ) : ?>
								<span class="up2a-actualites__card-date"><?php echo up2a_core_content_icon( 'clock' ); ?> <?php echo esc_html( $a['date'] ); ?></span>
							<?php endif; ?>
							<h3 class="up2a-actualites__card-title"><?php echo esc_html( $a['titre'] ); ?></h3>
							<?php if ( $a['extrait'] ) : ?>
								<p class="up2a-actualites__card-extrait"><?php echo esc_html( $a['extrait'] ); ?></p>
							<?php endif; ?>
							<?php if ( $a['lien'] ) : ?>
								<span class="up2a-actualites__card-link"><?php esc_html_e( 'Lire la suite', 'up2a-actualites' ); ?> <?php echo up2a_core_content_icon( 'arrow' ); ?></span>
							<?php endif; ?>
						</div>
					</<?php echo esc_attr( $carte_tag ); ?>>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Shortcode `[up2a_actualites]` — permet d'insérer la section depuis
 * n'importe quelle page sans toucher au code. Utilisé par défaut par la
 * home onepage d'up2a-core (voir templates/front-page-onepage.php).
 */
add_shortcode(
	'up2a_actualites',
	function (): string {
		ob_start();
		up2a_actualites_render();
		return (string) ob_get_clean();
	}
);
