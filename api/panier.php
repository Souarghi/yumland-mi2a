<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/panier.php';
require_once __DIR__ . '/includes/plats.php';

// Fonction pour calculer la distance et les frais de livraison via l'API Adresse du gouvernement
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

// Traiter les actions sur le panier
$message = '';

// Info depuis la modification de commande
if (isset($_GET['info']) && $_GET['info'] === 'editing' && isset($_SESSION['edit_commande_id'])) {
    $message = '✏️ Vous modifiez actuellement la commande #' . $_SESSION['edit_commande_id'] . '. Ajustez vos plats et cliquez sur Enregistrer !';
}

$action = $_POST['action'] ?? '';
// Fix pour les navigateurs qui n'envoient pas la valeur du bouton lors d'un form.submit() ou button.click()
if (empty($action) && isset($_POST['quantite'])) {
    $action = 'update';
}

// Action: Mettre à jour la quantité OU soumission via bouton Enregistrer/Payer/Checkout
if ($action === 'update' || $action === 'save_edit' || $action === 'checkout') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = 'Erreur de sécurité, veuillez réessayer.';
        // On bloque formellement la suite de l'exécution pour protéger la base de données
        $action = ''; 
    } else {
        foreach ($_POST['quantite'] as $index => $quantite) {
            $note = $_POST['note'][$index] ?? null;
            updateCartQuantity($index, (int)$quantite, $note);
        }
        
        if ($action === 'checkout') {
            $rue = trim($_POST['rue'] ?? '');
            $code_postal = trim($_POST['code_postal'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $complement = trim($_POST['complement'] ?? '');
            
            $adresse_parts = [];
            if (!empty($rue)) $adresse_parts[] = $rue . (!empty($complement) ? ' ' . $complement : '');
            if (!empty($code_postal) || !empty($ville)) $adresse_parts[] = trim($code_postal . ' ' . $ville);
            $adresse_livraison = implode(', ', $adresse_parts);

            $mode_retrait = trim($_POST['mode_retrait'] ?? 'livraison');
            $distance = 0;
            $frais = 0;
            if ($mode_retrait === 'livraison') {
                $calc = calculateDeliveryFeeAndDistance($adresse_livraison);
                $distance = $calc['distance'];
                $frais = $calc['fee'];
            }

            if (isset($_POST['save_address_profile']) && $_POST['save_address_profile'] === '1' && isLoggedIn()) {
                $stmtUpdateProfile = $pdo->prepare("UPDATE Utilisateurs SET rue = ?, code_postal = ?, ville = ?, complement = ? WHERE id_user = ?");
                $stmtUpdateProfile->execute([$rue, $code_postal, $ville, $complement, $_SESSION['user_id']]);
            }

            // Sauvegarde de l'adresse en session avant d'aller vers CYBank
            $_SESSION['adresse_livraison_temp'] = $adresse_livraison;
            $_SESSION['mode_retrait_temp'] = $mode_retrait;
            $_SESSION['distance_km_temp'] = $distance;
            $_SESSION['frais_livraison_temp'] = $frais;
            header('Location: /api/commander.php');
            exit;
        }
        
        if ($action === 'update') {
            $message = 'Le panier a été mis à jour.';
        }
    }
}

// Action: Sauvegarder l'édition d'une commande
if ((isset($_GET['action']) && $_GET['action'] === 'save_edit') || ($action === 'save_edit')) {
    if (isset($_SESSION['edit_commande_id'])) {
        $id_commande = $_SESSION['edit_commande_id'];
        $cart = getCart(); // On rafraîchit le panier après l'update ci-dessus !
        
        $rue = trim($_POST['rue'] ?? '');
        $code_postal = trim($_POST['code_postal'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $complement = trim($_POST['complement'] ?? '');
        
        $adresse_parts = [];
        if (!empty($rue)) $adresse_parts[] = $rue . (!empty($complement) ? ' ' . $complement : '');
        if (!empty($code_postal) || !empty($ville)) $adresse_parts[] = trim($code_postal . ' ' . $ville);
        $adresse_livraison = implode(', ', $adresse_parts);

        if (isset($_POST['save_address_profile']) && $_POST['save_address_profile'] === '1' && isLoggedIn()) {
            $stmtUpdateProfile = $pdo->prepare("UPDATE Utilisateurs SET rue = ?, code_postal = ?, ville = ?, complement = ? WHERE id_user = ?");
            $stmtUpdateProfile->execute([$rue, $code_postal, $ville, $complement, $_SESSION['user_id']]);
        }
        
        if (!empty($cart['items'])) {
            // Application du statut LÉGENDE DU STEAK (-10%)
            $stmtMiams = $pdo->prepare("SELECT total_miams_historique FROM Utilisateurs WHERE id_user = ?");
            $stmtMiams->execute([$_SESSION['user_id']]);
            $miams_historique = $stmtMiams->fetchColumn() ?: 0;
            if ($miams_historique >= 3000) {
                $cart['total'] = $cart['total'] * 0.90;
            }

            // Calcul de l'ancien total pour vérifier s'il y a une différence à payer
            $stmt = $pdo->prepare("SELECT prix_total FROM Commandes WHERE id_commande = ? AND id_client = ?");
            $stmt->execute([$id_commande, $_SESSION['user_id']]);
            $old_total = $stmt->fetchColumn();
            
            $mode_retrait = trim($_POST['mode_retrait'] ?? 'livraison');
            
            $distance = 0;
            $frais = 0;
            if ($mode_retrait === 'livraison') {
                $calc = calculateDeliveryFeeAndDistance($adresse_livraison);
                $distance = $calc['distance'];
                $frais = $calc['fee'];
            }
            
            $total_avec_frais = $cart['total'] + $frais;
            
            if ($old_total !== false && $total_avec_frais > $old_total) {
                $pdo->prepare("UPDATE Commandes SET adresse_livraison = COALESCE(NULLIF(?, ''), adresse_livraison), mode_retrait = ?, frais_livraison = ?, distance_km = ? WHERE id_commande = ? AND id_client = ?")->execute([$adresse_livraison, $mode_retrait, $frais, $distance, $id_commande, $_SESSION['user_id']]);
                $_SESSION['distance_km_temp'] = $distance;
                $_SESSION['frais_livraison_temp'] = $frais;
                // Différence à payer -> Redirection vers la passerelle de paiement
                header('Location: /api/commander.php?mode=supplement');
                exit;
            }
            
            // Si le prix est identique ou inférieur, on met à jour directement (sans paiement)
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE Commandes SET prix_total = ?, adresse_livraison = COALESCE(NULLIF(?, ''), adresse_livraison), mode_retrait = ?, frais_livraison = ?, distance_km = ? WHERE id_commande = ? AND id_client = ?");
                $stmt->execute([$total_avec_frais, $adresse_livraison, $mode_retrait, $frais, $distance, $id_commande, $_SESSION['user_id']]);
                
                $pdo->prepare("DELETE FROM Contenu_Commandes WHERE id_commande = ?")->execute([$id_commande]);
                
                $stmtContenu = $pdo->prepare("INSERT INTO Contenu_Commandes (id_commande, id_produit, quantite, prix_unitaire, options_choisies) VALUES (?, ?, ?, ?, ?)");
                foreach ($cart['items'] as $item) {
                    $options = $item['options'] ?? [];
                    if (!empty($item['note'])) {
                        $options[] = "📝 " . $item['note'];
                    }
                    $optionsJson = json_encode($options);
                    $id_produit = $item['plat_id'] ?? $item['id'];
                    $stmtContenu->execute([$id_commande, $id_produit, $item['quantite'], $item['prix_unitaire'], $optionsJson]);
                }
                
                $pdo->commit();
                clearCart();
                unset($_SESSION['edit_commande_id']);
                
                header('Location: /api/client/commandes.php?success=commande_modifiee');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'Erreur lors de la modification de la commande.';
            }
        }
    }
}

// Action: Annuler la modification
if (isset($_GET['action']) && $_GET['action'] === 'cancel_edit') {
    clearCart();
    unset($_SESSION['edit_commande_id']);
    header('Location: /api/client/commandes.php');
    exit;
}

// Action: Supprimer un élément du panier
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    if (removeFromCart($index)) {
        $message = 'L\'article a été retiré du panier.';
    }
}

// Action: Vider le panier
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    clearCart();
    $message = 'Votre panier a été vidé.';
}

