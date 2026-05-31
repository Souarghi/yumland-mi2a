<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/commandes.php';
require_once __DIR__ . '/../includes/panier.php';

if (!function_exists('calculateDeliveryFeeAndDistance')) {
    function calculateDeliveryFeeAndDistance($adresse) {
        if (empty(trim($adresse))) return ['distance' => 0, 'fee' => 0];
        $restaurant_lat = 49.0389; // 3 Fontaines Cergy
        $restaurant_lon = 2.0811; 
        $url = "https://api-adresse.data.gouv.fr/search/?q=" . urlencode($adresse) . "&limit=1";
        $context = stream_context_create(["http" => ["method" => "GET", "header" => "User-Agent: Yumland/1.0\r\n"]]);
        $response = @file_get_contents($url, false, $context);
        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data['features'])) {
                $lon = $data['features'][0]['geometry']['coordinates'][0];
                $lat = $data['features'][0]['geometry']['coordinates'][1];
                $earth_radius = 6371; // Rayon de la terre en km
                $dLat = deg2rad($lat - $restaurant_lat);
                $dLon = deg2rad($lon - $restaurant_lon);
                $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($restaurant_lat)) * cos(deg2rad($lat)) * sin($dLon/2) * sin($dLon/2);
                $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                $distance = round($earth_radius * $c, 2);
                
                $fee = 0;
                if ($distance < 5) $fee = 0;
                elseif ($distance <= 10) $fee = 3;
                elseif ($distance <= 15) $fee = 5;
                else $fee = 10;
                
                return ['distance' => $distance, 'fee' => $fee];
            }
        }
        return ['distance' => 0, 'fee' => 0];
    }
}

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('/api/pages/connexion.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification de sécurité CSRF sur toutes les actions POST de cette page
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // hash_equals prévient les attaques par analyse temporelle (timing attacks)
        die("Erreur de sécurité : jeton CSRF invalide.");
    }
}

// Traitement de l'annulation pour modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_panier') {
    $id_commande = (int)$_POST['id_commande'];
    
    // Vérifier la commande (doit être "En attente")
    $stmtCheck = $pdo->prepare("SELECT id_commande, prix_total, paiement_statut FROM Commandes WHERE id_commande = ? AND id_client = ? AND statut = 'En attente'");
    $stmtCheck->execute([$id_commande, $_SESSION['user_id']]);
    $cmdToEdit = $stmtCheck->fetch();
    
    if ($cmdToEdit) {
        // 1. Remettre les plats dans le panier
        $stmtDetails = $pdo->prepare("SELECT id_produit, quantite, prix_unitaire, options_choisies FROM Contenu_Commandes WHERE id_commande = ?");
        $stmtDetails->execute([$id_commande]);
        $details = $stmtDetails->fetchAll();
        
        clearCart(); // On vide le panier actuel
        foreach ($details as $item) {
            $options = json_decode($item['options_choisies'], true) ?: [];
            $clean_options = [];
            $note = '';
            foreach ($options as $opt) {
                if (preg_match('/^📝\s*(.*)$/u', $opt, $matches)) {
                    $note = $matches[1];
                } else {
                    $clean_options[] = $opt;
                }
            }
            
            addToCart($item['id_produit'], $item['quantite'], $clean_options);
            
            $cart_keys = array_keys($_SESSION['cart']['items']);
            $last_index = end($cart_keys);
            
            // Restauration du vrai prix unitaire (indispensable pour conserver la gratuité des cadeaux Miams)
            $_SESSION['cart']['items'][$last_index]['prix_unitaire'] = (float)$item['prix_unitaire'];
            
            if ($note !== '') {
                $_SESSION['cart']['items'][$last_index]['note'] = $note;
            }
        }
        if (function_exists('updateCartTotal')) updateCartTotal();
        
        // 2. On enregistre en session qu'on est en train d'éditer cette commande
        $_SESSION['edit_commande_id'] = $id_commande;
        
        // 3. Rediriger vers le panier pour qu'il puisse éditer librement
        header('Location: /api/panier.php?info=editing');
        exit;
    }
}

