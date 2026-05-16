<?php
// La session est démarrée centralement dans config.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/commandes.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Livreur') {
    header('Location: /api/index.php');
    exit;
}

// On inclut le header APRÈS le traitement PHP pour éviter l'erreur d'affichage (l'écran noir)
require_once __DIR__ . '/../includes/header.php';

// Récupération du livreur connecté
$livreur_id = $_SESSION['user_id'] ?? null;
if (!$livreur_id) {
    $stmtLiv = $pdo->query("SELECT id_user FROM Utilisateurs WHERE role = 'Livreur' LIMIT 1");
    $liv = $stmtLiv->fetch();
    $livreur_id = $liv ? $liv['id_user'] : 9;
}
$mes_livraisons = getCommandesByLivreur($livreur_id);
?>

<script defer>
// Script Asynchrone (Exigence Phase 3)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-btn-deliver').forEach(button => {
        button.addEventListener('click', function() {
            const idCommande = this.getAttribute('data-id');
            const articleCard = this.closest('article');
            const btn = this;
            
            // Changer l'état du bouton pendant le chargement
            btn.disabled = true;
            btn.innerHTML = '⏳ MISE À JOUR...';

            fetch('/api/livreur/update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_commande: idCommande,
                    new_statut: 'Livrée'
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Disparition en douceur de la carte sans recharger la page
                    articleCard.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    articleCard.style.opacity = '0';
                    articleCard.style.transform = 'scale(0.9)';
                    setTimeout(() => articleCard.remove(), 400);
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerHTML = '✅ MARQUER COMME LIVRÉE';
                }
            })
            .catch(err => {
                alert('Erreur réseau lors de la communication avec le serveur.');
                btn.disabled = false;
                btn.innerHTML = '✅ MARQUER COMME LIVRÉE';
            });
        });
    });
});
</script>

<section class="container container-small">
    <div class="livraisons-header">
        <h1 class="livraisons-title">Mes Courses</h1>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?> alert-livraisons">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>
    
    <?php if (empty($mes_livraisons)): ?>
        <div class="alert alert-success">
            Aucune livraison en cours. En attente de courses...
        </div>
    <?php endif; ?>

    <?php foreach ($mes_livraisons as $livraison): ?>
        <article class="card-style card-livraison">
            <h2 class="livraison-id">Commande #<?= $livraison['id_commande'] ?></h2>
            <?php $adresse_a_afficher = !empty($livraison['adresse_livraison']) ? $livraison['adresse_livraison'] : (!empty($livraison['client_adresse']) ? $livraison['client_adresse'] : 'Adresse non spécifiée'); ?>
            <p class="livraison-address">
                📍 <?= htmlspecialchars($adresse_a_afficher) ?>
            </p>
            
            <!-- Boutons XXL (Hauteur mini 60px pour gants selon le README) -->
            <div class="livraison-actions-wrapper">
                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($adresse_a_afficher) ?>" target="_blank" class="btn btn-livreur btn-map">
                    🗺️ OUVRIR DANS MAPS
                </a>
                
                <?php if (!empty($livraison['client_tel'])): ?>
                <a href="tel:<?= htmlspecialchars($livraison['client_tel']) ?>" class="btn btn-livreur btn-call-client">
                    📞 APPELER LE CLIENT (<?= htmlspecialchars(strtoupper($livraison['client_nom'])) ?>)
                </a>
                <?php endif; ?>
                
                <div class="livraison-form">
                    <button type="button" class="btn btn-livreur btn-deliver btn-no-border js-btn-deliver" data-id="<?= $livraison['id_commande'] ?>">
                        ✅ MARQUER COMME LIVRÉE
                    </button>
                    <button type="button" class="btn btn-livreur btn-problem btn-no-border" onclick="alert('Contactez le support :\n- Myriam Bensaid : 06 68 39 92 06\n- Sheryne Ouarghi : 06 17 67 77 02')">
                        ❌ PROBLÈME DE LIVRAISON
                    </button>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
