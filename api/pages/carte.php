<?php
$currentPage = 'carte';
$pageTitle = 'Notre Carte';
$additionalCss = ['/css/carte.css?v=' . time()];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/config.php';
// require_once __DIR__ . '/../includes/plats.php'; // Plus besoin si tout est dans la DB !
// require_once __DIR__ . '/../includes/panier.php';

// Message pour l'ajout au panier
$message = '';
if (isset($_SESSION['cart_message'])) {
    $message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

// Paramètres du fetch asynchrone (Phase 3)
$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] == '1';
$filter_cat = $_GET['category'] ?? 'all';
$filter_search = strtolower($_GET['search'] ?? '');
$filter_spec = $_GET['spec'] ?? 'all';

// 1. RÉCUPÉRATION DYNAMIQUE DE TOUS LES PRODUITS
$stmt = $pdo->query("SELECT * FROM Produits ORDER BY id_produit ASC");
$tous_les_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. INITIALISATION DU CATALOGUE
$catalogue = [
    'Entrées' => [],
    'Viandes' => [],
    'Burgers' => [],
    'Desserts' => [],
    'Boissons' => [],
    'Menus' => []
];

// 3. GESTION DES SPÉCIFICITÉS (Vu qu'elles ne sont pas dans la DB, on les associe par l'ID)
$specificites = [
    1  => ['type' => 'vege', 'html' => '<span class="spec-badge spec-vege"><i class="fas fa-leaf"></i> Végétarien</span>'],
    2  => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    3  => ['type' => 'porc', 'html' => '<span class="spec-badge spec-porc"><i class="fas fa-bacon"></i> Contient Porc</span>'],
    4  => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    5  => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    6  => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    7  => ['type' => 'porc', 'html' => '<span class="spec-badge spec-porc"><i class="fas fa-bacon"></i> Contient Porc</span>'],
    8  => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    9  => ['type' => 'poisson', 'html' => '<span class="spec-badge spec-poisson"><i class="fas fa-fish"></i> Poisson</span>'],
    10 => ['type' => 'porc', 'html' => '<span class="spec-badge spec-porc"><i class="fas fa-bacon"></i> Contient Porc</span>'],
    11 => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    12 => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    13 => ['type' => 'porc', 'html' => '<span class="spec-badge spec-porc"><i class="fas fa-bacon"></i> Contient Porc (Lardons)</span>'],
    14 => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    15 => ['type' => 'vege', 'html' => '<span class="spec-badge spec-vege"><i class="fas fa-leaf"></i> Végétarien</span>'],
];

// 4. FILTRAGE CÔTÉ SERVEUR (Pour validation Phase 3)
foreach ($tous_les_produits as $produit) {
    $id = $produit['id_produit'];
    $match_search = empty($filter_search) || str_contains(strtolower($produit['nom']), $filter_search) || str_contains(strtolower($produit['description']), $filter_search);
    $match_cat = ($filter_cat === 'all' || $produit['categorie'] === $filter_cat);
    $specType = isset($specificites[$id]) ? $specificites[$id]['type'] : '';
    $match_spec = ($filter_spec === 'all' || $specType === $filter_spec);

    if ($match_search && $match_cat && $match_spec) {
        $cat = $produit['categorie'];
        if (isset($catalogue[$cat])) {
            $catalogue[$cat][] = $produit;
        }
    }
}

// Fonction utilitaire pour trouver un produit spécifique (utile pour les Menus)
function getProduitById($id, $produits) {
    foreach ($produits as $p) {
        if ($p['id_produit'] == $id) return $p;
    }
    return null;
}

function normalizeProductImagePath(?string $imagePath): string {
    $imagePath = trim((string) $imagePath);
    if ($imagePath === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $imagePath) || str_starts_with($imagePath, '/')) {
        return $imagePath;
    }

    return '/' . ltrim($imagePath, '/');
}

// Générer le token pour sécuriser l'ajout au panier en AJAX
$csrf_token = generateCSRFToken();
?>

