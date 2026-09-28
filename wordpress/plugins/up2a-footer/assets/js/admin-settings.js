/**
 * Interactions de l'écran de réglages "Pied de page" (wp-admin) : choisir
 * le logo (média unique) + répéteur des liens rapides (ajouter/supprimer,
 * pas de réordonnancement — l'ordre des liens rapides importe peu). Même
 * logique que up2a-header/assets/js/admin-settings.js pour le logo, et
 * up2a-formations pour le répéteur (voir ces fichiers pour le détail).
 */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var pickBtn = document.querySelector(".up2a-footer-pick-image");
    var removeBtn = document.querySelector(".up2a-footer-remove-image");
    var thumb = document.querySelector(".up2a-footer-thumb");
    var hiddenInput = document.querySelector(".up2a-footer-image-id");

    if (pickBtn && hiddenInput && window.wp && window.wp.media) {
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
    }

    if (removeBtn) {
      removeBtn.addEventListener("click", function () {
        hiddenInput.value = "0";
        thumb.innerHTML = "";
        removeBtn.style.display = "none";
      });
    }

    var container = document.getElementById("up2a-footer-liens");
    var template = document.getElementById("up2a-footer-lien-template");
    var addBtn = document.getElementById("up2a-footer-add-lien");

    if (!container || !template || !addBtn) {
      return;
    }

    var counter = container.querySelectorAll(".up2a-footer-lien-row").length;

    function bindRow(row) {
      var delBtn = row.querySelector(".up2a-footer-remove-lien");
      if (delBtn) {
        delBtn.addEventListener("click", function () {
          row.remove();
        });
      }
    }

    container.querySelectorAll(".up2a-footer-lien-row").forEach(bindRow);

    addBtn.addEventListener("click", function () {
      var index = "new" + counter++;
      var fragment = template.content.cloneNode(true);
      var wrapper = document.createElement("div");
      wrapper.appendChild(fragment);
      wrapper.innerHTML = wrapper.innerHTML.split("__INDEX__").join(index);
      var newRow = wrapper.firstElementChild;
      container.appendChild(newRow);
      bindRow(newRow);
    });
  });
})();
