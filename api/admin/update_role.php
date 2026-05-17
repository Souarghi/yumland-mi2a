<?php
// api/admin/update_role.php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? $_SESSION['type'] ?? '';

if (!isset($_SESSION['user_id']) || $role !== 'Administrateur') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$cible_id = (int)($data['id_user'] ?? 0);
$new_role = trim($data['new_role'] ?? '');

$valid_roles = ['Client', 'Administrateur', 'Restaurateur', 'Livreur'];

if ($cible_id <= 0 || !in_array($new_role, $valid_roles)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

if ($cible_id === (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas modifier votre propre rôle.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE Utilisateurs SET role = ? WHERE id_user = ?");
    $stmt->execute([$new_role, $cible_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Rôle mis à jour avec succès.'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données.']);
}
?>