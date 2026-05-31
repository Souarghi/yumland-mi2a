<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/commandes.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !hasRole('Administrateur')) {
    redirect('/api/pages/connexion.php');
}

// Récupération de toutes les commandes de la plateforme via la fonction déjà existante
$commandes = getAllCommandes(null, null, 'DESC');

$currentPage = 'admin_historique';
$pageTitle = 'Historique des Commandes';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-section">
    <div class="container">
        <h1>📦 Historique de toutes les commandes</h1>
        
        <div class="admin-header">
            <p>Consultez l'ensemble des commandes passées sur la plateforme par tous les clients.</p>
            <a href="/api/admin/dashboard.php" class="btn-secondary">Retour au Dashboard</a>
        </div>

        <div class="admin-table-container card-style">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Total payé</th>
                        <th>Statut</th>
                        <th>Mode Retrait</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($commandes)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">Aucune commande n'a encore été passée sur la plateforme.</td></tr>
                    <?php else: ?>
                        <?php foreach ($commandes as $cmd): ?>
                            <tr>
                                <td><strong>#<?= $cmd['id_commande'] ?></strong></td>
                                <td><?= date('d/m/Y à H:i', strtotime($cmd['date_commande'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($cmd['client_nom'] ?? 'Ancien') ?> 
                                    <?= htmlspecialchars($cmd['client_prenom'] ?? 'Client') ?>
                                </td>
                                <td><strong><?= number_format($cmd['prix_total'], 2, ',', ' ') ?> €</strong></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $cmd['statut'])) ?>">
                                        <?= htmlspecialchars($cmd['statut']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($cmd['mode_retrait'] ?? 'Livraison') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>