// Traitement de la re-commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'recommander') {
    $id_commande = (int)$_POST['id_commande'];
    
    // Par sécurité, on vérifie que le client tente bien de recommander SA propre commande
    $stmtCheck = $pdo->prepare("SELECT id_commande FROM Commandes WHERE id_commande = ? AND id_client = ?");
    $stmtCheck->execute([$id_commande, $_SESSION['user_id']]);
    if ($stmtCheck->fetch()) {
        $stmtDetails = $pdo->prepare("SELECT id_produit, quantite, options_choisies FROM Contenu_Commandes WHERE id_commande = ?");
        $stmtDetails->execute([$id_commande]);
        $details = $stmtDetails->fetchAll();
        
        // On boucle sur l'ancienne commande et on balance tout dans le panier actuel
        foreach ($details as $item) {
            $options = json_decode($item['options_choisies'], true) ?: [];
            $clean_options = [];
            foreach ($options as $opt) {
                // On retire les mentions de Miams pour ne pas fausser le solde sur la nouvelle commande
                if (!preg_match('/Cadeau Club/u', $opt) && !preg_match('/^📝/u', $opt)) {
                    $clean_options[] = $opt;
                }
            }
            addToCart($item['id_produit'], $item['quantite'], $clean_options);
        }
        header('Location: /api/panier.php');
        exit;
    }
}