// Récupérer le contenu du panier
$cart = getCart();

$subtotal = $cart['total'];
$discount = 0;

// Récupération du solde Miams si connecté
$miams = 0;
$statut_miams = "";
$tier_class = "tier-1"; // Classe par défaut (Niveau 1)

if (isLoggedIn()) {
    $stmtMiams = $pdo->prepare("SELECT solde_miams, total_miams_historique FROM Utilisateurs WHERE id_user = ?");
    $stmtMiams->execute([$_SESSION['user_id']]);
    $userMiams = $stmtMiams->fetch();
    $miams = $userMiams['solde_miams'] ?? 0;
    $miams_historique = $userMiams['total_miams_historique'] ?? $miams;
    
    // Application des Paliers de Fidélité (D'après la doc)
    if ($miams_historique < 1000) {
        $statut_miams = "PETIT GRILLEUR";
        $tier_class = "tier-1";
    } elseif ($miams_historique < 3000) {
        $statut_miams = "SAUCE CHEF";
        $tier_class = "tier-2";
    } else {
        $statut_miams = "LÉGENDE DU STEAK";
        $tier_class = "tier-3";
        $discount = $subtotal * 0.10;
        $cart['total'] = $subtotal - $discount;
    }
}

// Calcul de la différence si on est en train de modifier une commande
$difference = 0;
if (isset($_SESSION['edit_commande_id'])) {
    $stmtDiff = $pdo->prepare("SELECT prix_total FROM Commandes WHERE id_commande = ? AND id_client = ?");
    $stmtDiff->execute([$_SESSION['edit_commande_id'], $_SESSION['user_id']]);
    $old_total = $stmtDiff->fetchColumn();
    if ($old_total !== false) {
        $difference = $cart['total'] - $old_total;
    }
}

