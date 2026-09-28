<?php
/**
 * SEO de base (méta description, Open Graph, Twitter Card, URL canonique)
 * — voir CLAUDE.md tâche SEO du 2026-09-28. Volontairement minimal (pas
 * de sitemap ni de robots.txt codés en dur : WordPress génère déjà un
 * sitemap XML natif depuis la 5.5 sur /wp-sitemap.xml et un robots.txt
 * virtuel sur /robots.txt — rien à coder, juste à vérifier une fois le
 * site en ligne, voir wordpress/README.md "SEO").
 *
 * Si un plugin SEO dédié (Yoast, RankMath, All in One SEO...) est
 * installé plus tard, ce module se désactive automatiquement
 * (up2a_core_seo_plugin_actif_detecte()) plutôt que de produire des
 * balises en double.
 *
 * Réglages éditables depuis wp-admin (menu "SEO") : description par
 * défaut et image de partage par défaut. Sur une page/formation
 * individuelle, la description utilise l'extrait de la page si elle en a
 * un, et l'image de partage utilise l'image mise en avant si elle existe
 * — la valeur par défaut ne sert que de filet de sécurité.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function up2a_core_seo_plugin_actif_detecte(): bool {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| class_exists( 'All_in_One_SEO_Pack' );
}

add_action(
	'admin_menu',
	function (): void {
		add_menu_page(
			__( 'SEO', 'up2a-core' ),
			__( 'SEO', 'up2a-core' ),
			'manage_options',
			'up2a-seo',
			'up2a_core_seo_render_settings_page',
			'dashicons-search',
			27
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( UP2A_CORE_FILE ),
	function ( array $links ): array {
		$url = admin_url( 'admin.php?page=up2a-seo' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Réglages SEO', 'up2a-core' ) . '</a>' );
		return $links;
	}
);

/**
 * Copie la photo du bâtiment (seul visuel réel disponible à ce jour,
 * voir CLAUDE.md §3) dans la médiathèque au premier chargement, pour
 * servir d'image de partage par défaut — même raisonnement de
 * préservation que les autres seeds (up2a-header, up2a-slider...).
 */
function up2a_core_seo_sideload_image( string $chemin_absolu, string $alt = '' ): int {
	if ( ! file_exists( $chemin_absolu ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$contenu = file_get_contents( $chemin_absolu ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( false === $contenu ) {
		return 0;
	}

	$upload = wp_upload_bits( wp_basename( $chemin_absolu ), null, $contenu );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => wp_check_filetype( $upload['file'] )['type'],
			'post_title'     => sanitize_file_name( wp_basename( $chemin_absolu ) ),
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	if ( '' !== $alt ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	}

	return $attachment_id;
}

function up2a_core_seo_seed_default_option(): array {
	$image_id = up2a_core_seo_sideload_image(
		UP2A_CORE_PATH . 'assets/img/gallery-campus-facade.webp',
		__( "Campus de l'Université Privée An-Nahdah d'Afrique (UP-2A), Ouagadougou", 'up2a-core' )
	);

	$defaut = array(
		'description_defaut' => __( "Université Privée An-Nahdah d'Afrique (UP-2A) à Ouagadougou — « Former aujourd'hui les élites de demain ». Découvrez nos formations et notre préinscription en ligne, sans frais de dossier.", 'up2a-core' ),
		'image_defaut_id'     => $image_id,
	);

	add_option( 'up2a_seo_option', $defaut );
	return $defaut;
}

function up2a_core_seo_get_option(): array {
	$stored = get_option( 'up2a_seo_option', false );
	if ( false === $stored || ! is_array( $stored ) ) {
		return up2a_core_seo_seed_default_option();
	}
	return $stored;
}

function up2a_core_seo_save_settings( array $post ): void {
	$actuel = up2a_core_seo_get_option();

	$actuel['description_defaut'] = sanitize_textarea_field( wp_unslash( $post['description_defaut'] ?? '' ) );
	$actuel['image_defaut_id']    = absint( $post['image_defaut_id'] ?? 0 );

	update_option( 'up2a_seo_option', $actuel );
}

/**
 * Description affichée en méta et dans les partages : l'extrait de la
 * page/formation si elle en a un, sinon la description par défaut
 * réglée dans le menu "SEO" — jamais un texte inventé au niveau du
 * contenu individuel.
 */
function up2a_core_seo_description(): string {
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$extrait = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
			if ( '' === trim( (string) $extrait ) && ! post_password_required( $post ) ) {
				$extrait = wp_trim_words( wp_strip_all_tags( $post->post_content ), 35, '…' );
			}
			$extrait = trim( (string) $extrait );
			if ( '' !== $extrait ) {
				return $extrait;
			}
		}
	}

	return up2a_core_seo_get_option()['description_defaut'];
}

/**
 * Image de partage : l'image mise en avant de la page si elle existe,
 * sinon l'image par défaut réglée dans le menu "SEO".
 */
function up2a_core_seo_image_url(): string {
	if ( is_singular() && has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
		if ( $url ) {
			return $url;
		}
	}

	$image_id = up2a_core_seo_get_option()['image_defaut_id'];
	if ( $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, 'large' );
		if ( $url ) {
			return $url;
		}
	}

	return '';
}

