/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.2.2-MariaDB, for Linux (x86_64)
--
-- Host: yumlandbase-yumland.l.aivencloud.com    Database: defaultdb
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `Avis`
--

DROP TABLE IF EXISTS `Avis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Avis` (
  `id_avis` int NOT NULL AUTO_INCREMENT,
  `id_commande` int NOT NULL,
  `id_client` int NOT NULL,
  `note_globale` int DEFAULT NULL,
  `note_livreur` int NOT NULL,
  `note_nourriture` int NOT NULL,
  `commentaire` text,
  `date_avis` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_avis`),
  UNIQUE KEY `unique_commande` (`id_commande`),
  CONSTRAINT `Avis_Commandes_FK` FOREIGN KEY (`id_commande`) REFERENCES `Commandes` (`id_commande`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Commandes`
--

DROP TABLE IF EXISTS `Commandes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Commandes` (
  `id_commande` int NOT NULL AUTO_INCREMENT,
  `id_client` int DEFAULT NULL,
  `id_livreur` int DEFAULT NULL,
  `date_commande` datetime DEFAULT CURRENT_TIMESTAMP,
  `prix_total` float DEFAULT NULL,
  `statut` varchar(50) DEFAULT 'En attente',
  `paiement_statut` varchar(50) DEFAULT 'Non payé',
  `cybank_transaction` varchar(255) DEFAULT NULL,
  `mode_retrait` varchar(50) DEFAULT 'livraison',
  `adresse_livraison` text,
  PRIMARY KEY (`id_commande`),
  KEY `id_client` (`id_client`),
  KEY `id_livreur` (`id_livreur`),
  CONSTRAINT `Commandes_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `Utilisateurs` (`id_user`),
  CONSTRAINT `Commandes_ibfk_2` FOREIGN KEY (`id_livreur`) REFERENCES `Utilisateurs` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Contenu_Commandes`
--

DROP TABLE IF EXISTS `Contenu_Commandes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Contenu_Commandes` (
  `id_commande` int NOT NULL,
  `id_produit` int NOT NULL,
  `quantite` int DEFAULT NULL,
  `prix_unitaire` float DEFAULT NULL,
  `options_choisies` text,
  PRIMARY KEY (`id_commande`,`id_produit`),
  KEY `id_produit` (`id_produit`),
  CONSTRAINT `Contenu_Commandes_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `Commandes` (`id_commande`),
  CONSTRAINT `Contenu_Commandes_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `Produits` (`id_produit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Coupons`
--

DROP TABLE IF EXISTS `Coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Coupons` (
  `id_coupon` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `remise_pourcentage` int DEFAULT '0',
  `remise_fixe` float DEFAULT '0',
  PRIMARY KEY (`id_coupon`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Paiements`
--

DROP TABLE IF EXISTS `Paiements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Paiements` (
  `id_paiement` int NOT NULL AUTO_INCREMENT,
  `id_commande` int DEFAULT NULL,
  `id_client` int DEFAULT NULL,
  `montant` float NOT NULL,
  `date_transaction` datetime DEFAULT CURRENT_TIMESTAMP,
  `cybank_transaction_id` varchar(255) DEFAULT NULL,
  `carte_masquee` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_paiement`),
  KEY `id_commande` (`id_commande`),
  KEY `id_client` (`id_client`),
  CONSTRAINT `Paiements_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `Commandes` (`id_commande`),
  CONSTRAINT `Paiements_ibfk_2` FOREIGN KEY (`id_client`) REFERENCES `Utilisateurs` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Produits`
--

DROP TABLE IF EXISTS `Produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Produits` (
  `id_produit` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `prix` float NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text,
  `options_config` text,
  PRIMARY KEY (`id_produit`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Utilisateurs`
--

DROP TABLE IF EXISTS `Utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Utilisateurs` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `rue` varchar(255) DEFAULT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `complement` varchar(255) DEFAULT NULL,
  `solde_miams` int DEFAULT '0',
  `total_miams_historique` int DEFAULT '0',
  `statut` varchar(20) NOT NULL DEFAULT 'Actif',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-05-12 13:04:29