// Traitement de la modification d'adresse de livraison
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_adresse') {
    $id_commande = (int)$_POST['id_commande'];
    
    $rue = trim($_POST['rue'] ?? '');
    $code_postal = trim($_POST['code_postal'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $complement = trim($_POST['complement'] ?? '');
    
    $adresse_parts = [];
    if (!empty($rue)) $adresse_parts[] = $rue . (!empty($complement) ? ' ' . $complement : '');
    if (!empty($code_postal) || !empty($ville)) $adresse_parts[] = trim($code_postal . ' ' . $ville);
    $nouvelle_adresse = implode(', ', $adresse_parts);
    
    if (!empty($nouvelle_adresse)) {
        // Vérifier que la commande appartient au client et n'est pas encore en livraison
        $stmtCheck = $pdo->prepare("SELECT id_commande, prix_total, frais_livraison FROM Commandes WHERE id_commande = ? AND id_client = ? AND statut NOT IN ('En livraison', 'Livrée', 'Annulée')");
        $stmtCheck->execute([$id_commande, $_SESSION['user_id']]);
        $commandeData = $stmtCheck->fetch();
        
        if ($commandeData) {
            $old_frais = (float)($commandeData['frais_livraison'] ?? 0);
            $old_total = (float)$commandeData['prix_total'];
            
            $calc = calculateDeliveryFeeAndDistance($nouvelle_adresse);
            $new_distance = $calc['distance'];
            $new_frais = $calc['fee'];
            
            if ($new_frais > $old_frais) {
                // Le total augmente : il faut charger le panier et rediriger vers le paiement
                $stmtDetails = $pdo->prepare("SELECT id_produit, quantite, prix_unitaire, options_choisies FROM Contenu_Commandes WHERE id_commande = ?");
                $stmtDetails->execute([$id_commande]);
                $details = $stmtDetails->fetchAll();
                
                clearCart();
                foreach ($details as $item) {
                    $options = json_decode($item['options_choisies'], true) ?: [];
                    $clean_options = [];
                    $note = '';
                    foreach ($options as $opt) {
                        if (preg_match('/^📝\s*(.*)$/u', $opt, $matches)) {
                            $note = $matches[1];
                        } else {
                            $clean_options[] = $opt;
                        }
                    }
                    addToCart($item['id_produit'], $item['quantite'], $clean_options);
                    
                    $cart_keys = array_keys($_SESSION['cart']['items']);
                    $last_index = end($cart_keys);
                    $_SESSION['cart']['items'][$last_index]['prix_unitaire'] = (float)$item['prix_unitaire'];
                    
                    if ($note !== '') {
                        $_SESSION['cart']['items'][$last_index]['note'] = $note;
                    }
                }
                if (function_exists('updateCartTotal')) updateCartTotal();
                
                $stmtUpdate = $pdo->prepare("UPDATE Commandes SET adresse_livraison = ?, frais_livraison = ?, distance_km = ? WHERE id_commande = ?");
                $stmtUpdate->execute([$nouvelle_adresse, $new_frais, $new_distance, $id_commande]);
                
                $_SESSION['edit_commande_id'] = $id_commande;
                $_SESSION['frais_livraison_temp'] = $new_frais;
                $_SESSION['distance_km_temp'] = $new_distance;
                
                header('Location: /api/commander.php?mode=supplement');
                exit;
            } else {
                // Le total baisse ou reste identique : mise à jour simple (les frais baissent mais la commande diminue sans remboursement)
                $food_price = $old_total - $old_frais;
                $new_total = $food_price + $new_frais;
                
                $stmtUpdate = $pdo->prepare("UPDATE Commandes SET adresse_livraison = ?, frais_livraison = ?, distance_km = ?, prix_total = ? WHERE id_commande = ?");
                $stmtUpdate->execute([$nouvelle_adresse, $new_frais, $new_distance, $new_total, $id_commande]);
                
                // Remboursement automatique de la différence en Miams
                $difference = $old_total - $new_total;
                if ($difference > 0) {
                    $miams_rembourses = floor($difference * 10);
                    $stmtMiams = $pdo->prepare("UPDATE Utilisateurs SET solde_miams = solde_miams + ?, total_miams_historique = total_miams_historique + ? WHERE id_user = ?");
                    $stmtMiams->execute([$miams_rembourses, $miams_rembourses, $_SESSION['user_id']]);
                    header("Location: /api/client/commandes.php?success=adresse_modifiee&refund_miams=$miams_rembourses");
                } else {
                    header('Location: /api/client/commandes.php?success=adresse_modifiee');
                }
                exit;
            }
        }
    }
}

// Récupérer les commandes de l'utilisateur
$commandes = getAllCommandes(null, $_SESSION['user_id'], 'DESC');

// NOUVEAU : Vérifier quelles commandes ont déjà été notées
$commandes_notees = [];
try {
    $stmtAvis = $pdo->prepare("SELECT id_commande FROM Avis WHERE id_client = ?");
    $stmtAvis->execute([$_SESSION['user_id']]);
    $commandes_notees = $stmtAvis->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { }

// Définir la page courante pour le menu actif
$currentPage = 'client_commandes';
$pageTitle = 'Mes Commandes';

// Génération du jeton CSRF pour les formulaires de la page.
// La fonction generateCSRFToken() est supposée venir de includes/auth.php comme dans les autres pages sécurisées.
if (function_exists('generateCSRFToken')) {
    $csrf_token = generateCSRFToken();
}

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="client-section">
    <div class="container">
        <h1>Mes Commandes</h1>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'commande_validee'): ?>
            <div class="alert alert-success">
                ✅ Votre commande a bien été validée et payée !
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'commande_modifiee'): ?>
            <div class="alert alert-success">
                ✏️ Votre commande a été mise à jour avec succès ! Le Chef a reçu les modifications.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'adresse_modifiee'): ?>
            <div class="alert alert-success">
                📍 L'adresse de livraison a été mise à jour avec succès !
                <?php if (isset($_GET['refund_miams']) && $_GET['refund_miams'] > 0): ?>
                    <br>🎁 Bonne nouvelle ! La livraison étant moins chère, nous vous avons crédité la différence : <strong>+<?= (int)$_GET['refund_miams'] ?> Miams</strong> sur votre compte.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($commandes)): ?>
            <div class="empty-commandes">
                <p>Vous n'avez pas encore passé de commande.</p>
                <a href="/api/pages/carte.php" class="btn-primary">Voir la carte</a>
            </div>
        <?php else: ?>
            <div class="commandes-list">
                <?php foreach ($commandes as $commande): ?>
                    <div class="commande-item card-style">
                        <div class="commande-header">
                            <h3>Commande #<?= $commande['id_commande'] ?></h3>
                            <span class="commande-date">
                                <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?>
                            </span>
                        </div>
                        
                        <div class="commande-details">
                            <p><strong>Statut:</strong> 
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $commande['statut'])) ?>">
                                    <?= htmlspecialchars($commande['statut']) ?>
                                </span>
                            </p>
                            <p><strong>Mode:</strong> <?= htmlspecialchars($commande['mode_retrait'] ?? 'Livraison') ?></p>
                            <?php if (strtolower($commande['mode_retrait'] ?? 'livraison') === 'livraison'): ?>
                                <div class="adresse-container">
                                    <strong>Adresse:</strong> 
                                    
                                    <div id="view-addr-<?= $commande['id_commande'] ?>" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 5px;">
                                        <span><?= htmlspecialchars(!empty($commande['adresse_livraison']) ? $commande['adresse_livraison'] : ($commande['client_adresse'] ?? 'Non spécifiée')) ?></span>
                                        <?php if (!in_array($commande['statut'], ['En livraison', 'Livrée', 'Annulée'])): ?>
                                            <button type="button" class="btn-outline btn-sm" onclick="toggleEditAddr(<?= $commande['id_commande'] ?>, true)"><i class="fas fa-edit"></i> Modifier</button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!in_array($commande['statut'], ['En livraison', 'Livrée', 'Annulée'])): ?>
                                        <form id="form-edit-addr-<?= $commande['id_commande'] ?>" method="POST" class="form-edit-inline" style="display: none; padding: 15px; background: #f9f9f9; border-radius: 8px; margin-top: 10px; border: 1px solid #ddd;" onsubmit="toggleEditAddr(<?= $commande['id_commande'] ?>, false, true)">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                            <input type="hidden" name="action" value="modifier_adresse">
                                            <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                            
                                            <div style="margin-bottom: 15px;" class="autocomplete-container">
                                                <label for="adresse_search_<?= $commande['id_commande'] ?>" style="display:block; margin-bottom: 5px; font-weight: bold; color: var(--color-primary);">Rechercher la nouvelle adresse *</label>
                                                <input type="text" id="adresse_search_<?= $commande['id_commande'] ?>" placeholder="Commencez à taper (ex: 3 Fontaines Cergy)..." autocomplete="off" class="cart-address-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;" oninput="fetchAddr(this.value, <?= $commande['id_commande'] ?>)">
                                                <ul id="addr-results-<?= $commande['id_commande'] ?>" class="autocomplete-results"></ul>
                                            </div>
                                            <div style="margin-bottom: 10px;">
                                                <label style="display:block; margin-bottom: 5px;">Rue/Numéro</label>
                                                <input type="text" name="rue" id="rue_<?= $commande['id_commande'] ?>" class="cart-address-input readonly-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; background-color: #f5f5f5;" readonly placeholder="Auto-rempli" required>
                                            </div>
                                            <div style="margin-bottom: 10px;">
                                                <label style="display:block; margin-bottom: 5px;">Complément d'adresse (Bâtiment, Étage...)</label>
                                                <input type="text" name="complement" id="complement_<?= $commande['id_commande'] ?>" class="cart-address-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;" placeholder="Ex: Bâtiment B, 3ème étage">
                                            </div>
                                            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                                <div style="flex: 1;">
                                                    <label style="display:block; margin-bottom: 5px;">Code Postal</label>
                                                    <input type="text" name="code_postal" id="code_postal_<?= $commande['id_commande'] ?>" class="cart-address-input readonly-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; background-color: #f5f5f5;" readonly required placeholder="Auto">
                                                </div>
                                                <div style="flex: 2;">
                                                    <label style="display:block; margin-bottom: 5px;">Ville</label>
                                                    <input type="text" name="ville" id="ville_<?= $commande['id_commande'] ?>" class="cart-address-input readonly-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; background-color: #f5f5f5;" readonly required placeholder="Auto">
                                                </div>
                                            </div>
                                            <div class="edit-actions-wrapper">
                                                <button type="submit" class="btn-primary btn-sm">Enregistrer l'adresse</button>
                                                <button type="button" class="btn-secondary btn-sm" onclick="toggleEditAddr(<?= $commande['id_commande'] ?>, false, false)">Annuler</button>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($commande['distance_km']) && isset($commande['frais_livraison']) && strtolower($commande['mode_retrait'] ?? 'livraison') === 'livraison'): ?>
                                        <p style="margin-top: 5px;"><strong>Distance:</strong> <?= number_format($commande['distance_km'], 2, ',', ' ') ?> km</p>
                                        <p><strong>Frais de livraison:</strong> <?= $commande['frais_livraison'] > 0 ? number_format($commande['frais_livraison'], 2, ',', ' ') . ' €' : '<span style="color:var(--color-success);font-weight:bold;">Gratuit</span>' ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <p><strong>Montant:</strong> <?= number_format($commande['prix_total'], 2, ',', ' ') ?> €</p>
                        </div>
                        
                        <div class="commande-items">
                            <h4>Détails de la commande</h4>
                            <?php
                            // Récupérer les détails de cette commande spécifique
                            $stmtDetails = $pdo->prepare("SELECT cc.*, p.nom FROM Contenu_Commandes cc JOIN Produits p ON cc.id_produit = p.id_produit WHERE cc.id_commande = ?");
                            $stmtDetails->execute([$commande['id_commande']]);
                            $details = $stmtDetails->fetchAll();
                            
                            $sous_total_items = 0;
                            ?>
                            <ul>
                                <?php foreach ($details as $detail): ?>
                                    <?php $sous_total_items += $detail['prix_unitaire'] * $detail['quantite']; ?>
                                    <li>
                                        <span class="item-name"><?= htmlspecialchars($detail['nom']) ?></span>
                                        <span class="item-quantity">x<?= $detail['quantite'] ?></span>
                                        <span class="item-price"><?= number_format($detail['prix_unitaire'] * $detail['quantite'], 2, ',', ' ') ?> €</span>
                                        <?php 
                                        $options = json_decode($detail['options_choisies'], true);
                                        if (!empty($options)): 
                                        ?>
                                            <span class="item-options">
                                                <em>↳ <?= htmlspecialchars(implode(', ', $options)) ?></em>
                                            </span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <?php 
                            $frais = $commande['frais_livraison'] ?? 0;
                            $expected_total = $sous_total_items + $frais;
                            $diff = $expected_total - $commande['prix_total'];
                            if ($diff > 0.01): 
                            ?>
                                <p style="text-align: right; margin-top: 10px; font-size: 0.95em; color: var(--color-success);">
                                    <strong>🎁 Remise Club Fidélité (-10%) : -<?= number_format($diff, 2, ',', ' ') ?> €</strong>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="commande-actions">
                            <?php if ($commande['statut'] === 'En attente'): ?>
                                <form method="POST" onsubmit="return confirm('Voulez-vous modifier cette commande ? Son contenu sera placé dans votre panier pour que vous puissiez l\'éditer librement.');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                    <input type="hidden" name="action" value="modifier_panier">
                                    <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                    <button type="submit" class="btn-outline btn-sm">
                                        <i class="fas fa-shopping-cart"></i> Modifier les plats
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($commande['statut'] === 'Livrée'): ?>
                                <?php if (in_array($commande['id_commande'], $commandes_notees)): ?>
                                    <span class="btn-avis-laisse btn-sm" title="Vous avez déjà noté cette commande.">✅ Avis laissé</span>
                                <?php else: ?>
                                    <a href="/api/client/noter.php?commande_id=<?= $commande['id_commande'] ?>" class="btn-outline btn-sm">⭐ Noter</a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                <input type="hidden" name="action" value="recommander">
                                <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                <button type="submit" class="btn-primary btn-sm">
                                    <i class="fas fa-sync-alt"></i> Recommander
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<script>
// Fonction propre pour gérer l'apparition/disparition du formulaire d'adresse
function toggleEditAddr(id, showForm, isSubmit = false) {
    const viewDiv = document.getElementById('view-addr-' + id);
    const formForm = document.getElementById('form-edit-addr-' + id);
    if (showForm) {
        if (viewDiv) viewDiv.style.display = 'none';
        if (formForm) formForm.style.display = 'block';
    } else {
        if (formForm) formForm.style.display = 'none';
        if (viewDiv) {
            if (isSubmit) {
                viewDiv.innerHTML = '<span style="color:var(--color-primary); font-weight:bold;"><i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...</span>';
            }
            viewDiv.style.display = 'flex';
        }
    }
}

