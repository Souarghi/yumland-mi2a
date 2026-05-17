<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !hasRole('Administrateur')) {
    redirect('/api/pages/connexion.php');
}

// Récupérer tous les utilisateurs
$stmt = $pdo->query("SELECT * FROM Utilisateurs");
$users = $stmt->fetchAll();

// Définir la page courante pour le menu actif
$currentPage = 'admin_dashboard';
$pageTitle = 'Administration';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<script defer>
async function toggleBlock(userId, btn) {
  const response = await fetch('/api/admin/toggle_block.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id_user: userId })
  });
  const data = await response.json();
  if (data.success) {
    const badge = document.querySelector(`#statut-${userId}`);
    if (badge) badge.textContent = data.new_statut;
    btn.textContent = data.new_statut === 'Bloqué' ? '🔓 Débloquer' : '🔒 Bloquer';
    btn.classList.toggle('btn-danger', data.new_statut === 'Bloqué');
  } else {
    alert(data.message);
  }
}

function editRole(userId, currentRole) {
    const td = document.getElementById('role-td-' + userId);
    if (!td) return;
    
    const select = document.createElement('select');
    select.style.padding = '5px';
    select.style.borderRadius = '4px';
    
    ['Client', 'Administrateur', 'Restaurateur', 'Livreur'].forEach(r => {
        let opt = document.createElement('option');
        opt.value = r;
        opt.textContent = r;
        if (r === currentRole) opt.selected = true;
        select.appendChild(opt);
    });
    
    select.onchange = async function() {
        const newRole = this.value;
        const response = await fetch('/api/admin/update_role.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_user: userId, new_role: newRole })
        });
        const data = await response.json();
        if (data.success) {
            td.setAttribute('data-role', newRole);
            td.innerHTML = `<span class="role-badge role-${newRole.toLowerCase()}">${newRole}</span>`;
            const btnEdit = td.parentNode.querySelector('.btn-edit');
            if (btnEdit) {
                btnEdit.setAttribute('onclick', `editRole(${userId}, '${newRole}')`);
            }
        } else {
            alert(data.message);
            td.innerHTML = `<span class="role-badge role-${currentRole.toLowerCase()}">${currentRole}</span>`;
        }
    };
    
    select.onblur = function() {
        if (this.value === currentRole) {
             td.innerHTML = `<span class="role-badge role-${currentRole.toLowerCase()}">${currentRole}</span>`;
        }
    };
    
    td.innerHTML = '';
    td.appendChild(select);
    select.focus();
}

async function deleteUser(userId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.")) {
        const response = await fetch('/api/admin/delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_user: userId })
        });
        const data = await response.json();
        if (data.success) {
            const row = document.getElementById('row-' + userId);
            if (row) row.remove();
        } else {
            alert(data.message);
        }
    }
}
</script>

<section class="admin-section">
    <div class="container">
        <h1>Tableau de bord administrateur</h1>
        
        <div class="admin-container">           
            <div class="admin-content">
                <div class="admin-header">
                    <h2>Gestion des utilisateurs</h2>
                    <p>Vous pouvez consulter et gérer tous les utilisateurs de la plateforme.</p>
                </div>
                
                <div class="admin-table-container card-style">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom d'utilisateur</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr id="row-<?= $user['id_user'] ?>">
                                    <td><?= $user['id_user'] ?></td>
                                    <td><?= htmlspecialchars($user['nom'] . ' ' . ($user['prenom'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($user['nom']) ?></td>
                                    <td><?= htmlspecialchars($user['prenom'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td id="role-td-<?= $user['id_user'] ?>" data-role="<?= htmlspecialchars($user['role']) ?>">
                                        <span class="role-badge role-<?= strtolower($user['role']) ?>">
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span id="statut-<?= $user['id_user'] ?>" class="status-badge">
                                            <?= htmlspecialchars($user['statut'] ?? 'Actif') ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <button class="btn-edit" title="Modifier le rôle" onclick="editRole(<?= $user['id_user'] ?>, '<?= htmlspecialchars($user['role'], ENT_QUOTES) ?>')">✏️</button>
                                        <button
                                                onclick="toggleBlock(<?= $user['id_user'] ?>, this)"
                                                class="<?= ($user['statut'] ?? '') === 'Bloqué' ? 'btn-activate' : 'btn-block' ?>">
                                                <?= ($user['statut'] ?? '') === 'Bloqué' ? '🔓 Débloquer' : '🔒 Bloquer' ?>
                                            </button>
                                        <button class="btn-delete" title="Supprimer" onclick="deleteUser(<?= $user['id_user'] ?>)">🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
    </div>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>