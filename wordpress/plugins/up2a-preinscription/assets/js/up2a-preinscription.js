/**
 * Formulaire de préinscription multi-étapes (wordpress/plugins/
 * up2a-preinscription/inc/shortcode.php). Indépendant de tout CDN externe
 * — doit fonctionner même si GSAP/autre échoue à charger ailleurs sur le
 * site (voir CLAUDE.md §7). La validation ici n'est qu'un confort pour le
 * visiteur : la vraie autorité est la validation serveur dans inc/rest.php.
 */
(function () {
  "use strict";

  var root = document.querySelector(".js-preinsc");
  if (!root) {
    return;
  }

  var form = root.querySelector(".js-preinsc-form");
  var stepperItems = root.querySelectorAll(".js-preinsc-stepper li");
  var steps = root.querySelectorAll(".up2a-preinsc__step");
  var successEl = root.querySelector(".js-preinsc-success");
  var errorEl = root.querySelector(".js-preinsc-error");
  var recapEl = root.querySelector(".js-preinsc-recap");
  var submitBtn = root.querySelector(".js-preinsc-submit");
  var current = 1;

  function showStep(n) {
    current = n;
    steps.forEach(function (step) {
      step.classList.toggle("is-active", parseInt(step.dataset.step, 10) === n);
    });
    stepperItems.forEach(function (item) {
      var stepNum = parseInt(item.dataset.step, 10);
      item.classList.toggle("is-active", stepNum === n);
      item.classList.toggle("is-done", stepNum < n);
    });
    if (n === 4) {
      buildRecap();
    }
    root.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  function currentStepEl() {
    return root.querySelector('.up2a-preinsc__step[data-step="' + current + '"]');
  }

  function validateStep(stepEl) {
    var fields = stepEl.querySelectorAll("input[required], select[required]");
    var valid = true;
    fields.forEach(function (field) {
      if (!field.checkValidity()) {
        field.reportValidity();
        valid = false;
      }
    });
    return valid;
  }

  root.querySelectorAll(".js-preinsc-next").forEach(function (btn) {
    btn.addEventListener("click", function () {
      if (!validateStep(currentStepEl())) {
        return;
      }
      if (current < steps.length) {
        showStep(current + 1);
      }
    });
  });

  root.querySelectorAll(".js-preinsc-prev").forEach(function (btn) {
    btn.addEventListener("click", function () {
      if (current > 1) {
        showStep(current - 1);
      }
    });
  });

  root.querySelectorAll(".js-preinsc-upload input[type=file]").forEach(function (input) {
    input.addEventListener("change", function () {
      var nameEl = input.closest(".js-preinsc-upload").querySelector(".js-preinsc-upload-name");
      if (nameEl) {
        nameEl.textContent = input.files.length ? input.files[0].name : "";
      }
    });
  });

  function fieldLabel(name) {
    var el = form.querySelector('[name="' + name + '"]');
    if (!el) {
      return "";
    }
    if (el.tagName === "SELECT") {
      return el.options[el.selectedIndex] ? el.options[el.selectedIndex].textContent : "";
    }
    return el.value;
  }

  function buildRecap() {
    if (!recapEl) {
      return;
    }
    var rows = [
      [up2aL("Nom complet"), fieldLabel("prenom") + " " + fieldLabel("nom")],
      [up2aL("E-mail"), fieldLabel("email")],
      [up2aL("Téléphone"), fieldLabel("telephone")],
      [up2aL("Formation"), fieldLabel("formation")],
    ];
    recapEl.innerHTML = "";
    rows.forEach(function (row) {
      var dt = document.createElement("dt");
      dt.textContent = row[0];
      var dd = document.createElement("dd");
      dd.textContent = row[1] || "—";
      recapEl.appendChild(dt);
      recapEl.appendChild(dd);
    });
  }

  function up2aL(s) {
    return s; // libellés du récapitulatif, volontairement simples (pas de contenu dupliqué depuis le PHP ici).
  }

  function showError(message) {
    if (!errorEl) {
      return;
    }
    errorEl.textContent = message;
    errorEl.hidden = false;
  }

  function clearError() {
    if (errorEl) {
      errorEl.hidden = true;
      errorEl.textContent = "";
    }
  }

  form.addEventListener("submit", function (event) {
    event.preventDefault();
    if (!validateStep(currentStepEl())) {
      return;
    }
    clearError();

    var config = window.UP2A_PREINSCRIPTION_CONFIG;
    if (!config || !config.endpoint) {
      showError("Configuration manquante, merci de recharger la page.");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.classList.add("is-loading");

    var formData = new FormData(form);

    fetch(config.endpoint, {
      method: "POST",
      headers: { "X-WP-Nonce": config.nonce },
      body: formData,
    })
      .then(function (response) {
        return response.json().then(function (data) {
          return { ok: response.ok, data: data };
        });
      })
      .then(function (result) {
        if (!result.ok) {
          throw new Error((result.data && result.data.message) || "Une erreur est survenue.");
        }
        form.hidden = true;
        root.querySelector(".js-preinsc-stepper").hidden = true;
        successEl.hidden = false;
      })
      .catch(function (err) {
        showError(err.message || "Une erreur est survenue, merci de réessayer.");
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.classList.remove("is-loading");
      });
  });

  showStep(1);
})();
