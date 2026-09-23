<?php
/**
 * Données partagées entre le shortcode (affichage) et la route REST
 * (validation serveur) — une seule source de vérité pour les deux.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les 4 licences proposées (slug, faculté, nom). Les slugs DOIVENT
 * correspondre exactement à `formations.slug` dans
 * `supabase/migrations/0003_seed.sql` — c'est ce slug qui sert à
 * résoudre le `formation_id` réel côté Supabase à la soumission (voir
 * `up2a_preinscription_resolve_formation_id()` dans inc/rest.php).
 * Repris aussi dans `wordpress/plugins/up2a-core/inc/front-page.php`
 * (`up2a_core_formations()`) — à maintenir en cohérence si une licence
 * change.
 */
function up2a_preinscription_formations(): array {
	$defaults = array(
		array(
			'slug'    => 'licence-droit-public',
			'nom'     => __( 'Licence en Droit Public', 'up2a-preinscription' ),
			'faculte' => 'SJPA',
		),
		array(
			'slug'    => 'licence-droit-prive',
			'nom'     => __( 'Licence en Droit Privé', 'up2a-preinscription' ),
			'faculte' => 'SJPA',
		),
		array(
			'slug'    => 'licence-logistique-internationale',
			'nom'     => __( 'Licence en Logistique Internationale', 'up2a-preinscription' ),
			'faculte' => 'SEG',
		),
		array(
			'slug'    => 'licence-marketing-communication',
			'nom'     => __( 'Licence en Marketing Communication', 'up2a-preinscription' ),
			'faculte' => 'SEG',
		),
	);

	return apply_filters( 'up2a_preinscription_formations', $defaults );
}

/**
 * true si $slug correspond à une formation connue (protège la validation
 * serveur contre un slug arbitraire injecté dans la requête).
 */
function up2a_preinscription_is_valid_formation_slug( string $slug ): bool {
	foreach ( up2a_preinscription_formations() as $f ) {
		if ( $f['slug'] === $slug ) {
			return true;
		}
	}
	return false;
}

/**
 * Types de pièces jointes attendues. `requis` = bloquant côté serveur.
 * Types MIME et taille max alignés sur ce qu'un candidat peut
 * raisonnablement photographier/scanner depuis un téléphone en zone
 * 3G/4G (voir CLAUDE.md §7 — pas de limite déraisonnablement haute).
 */
function up2a_preinscription_pieces_attendues(): array {
	return array(
		'piece_identite' => array(
			'label'  => __( "Pièce d'identité (CNIB, passeport...)", 'up2a-preinscription' ),
			'requis' => true,
		),
		'diplome'        => array(
			'label'  => __( 'Dernier diplôme ou attestation obtenu(e)', 'up2a-preinscription' ),
			'requis' => true,
		),
		'autre'          => array(
			'label'  => __( 'Autre document (optionnel)', 'up2a-preinscription' ),
			'requis' => false,
		),
	);
}

function up2a_preinscription_max_file_size(): int {
	return apply_filters( 'up2a_preinscription_max_file_size', 5 * 1024 * 1024 ); // 5 Mo.
}

function up2a_preinscription_allowed_mimes(): array {
	return array(
		'jpg|jpeg' => 'image/jpeg',
		'png'      => 'image/png',
		'pdf'      => 'application/pdf',
	);
}