// Calcul des Miams déjà utilisés dans le panier actuel
$miams_used = 0;
foreach ($cart['items'] as $item) {
    if (!empty($item['options']) && is_array($item['options'])) {
        foreach ($item['options'] as $opt) {
            if (preg_match('/-\s*([0-9]+)\s*Miams/', $opt, $matches)) {
                $miams_used += (int)$matches[1];
            }
        }
    }
}

// Recherche des IDs génériques pour associer les récompenses Miams
$stmtProd = $pdo->query("SELECT id_produit, nom FROM Produits");
$produits_db = $stmtProd->fetchAll();
$id_sauce = 1; $id_boisson = 1; $id_dessert = 1; $id_burger = 1;
foreach($produits_db as $p) { if(stripos($p['nom'], 'Sauce') !== false) $id_sauce = $p['id_produit']; }
foreach($produits_db as $p) { if(stripos($p['nom'], 'Sodas') !== false || stripos($p['nom'], 'Boisson') !== false) $id_boisson = $p['id_produit']; }
foreach($produits_db as $p) { if(stripos($p['nom'], 'Cookie') !== false || stripos($p['nom'], 'Dessert') !== false) $id_dessert = $p['id_produit']; }
foreach($produits_db as $p) { if(stripos($p['nom'], 'Grand Miam') !== false) $id_burger = $p['id_produit']; }

$sauce_reward = getPlatById($id_sauce);
$boisson_reward = getPlatById($id_boisson);
$dessert_reward = getPlatById($id_dessert);
$burger_reward = getPlatById($id_burger);

// Calcul du solde prévisionnel en soustrayant ceux du panier
$miams_dispo = max(0, $miams - $miams_used);

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'panier';
$pageTitle = 'Mon Panier';

// Inclure le header
include_once __DIR__ . '/includes/header.php';
?>

