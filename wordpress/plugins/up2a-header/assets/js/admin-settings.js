/**
 * Interactions de l'écran de réglages "En-tête" (wp-admin) : choisir le
 * logo depuis la médiathèque (API `wp.media`, chargée par
 * `wp_enqueue_media()` — voir inc/settings.php). Pas de répéteur ici
 * (une seule instance d'en-tête) — juste un bouton de sélection d'image,
 * même logique que les plugins Formations/Galerie mais sans les lignes
 * ajouter/supprimer/réordonner.
 */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var pickBtn = document.querySelector(".up2a-header-pick-image");
    var removeBtn = document.querySelector(".up2a-header-remove-image");
    var thumb = document.querySelector(".up2a-header-thumb");
    var hiddenInput = document.querySelector(".up2a-header-image-id");

    if (!pickBtn || !hiddenInput || !window.wp || !window.wp.media) {
      return;
    }

    pickBtn.addEventListener("click", function () {
      var frame = window.wp.media({
        title: pickBtn.textContent,
        multiple: false,
        library: { type: "image" },
      });
      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();
        hiddenInput.value = attachment.id;
        var url =
          attachment.sizes && attachment.sizes.medium
            ? attachment.sizes.medium.url
            : attachment.url;
        thumb.innerHTML = "";
        var img = document.createElement("img");
        img.src = url;
        thumb.appendChild(img);
        if (removeBtn) {
          removeBtn.style.display = "";
        }
      });
      frame.open();
    });

    if (removeBtn) {
      removeBtn.addEventListener("click", function () {
        hiddenInput.value = "0";
        thumb.innerHTML = "";
        removeBtn.style.display = "none";
      });
    }
  });
})();
