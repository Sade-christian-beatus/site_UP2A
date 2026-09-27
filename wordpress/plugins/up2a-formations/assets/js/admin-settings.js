/**
 * Interactions de l'écran de réglages "Formations" (wp-admin) : ajouter/
 * supprimer/réordonner une ligne, choisir une photo depuis la médiathèque
 * (API `wp.media`, chargée par `wp_enqueue_media()` — voir inc/settings.php).
 * Vanilla JS, pas de dépendance jQuery dans ce fichier (même si wp.media
 * en dépend en interne, ce n'est pas notre affaire ici).
 */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var container = document.getElementById("up2a-formations-rows");
    var template = document.getElementById("up2a-formations-row-template");
    var addBtn = document.getElementById("up2a-formations-add");

    if (!container || !template || !addBtn) {
      return;
    }

    var counter = container.querySelectorAll(".up2a-formations-row").length;

    function bindRow(row) {
      var pickBtn = row.querySelector(".up2a-formations-pick-image");
      var removeImgBtn = row.querySelector(".up2a-formations-remove-image");
      var thumb = row.querySelector(".up2a-formations-row__thumb");
      var hiddenInput = row.querySelector(".up2a-formations-image-id");

      if (pickBtn && window.wp && window.wp.media) {
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
              attachment.sizes && attachment.sizes.thumbnail
                ? attachment.sizes.thumbnail.url
                : attachment.url;
            thumb.innerHTML = "";
            var img = document.createElement("img");
            img.src = url;
            thumb.appendChild(img);
            if (removeImgBtn) {
              removeImgBtn.style.display = "";
            }
          });
          frame.open();
        });
      }

      if (removeImgBtn) {
        removeImgBtn.addEventListener("click", function () {
          hiddenInput.value = "0";
          thumb.innerHTML = "";
          removeImgBtn.style.display = "none";
        });
      }

      var upBtn = row.querySelector(".up2a-formations-move-up");
      var downBtn = row.querySelector(".up2a-formations-move-down");
      var delBtn = row.querySelector(".up2a-formations-remove-row");

      if (upBtn) {
        upBtn.addEventListener("click", function () {
          var prev = row.previousElementSibling;
          if (prev) {
            row.parentNode.insertBefore(row, prev);
          }
        });
      }

      if (downBtn) {
        downBtn.addEventListener("click", function () {
          var next = row.nextElementSibling;
          if (next) {
            row.parentNode.insertBefore(next, row);
          }
        });
      }

      if (delBtn) {
        delBtn.addEventListener("click", function () {
          if (window.confirm(delBtn.dataset.confirm || "Supprimer cette entrée ?")) {
            row.remove();
          }
        });
      }
    }

    container.querySelectorAll(".up2a-formations-row").forEach(bindRow);

    addBtn.addEventListener("click", function () {
      var index = "new" + counter++;
      var fragment = template.content.cloneNode(true);
      var wrapper = document.createElement("div");
      wrapper.appendChild(fragment);
      wrapper.innerHTML = wrapper.innerHTML.split("__INDEX__").join(index);
      var newRow = wrapper.firstElementChild;
      container.appendChild(newRow);
      bindRow(newRow);
      newRow.scrollIntoView({ behavior: "smooth", block: "center" });
    });
  });
})();