<script defer>
    // --- FONCTION POUR OUVRIR LA MODAL MENU ---
    function openMenuModal(btn) {
        const id = btn.getAttribute('data-id');
        const nom = btn.getAttribute('data-nom');
        const options = btn.getAttribute('data-options');
        const image = btn.getAttribute('data-image') || '';
        showOptionsModal(id, nom, options, 0, '', image);
    }

    // --- FONCTION AJOUTER AU PANIER EN AJAX ---
    function ajouterAuPanier(id_produit) {
        const formData = new FormData();
        formData.append('id_produit', id_produit);
        formData.append('quantite', 1);
        formData.append('csrf_token', '<?= $csrf_token ?>');

        fetch('/api/ajouter_panier.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.count;
                } else {
                    const cartIcon = document.querySelector('.cart-icon');
                    if (cartIcon) {
                        cartIcon.innerHTML = '🛒 <span class="cart-count">' + data.count + '</span>';
                    }
                }
                alert("😋 Plat ajouté à votre panier avec succès !");
            } else {
                alert("Erreur lors de l'ajout au panier.");
            }
        })
        .catch(err => console.error(err));
    }

    // --- FONCTION POUR LE MENU MYSTÈRE ---
    function ajouterMenuMystere(id_produit, imagePath = '') {
        const restriction = document.getElementById('mystere-restriction')
            ? document.getElementById('mystere-restriction').value
            : 'none';

        const getItems = (cat) => {
            const table = document.querySelector(`.menu-table[data-category="${cat}"]`);
            if (!table) return [];
            return Array.from(table.querySelectorAll('tbody tr')).map(row => {
                const td = row.querySelector('td:first-child');
                const nom = td ? td.textContent.trim() : '';
                const btn = row.querySelector('button');
                const options = btn ? btn.getAttribute('data-options') : '';
                const specCell = row.querySelector('td[data-spec]');
                const spec = specCell ? specCell.getAttribute('data-spec') : '';
                return { nom, options, spec };
            }).filter(item => {
                if (item.nom === '') return false;
                if (restriction === 'none') return true;
                if (restriction === 'vege') {
                    if (cat === 'Entrées' || cat === 'Viandes' || cat === 'Burgers')
                        return item.spec === 'vege';
                    return true;
                }
                if (restriction === 'halal') {
                    if (cat === 'Entrées' || cat === 'Viandes' || cat === 'Burgers')
                        return item.spec === 'halal' || item.spec === 'vege' || item.spec === 'poisson';
                    return true;
                }
                if (restriction === 'sans-porc') {
                    return item.spec !== 'porc';
                }
                return true;
            });
        };

        const entrees  = getItems('Entrées');
        const viandes  = getItems('Viandes');
        const burgers  = getItems('Burgers');
        const desserts = getItems('Desserts');
        const boissons = getItems('Boissons');

        const plats = viandes.concat(burgers);

        if (entrees.length === 0 || plats.length === 0 || desserts.length === 0 || boissons.length === 0) {
            alert("Erreur : la carte ne contient pas assez d'options pour cette restriction alimentaire.");
            return;
        }

        const random = (arr) => arr[Math.floor(Math.random() * arr.length)];

        const e = random(entrees);
        const p = random(plats);
        const d = random(desserts);
        const b = random(boissons);

        // Utilitaire : parse les options JSON d'un item
        const parseOptions = (itemOptionsStr) => {
            if (!itemOptionsStr) return [];
            try { return JSON.parse(itemOptionsStr); } catch (err) { return []; }
        };

        // Construction de modalOptions :
        // Pour chaque item tiré :
        //   1. Un groupe "titre" = "🎲 Entrée tirée : [nom]" avec un seul choix (l'item lui-même)
        //   2. Suivi de ses options propres (cuisson, sauce, etc.) si elles existent
        const modalOptions = [];

        const itemsDrawn = [
            { label: '🥗 Entrée tirée',  item: e },
            { label: '🍖 Plat tiré',     item: p },
            { label: '🍰 Dessert tiré',  item: d },
            { label: '🥤 Boisson tirée', item: b },
        ];

        itemsDrawn.forEach(({ label, item }) => {
            // 1. L'item lui-même — affiché comme un choix unique (pré-sélectionné, non modifiable)
            modalOptions.push({
                titre: label,
                choix: [item.nom],
                locked: true   // flag custom pour la modal : choix unique, non modifiable
            });

            // 2. Les options propres à cet item (ex: Cuisson, Sauce, Viande…)
            const opts = parseOptions(item.options);
            opts.forEach(opt => {
                // On préfixe le titre de l'option pour savoir à quel item elle appartient
                modalOptions.push({
                    ...opt,
                    titre: `↳ ${opt.titre}`   // indentation visuelle dans la modal
                });
            });
        });

        // Afficher la modale
        if (typeof showOptionsModal === 'function') {
            showOptionsModal(id_produit, "🎲 Votre Tirage Menu Mystère", JSON.stringify(modalOptions), 0, '', imagePath);
        } else {
            alert("Erreur: la fonction de modale n'est pas disponible.");
        }
    }

    function initMenuHoverPreview() {
        const preview = document.getElementById('menuHoverPreview');
        const previewImage = document.getElementById('menuHoverPreviewImage');
        const previewLabel = document.getElementById('menuHoverPreviewLabel');

        if (!preview || !previewImage || !previewLabel || window.matchMedia('(hover: none)').matches) {
            return;
        }

        const movePreview = (event) => {
            const offset = 24;
            const previewWidth = preview.offsetWidth || 220;
            const previewHeight = preview.offsetHeight || 260;
            const left = Math.min(event.clientX + offset, window.innerWidth - previewWidth - 16);
            const top = Math.min(Math.max(16, event.clientY - previewHeight / 2), window.innerHeight - previewHeight - 16);

            preview.style.left = `${left}px`;
            preview.style.top = `${top}px`;
        };

        document.querySelectorAll('.menu-item-row[data-image]').forEach((row) => {
            const image = row.getAttribute('data-image');
            const name = row.getAttribute('data-name') || '';

            if (!image) {
                return;
            }

            row.addEventListener('mouseenter', (event) => {
                previewImage.src = image;
                previewImage.alt = name;
                previewLabel.textContent = name;
                preview.classList.add('is-visible');
                movePreview(event);
            });

            row.addEventListener('mousemove', movePreview);
            row.addEventListener('mouseleave', () => {
                preview.classList.remove('is-visible');
            });
        });

        window.addEventListener('scroll', () => {
            preview.classList.remove('is-visible');
        }, { passive: true });
    }

    // --- FONCTION DE FILTRE DE RECHERCHE ---
    function applyFilters() {
        const searchQuery = document.getElementById('searchInput').value.toLowerCase();
        const selectedCategory = document.getElementById('categoryFilter').value;
        const selectedSpec = document.getElementById('specFilter').value;
        
        document.querySelectorAll('.menu-table').forEach(table => {
            const tableCategory = table.getAttribute('data-category');
            let tableHasVisibleRows = false;
            
            table.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                const specCell = row.querySelector('td[data-spec]');
                const spec = specCell ? specCell.getAttribute('data-spec') : null;
                
                const searchMatch = text.includes(searchQuery);
                const specMatch = (selectedSpec === 'all' || spec === selectedSpec);
                
                if (searchMatch && specMatch) {
                    row.style.display = '';
                    if (selectedCategory === 'all' || selectedCategory === tableCategory) {
                        tableHasVisibleRows = true;
                    }
                } else {
                    row.style.display = 'none';
                }
            });
            
            if (selectedCategory === 'all') {
                table.style.display = tableHasVisibleRows ? '' : 'none';
            } else {
                table.style.display = (selectedCategory === tableCategory && tableHasVisibleRows) ? '' : 'none';
            }
        });
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        applyFilters();
        initMenuHoverPreview();
        document.querySelectorAll('.menu-table td').forEach(td => {
            if (td.textContent.includes('€') && !td.querySelector('button')) {
                td.classList.add('price-cell');
            }
        });
    });
