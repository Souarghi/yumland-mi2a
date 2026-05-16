<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirigé si déjà connecté
if (isLoggedIn()) {
    redirect('/api/index.php');
}

$error = '';
$success = '';

// Valeurs par défaut pour le pré-remplissage
$nom_val = '';
$prenom_val = '';
$email_val = '';
$telephone_val = '';
$rue_val = '';
$cp_val = '';
$ville_val = '';
$complement_val = '';

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Erreur de sécurité, veuillez réessayer.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Récupération pour pré-remplissage et traitement
        $email_val = trim($_POST['email'] ?? '');
        $nom_val = trim($_POST['nom'] ?? '');
        $prenom_val = trim($_POST['prenom'] ?? '');
        $telephone_val = trim($_POST['telephone'] ?? '');
        $rue_val = trim($_POST['rue'] ?? '');
        $cp_val = trim($_POST['code_postal'] ?? '');
        $ville_val = trim($_POST['ville'] ?? '');
        $complement_val = trim($_POST['complement'] ?? '');
        
        // Validation des champs
        if (empty($password) || empty($confirm_password) || empty($email_val) || empty($nom_val) || empty($prenom_val) || empty($rue_val) || empty($cp_val) || empty($ville_val)) {
            $error = 'Veuillez remplir tous les champs obligatoires.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } elseif (strlen($password) < 8) {
            $error = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif (!filter_var($email_val, FILTER_VALIDATE_EMAIL)) {
            $error = 'Veuillez entrer une adresse email valide.';
        } else {
            // Préparer les données pour la fonction registerUser
            $userData = [
                'password' => $password,
                'email' => $email_val,
                'nom' => $nom_val,
                'prenom' => $prenom_val,
                'telephone' => $telephone_val,
                'rue' => $rue_val,
                'code_postal' => $cp_val,
                'ville' => $ville_val,
                'complement' => $complement_val
            ];
            
            // Appel à la base de données
            $result = registerUser($userData);
            
            if ($result) {
                $success = 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.';
            } else {
                $error = 'Cette adresse email est déjà utilisée. Veuillez en choisir une autre ou vous connecter.';
            }
        }
    }
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'inscription';
$pageTitle = 'Inscription';

// Ajout de la feuille de style spécifique
$additionalCss = ['/css/auth.css'];

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <h2>Créer un compte</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger auth-alert-custom auth-alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success auth-alert-custom auth-alert-success">
                    <?= htmlspecialchars($success) ?>
                    <p><a href="/api/pages/connexion.php">Se connecter</a></p>
                </div>
            <?php else: ?>
                <form action="/api/pages/inscription.php" method="post" class="auth-form">
                    <div id="js-error-message" class="alert alert-danger auth-alert-custom auth-alert-danger hidden-alert"></div>
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom_val) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom_val) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email_val) ?>" required autocomplete="username">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Mot de passe *</label>
                            <div class="pwd-input-wrapper">
                                <input type="password" id="password" name="password" required autocomplete="new-password" class="pwd-input-flex">
                                <button type="button" class="toggle-password" data-target="password">👁️</button>
                            </div>
                            <small id="pwd-counter" class="pwd-counter">0 / 8 — minimum 8 caractères</small>
                            <div id="pwd-strength-bar" class="pwd-strength-bar">
                                <div id="pwd-strength-fill" class="pwd-strength-fill"></div>
                            </div>
                            <small id="pwd-strength-label" class="pwd-strength-label"></small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe *</label>
                            <div class="pwd-input-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" class="pwd-input-flex">
                                <button type="button" class="toggle-password" data-target="confirm_password">👁️</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($telephone_val) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="rue">Rue / Numéro *</label>
                        <input type="text" id="rue" name="rue" value="<?= htmlspecialchars($rue_val) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="complement">Complément (Bâtiment, Étage...)</label>
                        <input type="text" id="complement" name="complement" value="<?= htmlspecialchars($complement_val) ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="code_postal">Code Postal *</label>
                            <input type="text" id="code_postal" name="code_postal" value="<?= htmlspecialchars($cp_val) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="ville">Ville *</label>
                            <input type="text" id="ville" name="ville" value="<?= htmlspecialchars($ville_val) ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary">S'inscrire</button>
                </form>
                
                <div class="auth-links auth-links-top">
                    <p>Déjà inscrit ? <a href="/api/pages/connexion.php">Se connecter</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    // Script évaluation de la force du mot de passe
(function () {
    const input   = document.getElementById('password');
    const counter = document.getElementById('pwd-counter');
    const bar     = document.getElementById('pwd-strength-bar');
    const fill    = document.getElementById('pwd-strength-fill');
    const label   = document.getElementById('pwd-strength-label');

    const levels = [
        { min: 0,   max: 25,  color: '#D32F2F', text: '❌ Très faible', textColor: '#D32F2F' },
        { min: 26,  max: 50,  color: '#FF7043', text: '⚠️ Faible',      textColor: '#FF7043' },
        { min: 51,  max: 75,  color: '#FFC107', text: '🔶 Moyen',       textColor: '#e6a800' },
        { min: 76,  max: 99,  color: '#8BC34A', text: '✅ Fort',        textColor: '#558B2F' },
        { min: 100, max: 100, color: '#4CAF50', text: '🔒 Très fort',   textColor: '#2E7D32' },
    ];

    function getScore(pwd) { // Calcul du score de fiabilité du mot de passe
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

    input.addEventListener('input', function () { // Mise à jour de l'affichage à chaque frappe
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

        const level = levels.find(l => score >= l.min && score <= l.max) || levels[0]; // Affichage de la barre adapté au mot de pase
        fill.style.backgroundColor = level.color;
        label.textContent          = level.text;
        label.style.color          = level.textColor;
    });
})();

// Script pour afficher / masquer les mots de passe
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = '🙈'; // Oeil fermé
        } else {
            input.type = 'password';
            this.textContent = '👁️'; // Oeil ouvert
        }
    });
});

// Script de validation asynchrone du formulaire (Phase 3)
document.querySelector('.auth-form').addEventListener('submit', function(event) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const email = document.getElementById('email').value;
    const errorDiv = document.getElementById('js-error-message');
    let errors = [];

    if (password.length < 8) {
        errors.push("Le mot de passe doit contenir au moins 8 caractères.");
    }
    if (password !== confirmPassword) {
        errors.push("Les mots de passe ne correspondent pas.");
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        errors.push("Veuillez entrer une adresse email valide.");
    }

    if (errors.length > 0) {
        event.preventDefault(); // Stoppe l'envoi au serveur et le rechargement de la page
        errorDiv.innerHTML = errors.join('<br>');
        errorDiv.style.display = 'block';
    }
});
</script>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>
