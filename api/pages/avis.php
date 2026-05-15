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

<section class="container" style="padding-top: 40px; min-height: 70vh;">
    <h1 style="text-align: center; margin-bottom: 30px;"><i class="fas fa-star" style="color: var(--color-accent);"></i> L'Avis de nos Clients</h1>
    
    <?php if (empty($avis_list)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">📝</div>
            <p>Aucun avis n'a encore été publié. Commandez et soyez le premier !</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php foreach ($avis_list as $avis): ?>
                <div class="card-style" style="display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <strong><?= htmlspecialchars($avis['prenom'] . ' ' . substr($avis['nom'], 0, 1) . '.') ?></strong>
                        <span style="color: #f39c12; font-size: 1.2rem;">
                            <?= str_repeat('★', $avis['note_globale'] ?? round(($avis['note_livreur'] + $avis['note_nourriture']) / 2)) ?><?= str_repeat('☆', 5 - ($avis['note_globale'] ?? round(($avis['note_livreur'] + $avis['note_nourriture']) / 2))) ?>
                        </span>
                    </div>
                    
                    <p style="font-style: italic; color: #555; margin: 10px 0; flex-grow: 1;">
                        "<?= nl2br(htmlspecialchars($avis['commentaire'])) ?>"
                    </p>
                    
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; font-size: 0.85rem; color: #888;">
                        <div style="margin-bottom: 5px;"><i class="fas fa-utensils"></i> A commandé : <?= htmlspecialchars($avis['plats_commandes']) ?></div>
                        <div><i class="far fa-calendar-alt"></i> Le <?= date('d/m/Y', strtotime($avis['date_commande'])) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
