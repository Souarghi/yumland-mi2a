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
    // On doit supprimer toutes les données liées à ses commandes.

    // 2a. Récupérer les ID de toutes les commandes passées par le client.
    $stmtCmdIds = $pdo->prepare("SELECT id_commande FROM Commandes WHERE id_client = ?");
    $stmtCmdIds->execute([$cible_id]);
    $commandes_a_supprimer_ids = $stmtCmdIds->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($commandes_a_supprimer_ids)) {
        $placeholders = implode(',', array_fill(0, count($commandes_a_supprimer_ids), '?'));

        // 2b. Supprimer les avis liés à ces commandes
        $stmtAvis = $pdo->prepare("DELETE FROM Avis WHERE id_commande IN ($placeholders)");
        $stmtAvis->execute($commandes_a_supprimer_ids);

        // 2c. Supprimer les paiements liés à ces commandes
        $stmtPaiements = $pdo->prepare("DELETE FROM Paiements WHERE id_commande IN ($placeholders)");
        $stmtPaiements->execute($commandes_a_supprimer_ids);

        // 2d. Supprimer le contenu détaillé de ces commandes
        $stmtContenu = $pdo->prepare("DELETE FROM Contenu_Commandes WHERE id_commande IN ($placeholders)");
        $stmtContenu->execute($commandes_a_supprimer_ids);
    }
    
    // 2e. Supprimer les avis du client qui ne seraient pas liés à une commande (sécurité)
    $stmtAvisClient = $pdo->prepare("DELETE FROM Avis WHERE id_client = ?");
    $stmtAvisClient->execute([$cible_id]);

    // 2f. Supprimer les commandes elles-mêmes
    $stmtCommandes = $pdo->prepare("DELETE FROM Commandes WHERE id_client = ?");
    $stmtCommandes->execute([$cible_id]);

    // Étape 3: Supprimer l'utilisateur
    $stmtUser = $pdo->prepare("DELETE FROM Utilisateurs WHERE id_user = ?");
    $stmtUser->execute([$cible_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur et toutes ses données associées ont été supprimés avec succès.'
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    // Message d'erreur plus explicite en cas de problème
    echo json_encode(['success' => false, 'message' => "Erreur lors de la suppression. Il est possible qu'une contrainte de base de données n'ait pas pu être résolue (ex: `id_livreur` non nullable). Erreur technique : " . $e->getMessage()]);
}
?>