<section class="cart-section">
    <div class="container">
        <h1>Mon Panier</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart['items'])): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <p>Votre panier est tristement vide...</p>
                <?php if (isset($_SESSION['edit_commande_id'])): ?>
                    <a href="/api/panier.php?action=cancel_edit" class="btn-primary btn-cancel-edit">Annuler la modification</a>
                <?php endif; ?>
                <a href="/api/pages/carte.php" class="btn-primary btn-discover">Découvrir la carte</a>
            </div>
        <?php else: ?>
            <form action="/api/panier.php" method="post" class="cart-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="cart-items">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Total</th>
                                <th class="cart-actions-header">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart['items'] as $index => $item): ?>
                                <tr>
                                    <td class="cart-item-info">
                                        <?php if(!empty($item['image'])): ?>
                                            <img src="<?= (strpos($item['image'], '/') === 0) ? htmlspecialchars($item['image']) : '/' . htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="cart-item-image" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none'); this.nextElementSibling.classList.add('d-flex');">
                                            <div class="cart-item-image fallback-img d-none">🍔</div>
                                        <?php else: ?>
                                            <div class="cart-item-image fallback-img d-flex">🍔</div>
                                        <?php endif; ?>
                                        <div class="cart-item-details">
                                            <h3><?= htmlspecialchars($item['nom']) ?></h3>
                                            <?php 
                                            $options_dispos = $item['options_dispos'] ?? '[]';
                                            ?>
                                            <?php if (!empty($item['options']) || $options_dispos !== '[]'): ?>
                                                <p class="cart-item-options-text">
                                                    Options: <?= !empty($item['options']) ? htmlspecialchars(is_array($item['options']) ? implode(', ', $item['options']) : $item['options']) : 'Aucune' ?>
                                                    <?php if ($options_dispos !== '[]'): ?>
                                                        <br><button type="button" onclick='showOptionsModal(<?= $item['plat_id'] ?? $item['id'] ?>, <?= json_encode($item['nom'], JSON_HEX_APOS) ?>, <?= json_encode($options_dispos, JSON_HEX_APOS) ?>, 0, <?= $index ?>, <?= json_encode($item['image'] ?? '', JSON_HEX_APOS) ?>)' class="btn-edit-options"><i class="fas fa-edit"></i> Modifier les choix du menu</button>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            <textarea name="note[<?= $index ?>]" placeholder="Modifications (ex: sans cornichons, changer Coca en Sprite...)" class="cart-note"><?= htmlspecialchars($item['note'] ?? '') ?></textarea>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['prix_unitaire'], 2, ',', ' ') ?> €</td>
                                    <td>
                                        <input type="number" name="quantite[<?= $index ?>]" value="<?= $item['quantite'] ?>" min="1" max="10" class="quantity-input" onchange="document.getElementById('btn-update-cart').click();">
                                    </td>
                                    <td><?= number_format($item['prix_unitaire'] * $item['quantite'], 2, ',', ' ') ?> €</td>
                                    <td class="cart-action-cell">
                                        <a href="/api/panier.php?action=remove&index=<?= $index ?>" class="btn-remove btn-remove-wrapper" title="Retirer ce plat">
                                            <i class="fas fa-trash-alt"></i> Retirer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isLoggedIn()): ?>
                <?php
                $userAddr = [];
                $has_profile_address = false;
                $rue_val = '';
                $cp_val = '';
                $ville_val = '';
                $complement_val = '';
                
                try {
                    $stmtAddr = $pdo->prepare("SELECT rue, complement, code_postal, ville FROM Utilisateurs WHERE id_user = ?");
                    $stmtAddr->execute([$_SESSION['user_id']]);
                    $userAddr = $stmtAddr->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userAddr) {
                        $rue_val = $userAddr['rue'] ?? '';
                        $cp_val = $userAddr['code_postal'] ?? '';
                        $ville_val = $userAddr['ville'] ?? '';
                        $complement_val = $userAddr['complement'] ?? '';
                        
                        if (!empty($rue_val) || !empty($cp_val) || !empty($ville_val)) {
                            $has_profile_address = true;
                        }
                    }
                } catch (Exception $e) {
                    // Ignore
                }
                ?>
                
                <div class="cart-mode-retrait cart-address-box">
                    <h3 class="cart-address-title"><i class="fas fa-shopping-bag" style="color: var(--color-primary);"></i> Mode de retrait</h3>
                    <div style="display: flex; gap: 20px; margin-top: 15px; flex-wrap: wrap;">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="mode_retrait" value="livraison" checked onchange="document.getElementById('adresse-box').style.display='block';">
                            🛵 Livraison
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="mode_retrait" value="click and collect" onchange="document.getElementById('adresse-box').style.display='none';">
                            🛍️ Click & Collect
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="mode_retrait" value="manger sur place for some reason" onchange="document.getElementById('adresse-box').style.display='none';">
                            🍽️ Manger sur place for some reason
                        </label>
                    </div>
                </div>

                <div class="cart-address cart-address-box" id="adresse-box">
                    <h3 class="cart-address-title"><i class="fas fa-map-marker-alt" style="color: var(--color-primary);"></i> Adresse de livraison</h3>
                    
                    <?php if ($has_profile_address): ?>
                        <div id="address-display" class="card-style" style="padding: 15px; margin-bottom: 15px; box-shadow: none; border: 1px solid #ddd;">
                            <p style="margin-top: 0;"><strong>Adresse par défaut :</strong><br>
                                <?= htmlspecialchars($rue_val . (!empty($complement_val) ? ' - ' . $complement_val : '')) ?><br>
                                <?= htmlspecialchars($cp_val . ' ' . $ville_val) ?>
                            </p>
                            <button type="button" class="btn-outline btn-sm" onclick="document.getElementById('address-form').style.display = 'block'; document.getElementById('address-display').style.display = 'none';">Changer l'adresse de livraison</button>
                        </div>
                    <?php endif; ?>

                    <div id="address-form" style="<?= $has_profile_address ? 'display: none;' : '' ?>">
                        <div style="margin-bottom: 15px;" class="autocomplete-container">
                            <label for="adresse_search" style="display:block; margin-bottom: 5px; font-weight: bold; color: var(--color-primary);">Rechercher votre adresse *</label>
                            <input type="text" id="adresse_search" placeholder="Commencez à taper (ex: 3 Fontaines Cergy)..." autocomplete="off" class="cart-address-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <ul id="adresse_results" class="autocomplete-results"></ul>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label for="rue" style="display:block; margin-bottom: 5px;">Rue/Numéro</label>
                            <input type="text" name="rue" id="rue" value="<?= htmlspecialchars($rue_val) ?>" class="cart-address-input readonly-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; background-color: #f5f5f5;" readonly placeholder="Auto-rempli">
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label for="complement" style="display:block; margin-bottom: 5px;">Complément d'adresse (Bâtiment, Étage...)</label>
                            <input type="text" name="complement" id="complement" value="<?= htmlspecialchars($complement_val) ?>" class="cart-address-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;" placeholder="Ex: Bâtiment B, 3ème étage">
                        </div>
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <div style="flex: 1;">
                                <label for="code_postal" style="display:block; margin-bottom: 5px;">Code Postal</label>
                                <input type="text" name="code_postal" id="code_postal" value="<?= htmlspecialchars($cp_val) ?>" class="cart-address-input readonly-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; background-color: #f5f5f5;" readonly placeholder="Auto">
                            </div>
                            <div style="flex: 2;">
                                <label for="ville" style="display:block; margin-bottom: 5px;">Ville</label>
                                <input type="text" name="ville" id="ville" value="<?= htmlspecialchars($ville_val) ?>" class="cart-address-input readonly-input" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; background-color: #f5f5f5;" readonly placeholder="Auto">
                            </div>
                        </div>
                        <?php if (isLoggedIn()): ?>
                        <div style="margin-top: 15px;">
                            <label style="cursor: pointer; display: flex; align-items: flex-start; gap: 10px; font-size: 0.9em; line-height: 1.4;">
                                <input type="checkbox" name="save_address_profile" value="1" style="margin-top: 2px;">
                                <span>Enregistrer l'adresse de livraison comme adresse de livraison par défaut sur le profil ?<br><small style="color: var(--color-primary); font-weight: bold;">(Ceci écrasera toute ancienne adresse attachée au profil)</small></span>
                            </label>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="loyalty-box <?= $tier_class ?>">
                    <h3 class="loyalty-title">🥩 Le Grand Miam Club</h3>
                    <p>Miams disponibles : <strong><?= $miams_dispo ?> Miams</strong> (Rang : <strong class="text-<?= $tier_class ?>"><?= $statut_miams ?></strong>)</p>
                    
                    <?php if ($statut_miams === "SAUCE CHEF" || $statut_miams === "LÉGENDE DU STEAK"): ?>
                        <div class="loyalty-benefit-tier1">
                            <p>🔥 Avantage Rang : Une portion de frites "Sweet Potato" offerte !</p>
                        </div>
                    <?php endif; ?>
                    <?php if ($statut_miams === "LÉGENDE DU STEAK"): ?>
                        <div class="loyalty-benefit-tier2">
                            <p>👑 Avantage Ultime : -10% sur toute la carte & Livraison Prioritaire !</p>
                        </div>
                    <?php endif; ?>

                    <div class="loyalty-shop">
                        <p class="shop-title">Le Shop (Échangez vos Miams) :</p>
                        
                        <div class="shop-items">
                            <!-- Option 150 Miams -->
                            <div class="shop-item <?= $miams_dispo >= 150 ? 'unlocked' : 'locked' ?>">
                                <div><strong>150 Miams</strong> : Une Sauce Maison offerte 🥫</div>
                                <button type="button" class="btn-primary shop-item-btn" 
                                    onclick='showOptionsModal(<?= $id_sauce ?>, "Sauce Maison", "[{&quot;titre&quot;:&quot;Choix&quot;,&quot;choix&quot;:[&quot;Sauce BBQ&quot;,&quot;Sauce Béarnaise&quot;,&quot;Sauce au Poivre&quot;,&quot;Sauce Roquefort&quot;,&quot;Moutarde Ancienne&quot;]}]", 150, "", <?= json_encode($sauce_reward['image'] ?? '', JSON_HEX_APOS) ?>)' 
                                    <?= $miams_dispo < 150 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>
                            
                            <!-- Option 300 Miams -->
                            <div class="shop-item <?= $miams_dispo >= 300 ? 'unlocked' : 'locked' ?>">
                                <div><strong>300 Miams</strong> : Un Soft ou une Bière (25cl) 🍺</div>
                                <button type="button" class="btn-primary shop-item-btn" 
                                    onclick='showOptionsModal(<?= $id_boisson ?>, "Boisson Offerte", "[{&quot;titre&quot;:&quot;Choix&quot;,&quot;choix&quot;:[&quot;Coca-Cola (33cl)&quot;,&quot;Sprite (33cl)&quot;,&quot;Ice Tea (25cl)&quot;,&quot;Bière Blonde (25cl)&quot;,&quot;Bière IPA (25cl)&quot;]}]", 300, "", <?= json_encode($boisson_reward['image'] ?? '', JSON_HEX_APOS) ?>)' 
                                    <?= $miams_dispo < 300 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>

                            <!-- Option 800 Miams -->
                            <div class="shop-item <?= $miams_dispo >= 800 ? 'unlocked' : 'locked' ?>">
                                <div><strong>800 Miams</strong> : Un Dessert au choix 🍪</div>
                                <button type="button" class="btn-primary shop-item-btn" 
                                    onclick='showOptionsModal(<?= $id_dessert ?>, "Dessert Offert", "[{&quot;titre&quot;:&quot;Choix&quot;,&quot;choix&quot;:[&quot;Cookie Skillet&quot;,&quot;Cheesecake NY&quot;,&quot;Brioche Perdue&quot;]}]", 800, "", <?= json_encode($dessert_reward['image'] ?? '', JSON_HEX_APOS) ?>)' 
                                    <?= $miams_dispo < 800 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>
                            
                            <!-- Option 1500 Miams -->
                            <div class="shop-item <?= $miams_dispo >= 1500 ? 'unlocked' : 'locked' ?>">
                                <div><strong>1500 Miams</strong> : Le Burger "Grand Miam" 🍔</div>
                                <button type="button" class="btn-primary shop-item-btn" 
                                    onclick='showOptionsModal(<?= $id_burger ?>, "Burger Grand Miam", "[{&quot;titre&quot;:&quot;Viande&quot;,&quot;choix&quot;:[&quot;Bœuf Limousin&quot;,&quot;Bœuf (Halal)&quot;,&quot;Poulet Croustillant&quot;,&quot;Galette Veggie&quot;]},{&quot;titre&quot;:&quot;Cuisson&quot;,&quot;choix&quot;:[&quot;Saignant&quot;,&quot;À point&quot;,&quot;Bien cuit&quot;]}]", 1500, "", <?= json_encode($burger_reward['image'] ?? '', JSON_HEX_APOS) ?>)' 
                                    <?= $miams_dispo < 1500 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>
                        </div>
                    </div>
                    
                    <p class="loyalty-earn-info">
                        ✨ En réglant cette commande, vous cumulerez <strong><?= floor((float)($cart['total'] ?? 0) * 10) ?> Miams</strong> supplémentaires !
                    </p>
                </div>
                <?php endif; ?>
                
                <div class="cart-summary">
                    <div class="cart-total" style="padding: 20px; background: #f9f9f9; border-radius: 8px;">
                        <h3 class="summary-title" style="margin-top: 0; margin-bottom: 20px; font-size: 1.3em; color: #333;">Récapitulatif</h3>
                        
                        <?php if ($discount > 0): ?>
                            <div class="summary-line" style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95em;">
                                <span>Sous-total</span>
                                <span class="discount-old-price" style="text-decoration: line-through; color: #888;"><?= number_format((float)$subtotal, 2, ',', ' ') ?> €</span>
                            </div>
                            <div class="summary-line" style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95em;">
                                <span>Remise "Légende" (-10%)</span>
                                <span style="color: var(--color-success); font-weight: bold;">-<?= number_format((float)$discount, 2, ',', ' ') ?> €</span>
                            </div>
                            <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">
                        <?php endif; ?>

                        <div class="summary-line" style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 1em;">
                            <span>Total des articles</span>
                            <span id="cart-subtotal-value"><?= number_format((float)($cart['total'] ?? 0), 2, ',', ' ') ?> €</span>
                        </div>

                        <div id="delivery-fee-line" class="summary-line" style="display: none; justify-content: space-between; margin-bottom: 8px; font-size: 1em; color: var(--color-primary);">
                            <span>Frais de livraison</span>
                            <strong id="delivery-fee-value">0,00 €</strong>
                        </div>

                        <hr style="border: 0; border-top: 1px solid #ddd; margin: 15px 0;">

                        <div class="summary-line total-line" style="display: flex; justify-content: space-between; font-size: 1.4em; font-weight: bold; color: #000;">
                            <span>Total à payer</span>
                            <strong id="cart-grand-total"><?= number_format((float)($cart['total'] ?? 0), 2, ',', ' ') ?> €</strong>
                        </div>
                    </div>
                    
                    <div class="cart-actions">
                        <button type="submit" name="action" value="update" id="btn-update-cart" class="btn-update btn-update-cart-action">🔄 Actualiser</button>
                        <?php if (isset($_SESSION['edit_commande_id'])): ?>
                            <a href="/api/panier.php?action=cancel_edit" class="btn-clear">Annuler la modification</a>
                            <?php if ($difference > 0): ?>
                                <button type="submit" name="action" value="save_edit" class="btn-checkout btn-pay-supplement">💳 Payer supplément (<?= number_format((float)$difference, 2, ',', ' ') ?> €)</button>
                            <?php else: ?>
                                <button type="submit" name="action" value="save_edit" class="btn-checkout btn-save-edit">💾 Enregistrer</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/api/panier.php?action=clear" class="btn-clear">Vider le panier</a>
                            <?php if (isLoggedIn()): ?>
                                <button type="submit" name="action" value="checkout" class="btn-checkout btn-pay-order">Payer la commande 💳</button>
                            <?php else: ?>
                                <a href="/api/pages/connexion.php?error=must_login" class="btn-checkout btn-login-pay">Me connecter pour payer 🔒</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('adresse_search');
    const resultsList = document.getElementById('adresse_results');
    const rueInput = document.getElementById('rue');
    const cpInput = document.getElementById('code_postal');
    const villeInput = document.getElementById('ville');
    
    const feeLine = document.getElementById('delivery-fee-line');
    const feeValueSpan = document.getElementById('delivery-fee-value');
    const grandTotalSpan = document.getElementById('cart-grand-total');
    const subtotalSpan = document.getElementById('cart-subtotal-value');
    
    const subtotal = parseFloat(subtotalSpan.textContent.replace(/\s/g, '').replace(',', '.'));
    let lastCalculatedFee = { fee: 0, distance: 0 };

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; 
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function updateSummary(fee, distance) {
        const isDelivery = document.querySelector('input[name="mode_retrait"][value="livraison"]').checked;
        
        if (isDelivery) {
            feeLine.style.display = 'flex';
            feeValueSpan.textContent = fee > 0 ? `${fee.toLocaleString('fr-FR', {minimumFractionDigits: 2})} €` : 'Gratuit';
            if (distance) feeValueSpan.title = `Distance estimée : ${distance.toFixed(1)} km`;
            const newTotal = subtotal + fee;
            grandTotalSpan.textContent = `${newTotal.toLocaleString('fr-FR', {minimumFractionDigits: 2})} €`;
        } else {
            feeLine.style.display = 'none';
            grandTotalSpan.textContent = `${subtotal.toLocaleString('fr-FR', {minimumFractionDigits: 2})} €`;
        }
    }

    if (searchInput) {
        let timeoutId;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value;
            if (query.length < 3) { resultsList.innerHTML = ''; return; }
            timeoutId = setTimeout(async () => {
                const response = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`);
                const data = await response.json();
                resultsList.innerHTML = '';
                data.features.forEach(feature => {
                    const li = document.createElement('li');
                    li.textContent = feature.properties.label;
                    li.style.cssText = "padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;";
                    li.addEventListener('click', () => {
                        searchInput.value = feature.properties.label;
                        rueInput.value = feature.properties.name;
                        cpInput.value = feature.properties.postcode;
                        villeInput.value = feature.properties.city;
                        resultsList.innerHTML = ''; 
                        
                        const distance = calculateDistance(49.0389, 2.0811, feature.geometry.coordinates[1], feature.geometry.coordinates[0]);
                        let fee = 0;
                        if (distance < 5) fee = 0;
                        else if (distance <= 10) fee = 3;
                        else if (distance <= 15) fee = 5;
                        else fee = 10;
                        
                        lastCalculatedFee = { fee, distance };
                        updateSummary(fee, distance);
                    });
                    resultsList.appendChild(li);
                });
            }, 300);
        });
        document.addEventListener('click', (e) => { if (e.target !== searchInput) resultsList.innerHTML = ''; });
    }
    
    document.querySelectorAll('input[name="mode_retrait"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'livraison') {
                updateSummary(lastCalculatedFee.fee, lastCalculatedFee.distance);
            } else {
                updateSummary(0, null);
            }
        });
    });

    // Trigger initial fee calculation if a default address is present on page load
    const hasDefaultAddress = <?= $has_profile_address ? 'true' : 'false' ?>;
    const isDeliverySelectedOnLoad = document.querySelector('input[name="mode_retrait"][value="livraison"]:checked');

    if (hasDefaultAddress && isDeliverySelectedOnLoad) {
        const defaultAddress = "<?= addslashes(trim($rue_val . ' ' . $cp_val . ' ' . $ville_val)) ?>";
        
        if (defaultAddress) {
            (async () => {
                try {
                    const response = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(defaultAddress)}&limit=1`);
                    if (!response.ok) return;
                    const data = await response.json();

                    if (data.features && data.features.length > 0) {
                        const feature = data.features[0];
                        const distance = calculateDistance(49.0389, 2.0811, feature.geometry.coordinates[1], feature.geometry.coordinates[0]);
                        let fee = (distance < 5) ? 0 : (distance <= 10) ? 3 : (distance <= 15) ? 5 : 10;
                        
                        lastCalculatedFee = { fee, distance };
                        updateSummary(fee, distance);
                    }
                } catch (error) {
                    console.error("Erreur API Adresse au chargement:", error);
                }
            })();
        }
    }
});
</script>

<?php
// Inclure le footer
include_once __DIR__ . '/includes/footer.php';
?>
