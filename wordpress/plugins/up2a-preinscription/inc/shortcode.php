<?php
/**
 * Shortcode [up2a_preinscription] — formulaire multi-étapes. Le HTML est
 * entièrement statique (rendu serveur) : la navigation entre étapes, la
 * validation d'appoint et l'envoi sont gérés par assets/js/
 * up2a-preinscription.js, indépendamment de tout CDN (voir CLAUDE.md §7 —
 * doit fonctionner même si un script externe échoue à charger).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function up2a_preinscription_icon( string $name ): string {
	$icons = array(
		'user'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		'cap'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg>',
		'upload'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>',
		'check'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>',
		'arrow'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
		'arrow-l' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
		'shield'  => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V5Z"/><path d="m9 12 2 2 4-4"/></svg>',
	);
	return $icons[ $name ] ?? '';
}

function up2a_preinscription_shortcode_render(): string {
	$formations       = up2a_preinscription_formations();
	$pieces           = up2a_preinscription_pieces_attendues();
	$formation_preselect = isset( $_GET['formation'] ) ? sanitize_title( wp_unslash( $_GET['formation'] ) ) : '';
	if ( ! up2a_preinscription_is_valid_formation_slug( $formation_preselect ) ) {
		$formation_preselect = '';
	}

	ob_start();
	?>
	<div class="up2a-preinsc js-preinsc" data-endpoint="">
		<div class="up2a-preinsc__intro">
			<p class="up2a-preinsc__badge"><?php echo up2a_preinscription_icon( 'shield' ); ?> <?php esc_html_e( '100% en ligne · Aucun frais à régler', 'up2a-preinscription' ); ?></p>
			<h1 class="up2a-preinsc__title"><?php esc_html_e( 'Faire ma préinscription', 'up2a-preinscription' ); ?></h1>
			<p class="up2a-preinsc__lead"><?php esc_html_e( "Remplissez ce formulaire en quelques minutes. Vous recevrez une confirmation par e-mail dès réception, puis l'université étudie votre dossier et vous recontacte pour la suite.", 'up2a-preinscription' ); ?></p>
		</div>

		<ol class="up2a-preinsc__steps js-preinsc-stepper" aria-hidden="true">
			<li class="is-active" data-step="1"><span class="up2a-preinsc__step-num">1</span><span class="up2a-preinsc__step-label"><?php esc_html_e( 'Informations', 'up2a-preinscription' ); ?></span></li>
			<li data-step="2"><span class="up2a-preinsc__step-num">2</span><span class="up2a-preinsc__step-label"><?php esc_html_e( 'Formation', 'up2a-preinscription' ); ?></span></li>
			<li data-step="3"><span class="up2a-preinsc__step-num">3</span><span class="up2a-preinsc__step-label"><?php esc_html_e( 'Pièces jointes', 'up2a-preinscription' ); ?></span></li>
			<li data-step="4"><span class="up2a-preinsc__step-num">4</span><span class="up2a-preinsc__step-label"><?php esc_html_e( 'Envoi', 'up2a-preinscription' ); ?></span></li>
		</ol>

		<form class="up2a-preinsc__form js-preinsc-form" novalidate>
			<?php // Piège à robots — un vrai visiteur ne voit ni ne remplit jamais ce champ. ?>
			<div class="up2a-preinsc__honeypot" aria-hidden="true">
				<label for="up2a-preinsc-site">Site web</label>
				<input type="text" id="up2a-preinsc-site" name="site_web" tabindex="-1" autocomplete="off">
			</div>

			<section class="up2a-preinsc__step is-active" data-step="1">
				<h2><?php esc_html_e( 'Vos informations personnelles', 'up2a-preinscription' ); ?></h2>

				<div class="up2a-preinsc__row">
					<div class="up2a-preinsc__field">
						<label for="up2a-nom"><?php esc_html_e( 'Nom', 'up2a-preinscription' ); ?> *</label>
						<input type="text" id="up2a-nom" name="nom" required autocomplete="family-name">
					</div>
					<div class="up2a-preinsc__field">
						<label for="up2a-prenom"><?php esc_html_e( 'Prénom', 'up2a-preinscription' ); ?> *</label>
						<input type="text" id="up2a-prenom" name="prenom" required autocomplete="given-name">
					</div>
				</div>

				<div class="up2a-preinsc__row">
					<div class="up2a-preinsc__field">
						<label for="up2a-date-naissance"><?php esc_html_e( 'Date de naissance', 'up2a-preinscription' ); ?> *</label>
						<input type="date" id="up2a-date-naissance" name="date_naissance" required autocomplete="bday">
					</div>
					<div class="up2a-preinsc__field">
						<label for="up2a-sexe"><?php esc_html_e( 'Sexe', 'up2a-preinscription' ); ?> *</label>
						<select id="up2a-sexe" name="sexe" required>
							<option value=""><?php esc_html_e( 'Choisir…', 'up2a-preinscription' ); ?></option>
							<option value="F"><?php esc_html_e( 'Féminin', 'up2a-preinscription' ); ?></option>
							<option value="M"><?php esc_html_e( 'Masculin', 'up2a-preinscription' ); ?></option>
						</select>
					</div>
				</div>

				<div class="up2a-preinsc__row">
					<div class="up2a-preinsc__field">
						<label for="up2a-email"><?php esc_html_e( 'E-mail', 'up2a-preinscription' ); ?> *</label>
						<input type="email" id="up2a-email" name="email" required autocomplete="email">
					</div>
					<div class="up2a-preinsc__field">
						<label for="up2a-telephone"><?php esc_html_e( 'Téléphone', 'up2a-preinscription' ); ?> *</label>
						<input type="tel" id="up2a-telephone" name="telephone" required autocomplete="tel" placeholder="+226 ...">
					</div>
				</div>

				<div class="up2a-preinsc__field">
					<label for="up2a-adresse"><?php esc_html_e( 'Adresse (optionnel)', 'up2a-preinscription' ); ?></label>
					<input type="text" id="up2a-adresse" name="adresse" autocomplete="street-address">
				</div>

				<div class="up2a-preinsc__actions">
					<span></span>
					<button type="button" class="up2a-preinsc__btn up2a-preinsc__btn--primary js-preinsc-next"><?php esc_html_e( 'Suivant', 'up2a-preinscription' ); ?> <?php echo up2a_preinscription_icon( 'arrow' ); ?></button>
				</div>
			</section>

			<section class="up2a-preinsc__step" data-step="2">
				<h2><?php esc_html_e( 'La formation souhaitée', 'up2a-preinscription' ); ?></h2>

				<div class="up2a-preinsc__field">
					<label for="up2a-formation"><?php esc_html_e( 'Licence visée', 'up2a-preinscription' ); ?> *</label>
					<select id="up2a-formation" name="formation" required>
						<option value=""><?php esc_html_e( 'Choisir une formation…', 'up2a-preinscription' ); ?></option>
						<?php foreach ( $formations as $f ) : ?>
							<option value="<?php echo esc_attr( $f['slug'] ); ?>" <?php selected( $formation_preselect, $f['slug'] ); ?>>
								<?php echo esc_html( $f['nom'] ); ?> — <?php echo esc_html( $f['faculte'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="up2a-preinsc__row">
					<div class="up2a-preinsc__field">
						<label for="up2a-diplome"><?php esc_html_e( 'Dernier diplôme obtenu (optionnel)', 'up2a-preinscription' ); ?></label>
						<input type="text" id="up2a-diplome" name="diplome_obtenu" placeholder="<?php esc_attr_e( 'ex. Baccalauréat série D', 'up2a-preinscription' ); ?>">
					</div>
					<div class="up2a-preinsc__field">
						<label for="up2a-etablissement"><?php esc_html_e( "Établissement d'origine (optionnel)", 'up2a-preinscription' ); ?></label>
						<input type="text" id="up2a-etablissement" name="etablissement_origine">
					</div>
				</div>

				<div class="up2a-preinsc__actions">
					<button type="button" class="up2a-preinsc__btn up2a-preinsc__btn--ghost js-preinsc-prev"><?php echo up2a_preinscription_icon( 'arrow-l' ); ?> <?php esc_html_e( 'Précédent', 'up2a-preinscription' ); ?></button>
					<button type="button" class="up2a-preinsc__btn up2a-preinsc__btn--primary js-preinsc-next"><?php esc_html_e( 'Suivant', 'up2a-preinscription' ); ?> <?php echo up2a_preinscription_icon( 'arrow' ); ?></button>
				</div>
			</section>

			<section class="up2a-preinsc__step" data-step="3">
				<h2><?php esc_html_e( 'Vos pièces jointes', 'up2a-preinscription' ); ?></h2>
				<p class="up2a-preinsc__hint"><?php esc_html_e( 'Formats acceptés : JPG, PNG ou PDF, 5 Mo maximum par fichier. Une photo lisible prise avec votre téléphone convient parfaitement.', 'up2a-preinscription' ); ?></p>

				<?php foreach ( $pieces as $key => $piece ) : ?>
					<div class="up2a-preinsc__upload js-preinsc-upload">
						<label for="up2a-fichier-<?php echo esc_attr( $key ); ?>">
							<?php echo up2a_preinscription_icon( 'upload' ); ?>
							<span><?php echo esc_html( $piece['label'] ); ?><?php echo $piece['requis'] ? ' *' : ''; ?></span>
						</label>
						<input
							type="file"
							id="up2a-fichier-<?php echo esc_attr( $key ); ?>"
							name="fichier_<?php echo esc_attr( $key ); ?>"
							accept=".jpg,.jpeg,.png,.pdf"
							<?php echo $piece['requis'] ? 'required' : ''; ?>
						>
						<span class="up2a-preinsc__upload-name js-preinsc-upload-name" aria-live="polite"></span>
					</div>
				<?php endforeach; ?>

				<div class="up2a-preinsc__actions">
					<button type="button" class="up2a-preinsc__btn up2a-preinsc__btn--ghost js-preinsc-prev"><?php echo up2a_preinscription_icon( 'arrow-l' ); ?> <?php esc_html_e( 'Précédent', 'up2a-preinscription' ); ?></button>
					<button type="button" class="up2a-preinsc__btn up2a-preinsc__btn--primary js-preinsc-next"><?php esc_html_e( 'Suivant', 'up2a-preinscription' ); ?> <?php echo up2a_preinscription_icon( 'arrow' ); ?></button>
				</div>
			</section>

			<section class="up2a-preinsc__step" data-step="4">
				<h2><?php esc_html_e( 'Vérifiez et envoyez', 'up2a-preinscription' ); ?></h2>

				<dl class="up2a-preinsc__recap js-preinsc-recap" aria-live="polite"></dl>

				<label class="up2a-preinsc__consent">
					<input type="checkbox" name="consentement" required>
					<span><?php esc_html_e( "Je certifie que les informations fournies sont exactes et j'accepte d'être recontacté(e) par l'UP-2A au sujet de ma candidature.", 'up2a-preinscription' ); ?></span>
				</label>

				<p class="up2a-preinsc__error js-preinsc-error" role="alert" hidden></p>

				<div class="up2a-preinsc__actions">
					<button type="button" class="up2a-preinsc__btn up2a-preinsc__btn--ghost js-preinsc-prev"><?php echo up2a_preinscription_icon( 'arrow-l' ); ?> <?php esc_html_e( 'Précédent', 'up2a-preinscription' ); ?></button>
					<button type="submit" class="up2a-preinsc__btn up2a-preinsc__btn--primary js-preinsc-submit">
						<?php esc_html_e( 'Envoyer ma préinscription', 'up2a-preinscription' ); ?> <?php echo up2a_preinscription_icon( 'check' ); ?>
					</button>
				</div>
			</section>
		</form>

		<div class="up2a-preinsc__success js-preinsc-success" hidden>
			<?php echo up2a_preinscription_icon( 'check' ); ?>
			<h2><?php esc_html_e( 'Préinscription envoyée !', 'up2a-preinscription' ); ?></h2>
			<p><?php esc_html_e( "Un e-mail de confirmation vient de vous être envoyé. L'université étudie votre dossier et vous recontactera prochainement — aucune autre démarche n'est requise de votre part pour le moment.", 'up2a-preinscription' ); ?></p>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'up2a_preinscription', 'up2a_preinscription_shortcode_render' );
