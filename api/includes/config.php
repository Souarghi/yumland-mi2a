<?php
/**
 * Fichier de configuration principale - Projet Yumland
 * Gère la connexion hybride (Local / Vercel / Aiven)
 */

// 1. CONFIGURATION DE L'APPLICATION
define('APP_NAME', 'Le Grand Miam');
define('APP_VERSION', '3.0');
define('DEBUG_MODE', true); // Mettez à false une fois que tout fonctionne sur Vercel

// Configuration du fuseau horaire (Heure de Paris)
date_default_timezone_set('Europe/Paris');

// 2. SÉCURITÉ ET SESSIONS
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // On active cookie_secure UNIQUEMENT si on est en HTTPS (Vercel) pour ne pas casser le localhost
    if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
        ini_set('session.cookie_secure', 1); 
    }
    session_start();
}

// 3. RÉCUPÉRATION DES PARAMÈTRES
$host = getenv('DB_HOST')     ?: 'yumlandbase-yumland.l.aivencloud.com';
$port = getenv('DB_PORT')     ?: '25645';
$db   = getenv('DB_NAME')     ?: 'defaultdb';
$user = getenv('DB_USER')     ?: 'avnadmin';
$pass = getenv('DB_PASSWORD') ?: 'AVNS_PH3P24uM4D2Vg9YHMvZ';

// Certificat SSL Aiven — fichier directement dans le repo
$ssl_ca = __DIR__ . '/ca.pem';

// 4. CONNEXION À LA BASE DE DONNÉES
try {
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (file_exists($ssl_ca)) {
        $options[PDO::MYSQL_ATTR_SSL_CA]                 = $ssl_ca;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET time_zone = '" . date('P') . "'");

} catch (PDOException $e) {
    // Si l'erreur survient lors d'un appel AJAX (Fetch) via JS, on DOIT renvoyer du JSON et non du HTML !
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
              (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    if ($isAjax) {
        http_response_code(503);
        die(json_encode(['success' => false, 'message' => "La base de données est inaccessible ou en veille."]));
    }

    // Message d'erreur personnalisé en cas de mise en veille de la BDD Aiven (Plan Gratuit)
    http_response_code(503); // Service Unavailable
    $errorTitle = 'Base de données en veille';

    $errorContent = '<div class="db-error-container">';
    $errorContent .= '<h2>⚠️ ' . $errorTitle . '</h2>';
    $errorContent .= '<p class="error-intro">Notre application utilise un plan gratuit pour la base de données SQL. Celle-ci se désactive automatiquement après quelques jours d\'inactivité.</p>';
    $errorContent .= '<p class="error-solution">Pas de panique, il faut simplement la réactiver !</p>';
    $errorContent .= '<p>Veuillez nous envoyer un message pour que nous puissions la relancer immédiatement :</p>';
    $errorContent .= '<div class="contact-box">';
    $errorContent .= '<p>📞 <strong>Myriam Bensaid</strong> : 06 68 39 92 06</p>';
    $errorContent .= '<p>📞 <strong>Sheryne Ouarghi</strong> : 06 17 67 77 02</p>';
    $errorContent .= '<p class="contact-note">(Ou contactez-nous via Teams / Mail de l\'école)</p>';
    $errorContent .= '</div>';
    
    if (DEBUG_MODE) {
        $errorContent .= '<hr>';
        $errorContent .= '<p class="debug-info"><strong>Erreur technique :</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    $errorContent .= '</div>';
    
    // On génère une page HTML complète pour pouvoir lier le CSS
    $fullPageError = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$errorTitle - Le Grand Miam</title>
    <link rel="stylesheet" href="/css/errors.css">
</head>
<body>
    $errorContent
</body>
</html>
HTML;
    die($fullPageError);
}

/**
 * Debug propre
 */
function debug($var) {
    if (DEBUG_MODE) {
        echo '<pre class="debug-pre">';
        print_r($var);
        echo '</pre>';
    }
}

/**
 * Sécurité CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        die('Erreur de sécurité CSRF.');
    }
    return true;
}
?>