let addrTimeout;
async function fetchAddr(query, id) {
    clearTimeout(addrTimeout);
    const resultsList = document.getElementById('addr-results-' + id);
    const searchInput = document.getElementById('adresse_search_' + id);
    const rueInput = document.getElementById('rue_' + id);
    const cpInput = document.getElementById('code_postal_' + id);
    const villeInput = document.getElementById('ville_' + id);
    
    if (query.length < 3) {
        resultsList.innerHTML = '';
        return;
    }
    
    addrTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`);
            const data = await response.json();
            resultsList.innerHTML = '';
            data.features.forEach(feature => {
                const li = document.createElement('li');
                li.textContent = feature.properties.label;
                li.style.padding = "10px";
                li.style.cursor = "pointer";
                li.style.borderBottom = "1px solid #eee";
                li.addEventListener('click', () => {
                    if (searchInput) searchInput.value = feature.properties.label;
                    if (rueInput) rueInput.value = feature.properties.name || '';
                    if (cpInput) cpInput.value = feature.properties.postcode || '';
                    if (villeInput) villeInput.value = feature.properties.city || '';
                    resultsList.innerHTML = '';
                });
                resultsList.appendChild(li);
            });
        } catch (error) {
            console.error("Erreur API Adresse :", error);
        }
    }, 300);
}
document.addEventListener('click', (e) => {
    if (!e.target.classList.contains('cart-address-input')) {
        document.querySelectorAll('.autocomplete-results').forEach(el => el.innerHTML = '');
    }
});
</script>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>