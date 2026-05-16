<?php
// api/pages/avis.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/avis.php';

$currentPage = 'avis';
$pageTitle = 'Avis Clients';
include_once __DIR__ . '/../includes/header.php';

// Récupérer les avis via la fonction dédiée
$avis_list = getAllAvis();
?>

<section class="container avis-container">
    <h1 class="avis-page-title"><i class="fas fa-star avis-star-icon"></i> L'Avis de nos Clients</h1>
    
    <?php if (empty($avis_list)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">📝</div>
            <p>Aucun avis n'a encore été publié. Commandez et soyez le premier !</p>
        </div>
    <?php else: ?>
        <div class="avis-grid">
            <?php foreach ($avis_list as $avis): ?>
                <div class="card-style avis-card">
                    <div class="avis-header">
                        <strong><?= htmlspecialchars($avis['prenom'] . ' ' . substr($avis['nom'], 0, 1) . '.') ?></strong>
                        <span class="avis-rating">
                            <?= str_repeat('★', $avis['note_globale'] ?? round(($avis['note_livreur'] + $avis['note_nourriture']) / 2)) ?><?= str_repeat('☆', 5 - ($avis['note_globale'] ?? round(($avis['note_livreur'] + $avis['note_nourriture']) / 2))) ?>
                        </span>
                    </div>
                    
                    <p class="avis-comment">
                        "<?= nl2br(htmlspecialchars($avis['commentaire'])) ?>"
                    </p>
                    
                    <div class="avis-footer">
                        <div class="avis-plat"><i class="fas fa-utensils"></i> A commandé : <?= htmlspecialchars($avis['plats_commandes']) ?></div>
                        <div><i class="far fa-calendar-alt"></i> Le <?= date('d/m/Y', strtotime($avis['date_commande'])) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
