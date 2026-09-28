/**
 * Interactions de l'écran de réglages "Actualités" (wp-admin) : ajouter/
 * supprimer/réordonner une actualité, choisir l'image depuis la
 * médiathèque (API `wp.media`). Même logique que
 * up2a-slider/assets/js/admin-settings.js (voir ce fichier pour le
 * détail du raisonnement).
 */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var container = document.getElementById("up2a-actualites-rows");
    var template = document.getElementById("up2a-actualites-row-template");
    var addBtn = document.getElementById("up2a-actualites-add");

    if (!container || !template || !addBtn) {
      return;
    }

    var counter = container.querySelectorAll(".up2a-actualites-row").length;

    function bindRow(row) {
      var pickBtn = row.querySelector(".up2a-actualites-pick-image");
      var removeImgBtn = row.querySelector(".up2a-actualites-remove-image");
      var thumb = row.querySelector(".up2a-actualites-row__thumb");
      var hiddenInput = row.querySelector(".up2a-actualites-image-id");

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

      var upBtn = row.querySelector(".up2a-actualites-move-up");
      var downBtn = row.querySelector(".up2a-actualites-move-down");
      var delBtn = row.querySelector(".up2a-actualites-remove-row");

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
          if (window.confirm("Supprimer cette actualité ?")) {
            row.remove();
          }
        });
      }
    }

    container.querySelectorAll(".up2a-actualites-row").forEach(bindRow);

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
