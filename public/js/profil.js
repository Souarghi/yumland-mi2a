// ============================================================
// PROFIL — Modification asynchrone (fetch)
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
  const sanitizeAlphaText = (value) => value
    .replace(/[^\p{L}\p{M}\s'-]/gu, "")
    .replace(/\s{2,}/g, " ")
    .trimStart();
  const sanitizeDigits = (value, maxLength) => value.replace(/\D/g, "").slice(0, maxLength);
  const sanitizePhone = (value) => value
    .replace(/[^\d+\s.-]/g, "")
    .replace(/(?!^)\+/g, "")
    .replace(/\s{2,}/g, " ")
    .trimStart();
  const personRegex = /^[\p{L}\p{M}]+(?:[ '\-][\p{L}\p{M}]+)*$/u;
  const phoneRegex = /^(?:\+33|0)[1-9](?:[\s.-]?\d{2}){4}$/;

  const editBtn   = document.getElementById("btn-edit-profil");
  const saveBtn   = document.getElementById("btn-save-profil");
  const cancelBtn = document.getElementById("btn-cancel-profil");
  const msgBox    = document.getElementById("profil-message");
  const adresseContainer = document.getElementById("adresse-container"); // Nouveau conteneur

  // Champs éditables
  const fields = ["nom", "prenom", "tel", "rue", "code_postal", "ville", "complement", "pin"];

  // Valeurs originales (pour annulation)
  let originalValues = {};

  const setMessage = (type, text) => {
    if (!msgBox) return false;
    msgBox.className = type;
    msgBox.textContent = text;
    return true;
  };

  const inputByField = (name) => document.querySelector(`[data-field="${name}"]`);

  const applyFieldRestrictions = () => {
    ["nom", "prenom", "ville"].forEach(name => {
      const input = inputByField(name);
      if (!input) return;
      input.addEventListener("input", () => {
        input.value = sanitizeAlphaText(input.value);
      });
    });

    const postalCodeInput = inputByField("code_postal");
    if (postalCodeInput) {
      postalCodeInput.addEventListener("input", () => {
        postalCodeInput.value = sanitizeDigits(postalCodeInput.value, 5);
      });
    }

    const pinInput = inputByField("pin");
    if (pinInput) {
      pinInput.addEventListener("input", () => {
        pinInput.value = sanitizeDigits(pinInput.value, 6);
      });
    }

    const phoneInput = inputByField("tel");
    if (phoneInput) {
      phoneInput.addEventListener("input", () => {
        phoneInput.value = sanitizePhone(phoneInput.value);
      });
    }
  };

  const validatePayload = (payload) => {
    if (!personRegex.test(payload.nom.trim())) {
      return "Le nom ne doit contenir que des lettres, espaces, apostrophes ou tirets.";
    }
    if (!personRegex.test(payload.prenom.trim())) {
      return "Le prénom ne doit contenir que des lettres, espaces, apostrophes ou tirets.";
    }
    if (payload.tel.trim() && !phoneRegex.test(payload.tel.trim())) {
      return "Le numéro de téléphone doit être au format français valide.";
    }
    if (payload.code_postal.trim() && !/^\d{5}$/.test(payload.code_postal.trim())) {
      return "Le code postal doit contenir exactement 5 chiffres.";
    }
    if (payload.ville.trim() && !personRegex.test(payload.ville.trim())) {
      return "La ville ne doit contenir que des lettres, espaces, apostrophes ou tirets.";
    }
    if (payload.pin.trim() && !/^\d{6}$/.test(payload.pin.trim())) {
      return "Le code PIN doit contenir exactement 6 chiffres.";
    }

    return "";
  };

  applyFieldRestrictions();

  // ── Passer en mode édition ────────────────────────────────
  if (editBtn) {
    editBtn.addEventListener("click", () => {
      fields.forEach(name => {
        const input = document.querySelector(`[data-field="${name}"]`);
        if (!input) return;
        originalValues[name] = input.value; // Sauvegarder
        input.disabled = false;
        input.classList.add("editing");
      });
      editBtn.style.display   = "none";
      saveBtn.style.display   = "inline-block";
      cancelBtn.style.display = "inline-block";
      
      // Afficher les champs d'adresse
      if (adresseContainer) adresseContainer.style.display = "block";
    });
  }

  // ── Annuler les modifications ─────────────────────────────
  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      fields.forEach(name => {
        const input = document.querySelector(`[data-field="${name}"]`);
        if (!input) return;
        input.value    = originalValues[name]; // Restaurer
        input.disabled = true;
        input.classList.remove("editing");
      });
      editBtn.style.display   = "inline-block";
      saveBtn.style.display   = "none";
      cancelBtn.style.display = "none";
      if (msgBox) msgBox.textContent = "";
      
      // Masquer les champs d'adresse
      if (adresseContainer) adresseContainer.style.display = "none";
    });
  }

  // ── Sauvegarder via fetch ─────────────────────────────────
  if (saveBtn) {
    saveBtn.addEventListener("click", async () => {
      const payload = {};
      fields.forEach(name => {
        const input = inputByField(name);
        if (input) payload[name] = input.value.trim();
      });
      const csrfTokenInput = document.querySelector('#profile-form [name="csrf_token"]');
      if (csrfTokenInput) {
        payload.csrf_token = csrfTokenInput.value;
      }

      const validationError = validatePayload(payload);
      if (validationError) {
        setMessage("error", validationError);
        return;
      }

      try {
        const response = await fetch("/api/client/update_profil_ajax.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        const data = await response.json();

        setMessage(data.success ? "success" : "error", data.message);

        if (data.success) {
          // Repasser en mode lecture
          fields.forEach(name => {
            const input = inputByField(name);
            if (input) { input.disabled = true; input.classList.remove("editing"); }
          });
          editBtn.style.display   = "inline-block";
          saveBtn.style.display   = "none";
          cancelBtn.style.display = "none";
          
          // Masquer les champs d'adresse après sauvegarde réussie
          if (adresseContainer) adresseContainer.style.display = "none";
        }
      } catch (err) {
        setMessage("error", "Erreur réseau.");
      }
    });
  }
});
