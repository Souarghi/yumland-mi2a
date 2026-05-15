<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/avis.php';

// L'utilisateur doit être connecté pour accéder à cette page
if (!isLoggedIn()) {
    redirect('/api/pages/connexion.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = 'success'; // ou 'danger'

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $rue = trim($_POST['rue'] ?? '');
    $code_postal = trim($_POST['code_postal'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $complement = trim($_POST['complement'] ?? '');

    try {
        updateUserProfil($user_id, [
            'nom'         => $nom,
            'prenom'      => $prenom,
            'tel'         => $tel,
            'rue'         => $rue,
            'code_postal' => $code_postal,
            'ville'       => $ville,
            'complement'  => $complement,
        ]);
        
        // Mettre à jour le nom en session au cas où il a changé
        $_SESSION['user_name'] = $nom;
        
        $message = "Vos informations ont été mises à jour avec succès !";
    } catch (PDOException $e) {
        $message = "Erreur lors de la mise à jour de vos informations.";
        $messageType = 'danger';
    }
}

// Récupération des informations actuelles de l'utilisateur
$user = getUserById($user_id);

// Détermination du statut Miams
$miams = $user['solde_miams'] ?? 0;
$miams_historique = $user['total_miams_historique'] ?? $miams;

// Application des Paliers de Fidélité (D'après la doc)
if ($miams_historique < 1000) {
    $statut_miams = "PETIT GRILLEUR 🥉";
} elseif ($miams_historique < 3000) {
    $statut_miams = "SAUCE CHEF 🥈";
} else {
    $statut_miams = "LÉGENDE DU STEAK 🥇";
}

$currentPage = 'profil';
$pageTitle = 'Mon Profil';

$additionalCss = ['/css/profil.css'];
include_once __DIR__ . '/../includes/header.php';
?>
<script src="/js/profil.js" defer></script>

<section class="container form-page">
    <div class="form-container card-style">
        <h2>⚙️ Paramètres du compte</h2>
        <div class="profil-stats-box">
            <h3 class="profil-stats-title">Club Le Grand Miam</h3>
            <p>Solde Miams actuel : <strong><?= htmlspecialchars($miams) ?> 🍔</strong></p>
            <p class="profil-stats-historique">Total Miams cumulés (à vie) : <?= htmlspecialchars($miams_historique) ?></p>
            <p>Statut fidélité : <strong><?= $statut_miams ?></strong></p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 20px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form action="/api/client/profil.php" method="POST" id="profile-form">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group form-group-spacing">
                <label for="nom">Nom :</label>
                <input type="text" data-field="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" disabled>
            </div>
            <div class="form-group form-group-spacing">
                <label for="prenom">Prénom :</label>
                <input type="text" data-field="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" disabled>
            </div>
            <div class="form-group form-group-spacing">
                <label for="tel">Téléphone :</label>
                <input type="text" data-field="tel" value="<?= htmlspecialchars($user['tel'] ?? '') ?>" disabled>
            </div>
            <div class="form-group form-group-spacing">
                <label for="rue">Rue / Numéro :</label>
                <input type="text" data-field="rue" value="<?= htmlspecialchars($user['rue'] ?? '') ?>" disabled>
            </div>
            <div class="form-row form-row-spacing">
                <div class="form-group flex-1">
                    <label for="code_postal">Code Postal :</label>
                    <input type="text" data-field="code_postal" value="<?= htmlspecialchars($user['code_postal'] ?? '') ?>" disabled>
                </div>
                <div class="form-group flex-2">
                    <label for="ville">Ville :</label>
                    <input type="text" data-field="ville" value="<?= htmlspecialchars($user['ville'] ?? '') ?>" disabled>
                </div>
            </div>
            <div class="form-group form-group-spacing-lg">
                <label for="complement">Complément d'adresse (Bâtiment, Étage...) :</label>
                <input type="text" data-field="complement" value="<?= htmlspecialchars($user['complement'] ?? '') ?>" disabled>
            </div>
            <div id="profil-message"></div>
            <button type="button" id="btn-edit-profil">✏️ Modifier</button>
            <button type="button" id="btn-save-profil" style="display:none">💾 Enregistrer</button>
            <button type="button" id="btn-cancel-profil" style="display:none">❌ Annuler</button>
        </form>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