function up2a_core_seo_current_url(): string {
	if ( is_singular() ) {
		$canonique = wp_get_canonical_url();
		if ( $canonique ) {
			return $canonique;
		}
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	global $wp;
	$chemin = isset( $wp->request ) ? trim( (string) $wp->request, '/' ) : '';
	return home_url( '' !== $chemin ? '/' . $chemin . '/' : '/' );
}

function up2a_core_seo_head(): void {
	if ( up2a_core_seo_plugin_actif_detecte() ) {
		return;
	}

	$description = up2a_core_seo_description();
	$image       = up2a_core_seo_image_url();
	$url         = up2a_core_seo_current_url();
	$titre       = wp_get_document_title();
	$site        = get_bloginfo( 'name' );

	if ( '' !== $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";

	echo '<meta property="og:type" content="' . esc_attr( is_singular() ? 'article' : 'website' ) . '">' . "\n";
	if ( '' !== $site ) {
		echo '<meta property="og:site_name" content="' . esc_attr( $site ) . '">' . "\n";
	}
	echo '<meta property="og:title" content="' . esc_attr( $titre ) . '">' . "\n";
	if ( '' !== $description ) {
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	if ( '' !== $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}

	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $titre ) . '">' . "\n";
	if ( '' !== $description ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	if ( '' !== $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'up2a_core_seo_head', 1 );

function up2a_core_seo_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['up2a_seo_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['up2a_seo_nonce'] ) ), 'up2a_seo_save' ) ) {
		up2a_core_seo_save_settings( wp_unslash( $_POST ) );
		$message = __( 'Réglages SEO enregistrés.', 'up2a-core' );
	}

	$o = up2a_core_seo_get_option();

	wp_enqueue_media();
	wp_enqueue_style( 'up2a-seo-admin', UP2A_CORE_URL . 'assets/css/admin-seo.css', array(), UP2A_CORE_VERSION );
	wp_enqueue_script( 'up2a-seo-admin', UP2A_CORE_URL . 'assets/js/admin-seo.js', array( 'media-editor' ), UP2A_CORE_VERSION, true );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'SEO', 'up2a-core' ); ?></h1>

		<?php if ( up2a_core_seo_plugin_actif_detecte() ) : ?>
			<div class="notice notice-warning">
				<p><?php esc_html_e( "Un plugin SEO dédié (Yoast, RankMath ou All in One SEO) est actif : ces réglages sont ignorés pour éviter les balises en double. Désactivez ce plugin si vous souhaitez utiliser les réglages ci-dessous à la place.", 'up2a-core' ); ?></p>
			</div>
		<?php endif; ?>

		<p><?php esc_html_e( "Description et image utilisées pour le référencement (Google) et les partages (Facebook, WhatsApp, X...) quand une page n'a pas son propre extrait ou sa propre image mise en avant.", 'up2a-core' ); ?></p>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<form method="post" class="up2a-seo-form">
			<?php wp_nonce_field( 'up2a_seo_save', 'up2a_seo_nonce' ); ?>

			<p>
				<label for="up2a-seo-description"><strong><?php esc_html_e( 'Description par défaut', 'up2a-core' ); ?></strong></label><br>
				<textarea id="up2a-seo-description" name="description_defaut" rows="3" class="large-text"><?php echo esc_textarea( $o['description_defaut'] ); ?></textarea>
				<span class="description"><?php esc_html_e( "Utilisée sur la home et sur toute page sans extrait propre. Les pages Formations peuvent définir leur propre extrait (Pages → Attributs de l'extrait) pour une description plus précise.", 'up2a-core' ); ?></span>
			</p>

			<p class="up2a-seo-row__image">
				<label><strong><?php esc_html_e( 'Image de partage par défaut', 'up2a-core' ); ?></strong></label><br>
				<span class="up2a-seo-thumb">
					<?php if ( $o['image_defaut_id'] ) : ?>
						<?php echo wp_get_attachment_image( $o['image_defaut_id'], 'medium' ); ?>
					<?php endif; ?>
				</span>
				<input type="hidden" class="up2a-seo-image-id" name="image_defaut_id" value="<?php echo esc_attr( $o['image_defaut_id'] ); ?>">
				<button type="button" class="button up2a-seo-pick-image"><?php esc_html_e( "Choisir l'image", 'up2a-core' ); ?></button>
				<button type="button" class="button up2a-seo-remove-image" <?php echo $o['image_defaut_id'] ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Retirer', 'up2a-core' ); ?></button>
			</p>

			<?php submit_button( __( 'Enregistrer les modifications', 'up2a-core' ) ); ?>
		</form>

		<hr>
		<h2><?php esc_html_e( 'Sitemap et robots.txt', 'up2a-core' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: %s: sitemap URL */
				esc_html__( "WordPress génère automatiquement un sitemap XML (natif, sans plugin) consultable sur %s, ainsi qu'un fichier robots.txt qui l'y référence. Rien à configurer ici — à vérifier une fois le site en ligne.", 'up2a-core' ),
				'<code>' . esc_html( home_url( '/wp-sitemap.xml' ) ) . '</code>'
			);
			?>
		</p>
	</div>
	<?php
}
