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
    $pdo->beginTransaction();

    // Étape 1: Gérer le cas où l'utilisateur est un livreur
    // On anonymise les commandes qu'il a livrées pour ne pas perdre l'historique.
    // Cela suppose que la colonne `id_livreur` peut être NULL.
    $stmtLivreur = $pdo->prepare("UPDATE Commandes SET id_livreur = NULL WHERE id_livreur = ?");
    $stmtLivreur->execute([$cible_id]);

    // Étape 2: Gérer le cas où l'utilisateur est un client
    // Au lieu de supprimer ses commandes et avis, on les rend anonymes pour les garder dans l'historique
    
    // On s'assure d'abord que la table accepte les ID nulls
    $pdo->exec("ALTER TABLE Commandes MODIFY id_client INT NULL");
    $pdo->exec("ALTER TABLE Avis MODIFY id_client INT NULL");
    $pdo->exec("ALTER TABLE Paiements MODIFY id_client INT NULL");

    // Détachement des données (Anonymisation)
    $pdo->prepare("UPDATE Avis SET id_client = NULL WHERE id_client = ?")->execute([$cible_id]);
    $pdo->prepare("UPDATE Paiements SET id_client = NULL WHERE id_client = ?")->execute([$cible_id]);
    $pdo->prepare("UPDATE Commandes SET id_client = NULL WHERE id_client = ?")->execute([$cible_id]);

    // Étape 3: Supprimer l'utilisateur
    $stmtUser = $pdo->prepare("DELETE FROM Utilisateurs WHERE id_user = ?");
    $stmtUser->execute([$cible_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'L\'utilisateur a été supprimé. Ses commandes et avis ont été conservés anonymement.'
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    // Message d'erreur plus explicite en cas de problème
    echo json_encode(['success' => false, 'message' => "Erreur lors de la suppression. Il est possible qu'une contrainte de base de données n'ait pas pu être résolue (ex: `id_livreur` non nullable). Erreur technique : " . $e->getMessage()]);
}
?>