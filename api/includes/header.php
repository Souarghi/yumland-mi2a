<?php
// api/includes/header.php
require_once __DIR__ . '/bootstrap.php';

// Vérifier si l'utilisateur connecté est bloqué
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT statut FROM Utilisateurs WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u && $u['statut'] === 'Bloqué') {
        session_destroy();
        header('Location: /api/pages/connexion.php?error=compte_bloque');
        exit;
    }
}

// Récupérer le nombre d'articles dans le panier
$cartItemCount = getCartItemCount();

// Lecture des cookies d'accessibilité côté serveur (évite l'effet de flash blanc au chargement)
$themeClass = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-mode' : '';
$fontClass = (isset($_COOKIE['font']) && $_COOKIE['font'] === 'dyslexic') ? 'dyslexic-mode' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME ?></title>
    <link rel="icon" type="image/x-icon" href="/docs/logo-le-grand-miam.ico">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/header.css?v=<?= time() ?>">
    <?php if ($themeClass === 'dark-mode'): ?>
        <link rel="stylesheet" href="/css/dark-mode.css" id="dark-mode-stylesheet">
    <?php endif; ?>
    <!-- Intégration de FontAwesome pour des icônes professionnelles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Intégration de la police OpenDyslexic pour l'accessibilité -->
    <link href="https://fonts.cdnfonts.com/css/opendyslexic" rel="stylesheet">
    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <script defer src="/public/js/cookie-consent.js"></script>
    <?php if (isset($additionalJs)): ?>
        <?php foreach ($additionalJs as $js): ?>
            <script defer src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <script defer src="/js/script.js?v=<?= time() ?>"></script>
    <script defer>
        // Script pour rendre le menu déroulant persistant au clic (très utile sur mobile et tablette)
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggle = document.querySelector('.dropdown-toggle');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            if (dropdownToggle && dropdownMenu) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdownMenu.classList.toggle('show');
                });
                
                // Ferme le menu si on clique en dehors
                document.addEventListener('click', function(e) {
                    if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });
            }
            
            // Gestion des boutons d'accessibilité
            const btnDyslexic = document.getElementById('toggle-dyslexic-mode');

            if (btnDyslexic) {
                btnDyslexic.addEventListener('click', function() {
                    document.body.classList.toggle('dyslexic-mode');
                    document.cookie = "font=" + (document.body.classList.contains('dyslexic-mode') ? "dyslexic" : "standard") + "; path=/; max-age=31536000";
                });
            }
        });
    </script>
    <script defer src="/js/form-validation.js?v=<?= time() ?>"></script>
</head>
<body class="<?= trim($themeClass . ' ' . $fontClass) ?>">

<header class="main-site-header">
    <nav>
        <div class="logo-container">
            <a href="/api/index.php" class="logo-text">Le <span class="text-highlight">Grand</span> Miam</a>
        </div>
        <ul class="nav-links">
            <li><a href="/api/index.php" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Accueil</a></li>
            <li><a href="/api/pages/carte.php" class="<?= $currentPage === 'carte' ? 'active' : '' ?>">La Carte</a></li>
            <li><a href="/api/pages/avis.php" class="<?= $currentPage === 'avis' ? 'active' : '' ?>">Avis</a></li>
            
            <?php if (isLoggedIn()): ?>
                <!-- Boutons d'accès rapide pour le Staff -->
                <?php if (hasRole('Restaurateur')): ?>
                    <li><a href="/api/restaurateur/commandes.php" class="btn-nav-cuisine"><i class="fas fa-fire-burner"></i> Cuisine</a></li>
                <?php elseif (hasRole('Livreur')): ?>
                    <li><a href="/api/livreur/livraisons.php" class="btn-nav-courses"><i class="fas fa-motorcycle"></i> Courses</a></li>
                <?php endif; ?>
                
                <!-- Menu déroulant classique pour TOUS -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <?= htmlspecialchars($_SESSION['user_name']) ?> 
                        <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="/api/client/profil.php"><i class="fas fa-user"></i> Mon Profil</a></li>
                        <li><a href="/api/client/commandes.php"><i class="fas fa-box-open"></i> Mes Commandes</a></li>
                        <?php if (hasRole('Administrateur')): ?>
                            <li><a href="/api/admin/dashboard.php"><i class="fas fa-shield-alt"></i> Administration</a></li>
                        <?php endif; ?>
                        <li><a href="/api/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="/api/pages/connexion.php" class="btn-login">Mon Compte</a></li>
            <?php endif; ?>
            
            <!-- Icône du panier avec compteur -->
            <li>
                <a href="/api/panier.php" class="cart-icon">
                    <i class="fas fa-shopping-cart cart-icon-custom"></i>
                    <?php if ($cartItemCount > 0): ?>
                        <span class="cart-count"><?= $cartItemCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="accessibility-container">
                <button id="toggle-dark-mode" class="btn-accessibility">
                    🌓 Mode Sombre
                </button>
                <button id="toggle-dyslexic-mode" class="btn-accessibility">
                    👁️ Dyslexie
                </button>
            </li>
        </ul>
    </nav>
</header>

<main>