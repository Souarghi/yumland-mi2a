<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !hasRole('Administrateur')) {
    redirect('/api/pages/connexion.php');
}

// Définir la page courante pour le menu actif
$currentPage = 'admin_statistiques';
$pageTitle = 'Statistiques des Commandes';

// 1. Récupération des produits les plus commandés (Top 15)
// On additionne les quantités vendues en filtrant les commandes annulées
$stmtProduits = $pdo->query("
    SELECT p.nom, SUM(cc.quantite) as total_ventes
    FROM Contenu_Commandes cc
    JOIN Produits p ON cc.id_produit = p.id_produit
    JOIN Commandes c ON cc.id_commande = c.id_commande
    WHERE c.statut != 'Annulée'
    GROUP BY p.id_produit, p.nom
    ORDER BY total_ventes DESC
    LIMIT 15
");
$produits_populaires = $stmtProduits->fetchAll();

// 2. Récupération des commandes par code postal (Top 10)
// On joint l'utilisateur pour connaître sa zone géographique
$stmtCodePostal = $pdo->query("
    SELECT u.code_postal, COUNT(DISTINCT c.id_commande) as total_commandes
    FROM Commandes c
    JOIN Utilisateurs u ON c.id_client = u.id_user
    WHERE c.statut != 'Annulée' AND u.code_postal IS NOT NULL AND u.code_postal != ''
    GROUP BY u.code_postal
    ORDER BY total_commandes DESC
    LIMIT 10
");
$codes_postaux = $stmtCodePostal->fetchAll();

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Inclusion de Chart.js et du plugin DataLabels (via CDN) -->
<script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script defer>
document.addEventListener("DOMContentLoaded", function() {
    // Enregistrement du plugin pour écrire les valeurs
    Chart.register(ChartDataLabels);

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            datalabels: {
                anchor: 'end',
                align: 'top',
                color: '#333',
                font: { weight: 'bold', size: 14 }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grace: '15%' // Ajoute un peu d'espace en haut pour ne pas couper le texte
            }
        }
    };

    // 1. Histogramme des Produits
    const canvasProduits = document.getElementById('chartProduits');
    if (canvasProduits) {
        new Chart(canvasProduits, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($produits_populaires, 'nom')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($produits_populaires, 'total_ventes')) ?>,
                    backgroundColor: '#D32F2F', // --color-primary
                    borderRadius: 4
                }]
            },
            options: commonOptions
        });
    }

    // 2. Histogramme des Codes Postaux
    const canvasCP = document.getElementById('chartCP');
    if (canvasCP) {
        new Chart(canvasCP, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($codes_postaux, 'code_postal')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($codes_postaux, 'total_commandes')) ?>,
                    backgroundColor: '#FFC107', // --color-accent
                    borderRadius: 4
                }]
            },
            options: commonOptions
        });
    }
});
</script>

<section class="admin-container">
    <div class="admin-header">
        <h1>📊 Statistiques Globales</h1>
        <a href="/api/admin/dashboard.php" class="btn-secondary">Retour au Dashboard</a>
    </div>

    <div style="display: flex; flex-direction: column; gap: 30px; margin-top: 20px;">
        
        <!-- TOP PRODUITS -->
        <div class="card-style">
            <h3>🍔 Produits les plus commandés</h3>
            <?php if (empty($produits_populaires)): ?>
                <p style="color: #666; font-style: italic;">Aucune donnée disponible.</p>
            <?php else: ?>
                <!-- Conteneur avec hauteur fixe pour contrôler la taille du graphique -->
                <div style="position: relative; height: 350px; width: 100%;">
                    <canvas id="chartProduits"></canvas>
                </div>
            <?php endif; ?>
        </div>

        <!-- TOP CODES POSTAUX -->
        <div class="card-style">
            <h3>📍 Commandes par Code Postal</h3>
            <?php if (empty($codes_postaux)): ?>
                <p style="color: #666; font-style: italic;">Aucune donnée disponible.</p>
            <?php else: ?>
                <div style="position: relative; height: 350px; width: 100%;">
                    <canvas id="chartCP"></canvas>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>