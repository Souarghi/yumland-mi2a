<?php
// api/client/update_profil_ajax.php
// Endpoint AJAX pour la modification du profil (Phase 3)
header('Content-Type: application/json');
// La session est démarrée centralement dans config.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/input_validation.php';

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
$nom     = normalizeFormValue($data['nom'] ?? '');
$prenom  = normalizeFormValue($data['prenom'] ?? '');
$tel     = normalizeFormValue($data['tel'] ?? '');
$rue         = normalizeFormValue($data['rue'] ?? '');
$code_postal = trim($data['code_postal'] ?? '');
$ville       = normalizeFormValue($data['ville'] ?? '');
$complement  = normalizeFormValue($data['complement'] ?? '');
$pin         = trim($data['pin'] ?? '');

if (!isValidPersonName($nom)) {
    echo json_encode(['success' => false, 'message' => 'Le nom ne doit contenir que des lettres, espaces, apostrophes ou tirets.']);
    exit;
}
if (!isValidPersonName($prenom)) {
    echo json_encode(['success' => false, 'message' => 'Le prénom ne doit contenir que des lettres, espaces, apostrophes ou tirets.']);
    exit;
}
if ($tel !== '' && !isValidFrenchPhoneNumber($tel)) {
    echo json_encode(['success' => false, 'message' => 'Le numéro de téléphone doit être au format français valide.']);
    exit;
}
if ($code_postal !== '' && !isValidFrenchPostalCode($code_postal)) {
    echo json_encode(['success' => false, 'message' => 'Le code postal doit contenir exactement 5 chiffres.']);
    exit;
}
if ($ville !== '' && !isValidCityName($ville)) {
    echo json_encode(['success' => false, 'message' => 'La ville ne doit contenir que des lettres, espaces, apostrophes ou tirets.']);
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