</script>

<div class="menu-container">
    <h1>Notre Carte</h1>
    <p class="intro-text">Steakhouse, Grillades & Burgers XXL - Une expérience culinaire unique</p>
    
    <!-- Barre de recherche et filtre -->
    <div class="search-filter">
        <input type="text" id="searchInput" placeholder="Rechercher un plat…" onkeyup="applyFilters()" class="filter-input">
        <select id="categoryFilter" onchange="applyFilters()" class="filter-input">
            <option value="all">Toutes catégories</option>
            <option value="Entrées">Entrées</option>
            <option value="Viandes">Viandes & Poissons</option>
            <option value="Burgers">Burgers</option>
            <option value="Desserts">Desserts</option>
            <option value="Boissons">Boissons</option>
        </select>
        <select id="specFilter" onchange="applyFilters()" class="filter-input">
            <option value="all">Toutes spécificités</option>
            <option value="halal">Halal</option>
            <option value="porc">Contient du porc</option>
            <option value="vege">Végétarien</option>
            <option value="poisson">Poisson</option>
        </select>
    </div>
    
    <div id="menus-wrapper">
    <?php
    // Si c'est un appel asynchrone, on capture uniquement le HTML à partir d'ici
    if ($is_ajax) { ob_start(); }
    ?>

    <?php
    // Configuration des tableaux pour la boucle dynamique
    $sections = [
        'Entrées' => ['icon' => 'fa-seedling', 'titre' => 'Les Entrées & Partage'],
        'Viandes' => ['icon' => 'fa-fire', 'titre' => 'Le Grill (Viandes & Poissons)'],
        'Burgers' => ['icon' => 'fa-hamburger', 'titre' => 'Les Burgers'],
        'Desserts' => ['icon' => 'fa-ice-cream', 'titre' => 'Desserts (Sweet Ending)'],
        'Boissons' => ['icon' => 'fa-glass-cheers', 'titre' => 'Les Boissons']
    ];

    foreach ($sections as $catKey => $sectionInfo): 
        if (empty($catalogue[$catKey])) continue; // On ignore si la catégorie est vide
    ?>
        <table class="menu-table" data-category="<?= $catKey ?>">
            <caption><i class="fas <?= $sectionInfo['icon'] ?>"></i> <?= $sectionInfo['titre'] ?></caption>
            <thead>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Prix</th>
                <th>Spécificités</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($catalogue[$catKey] as $produit): 
                $id = $produit['id_produit'];
                $nom = htmlspecialchars($produit['nom'], ENT_QUOTES);
                $desc = htmlspecialchars($produit['description']);
                $imageUrl = normalizeProductImagePath($produit['image_url'] ?? '');
                
                $prix_affiche = number_format($produit['prix'], 2, '.', '') . ' €';
                $options = !empty($produit['options_config']) ? htmlspecialchars($produit['options_config'], ENT_QUOTES) : '';
                
                // Correction de l'affichage pour les boissons afin d'éviter les duplications et d'améliorer la lisibilité.
                if ($catKey === 'Boissons' && $options) {
                    $opts_array = json_decode($produit['options_config'], true);
                    $all_options_parts = [];
                    $is_main_choice = false;      // Flag pour savoir si c'est un choix de produit (ex: parfums de soda)
                    $has_quantity_option = false; // Flag pour savoir si une option de format/quantité existe

                    if (is_array($opts_array)) {
                        foreach ($opts_array as $opt) {
                            if (isset($opt['titre'])) {
                                if ($opt['titre'] === 'Choix') {
                                    $is_main_choice = true;
                                }
                                if (in_array($opt['titre'], ['Format', 'Quantité'])) {
                                    $has_quantity_option = true;
                                }
                                if (in_array($opt['titre'], ['Format', 'Quantité', 'Type', 'Choix', 'Parfum', 'Marque'])) {
                                    if (isset($opt['choix']) && !empty($opt['choix'])) {
                                        $all_options_parts[] = htmlspecialchars(implode(' / ', $opt['choix']));
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($all_options_parts)) {
                        if ($has_quantity_option) $prix_affiche = 'Dès ' . $prix_affiche;
                        // On ne remplace la description que pour les produits où l'option est un "Choix" (ex: Sodas),
                        // pour les autres (bières, vins...), on garde la description originale pour ne pas perdre d'info.
                        if ($is_main_choice) {
                            $desc = implode('<br>', $all_options_parts);
                        }
                    }
                }
                // Gestion du badge de spécificité
                $specType = isset($specificites[$id]) ? $specificites[$id]['type'] : '';
                $specHtml = isset($specificites[$id]) ? $specificites[$id]['html'] : '';
            ?>
                <tr class="menu-item-row" data-image="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>" data-name="<?= $nom ?>">
                    <td><?= $nom ?></td>
                    <td><?= $desc ?></td>
                    <td class="price-cell"><?= $prix_affiche ?></td>
                    <td data-spec="<?= $specType ?>"><?= $specHtml ?></td>
                    <td>
                        <?php if ($options): ?>
                            <button class="btn-primary" data-id="<?= $id ?>" data-nom="<?= $nom ?>" data-options='<?= $options ?>' data-image="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>" onclick="openMenuModal(this)"><i class="fas fa-cart-plus"></i> Ajouter</button>
                        <?php else: ?>
                            <button class="btn-primary" onclick="ajouterAuPanier(<?= $id ?>)"><i class="fas fa-cart-plus"></i> Ajouter</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
    
    <!-- MENUS & FORMULES (Traités séparément pour garder la belle mise en page HTML) -->
    <?php
    // Helper function to generate menu description from options
    function buildMenuDescriptionFromOptions($options_config) {
        $options = json_decode($options_config, true);
        $desc_parts = [];
        if (is_array($options)) {
            foreach ($options as $opt) {
                // On affiche seulement les choix principaux, pas les sous-options conditionnelles
                if (isset($opt['titre']) && !isset($opt['condition']) && isset($opt['choix']) && !empty($opt['choix'])) {
                    if (stripos($opt['titre'], 'sauce') !== false) continue;
                    $desc_parts[] = '<strong>' . htmlspecialchars(ucfirst($opt['titre'])) . ' au choix :</strong> ' . htmlspecialchars(implode(' / ', $opt['choix']));
                }
            }
        }
        return implode('<br>', $desc_parts);
    }
    ?>
    <h2><i class="fas fa-concierge-bell"></i> Nos Menus & Formules</h2>
    
    <?php 
    // Récupération dynamique des menus pour injecter les données de la base dans le design
    $menuLunch = getProduitById(42, $tous_les_produits);
    $menuCowboy = getProduitById(43, $tous_les_produits);
    $menuGrill = getProduitById(44, $tous_les_produits);
    
    // Récupération du Menu Mystère
    $menuMystere = null;
    foreach($tous_les_produits as $p) {
        if ($p['nom'] === 'Menu Mystère') {
            $menuMystere = $p;
            break;
        }
    }
    ?>

    <?php if ($menuLunch): ?>
    <?php $menuLunchImage = normalizeProductImagePath($menuLunch['image_url'] ?? ''); ?>
    <div class="menu-formule">
        <h3><i class="fas fa-briefcase"></i> Formule "<?= htmlspecialchars($menuLunch['nom']) ?>" - <?= number_format($menuLunch['prix'], 2) ?> €</h3>
        <p class="formule-details"><em>Disponible uniquement le midi, du lundi au vendredi</em></p>
        <div class="menu-description-db">
            <?= buildMenuDescriptionFromOptions($menuLunch['options_config']) ?>
        </div>
        <button class="btn-primary"
                data-id="<?= $menuLunch['id_produit'] ?>" 
                data-nom="<?= htmlspecialchars($menuLunch['nom'], ENT_QUOTES) ?>" 
                data-options='<?= htmlspecialchars($menuLunch['options_config'], ENT_QUOTES) ?>'
                data-image="<?= htmlspecialchars($menuLunchImage, ENT_QUOTES) ?>"
                onclick="openMenuModal(this)"><i class="fas fa-cart-plus"></i> Ajouter</button>
    </div>
    <?php endif; ?>
    
    <?php if ($menuCowboy): ?>
    <?php $menuCowboyImage = normalizeProductImagePath($menuCowboy['image_url'] ?? ''); ?>
    <div class="menu-formule">
        <h3><i class="fas fa-hat-cowboy"></i> Menu "<?= htmlspecialchars($menuCowboy['nom']) ?>" - <?= number_format($menuCowboy['prix'], 2) ?> €</h3>
        <p class="formule-details"><em>Pour les enfants de moins de 12 ans</em></p>
        <div class="menu-description-db">
            <?= buildMenuDescriptionFromOptions($menuCowboy['options_config']) ?>
        </div>
        <button class="btn-primary"
                data-id="<?= $menuCowboy['id_produit'] ?>" 
                data-nom="<?= htmlspecialchars($menuCowboy['nom'], ENT_QUOTES) ?>" 
                data-options='<?= htmlspecialchars($menuCowboy['options_config'], ENT_QUOTES) ?>'
                data-image="<?= htmlspecialchars($menuCowboyImage, ENT_QUOTES) ?>"
                onclick="openMenuModal(this)"><i class="fas fa-cart-plus"></i> Ajouter</button>
    </div>
    <?php endif; ?>
    
    <?php if ($menuGrill): ?>
    <?php $menuGrillImage = normalizeProductImagePath($menuGrill['image_url'] ?? ''); ?>
    <div class="menu-formule">
        <h3><i class="fas fa-fire-alt"></i> Menu "<?= htmlspecialchars($menuGrill['nom']) ?>" - <?= number_format($menuGrill['prix'], 2) ?> €</h3>
        <p class="formule-details"><em>Menu complet disponible le soir et le week-end</em></p>
        <div class="menu-description-db">
            <?= buildMenuDescriptionFromOptions($menuGrill['options_config']) ?>
        </div>
        <button class="btn-primary"
                data-id="<?= $menuGrill['id_produit'] ?>" 
                data-nom="<?= htmlspecialchars($menuGrill['nom'], ENT_QUOTES) ?>" 
                data-options='<?= htmlspecialchars($menuGrill['options_config'], ENT_QUOTES) ?>'
                data-image="<?= htmlspecialchars($menuGrillImage, ENT_QUOTES) ?>"
                onclick="openMenuModal(this)"><i class="fas fa-cart-plus"></i> Ajouter</button>
    </div>
    <?php endif; ?>

    <?php if ($menuMystere): ?>
    <?php $menuMystereImage = normalizeProductImagePath($menuMystere['image_url'] ?? ''); ?>
    <div class="menu-formule menu-formule-mystere">
        <h3><i class="fas fa-gift"></i> <?= htmlspecialchars($menuMystere['nom']) ?> - <?= number_format($menuMystere['prix'], 2) ?> €</h3>
        <p class="formule-details"><em>Laissez-vous surprendre ! Une entrée, un plat, un dessert et une boisson sélectionnés au hasard par notre Chef.</em></p>
        <div class="menu-description-db">
            <?= nl2br(htmlspecialchars($menuMystere['description'])) ?>
        </div>
        <div class="mystere-options">
            <label for="mystere-restriction"><i class="fas fa-exclamation-circle"></i> Restrictions alimentaires :</label>
            <select id="mystere-restriction">
                <option value="none">Aucune</option>
                <option value="vege">Végétarien</option>
                <option value="halal">Halal</option>
                <option value="sans-porc">Sans Porc</option>
            </select>
        </div>
        <button class="btn-primary btn-mystere" onclick='ajouterMenuMystere(<?= $menuMystere["id_produit"] ?>, <?= json_encode($menuMystereImage, JSON_HEX_APOS) ?>)'><i class="fas fa-magic"></i> Tirer au sort & Ajouter</button>
    </div>
    <?php endif; ?>

    <?php
    // Si c'est un appel asynchrone (Fetch), on renvoie le HTML généré et on stoppe le chargement
    if ($is_ajax) {
        $html_response = ob_get_clean();
        echo $html_response;
        exit;
    }
    ?>
    </div>
</div>

<div id="menuHoverPreview" class="menu-hover-preview" aria-hidden="true">
    <img id="menuHoverPreviewImage" src="" alt="" class="menu-hover-preview-image">
    <div id="menuHoverPreviewLabel" class="menu-hover-preview-label"></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
