<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !hasRole('Administrateur')) {
    redirect('/api/pages/connexion.php');
}

$message = '';
$messageType = 'success';

// Traitement des actions (Ajout, Modification, Suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken()) {
        $message = "Erreur de sécurité CSRF. Veuillez réessayer.";
        $messageType = "danger";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add' || $action === 'edit') {
            $id_produit = $_POST['id_produit'] ?? null;
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = (float)($_POST['prix'] ?? 0);
            $categorie = trim($_POST['categorie'] ?? 'Burgers');
            $image_url = trim($_POST['image_url'] ?? '');
            $options_config = trim($_POST['options_config'] ?? '[]');
            
            // Vérifier que le champ JSON est valide
            json_decode($options_config);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $options_config = '[]';
            }

            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO Produits (nom, description, prix, categorie, image_url, options_config) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nom, $description, $prix, $categorie, $image_url, $options_config]);
                    $message = "✅ Le nouveau plat a été ajouté à la carte avec succès.";
                } else {
                    $stmt = $pdo->prepare("UPDATE Produits SET nom=?, description=?, prix=?, categorie=?, image_url=?, options_config=? WHERE id_produit=?");
                    $stmt->execute([$nom, $description, $prix, $categorie, $image_url, $options_config, $id_produit]);
                    $message = "✅ Plat modifié avec succès.";
                }
            } catch (PDOException $e) {
                $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
                $messageType = "danger";
            }
        } elseif ($action === 'delete') {
            $id_produit = $_POST['id_produit'] ?? null;
            try {
                $stmt = $pdo->prepare("DELETE FROM Produits WHERE id_produit=?");
                $stmt->execute([$id_produit]);
                $message = "🗑️ Le plat a été retiré de la carte.";
            } catch (PDOException $e) {
                $message = "❌ Impossible de supprimer ce plat de manière définitive car il est lié à des commandes passées. Modifiez-le ou masquez-le à la place.";
                $messageType = "danger";
            }
        }
    }
}

// Récupérer tous les produits pour l'affichage
$stmt = $pdo->query("SELECT * FROM Produits ORDER BY categorie, nom");
$produits = $stmt->fetchAll();

$csrf_token = generateCSRFToken();
$currentPage = 'admin_menu';
$pageTitle = 'Gestion du Menu';

include_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-section">
    <div class="container">
        <h1>🍔 Gestion du Menu (La Carte)</h1>
        
        <div class="admin-header">
            <button onclick="openMenuModal('add')" class="btn-primary"><i class="fas fa-plus"></i> Ajouter un nouveau plat</button>
            <a href="/api/admin/dashboard.php" class="btn-secondary">Retour au Dashboard</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 20px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="admin-table-container card-style">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom du plat</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $plat): ?>
                        <tr>
                            <td>
                                <?php if (!empty($plat['image_url'])): ?>
                                    <img src="<?= htmlspecialchars((strpos($plat['image_url'], '/') === 0) ? $plat['image_url'] : '/' . $plat['image_url']) ?>" alt="Aperçu" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🍽️</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($plat['nom']) ?></strong><br><small style="color: #888;"><?= htmlspecialchars(substr($plat['description'] ?? '', 0, 50)) ?>...</small></td>
                            <td><span class="role-badge role-restaurateur"><?= htmlspecialchars($plat['categorie'] ?? 'Divers') ?></span></td>
                            <td><strong><?= number_format($plat['prix'], 2, ',', ' ') ?> €</strong></td>
                            <td class="actions-cell" style="text-align: right;">
                                <button class="action-btn edit-btn" onclick='openMenuModal("edit", <?= json_encode($plat, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️ Modifier</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Attention ! Êtes-vous sûr de vouloir supprimer ce plat de la carte ?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_produit" value="<?= $plat['id_produit'] ?>">
                                    <button type="submit" class="action-btn delete-btn">🗑️ Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Fenêtre modale gérant l'Ajout ET la Modification -->
<div id="crudMenuModal" class="modal-overlay" style="display: none; opacity: 0; transition: opacity 0.3s ease; z-index: 10000;">
    <div class="modal-container" style="max-width: 600px; width: 90%; transform: translateY(-50px); transition: transform 0.3s ease;">
        <h2 id="modal-menu-title" class="modal-title">Ajouter un plat</h2>
        <form method="POST" action="/api/admin/menu.php" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="action" id="modal-menu-action" value="add">
            <input type="hidden" name="id_produit" id="modal-menu-id" value="">
            
            <div class="form-group"><label>Nom du plat *</label><input type="text" name="nom" id="modal-menu-nom" required></div>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1;"><label>Prix unitaire (€) *</label><input type="number" step="0.01" name="prix" id="modal-menu-prix" required></div>
                <div class="form-group" style="flex: 1;">
                    <label>Catégorie *</label>
                    <select name="categorie" id="modal-menu-cat" required>
                        <option value="Burgers">Burgers</option>
                        <option value="Viandes">Viandes</option>
                        <option value="Accompagnements">Accompagnements</option>
                        <option value="Sauces">Sauces</option>
                        <option value="Boissons">Boissons</option>
                        <option value="Desserts">Desserts</option>
                    </select>
                </div>
            </div>
            <div class="form-group"><label>Description commercial du plat</label><textarea name="description" id="modal-menu-desc" rows="3"></textarea></div>
            <div class="form-group"><label>Chemin de l'image (ex: /images/nourriture/burger.png)</label><input type="text" name="image_url" id="modal-menu-img"></div>
            <div class="form-group"><label>Configuration des options (Format JSON) *</label><textarea name="options_config" id="modal-menu-opt" rows="4" style="font-family: monospace;">[]</textarea><small style="color: #666; display: block; margin-top: 5px;">⚠️ Réservé aux utilisateurs avancés. Le JSON doit suivre la structure : <code>[{"titre":"Cuisson", "choix":["Saignant","A point"]}]</code></small></div>
            
            <div class="modal-actions" style="margin-top: 10px;">
                <button type="button" onclick="closeMenuModal()" class="btn-secondary">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer ce plat</button>
            </div>
        </form>
    </div>
</div>

<script>
function openMenuModal(action, plat = null) {
    document.getElementById('modal-menu-action').value = action;
    document.getElementById('modal-menu-title').textContent = (action === 'edit') ? "Modifier le plat" : "Ajouter un plat";
    document.getElementById('modal-menu-id').value = plat ? plat.id_produit : '';
    document.getElementById('modal-menu-nom').value = plat ? plat.nom : '';
    document.getElementById('modal-menu-prix').value = plat ? plat.prix : '';
    document.getElementById('modal-menu-cat').value = plat ? (plat.categorie || 'Burgers') : 'Burgers';
    document.getElementById('modal-menu-desc').value = plat ? (plat.description || '') : '';
    document.getElementById('modal-menu-img').value = plat ? (plat.image_url || '') : '';
    document.getElementById('modal-menu-opt').value = plat ? (plat.options_config || '[]') : '[\n  \n]';
    
    const modal = document.getElementById('crudMenuModal');
    modal.style.display = 'flex';
    // Petit délai pour permettre au navigateur d'appliquer le display avant de lancer l'animation
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.querySelector('.modal-container').style.transform = 'translateY(0)';
    }, 10);
}

function closeMenuModal() {
    const modal = document.getElementById('crudMenuModal');
    modal.style.opacity = '0';
    modal.querySelector('.modal-container').style.transform = 'translateY(-50px)';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}
</script>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>