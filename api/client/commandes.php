<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/commandes.php';
require_once __DIR__ . '/../includes/panier.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('/api/pages/connexion.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification de sécurité CSRF sur toutes les actions POST de cette page
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // hash_equals prévient les attaques par analyse temporelle (timing attacks)
        die("Erreur de sécurité : jeton CSRF invalide.");
    }
}

// Traitement de l'annulation pour modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_panier') {
    $id_commande = (int)$_POST['id_commande'];
    
    // Vérifier la commande (doit être "En attente")
    $stmtCheck = $pdo->prepare("SELECT id_commande, prix_total, paiement_statut FROM Commandes WHERE id_commande = ? AND id_client = ? AND statut = 'En attente'");
    $stmtCheck->execute([$id_commande, $_SESSION['user_id']]);
    $cmdToEdit = $stmtCheck->fetch();
    
    if ($cmdToEdit) {
        // 1. Remettre les plats dans le panier
        $stmtDetails = $pdo->prepare("SELECT id_produit, quantite, options_choisies FROM Contenu_Commandes WHERE id_commande = ?");
        $stmtDetails->execute([$id_commande]);
        $details = $stmtDetails->fetchAll();
        
        clearCart(); // On vide le panier actuel
        foreach ($details as $item) {
            $options = json_decode($item['options_choisies'], true) ?: [];
            $clean_options = [];
            $note = '';
            foreach ($options as $opt) {
                if (preg_match('/^📝\s*(.*)$/u', $opt, $matches)) {
                    $note = $matches[1];
                } else {
                    $clean_options[] = $opt;
                }
            }
            
            addToCart($item['id_produit'], $item['quantite'], $clean_options);
            
            if ($note !== '') {
                $cart_keys = array_keys($_SESSION['cart']['items']);
                $last_index = end($cart_keys);
                $_SESSION['cart']['items'][$last_index]['note'] = $note;
            }
        }
        
        // 2. On enregistre en session qu'on est en train d'éditer cette commande
        $_SESSION['edit_commande_id'] = $id_commande;
        
        // 3. Rediriger vers le panier pour qu'il puisse éditer librement
        header('Location: /api/panier.php?info=editing');
        exit;
    }
}

// Traitement de la re-commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'recommander') {
    $id_commande = (int)$_POST['id_commande'];
    
    // Par sécurité, on vérifie que le client tente bien de recommander SA propre commande
    $stmtCheck = $pdo->prepare("SELECT id_commande FROM Commandes WHERE id_commande = ? AND id_client = ?");
    $stmtCheck->execute([$id_commande, $_SESSION['user_id']]);
    if ($stmtCheck->fetch()) {
        $stmtDetails = $pdo->prepare("SELECT id_produit, quantite, options_choisies FROM Contenu_Commandes WHERE id_commande = ?");
        $stmtDetails->execute([$id_commande]);
        $details = $stmtDetails->fetchAll();
        
        // On boucle sur l'ancienne commande et on balance tout dans le panier actuel
        foreach ($details as $item) {
            $options = json_decode($item['options_choisies'], true) ?: [];
            addToCart($item['id_produit'], $item['quantite'], $options);
        }
        header('Location: /api/panier.php');
        exit;
    }
}

// Traitement de la modification d'adresse de livraison
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_adresse') {
    $id_commande = (int)$_POST['id_commande'];
    $nouvelle_adresse = trim($_POST['nouvelle_adresse'] ?? '');
    
    if (!empty($nouvelle_adresse)) {
        // Vérifier que la commande appartient au client et n'est pas encore en livraison
        $stmtCheck = $pdo->prepare("SELECT id_commande FROM Commandes WHERE id_commande = ? AND id_client = ? AND statut NOT IN ('En livraison', 'Livrée', 'Annulée')");
        $stmtCheck->execute([$id_commande, $_SESSION['user_id']]);
        
        if ($stmtCheck->fetch()) {
            $stmtUpdate = $pdo->prepare("UPDATE Commandes SET adresse_livraison = ? WHERE id_commande = ?");
            $stmtUpdate->execute([$nouvelle_adresse, $id_commande]);
            header('Location: /api/client/commandes.php?success=adresse_modifiee');
            exit;
        }
    }
}

// Récupérer les commandes de l'utilisateur
$commandes = getAllCommandes(null, $_SESSION['user_id'], 'DESC');

