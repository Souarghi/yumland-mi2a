<?php
/**
 * Fonctions d'accès aux données — Table Avis
 * À inclure après config.php (qui fournit $pdo)
 */

/**
 * Récupère tous les avis publiés avec infos client et commande.
 * Utilisé par : api/pages/avis.php
 *
 * @return array Liste des avis ou tableau vide si la table n'existe pas.
 */
function getAllAvis(): array {
    global $pdo;
    try {
        $tableExists = $pdo->query("SHOW TABLES LIKE 'Avis'")->rowCount() > 0;
        if (!$tableExists) return [];

        $stmt = $pdo->query("
            SELECT a.*, COALESCE(u.nom, 'Ancien') AS nom, COALESCE(u.prenom, 'Client') AS prenom, c.date_commande,
            (SELECT GROUP_CONCAT(CONCAT(cc.quantite, 'x ', p.nom) SEPARATOR ', ')
             FROM Contenu_Commandes cc
             JOIN Produits p ON cc.id_produit = p.id_produit
             WHERE cc.id_commande = a.id_commande) AS plats_commandes
            FROM Avis a
            LEFT JOIN Utilisateurs u ON a.id_client = u.id_user
            LEFT JOIN Commandes c ON a.id_commande = c.id_commande
            ORDER BY a.date_avis DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Récupère la commande livrée d'un client (avec ses plats) pour affichage
 * dans le formulaire de notation.
 * Utilisé par : api/client/noter.php
 *
 * @param int $commande_id  Identifiant de la commande.
 * @param int $user_id      Identifiant du client connecté.
 * @return array|false      Données de la commande, ou false si non trouvée.
 */
function getCommandeForNoting(int $commande_id, int $user_id): array|false {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*,
        (SELECT GROUP_CONCAT(CONCAT(cc.quantite, 'x ', p.nom) SEPARATOR ', ')
         FROM Contenu_Commandes cc
         JOIN Produits p ON cc.id_produit = p.id_produit
         WHERE cc.id_commande = c.id_commande) AS plats_commandes
        FROM Commandes c
        WHERE id_commande = ? AND id_client = ? AND statut = 'Livrée'
    ");
    $stmt->execute([$commande_id, $user_id]);
    return $stmt->fetch();
}

/**
 * Enregistre ou met à jour un avis pour une commande donnée.
 * Crée la table Avis si elle n'existe pas encore.
 * Utilisé par : api/client/noter.php
 *
 * @param int    $commande_id
 * @param int    $user_id
 * @param int    $delivery_note   Note livreur (1-5).
 * @param int    $food_note       Note nourriture (1-5).
 * @param string $commentaire
 * @return bool  true si succès.
 */
function saveAvis(int $commande_id, int $user_id, int $delivery_note, int $food_note, string $commentaire): bool {
    global $pdo;

    $note_globale = (int) round(($delivery_note + $food_note) / 2);

    // Création de la table si absente (migration sécurisée)
    $tableExists = $pdo->query("SHOW TABLES LIKE 'Avis'")->rowCount() > 0;
    if (!$tableExists) {
        $pdo->exec("CREATE TABLE Avis (
            id_avis INT AUTO_INCREMENT PRIMARY KEY,
            id_commande INT NOT NULL,
            id_client INT NOT NULL,
            note_globale INT,
            note_livreur INT,
            note_nourriture INT,
            commentaire TEXT,
            date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_commande (id_commande)
        )");
        $pdo->exec("DROP TABLE IF EXISTS Evaluations");
    } else {
        try {
            $pdo->exec("ALTER TABLE Avis ADD COLUMN note_globale INT AFTER id_client");
        } catch (Exception $e) {
            // La colonne existe déjà — on ignore
        }
        $pdo->exec("DROP TABLE IF EXISTS Evaluations");
    }
    
    // S'assurer que id_client peut être NULL pour la suppression de compte
    try {
        $pdo->exec("ALTER TABLE Avis MODIFY id_client INT NULL");
    } catch (Exception $e) {}

    $stmt = $pdo->prepare("
        INSERT INTO Avis (id_commande, id_client, note_globale, note_livreur, note_nourriture, commentaire)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            note_globale = ?, note_livreur = ?, note_nourriture = ?, commentaire = ?
    ");
    return $stmt->execute([
        $commande_id, $user_id, $note_globale, $delivery_note, $food_note, $commentaire,
        $note_globale, $delivery_note, $food_note, $commentaire
    ]);
}

/**
 * Met à jour le profil d'un utilisateur.
 * Utilisé par : api/client/profil.php
 *
 * @param int    $user_id     Identifiant de l'utilisateur.
 * @param array  $data        Associatif : nom, prenom, tel, rue, code_postal, ville, complement.
 * @return bool  true si succès.
 */
function updateUserProfil(int $user_id, array $data): bool {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE Utilisateurs
        SET nom = ?, prenom = ?, tel = ?, rue = ?, code_postal = ?, ville = ?, complement = ?, pin = ?
        WHERE id_user = ?
    ");
    return $stmt->execute([
        $data['nom'],
        $data['prenom'],
        $data['tel'],
        $data['rue'],
        $data['code_postal'],
        $data['ville'],
        $data['complement'],
        $data['pin'] ?? null,
        $user_id
    ]);
}
