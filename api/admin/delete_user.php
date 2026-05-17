<?php
// api/admin/delete_user.php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? $_SESSION['type'] ?? '';

if (!isset($_SESSION['user_id']) || $role !== 'Administrateur') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$cible_id = (int)($data['id_user'] ?? 0);

if ($cible_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide.']);
    exit;
}

if ($cible_id === (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas vous supprimer vous-même.']);
    exit;
}

try {
    // Suppression de l'utilisateur (la requête échouera via PDOException si l'utilisateur possède des commandes sans contrainte de suppression CASCADE)
    $stmt = $pdo->prepare("DELETE FROM Utilisateurs WHERE id_user = ?");
    $stmt->execute([$cible_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur supprimé avec succès.'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Erreur base de données: l'utilisateur possède sûrement des commandes ou avis liés et ne peut pas être supprimé, veuillez plutôt le bloquer."]);
}
?>