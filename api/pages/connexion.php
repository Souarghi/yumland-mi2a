<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Rediriger si l'utilisateur est déjà connecté
if (isLoggedIn()) {
    redirect('/api/index.php');
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'connexion';
$pageTitle = 'Connexion';

// Ajout de la feuille de style spécifique
$additionalCss = ['/css/auth.css'];

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container auth-container-center">
            <h2>Connexion</h2>
            
            <div id="login-error" class="alert alert-danger hidden-alert"></div>
            
            <?php if (isset($_GET['error']) && $_GET['error'] === 'must_login'): ?>
                <div class="alert alert-info auth-alert-info">
                    ⚠️ <strong>Accès requis :</strong> Vous devez vous connecter ou créer un compte pour valider votre panier et procéder au paiement.
                </div>
            <?php endif; ?>
            
            <form id="loginForm" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="current-password" class="password-input">
                        <button type="button" class="toggle-password btn-toggle-password" data-target="password">👁️</button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login-modern">Se connecter 🔐</button>
            </form>
            
            <div class="auth-links auth-links-center">
                <p>Pas encore de compte ? <a href="/api/pages/inscription.php">S'inscrire</a></p>
            </div>

            <div class="test-accounts card-style test-accounts-box">
                <h3 class="test-accounts-title">🧪 Accès rapides (Démo)</h3>
                <p class="test-accounts-desc">Cliquez sur un profil pour auto-remplir les identifiants :</p>
                
                <div class="test-accounts-row">
                    <button type="button" class="btn-primary btn-test-account bg-client" onclick="fillLogin('client1@example.com', 'password')">👤 Client 1</button>
                    <button type="button" class="btn-primary btn-test-account bg-client" onclick="fillLogin('client2@example.com', 'password')">👤 Client 2</button>
                    <button type="button" class="btn-primary btn-test-account bg-client" onclick="fillLogin('client3@example.com', 'password')">👤 Client 3</button>
                    <button type="button" class="btn-primary btn-test-account bg-client" onclick="fillLogin('client4@example.com', 'password')">👤 Client 4</button>
                    <button type="button" class="btn-primary btn-test-account bg-client" onclick="fillLogin('client5@example.com', 'password')">👤 Client 5</button>
                </div>
                
                <div class="test-accounts-row last">
                    <button type="button" class="btn-primary btn-test-account bg-admin" onclick="fillLogin('admin1@grandmiam.com', 'password')">🛡️ Admin 1</button>
                    <button type="button" class="btn-primary btn-test-account bg-admin" onclick="fillLogin('admin2@grandmiam.com', 'password')">🛡️ Admin 2</button>
                    <button type="button" class="btn-primary btn-test-account bg-resto" onclick="fillLogin('resto@grandmiam.com', 'password')">👨‍🍳 Chef</button>
                    <button type="button" class="btn-primary btn-test-account bg-livreur" onclick="fillLogin('livreur1@grandmiam.com', 'password')">🛵 Livreur 1</button>
                    <button type="button" class="btn-primary btn-test-account bg-livreur" onclick="fillLogin('livreur2@grandmiam.com', 'password')">🛵 Livreur 2</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function fillLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}

// Soumission AJAX du formulaire de connexion
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('login-error');
    
    fetch('/api/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirection unique vers l'accueil pour utiliser le menu déroulant
            window.location.href = '/api/index.php';
        } else {
            errorDiv.style.display = 'block';
            errorDiv.textContent = data.message;
        }
    })
    .catch(err => {
        console.error(err);
        errorDiv.style.display = 'block';
        errorDiv.textContent = "Erreur de connexion au serveur.";
    });
});

// Script pour afficher/masquer le mot de passe
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
</script>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>
