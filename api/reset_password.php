<?php
require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email_confirmed'] ?? '';
    $token = $_POST['reset_token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    // Vérifier la session (Sécurité)
    if (empty($token) || $token !== ($_SESSION['reset_token'] ?? '')) {
        echo json_encode(["success" => false, "message" => "La session de récupération est expirée ou invalide."]);
        exit;
    }

    if ($email !== ($_SESSION['reset_email'] ?? '')) {
        echo json_encode(["success" => false, "message" => "Incohérence des données de sécurité."]);
        exit;
    }

    if (strlen($new_password) < 8) {
        echo json_encode(["success" => false, "message" => "Le mot de passe doit contenir au moins 8 caractères."]);
        exit;
    }

    try {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE Utilisateurs SET mot_de_passe = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);

        // Nettoyage de la session de récupération
        unset($_SESSION['reset_token']);
        unset($_SESSION['reset_email']);

        echo json_encode(["success" => true, "message" => "Mot de passe réinitialisé avec succès ! Redirection..."]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
}