// NOUVEAU : Vérifier quelles commandes ont déjà été notées
$commandes_notees = [];
try {
    $stmtAvis = $pdo->prepare("SELECT id_commande FROM Avis WHERE id_client = ?");
    $stmtAvis->execute([$_SESSION['user_id']]);
    $commandes_notees = $stmtAvis->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { }

// Définir la page courante pour le menu actif
$currentPage = 'client_commandes';
$pageTitle = 'Mes Commandes';

// Génération du jeton CSRF pour les formulaires de la page.
// La fonction generateCSRFToken() est supposée venir de includes/auth.php comme dans les autres pages sécurisées.
if (function_exists('generateCSRFToken')) {
    $csrf_token = generateCSRFToken();
}

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="client-section">
    <div class="container">
        <h1>Mes Commandes</h1>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'commande_validee'): ?>
            <div class="alert alert-success">
                ✅ Votre commande a bien été validée et payée !
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'commande_modifiee'): ?>
            <div class="alert alert-success">
                ✏️ Votre commande a été mise à jour avec succès ! Le Chef a reçu les modifications.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'adresse_modifiee'): ?>
            <div class="alert alert-success">
                📍 L'adresse de livraison a été mise à jour avec succès !
            </div>
        <?php endif; ?>
        
        <?php if (empty($commandes)): ?>
            <div class="empty-commandes">
                <p>Vous n'avez pas encore passé de commande.</p>
                <a href="/api/pages/carte.php" class="btn-primary">Voir la carte</a>
            </div>
        <?php else: ?>
            <div class="commandes-list">
                <?php foreach ($commandes as $commande): ?>
                    <div class="commande-item card-style">
                        <div class="commande-header">
                            <h3>Commande #<?= $commande['id_commande'] ?></h3>
                            <span class="commande-date">
                                <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?>
                            </span>
                        </div>
                        
                        <div class="commande-details">
                            <p><strong>Statut:</strong> 
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $commande['statut'])) ?>">
                                    <?= htmlspecialchars($commande['statut']) ?>
                                </span>
                            </p>
                            <p><strong>Mode:</strong> <?= htmlspecialchars($commande['mode_retrait'] ?? 'Livraison') ?></p>
                            <?php if (strtolower($commande['mode_retrait'] ?? 'livraison') === 'livraison'): ?>
                                <div class="adresse-container">
                                    <strong>Adresse:</strong> 
                                    
                                    <div id="view-addr-<?= $commande['id_commande'] ?>" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 5px;">
                                        <span><?= htmlspecialchars(!empty($commande['adresse_livraison']) ? $commande['adresse_livraison'] : ($commande['client_adresse'] ?? 'Non spécifiée')) ?></span>
                                        <?php if (!in_array($commande['statut'], ['En livraison', 'Livrée', 'Annulée'])): ?>
                                            <button type="button" class="btn-outline btn-sm" onclick="toggleEditAddr(<?= $commande['id_commande'] ?>, true)"><i class="fas fa-edit"></i> Modifier</button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!in_array($commande['statut'], ['En livraison', 'Livrée', 'Annulée'])): ?>
                                        <form id="form-edit-addr-<?= $commande['id_commande'] ?>" method="POST" class="form-edit-inline" style="display: none;" onsubmit="toggleEditAddr(<?= $commande['id_commande'] ?>, false, true)">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                            <input type="hidden" name="action" value="modifier_adresse">
                                            <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                            <div class="form-group">
                                                <input type="text" name="nouvelle_adresse" value="<?= htmlspecialchars(!empty($commande['adresse_livraison']) ? $commande['adresse_livraison'] : ($commande['client_adresse'] ?? '')) ?>" required>
                                                <div class="edit-actions-wrapper">
                                                    <button type="submit" class="btn-primary btn-sm">Enregistrer</button>
                                                    <button type="button" class="btn-secondary btn-sm" onclick="toggleEditAddr(<?= $commande['id_commande'] ?>, false, false)">Annuler</button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <p><strong>Montant:</strong> <?= number_format($commande['prix_total'], 2, ',', ' ') ?> €</p>
                        </div>
                        
                        <div class="commande-items">
                            <h4>Détails de la commande</h4>
                            <?php
                            // Récupérer les détails de cette commande spécifique
                            $stmtDetails = $pdo->prepare("SELECT cc.*, p.nom FROM Contenu_Commandes cc JOIN Produits p ON cc.id_produit = p.id_produit WHERE cc.id_commande = ?");
                            $stmtDetails->execute([$commande['id_commande']]);
                            $details = $stmtDetails->fetchAll();
                            ?>
                            <ul>
                                <?php foreach ($details as $detail): ?>
                                    <li>
                                        <span class="item-name"><?= htmlspecialchars($detail['nom']) ?></span>
                                        <span class="item-quantity">x<?= $detail['quantite'] ?></span>
                                        <span class="item-price"><?= number_format($detail['prix_unitaire'] * $detail['quantite'], 2, ',', ' ') ?> €</span>
                                        <?php 
                                        $options = json_decode($detail['options_choisies'], true);
                                        if (!empty($options)): 
                                        ?>
                                            <span class="item-options">
                                                <em>↳ <?= htmlspecialchars(implode(', ', $options)) ?></em>
                                            </span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="commande-actions">
                            <?php if ($commande['statut'] === 'En attente'): ?>
                                <form method="POST" onsubmit="return confirm('Voulez-vous modifier cette commande ? Son contenu sera placé dans votre panier pour que vous puissiez l\'éditer librement.');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                    <input type="hidden" name="action" value="modifier_panier">
                                    <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                    <button type="submit" class="btn-outline btn-sm">
                                        <i class="fas fa-shopping-cart"></i> Modifier les plats
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($commande['statut'] === 'Livrée'): ?>
                                <?php if (in_array($commande['id_commande'], $commandes_notees)): ?>
                                    <span class="btn-avis-laisse btn-sm" title="Vous avez déjà noté cette commande.">✅ Avis laissé</span>
                                <?php else: ?>
                                    <a href="/api/client/noter.php?commande_id=<?= $commande['id_commande'] ?>" class="btn-outline btn-sm">⭐ Noter</a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                <input type="hidden" name="action" value="recommander">
                                <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                <button type="submit" class="btn-primary btn-sm">
                                    <i class="fas fa-sync-alt"></i> Recommander
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<script>
// Fonction propre pour gérer l'apparition/disparition du formulaire d'adresse
function toggleEditAddr(id, showForm, isSubmit = false) {
    const viewDiv = document.getElementById('view-addr-' + id);
    const formForm = document.getElementById('form-edit-addr-' + id);
    if (showForm) {
        if (viewDiv) viewDiv.style.display = 'none';
        if (formForm) formForm.style.display = 'block';
    } else {
        if (formForm) formForm.style.display = 'none';
        if (viewDiv) {
            if (isSubmit) {
                viewDiv.innerHTML = '<span style="color:var(--color-primary); font-weight:bold;"><i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...</span>';
            }
            viewDiv.style.display = 'flex';
        }
    }
}
</script>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>