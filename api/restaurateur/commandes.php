<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/commandes.php';

// Vérifier si l'utilisateur est connecté et est un restaurateur
if (!isLoggedIn() || !hasRole('Restaurateur')) {
    redirect('/api/pages/connexion.php');
}

// Traitement des actions (Changement de statut)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Vérification de sécurité CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de sécurité CSRF.");
    }

    $id_commande = (int)$_POST['id_commande'];
    if ($_POST['action'] === 'preparer') {
        updateCommandeStatus($id_commande, 'En préparation');
    } elseif ($_POST['action'] === 'prete') {
        updateCommandeStatus($id_commande, 'Prête');
    } elseif ($_POST['action'] === 'livrer') {
            // Assigner au premier livreur disponible dans la base (évite les conflits d'ID)
            $stmtLiv = $pdo->query("SELECT id_user FROM Utilisateurs WHERE role = 'Livreur' LIMIT 1");
            $liv = $stmtLiv->fetch();
            assignLivreur($id_commande, $liv ? $liv['id_user'] : 9);
    } elseif ($_POST['action'] === 'servie') {
        updateCommandeStatus($id_commande, 'Livrée');
    }
    header('Location: /api/restaurateur/commandes.php');
    exit;
}

// Récupérer les commandes à traiter
$commandes_attente = getAllCommandes('En attente', null, 'ASC');
$commandes_preparation = getAllCommandes('En préparation', null, 'ASC');
$commandes_pretes = getAllCommandes('Prête', null, 'ASC');

// Définir la page courante pour le menu actif
$currentPage = 'restaurateur_commandes';
$pageTitle = 'Gestion des Commandes';

// Génération du jeton CSRF
$csrf_token = generateCSRFToken();

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<script>
    // Horloge temps réel pour la cuisine
    setInterval(() => {
        const now = new Date();
        const clock = document.getElementById('clock');
        if (clock) clock.textContent = now.toLocaleTimeString('fr-FR');
    }, 1000);
</script>

<section class="restaurateur-section">
    <div class="container" style="max-width: 1400px;">
        <header class="resto-header">
            <div style="display:flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;"></div>
                <div style="flex: 2; text-align: center;">
                    <h1>👨‍🍳 CUISINE - LE GRAND MIAM</h1>
                    <span class="resto-time" id="clock"><?= date('H:i:s') ?></span>
                </div>
                <div style="flex: 1;"></div>
            </div>
        </header>
        
        <?php 
        // Préparation unique de la requête pour éviter les crashs si une colonne est vide
        $stmtDetails = $pdo->prepare("SELECT cc.*, p.nom FROM Contenu_Commandes cc LEFT JOIN Produits p ON cc.id_produit = p.id_produit WHERE cc.id_commande = ?");
        ?>
        <main class="kitchen-board">
            
            <!-- COLONNE 1 : EN ATTENTE -->
            <section class="column col-waiting" id="col-waiting">
                <h2>🔥 En Attente (<?= count($commandes_attente) ?>)</h2>
                <div id="list-waiting">
                <?php if (empty($commandes_attente)): ?>
                    <p style="text-align:center; color:#7f8c8d; font-style:italic; padding:20px 0;">Aucune commande en attente.</p>
                <?php endif; ?>
                <?php foreach ($commandes_attente as $cmd): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span>#<?= $cmd['id_commande'] ?> <?= ($cmd['mode_retrait'] ?? 'livraison') === 'sur place' ? '🍽️' : '🛵' ?></span>
                            <span><?= date('H:i', strtotime($cmd['date_commande'] ?? 'now')) ?></span>
                        </div>
                        <ul class="order-items">
                            <?php
                            $stmtDetails->execute([$cmd['id_commande']]);
                            foreach ($stmtDetails->fetchAll() as $detail):
                            ?>
                                <li>
                                    <span class="item-qty"><?= $detail['quantite'] ?>x</span> <?= htmlspecialchars($detail['nom'] ?? 'Produit inconnu') ?>
                                    <?php 
                                    $options = json_decode($detail['options_choisies'], true);
                                    if (!empty($options)): 
                                    ?>
                                        <span class="item-opts">Info: <?= htmlspecialchars(implode(', ', $options)) ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="action" value="preparer">
                            <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                            <button type="submit" class="btn-move btn-start">
                                Lancer Préparation
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
                </div>
            </section>

            <!-- COLONNE 2 : EN PRÉPARATION -->
            <section class="column col-prep" id="col-prep">
                <h2>🔪 En Préparation (<?= count($commandes_preparation) ?>)</h2>
                <div id="list-prep">
                <?php if (empty($commandes_preparation)): ?>
                    <p style="text-align:center; color:#7f8c8d; font-style:italic; padding:20px 0;">Aucune commande en préparation.</p>
                <?php endif; ?>
                <?php foreach ($commandes_preparation as $cmd): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span>#<?= $cmd['id_commande'] ?> <?= ($cmd['mode_retrait'] ?? 'livraison') === 'sur place' ? '🍽️' : '🛵' ?></span>
                            <span><?= date('H:i', strtotime($cmd['date_commande'] ?? 'now')) ?></span>
                        </div>
                        <ul class="order-items">
                            <?php
                            $stmtDetails->execute([$cmd['id_commande']]);
                            foreach ($stmtDetails->fetchAll() as $detail):
                            ?>
                                <li>
                                    <span class="item-qty"><?= $detail['quantite'] ?>x</span> <?= htmlspecialchars($detail['nom'] ?? 'Produit inconnu') ?>
                                    <?php 
                                    $options = json_decode($detail['options_choisies'], true);
                                    if (!empty($options)): 
                                    ?>
                                        <span class="item-opts">Info: <?= htmlspecialchars(implode(', ', $options)) ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="action" value="prete">
                            <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                            <button type="submit" class="btn-move btn-ready">
                                Commande Prête
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
                </div>
            </section>

            <!-- COLONNE 3 : PRÊTES -->
            <section class="column col-ready" id="col-ready">
                <h2>✅ Prêt à livrer (<?= count($commandes_pretes) ?>)</h2>
                <div id="list-ready">
                <?php if (empty($commandes_pretes)): ?>
                    <p style="text-align:center; color:#7f8c8d; font-style:italic; padding:20px 0;">Aucune commande prête.</p>
                <?php endif; ?>
                <?php foreach ($commandes_pretes as $cmd): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span>#<?= $cmd['id_commande'] ?> <?= ($cmd['mode_retrait'] ?? 'livraison') === 'sur place' ? '🍽️' : '🛵' ?></span>
                            <span><?= date('H:i', strtotime($cmd['date_commande'] ?? 'now')) ?></span>
                        </div>
                        
                        <?php if(($cmd['mode_retrait'] ?? 'livraison') === 'livraison'): ?>
                            <div style="text-align:center; color:green; font-weight:bold; margin-bottom: 10px;">En attente livreur</div>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="livrer">
                                <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                                <button type="submit" class="btn-move btn-deliver">🚴 Assigner un livreur</button>
                            </form>
                        <?php else: ?>
                            <div style="text-align:center; color:green; font-weight:bold; margin-bottom: 10px;">En attente client (Sur place)</div>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="servie">
                                <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                                <button type="submit" class="btn-move btn-deliver">🍽️ Marquer comme Servie</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </div>
            </section>

        </main>
    </div>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>
