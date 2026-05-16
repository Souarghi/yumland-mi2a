<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/commandes.php';

// Simulation : Récupérer toutes les commandes pour la cuisine
$commandes = getAllCommandes(); 
?>

<section class="container">
    <h1>Flux de Cuisine (Restaurateur)</h1>
    <p>Tableau de bord des commandes à préparer.</p>

    <div class="gallery-grid">
        <?php foreach ($commandes as $cmd): ?>
            <article class="card-style cmd-card">
                <h3>Commande #<?= $cmd['id_commande'] ?></h3>
                <p><strong>Date :</strong> <?= date('H:i', strtotime($cmd['date_commande'])) ?></p>
                <p><strong>Statut :</strong> 
                    <span class="cmd-status">
                        <?= htmlspecialchars($cmd['statut']) ?>
                    </span>
                </p>
                
                <!-- Affichage des détails réels de la commande -->
                <div class="cmd-details-box">
                    <?php 
                    $details = getCommandeDetails($cmd['id_commande']);
                    if (!empty($details)): 
                    ?>
                        <ul class="cmd-list">
                            <?php foreach ($details as $item): ?>
                                <li class="cmd-item">
                                    <strong><?= $item['quantite'] ?>x</strong> <?= htmlspecialchars($item['nom']) ?>
                                    <?php 
                                    $options = json_decode($item['options_choisies'], true);
                                    if (!empty($options)) {
                                        echo "<br><em class='cmd-options'>- " . htmlspecialchars(implode(', ', $options)) . "</em>";
                                    }
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <em>Aucun détail trouvé.</em>
                    <?php endif; ?>
                </div>

                <div class="cmd-actions">
                    <button class="btn-primary btn-prep">
                        Passer "En préparation"
                    </button>
                    <button class="btn-primary btn-ready">
                        Commande Prête
                    </button>
                    <button class="btn-primary btn-assign">
                        Attribuer à un livreur
                    </button>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (empty($commandes)): ?>
            <p>Aucune commande pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>