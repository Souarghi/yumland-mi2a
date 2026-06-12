<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/panier.php';
require_once __DIR__ . '/includes/getapikey.php';

// Récupération des données envoyées par l'interface de l'école (GET)
$transaction = $_GET['transaction'] ?? '';
$montant = $_GET['montant'] ?? 0;
$vendeur = $_GET['vendeur'] ?? '';
$control = $_GET['control'] ?? '';

// CYBank peut renvoyer 'status' (comme dans l'exemple) ou 'statut' (comme dans le texte doc)
$statut = $_GET['statut'] ?? $_GET['status'] ?? 'declined'; 

// On recalcule la clé MD5 avec les données reçues. Si ça correspond à ce qu'envoie la banque,
// c'est que l'URL n'a pas été trafiquée par un petit malin.
$api_key = getAPIKey($vendeur);
$expected_control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#");

// On vire le préfixe "MI2A" (4 lettres) pour retrouver notre vrai ID de BDD auto-incrémenté
$id_commande = (int)substr($transaction, 4);

// Récupération du montant que NOUS avions demandé (stocké en session avant le départ vers CYBank),
// puis comparaison avec la somme que la banque dit avoir encaissée. Si ça diverge
// (URL trafiquée, montant modifié côté banque...), le paiement est refusé.
$montant_attendu = $_SESSION['cybank_montant_attendu'][$transaction] ?? null;
$montant_ok = $montant_attendu !== null && abs((float)$montant - (float)$montant_attendu) < 0.01;

// Vérification ultime : bonne signature + statut ok + ID cohérent + montant payé conforme
if ($control === $expected_control && $statut === 'accepted' && $id_commande > 0 && $montant_ok) {
    try {
        // Sécurisation des opérations multiples via transaction SQL
        $pdo->beginTransaction();

        // Validation de la commande
        $stmt = $pdo->prepare("UPDATE Commandes SET statut = 'En attente', paiement_statut = 'Payé', cybank_transaction = ? WHERE id_commande = ?");
        $stmt->execute([$transaction, $id_commande]);
        
        // Historisation de la transaction
        $stmtPaiement = $pdo->prepare("INSERT INTO Paiements (id_commande, id_client, montant, cybank_transaction_id) VALUES (?, ?, ?, ?)");
        $stmtPaiement->execute([$id_commande, $_SESSION['user_id'] ?? 1, $montant, $transaction]);

        // Attribution des points de fidélité
        $miams_gagnes = floor($montant * 10);
        $stmtMiams = $pdo->prepare("UPDATE Utilisateurs SET solde_miams = solde_miams + ?, total_miams_historique = total_miams_historique + ? WHERE id_user = ?");
        $stmtMiams->execute([$miams_gagnes, $miams_gagnes, $_SESSION['user_id'] ?? 1]);

        $pdo->commit();

        // Le montant attendu n'a plus de raison d'être conservé une fois le paiement validé
        unset($_SESSION['cybank_montant_attendu'][$transaction]);

        clearCart();

        header('Location: /api/client/commandes.php?success=commande_validee');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
    }
} else {
    // Paiement refusé : signature invalide, statut non accepté ou montant payé différent du montant attendu.
    // La commande passe en annulée et le paiement est marqué refusé.
    if ($id_commande > 0) {
        $pdo->prepare("UPDATE Commandes SET statut = 'Annulée', paiement_statut = 'Paiement refusé' WHERE id_commande = ?")->execute([$id_commande]);
    }
    unset($_SESSION['cybank_montant_attendu'][$transaction]);

    // Si tout était valide SAUF le montant, quelqu'un a probablement joué avec les chiffres...
    // On lui réserve un petit message spécial sur la page panier. 🕵️
    $erreur = (!$montant_ok && $control === $expected_control && $statut === 'accepted' && $id_commande > 0)
        ? 'montant_louche'
        : 'paiement_refuse';
    header('Location: /api/panier.php?error=' . $erreur);
    exit;
}
?>