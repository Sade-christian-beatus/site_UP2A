/**
 * Interactions de l'écran de réglages "SEO" (wp-admin) : choisir l'image
 * de partage par défaut depuis la médiathèque (API `wp.media`, chargée
 * par `wp_enqueue_media()` — voir inc/seo.php). Même logique que
 * up2a-header/assets/js/admin-settings.js.
 */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var pickBtn = document.querySelector(".up2a-seo-pick-image");
    var removeBtn = document.querySelector(".up2a-seo-remove-image");
    var thumb = document.querySelector(".up2a-seo-thumb");
    var hiddenInput = document.querySelector(".up2a-seo-image-id");

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
