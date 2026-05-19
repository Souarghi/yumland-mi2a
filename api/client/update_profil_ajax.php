<?php
// api/client/update_profil_ajax.php
// Endpoint AJAX pour la modification du profil (Phase 3)
header('Content-Type: application/json');
// La session est démarrée centralement dans config.php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// Vérification CSRF
if (!isset($data['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $data['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Erreur de sécurité CSRF.']);
    exit;
}

// Validation basique côté serveur
$nom     = trim($data['nom'] ?? '');
$prenom  = trim($data['prenom'] ?? '');
$tel     = trim($data['tel'] ?? '');
$rue         = trim($data['rue'] ?? '');
$code_postal = trim($data['code_postal'] ?? '');
$ville       = trim($data['ville'] ?? '');
$complement  = trim($data['complement'] ?? '');
$pin         = trim($data['pin'] ?? '');

if (strlen($nom) < 2 || strlen($prenom) < 2) {
    echo json_encode(['success' => false, 'message' => 'Nom ou prénom trop court.']);
    exit;
}
if ($pin !== '' && !preg_match('/^\d{6}$/', $pin)) {
    echo json_encode(['success' => false, 'message' => 'Le code PIN doit contenir exactement 6 chiffres.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "UPDATE Utilisateurs SET nom = ?, prenom = ?, tel = ?, rue = ?, code_postal = ?, ville = ?, complement = ?, pin = ? WHERE id_user = ?"
    );
    $stmt->execute([$nom, $prenom, $tel, $rue, $code_postal, $ville, $complement, $pin, $user_id]);

    $_SESSION['user_name'] = $nom; // Mise à jour de la session

    echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès !']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données.']);
}
