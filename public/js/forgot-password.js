document.addEventListener("DOMContentLoaded", () => {
    const step1Form = document.getElementById("step1-form");
    const step2Form = document.getElementById("step2-form");
    const msgBox = document.getElementById("reset-messageBox");

    if (step1Form) {
        step1Form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(step1Form);
            
            try {
                const res = await fetch("/api/verify_pin.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();
                
                if (data.success) {
                    msgBox.innerHTML = `<div class="alert alert-success auth-alert-custom auth-alert-success">${data.message}</div>`;
                    step1Form.style.display = "none";
                    step2Form.style.display = "block";
                    document.getElementById("reset_token").value = data.reset_token;
                    document.getElementById("email_confirmed").value = formData.get("email");
                } else {
                    msgBox.innerHTML = `<div class="alert alert-danger auth-alert-custom auth-alert-danger">${data.message}</div>`;
                }
            } catch (err) {
                msgBox.innerHTML = `<div class="alert alert-danger auth-alert-custom auth-alert-danger">Erreur de communication avec le serveur.</div>`;
            }
        });
    }

    if (step2Form) {
        step2Form.addEventListener("submit", async (e) => {
            e.preventDefault();
            
            const formData = new FormData(step2Form);
            if (formData.get("new_password") !== formData.get("confirm_password")) {
                msgBox.innerHTML = `<div class="alert alert-danger auth-alert-custom auth-alert-danger">Les mots de passe ne correspondent pas.</div>`;
                return;
            }

            try {
                const res = await fetch("/api/reset_password.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    msgBox.innerHTML = `<div class="alert alert-success auth-alert-custom auth-alert-success">${data.message}</div>`;
                    step2Form.style.display = "none";
                    setTimeout(() => window.location.href = "/api/pages/connexion.php", 1500);
                } else {
                    msgBox.innerHTML = `<div class="alert alert-danger auth-alert-custom auth-alert-danger">${data.message}</div>`;
                }
            } catch (err) {
                msgBox.innerHTML = `<div class="alert alert-danger auth-alert-custom auth-alert-danger">Erreur serveur.</div>`;
            }
        });
    }

    // --- Logique pour la visibilité et la robustesse du mot de passe ---

    // Script pour afficher / masquer les mots de passe
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput && targetInput.type === 'password') {
                targetInput.type = 'text';
                this.textContent = '🙈'; // Oeil fermé
            } else if (targetInput) {
                targetInput.type = 'password';
                this.textContent = '👁️'; // Oeil ouvert
            }
        });
    });

    // Évaluation de la force du mot de passe
    const pwdInput = document.getElementById('new_password');
    const counter = document.getElementById('pwd-counter');
    const bar = document.getElementById('pwd-strength-bar');
    const fill = document.getElementById('pwd-strength-fill');
    const label = document.getElementById('pwd-strength-label');

    if (pwdInput && counter && bar && fill && label) {
        const levels = [
            { min: 0,   max: 25,  color: '#D32F2F', text: '❌ Très faible', textColor: '#D32F2F' },
            { min: 26,  max: 50,  color: '#FF7043', text: '⚠️ Faible',      textColor: '#FF7043' },
            { min: 51,  max: 75,  color: '#FFC107', text: '🔶 Moyen',       textColor: '#e6a800' },
            { min: 76,  max: 99,  color: '#8BC34A', text: '✅ Fort',        textColor: '#558B2F' },
            { min: 100, max: 100, color: '#4CAF50', text: '🔒 Très fort',   textColor: '#2E7D32' },
        ];

        function getScore(pwd) { 
            if (!pwd) return 0;
            let score = 0;
            if (pwd.length >= 8)  score += 20;
            if (pwd.length >= 12) score += 10;
            if (pwd.length >= 16) score += 10;
            if (/[a-z]/.test(pwd))        score += 10;
            if (/[A-Z]/.test(pwd))        score += 20;
            if (/[0-9]/.test(pwd))        score += 15;
            if (/[^a-zA-Z0-9]/.test(pwd)) score += 25;
            if (/^[a-zA-Z]+$/.test(pwd))  score -= 10;
            if (/^[0-9]+$/.test(pwd))     score -= 15;
            return Math.max(0, Math.min(100, score));
        }

        pwdInput.addEventListener('input', function () { 
            const pwd   = this.value;
            const len   = pwd.length;
            const score = getScore(pwd);

            counter.textContent = len + ' / 8 — minimum 8 caractères';
            counter.style.color = len >= 8 ? '#4CAF50' : '#888';

            if (len === 0) {
                bar.style.display   = 'none';
                label.style.display = 'none';
                return;
            }

            bar.style.display   = 'block';
            label.style.display = 'block';
            fill.style.width    = score + '%';

            const level = levels.find(l => score >= l.min && score <= l.max) || levels[0]; 
            fill.style.backgroundColor = level.color;
            label.textContent          = level.text;
            label.style.color          = level.textColor;
        });
    }
});