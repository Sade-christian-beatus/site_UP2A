<?php
/**
 * CPT "Photo galerie" — rend le contenu de la Galerie modifiable depuis
 * le tableau de bord WordPress (menu "Galerie"), remplace le tableau
 * statique en dur dans le code (voir docs/06-storyboard.md "CPT Formation
 * depuis le dashboard", même logique appliquée ici).
 *
 * Champs du CPT :
 * - Titre               → légende affichée sous la photo.
 * - Image mise en avant → la photo (deux tailles dédiées générées :
 *                        desktop et mobile, voir
 *                        up2a_galerie_register_image_sizes()). Le texte
 *                        alternatif natif de la médiathèque WordPress
 *                        (Médias → modifier l'image → "Texte alternatif")
 *                        sert de `alt` — pas de champ dupliqué.
 * - Ordre (page-attributes) → la première photo (ordre le plus petit)
 *                        devient la vignette "en avant" avec le
 *                        défilement automatique (voir up2a_galerie_render()).
 *
 * Pas de page de détail publique pour ce CPT (`public => false`) : les
 * photos ne s'affichent que dans la grille/lightbox de la home, jamais
 * sur une URL propre.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function up2a_galerie_register_cpt(): void {
	register_post_type(
		'up2a_photo',
		array(
			'labels'        => array(
				'name'               => __( 'Galerie', 'up2a-galerie' ),
				'singular_name'      => __( 'Photo', 'up2a-galerie' ),
				'add_new_item'       => __( 'Ajouter une photo', 'up2a-galerie' ),
				'edit_item'          => __( 'Modifier la photo', 'up2a-galerie' ),
				'all_items'          => __( 'Galerie', 'up2a-galerie' ),
				'featured_image'     => __( 'Photo', 'up2a-galerie' ),
				'set_featured_image' => __( 'Définir la photo', 'up2a-galerie' ),
				'title_field'        => __( 'Légende', 'up2a-galerie' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 25,
			'menu_icon'     => 'dashicons-format-gallery',
			'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'up2a_galerie_register_cpt' );

/**
 * Tailles d'image dédiées, pour garder des cadrages cohérents avec le
 * design (grille/lightbox) sans dépendre des tailles WordPress
 * génériques ('large'/'medium', non recadrées par défaut).
 */
function up2a_galerie_register_image_sizes(): void {
	add_image_size( 'up2a_galerie_desktop', 900, 675, true );
	add_image_size( 'up2a_galerie_mobile', 600, 450, true );
}
add_action( 'after_setup_theme', 'up2a_galerie_register_image_sizes' );

/**
 * Rappel dans l'écran d'édition : le champ "Titre" WordPress fait office
 * de légende ici (renommé "Légende" dans les libellés ci-dessus, mais
 * l'input WordPress natif garde son placeholder par défaut) — précision
 * ajoutée sous le titre pour éviter toute confusion côté admin.
 */
add_action(
	'edit_form_after_title',
	function ( \WP_Post $post ): void {
		if ( 'up2a_photo' !== $post->post_type ) {
			return;
		}
		echo '<p class="description" style="margin: -8px 0 16px;">' . esc_html__( 'Ce titre est la légende affichée sous la photo sur le site. Le texte alternatif (accessibilité) se règle depuis Médias → modifier l\'image → "Texte alternatif".', 'up2a-galerie' ) . '</p>';
	}
);

/**
 * Copie un fichier image déjà présent sur le serveur (bundle d'up2a-core)
 * dans la médiathèque WordPress — même helper que
 * up2a_formations_sideload_image() (voir up2a-formations/inc/cpt.php pour
 * le détail du raisonnement, dupliqué ici plutôt que partagé entre
 * plugins pour rester indépendant d'up2a-formations).
 */
function up2a_galerie_sideload_image( string $chemin_absolu, string $alt = '' ): int {
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

/**
 * Amorçage : crée les 8 photos par défaut (légende + ordre + image) si le
 * CPT est vide, en reprenant les mêmes photos que l'ancienne version du
 * plugin — pour que la mise à jour ne fasse pas régresser visuellement un
 * site déjà en ligne (même filet de sécurité que
 * up2a_formations_seed_defaults(), voir up2a-formations/inc/cpt.php).
 * Une photo ajoutée plus tard depuis wp-admin (pas par cet amorçage) sans
 * image définie est ignorée par up2a_galerie_images() plutôt que
 * d'afficher un cadre cassé.
 */
function up2a_galerie_seed_defaults(): void {
	$photos = array(
		array( 'legende' => __( 'Le campus', 'up2a-galerie' ), 'fichier' => 'hero-slide-1.webp' ),
		array( 'legende' => __( 'Vie étudiante', 'up2a-galerie' ), 'fichier' => 'gallery-etudiants-batiment.webp' ),
		array( 'legende' => __( 'Le bâtiment', 'up2a-galerie' ), 'fichier' => 'hero-slide-2.webp' ),
		array( 'legende' => __( 'La façade principale', 'up2a-galerie' ), 'fichier' => 'gallery-campus-facade.webp' ),
		array( 'legende' => __( 'Nos étudiants', 'up2a-galerie' ), 'fichier' => 'pourquoi-photo.webp' ),
		array( 'legende' => __( 'Le campus, vue latérale', 'up2a-galerie' ), 'fichier' => 'gallery-campus-angle.webp' ),
		array( 'legende' => __( "L'architecture", 'up2a-galerie' ), 'fichier' => 'gallery-campus-perspective.webp' ),
		array( 'legende' => __( 'Nos étudiants sur le campus', 'up2a-galerie' ), 'fichier' => 'gallery-vie-etudiante-groupe.webp' ),
	);

	foreach ( $photos as $ordre => $p ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'up2a_photo',
				'post_status' => 'publish',
				'post_title'  => $p['legende'],
				'menu_order'  => $ordre,
			)
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		if ( defined( 'UP2A_CORE_PATH' ) ) {
			$attachment_id = up2a_galerie_sideload_image( UP2A_CORE_PATH . 'assets/img/' . $p['fichier'], $p['legende'] );
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}
		}
	}
}

add_action(
	'init',
	function (): void {
		if ( get_option( 'up2a_galerie_seeded' ) ) {
			return;
		}
		$existe_deja = get_posts(
			array(
				'post_type'      => 'up2a_photo',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( empty( $existe_deja ) ) {
			up2a_galerie_seed_defaults();
		}
		update_option( 'up2a_galerie_seeded', 1 );
	},
	15
);
