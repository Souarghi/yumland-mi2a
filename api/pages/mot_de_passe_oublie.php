<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirigé si déjà connecté
if (isLoggedIn()) {
    redirect('/api/index.php');
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

$currentPage = 'mot_de_passe_oublie';
$pageTitle = 'Mot de passe oublié';
$additionalCss = ['/css/auth.css'];

include_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container auth-container-center">
            <h2>Récupération</h2>
            <div id="reset-messageBox"></div>

            <!-- Etape 1: Vérification du compte via PIN -->
            <form id="step1-form" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="pin">Code PIN (6 chiffres) *</label>
                    <input type="text" id="pin" name="pin" pattern="\d{6}" maxlength="6" required autocomplete="off">
                    <small style="color: #666; display: block; margin-top: 5px;">Saisissez le code PIN défini dans votre profil.</small>
                </div>
                <button type="submit" class="btn-primary btn-login-modern">Vérifier le compte</button>
            </form>

            <!-- Etape 2: Saisie du nouveau mot de passe (Caché initialement) -->
            <form id="step2-form" class="auth-form" style="display: none;">
                <input type="hidden" name="reset_token" id="reset_token" value="">
                <input type="hidden" name="email_confirmed" id="email_confirmed" value="">
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe *</label>
                    <div class="pwd-input-wrapper">
                        <input type="password" id="new_password" name="new_password" required autocomplete="new-password" class="pwd-input-flex">
                        <button type="button" class="toggle-password" data-target="new_password">👁️</button>
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
                <button type="submit" class="btn-primary btn-login-modern">Réinitialiser le mot de passe</button>
            </form>

            <div class="auth-links auth-links-center auth-links-top">
                <p><a href="/api/pages/connexion.php">Retour à la connexion</a></p>
            </div>
        </div>
    </div>
</section>

<script src="/js/forgot-password.js" defer></script>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>