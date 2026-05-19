<?php
require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pin = trim($_POST['pin'] ?? '');
    
    if (empty($email) || empty($pin)) {
        echo json_encode(["success" => false, "message" => "Veuillez remplir tous les champs."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id_user, pin FROM Utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Vérification de la correspondance du PIN
        if ($user && $user['pin'] !== null && $user['pin'] === $pin) {
            // Création d'un jeton temporaire en session pour autoriser le reset
            $reset_token = bin2hex(random_bytes(32));
            $_SESSION['reset_token'] = $reset_token;
            $_SESSION['reset_email'] = $email;
            
            echo json_encode(["success" => true, "message" => "Code PIN validé. Veuillez définir votre nouveau mot de passe.", "reset_token" => $reset_token]);
        } else {
            echo json_encode(["success" => false, "message" => "Email introuvable ou code PIN incorrect."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Erreur du serveur."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
}