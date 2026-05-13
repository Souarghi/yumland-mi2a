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
-- Dumping data for table `Avis`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `Avis` WRITE;
/*!40000 ALTER TABLE `Avis` DISABLE KEYS */;
INSERT INTO `Avis` VALUES
(1,24,25,NULL,5,5,'','2026-04-05 14:55:45'),
(2,33,35,NULL,4,4,'Je ne sais plus ce que j\'ai commandé alors j\'ai mis une note au pif','2026-04-21 12:00:39'),
(3,46,25,4,4,3,'hey','2026-05-05 10:49:30'),
(4,48,25,5,5,5,'','2026-05-06 10:55:20');
/*!40000 ALTER TABLE `Avis` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping data for table `Commandes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `Commandes` WRITE;
/*!40000 ALTER TABLE `Commandes` DISABLE KEYS */;
INSERT INTO `Commandes` VALUES
(8,25,33,'2026-03-25 11:08:40',6.9,'Livrée','Payé','MI2A00000008','livraison','12 rue des Lilas, 95000 Cergy'),
(9,25,33,'2026-03-25 11:20:36',19.5,'Livrée','Payé','MI2A00000009','livraison','12 rue des Lilas, 95000 Cergy'),
(10,34,NULL,'2026-03-25 11:21:31',49.4,'Annulée','Échec',NULL,'livraison','38 QUATER Boulevard Du Port\r\nRésidence Good Morning Cergy II'),
(11,34,33,'2026-03-25 11:22:00',49.4,'Livrée','Payé','MI2A00000011','livraison','38 QUATER Boulevard Du Port\r\nRésidence Good Morning Cergy II'),
(12,25,33,'2026-03-25 11:51:53',32,'Livrée','Payé','MI2A00000012','livraison','12 rue des Lilas, 95000 Cergy'),
(13,25,33,'2026-03-25 18:10:18',15.5,'Livrée','Payé','MI2A00000013','livraison','12 rue des Lilas, 95000 Cergy'),
(14,25,33,'2026-03-25 18:41:07',22.5,'Livrée','En cours de paiement',NULL,'livraison','12 rue des Lilas, 95000 Cergy'),
(15,34,33,'2026-04-03 07:13:41',40,'Livrée','En cours de paiement',NULL,'livraison','38 QUATER Boulevard Du Port\r\nRésidence Good Morning Cergy II'),
(16,34,33,'2026-04-03 12:31:03',40,'Livrée','En cours de paiement',NULL,'livraison','38 QUATER Boulevard Du Port\r\nRésidence Good Morning Cergy II'),
(17,25,33,'2026-04-05 11:12:34',13,'Livrée','Non payé',NULL,'livraison',NULL),
(18,25,NULL,'2026-04-05 11:16:25',13,'Annulée','Échec',NULL,'livraison',NULL),
(19,25,33,'2026-04-05 11:16:48',13,'Livrée','Payé','MI2A000019','livraison',NULL),
(20,25,33,'2026-04-05 11:21:31',16.9,'Livrée','Payé','MI2A000020','livraison',NULL),
(21,25,33,'2026-04-05 11:29:50',15.5,'Livrée','Payé','MI2A000021','livraison',NULL),
(22,25,33,'2026-04-05 13:35:52',16.9,'Livrée','Payé','MI2A000022','livraison',NULL),
(23,34,33,'2026-04-05 14:16:14',10.9,'Livrée','Payé','MI2A000023','livraison',NULL),
(24,25,33,'2026-04-05 14:51:59',16.9,'Livrée','Payé','MI2A000024','livraison','12 rue des Lilas, 95000 Cergy'),
(25,25,NULL,'2026-04-05 15:10:47',6.5,'Annulée','Payé','MI2A000025','livraison','12 rue des Lilas, 95000 Cergy'),
(26,25,33,'2026-04-05 15:11:48',16.9,'Livrée','Payé','MI2A000026','livraison','12 rue des Lilas, 95000 Cergy'),
(27,25,33,'2026-04-05 15:35:00',53.9,'Livrée','Payé','MI2A000027','livraison','12 rue des Lilas, 95000 Cergy'),
(28,25,33,'2026-04-05 15:49:17',59,'Livrée','Payé','MI2A000028','livraison','12 rue des Lilas, 95000 Cergy'),
(29,34,33,'2026-04-05 20:20:00',10.9,'Livrée','Payé','MI2A000029','livraison','38 QUATER Boulevard Du Port\r\nRésidence Good Morning Cergy II'),
(30,25,33,'2026-04-06 23:22:44',38.5,'Livrée','Payé','MI2A000030','livraison','53 rue des aulnes'),
(31,25,33,'2026-04-07 08:56:08',28.8,'Livrée','Payé','MI2A000031','livraison','12 rue des Lilas, 95000 Cergy'),
(32,34,33,'2026-04-14 21:18:39',16.9,'Livrée','Payé','MI2A000032','livraison','38 QUATER Boulevard Du Port\r\nRésidence Good Morning Cergy II'),
(33,35,33,'2026-04-21 11:46:52',33.4,'Livrée','Payé','MI2A000033','livraison',''),
(34,35,NULL,'2026-04-21 11:49:21',41.9,'Annulée','Échec',NULL,'livraison','12 rue du poney qui tousse'),
(35,35,NULL,'2026-04-21 11:49:58',41.9,'Annulée','Échec',NULL,'livraison',''),
(36,35,NULL,'2026-04-21 11:50:41',41.9,'Annulée','Échec',NULL,'livraison',''),
(37,35,NULL,'2026-04-21 11:51:48',41.9,'Annulée','Échec',NULL,'livraison','12 rue du poney qui tousse, cergy'),
(38,35,33,'2026-04-21 11:52:04',41.9,'Livrée','Payé','MI2A000038','livraison',''),
(39,35,33,'2026-04-21 11:57:02',43.4,'Livrée','Payé','MI2A000039','livraison','12 rue du poney qui tousse, Cergy'),
(40,36,33,'2026-04-24 13:11:51',32,'Livrée','Payé','MI2A000040','livraison','3 places des fédérés\r\nAppt 308C'),
(41,25,33,'2026-05-05 08:57:05',23,'Livrée','Payé','MI2A000041','livraison','12 rue des Lilas, 95000 Cergy'),
(42,35,33,'2026-05-05 09:02:35',15.5,'Livrée','Payé','MI2A000042','livraison',''),
(43,34,33,'2026-05-05 10:11:38',16.9,'Livrée','Payé','MI2A000043','livraison','38 QUATER Boulevard Du Port\r\nRésidence Good Morning Cergy II'),
(44,26,33,'2026-05-05 10:18:40',9,'Livrée','Payé','MI2A000044','livraison','24 avenue des Roses, 95800 Cergy'),
(45,26,33,'2026-05-05 10:24:22',9,'Livrée','Payé','MI2A000045','livraison','24 avenue des Roses, 95800 Cergy'),
(46,25,33,'2026-05-05 10:43:01',20.7,'Livrée','Payé','MI2A000046','livraison','12 rue des Lilas, 95000 Cergy'),
(47,33,33,'2026-05-05 11:04:13',14,'Livrée','Payé','MI2A000047','livraison','Cergy-Pontoise, Île-de-France, France'),
(48,25,33,'2026-05-06 10:36:25',65.34,'Livrée','Payé','MI2A000048','livraison','3 places des fédérés,93160 Noisy-le-grand');
/*!40000 ALTER TABLE `Commandes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping data for table `Contenu_Commandes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `Contenu_Commandes` WRITE;
/*!40000 ALTER TABLE `Contenu_Commandes` DISABLE KEYS */;
INSERT INTO `Contenu_Commandes` VALUES
(8,19,1,6.9,NULL),
(9,1,3,6.5,NULL),
(10,2,1,9,NULL),
(10,4,1,24.9,NULL),
(10,12,1,15.5,NULL),
(11,2,1,9,NULL),
(11,4,1,24.9,NULL),
(11,12,1,15.5,NULL),
(12,44,1,32,'Entrée: Os à Moelle, Plat: Burger Le Grand Miam, Dessert: Cheesecake, Boisson: Coca Zéro (50cl)'),
(13,12,1,15.5,''),
(13,21,1,0,'Choix: Bière IPA (25cl), 🎁 Cadeau Club (-300 Miams)'),
(14,1,1,6.5,''),
(14,10,1,16,''),
(14,21,1,0,'Choix: Sprite (33cl), 🎁 Cadeau Club (-300 Miams)'),
(15,1,1,6.5,''),
(15,9,1,17.5,''),
(15,10,1,16,''),
(16,1,1,6.5,''),
(16,9,1,17.5,''),
(16,10,1,16,''),
(17,1,2,6.5,'[]'),
(18,1,2,6.5,'[]'),
(19,1,2,6.5,'[]'),
(20,11,1,16.9,'[\"Cuisson: Saignant\",\"Pr\\u00e9paration: Standard\"]'),
(21,12,1,15.5,'[\"Cuisson: \\u00c0 point\",\"Pr\\u00e9paration: Standard\"]'),
(22,11,1,16.9,'[\"Cuisson: \\u00c0 point\",\"Pr\\u00e9paration: Standard\"]'),
(23,43,1,10.9,'[\"Plat: Mini Cheeseburger\",\"Accompagnement: Haricots verts\",\"Dessert: Compote de fruits\",\"Boisson: Sirop \\u00e0 l\'eau\"]'),
(24,11,1,16.9,'[\"Cuisson: \\u00c0 point\",\"Pr\\u00e9paration: Standard\"]'),
(25,1,1,6.5,'[]'),
(26,11,1,16.9,'[\"Cuisson: Bien cuit\",\"Pr\\u00e9paration: Standard\"]'),
(27,5,2,18.5,'[\"Cuisson: \\u00c0 point\",\"Pr\\u00e9paration: Standard\"]'),
(27,42,1,16.9,'[\"Plat: Burger Le Grand Miam\",\"Cuisson (si viande): \\u00c0 point\",\"Pr\\u00e9paration: Standard\",\"Boisson: Ice Tea (33cl)\"]'),
(28,6,1,59,'[\"Cuisson: Bleu\",\"Pr\\u00e9paration: Standard\"]'),
(29,43,1,10.9,'[\"Plat: Mini Cheeseburger\",\"Accompagnement: Haricots verts\",\"Dessert: Compote de fruits\",\"Boisson: Sirop \\u00e0 l\'eau\",\"\\ud83d\\udcdd Changer un truc\"]'),
(30,1,1,6.5,'[]'),
(30,44,1,32,'[\"Entr\\u00e9e: Onion Rings\",\"Plat: Le Pav\\u00e9 du Chef\",\"Cuisson (si viande): Bleu\",\"Pr\\u00e9paration: Standard\",\"Dessert: Brioche Perdue\",\"Boisson: Ice Tea (50cl)\"]'),
(31,21,1,0,'[\"Choix: Sprite (33cl)\",\"\\ud83c\\udf81 Cadeau Club (-300 Miams)\"]'),
(31,44,1,32,'[\"Entr\\u00e9e: Os \\u00e0 Moelle\",\"Plat: Burger Le Grand Miam\",\"Cuisson (si viande): Bleu\",\"Pr\\u00e9paration: Viande Halal\",\"Dessert: Cheesecake\",\"Boisson: Fanta (50cl)\"]'),
(32,42,1,16.9,'[\"Plat: Burger Le Grand Miam\",\"Cuisson (si viande): Bien cuit\",\"Pr\\u00e9paration: Viande Halal\",\"Boisson: Ice Tea (33cl)\"]'),
(33,1,1,6.5,'[]'),
(33,2,1,9,'[]'),
(33,3,1,14,'[]'),
(33,21,1,3.9,'[\"Choix: Coca-Cola\"]'),
(34,1,1,6.5,'[]'),
(34,3,1,14,'[]'),
(34,16,1,9.5,'[]'),
(34,17,1,8,'[]'),
(34,21,1,3.9,'[\"Choix: Coca-Cola\"]'),
(34,45,1,0,'[\"Choix: Sauce B\\u00e9arnaise\",\"\\ud83c\\udf81 Cadeau Club (-150 Miams)\"]'),
(35,1,1,6.5,'[]'),
(35,3,1,14,'[]'),
(35,16,1,9.5,'[]'),
(35,17,1,8,'[]'),
(35,21,1,3.9,'[\"Choix: Coca-Cola\"]'),
(35,45,1,0,'[\"Choix: Sauce B\\u00e9arnaise\",\"\\ud83c\\udf81 Cadeau Club (-150 Miams)\"]'),
(36,1,1,6.5,'[]'),
(36,3,1,14,'[]'),
(36,16,1,9.5,'[]'),
(36,17,1,8,'[]'),
(36,21,1,3.9,'[\"Choix: Coca-Cola\"]'),
(36,45,1,0,'[\"Choix: Sauce B\\u00e9arnaise\",\"\\ud83c\\udf81 Cadeau Club (-150 Miams)\"]'),
(37,1,1,6.5,'[]'),
(37,3,1,14,'[]'),
(37,16,1,9.5,'[]'),
(37,17,1,8,'[]'),
(37,21,1,3.9,'[\"Choix: Coca-Cola\"]'),
(37,45,1,0,'[\"Choix: Sauce B\\u00e9arnaise\",\"\\ud83c\\udf81 Cadeau Club (-150 Miams)\"]'),
(38,1,1,6.5,'[]'),
(38,3,1,14,'[]'),
(38,16,1,9.5,'[]'),
(38,17,1,8,'[]'),
(38,21,1,3.9,'[\"Choix: Coca-Cola\"]'),
(38,45,1,0,'[\"Choix: Sauce B\\u00e9arnaise\",\"\\ud83c\\udf81 Cadeau Club (-150 Miams)\"]'),
(39,1,1,6.5,'[]'),
(39,3,1,14,'[]'),
(39,16,1,9.5,'[]'),
(39,17,1,8,'[]'),
(39,21,1,3.9,'[\"Choix: Coca-Cola\"]'),
(39,45,1,1.5,'[\"Choix: Sauce B\\u00e9arnaise\",\"\\ud83c\\udf81 Cadeau Club (-150 Miams)\"]'),
(40,44,1,32,'[\"Entr\\u00e9e: Onion Rings\",\"Plat: Burger Le Grand Miam\",\"Cuisson (si viande): Saignant\",\"Pr\\u00e9paration: Viande Halal\",\"Dessert: Cheesecake\",\"Boisson: Ice Tea (50cl)\"]'),
(41,1,1,6.5,'[]'),
(41,14,1,16.5,'[\"Cuisson: \\u00c0 point\",\"Pr\\u00e9paration: Viande Halal\"]'),
(42,1,1,6.5,'[]'),
(42,2,1,9,'[]'),
(43,42,1,16.9,'[\"Plat: Burger Le Grand Miam\",\"Cuisson (si viande): Bien cuit\",\"Pr\\u00e9paration: Viande Halal\",\"Boisson: Ice Tea (33cl)\"]'),
(44,2,1,9,'[]'),
(45,2,1,9,'[]'),
(46,1,1,6.5,'[]'),
(46,14,1,16.5,'[\"Cuisson: \\u00c0 point\",\"Pr\\u00e9paration: Viande Halal\"]'),
(47,3,1,14,'[]'),
(48,11,3,16.9,'[]'),
(48,13,1,17.9,'[]'),
(48,27,1,4,'[]');
/*!40000 ALTER TABLE `Contenu_Commandes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping data for table `Coupons`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `Coupons` WRITE;
/*!40000 ALTER TABLE `Coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `Coupons` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping data for table `Paiements`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `Paiements` WRITE;
/*!40000 ALTER TABLE `Paiements` DISABLE KEYS */;
INSERT INTO `Paiements` VALUES
(5,9,25,19.5,'2026-03-25 11:20:47','MI2A00000009',NULL),
(6,11,34,49.4,'2026-03-25 11:24:38','MI2A00000011',NULL),
(7,12,25,32,'2026-03-25 11:52:01','MI2A00000012',NULL),
(8,13,25,15.5,'2026-03-25 18:10:30','MI2A00000013',NULL),
(9,19,25,13,'2026-04-05 11:17:06','MI2A000019',NULL),
(10,20,25,16.9,'2026-04-05 11:21:43','MI2A000020',NULL),
(11,21,25,15.5,'2026-04-05 11:30:02','MI2A000021',NULL),
(12,22,25,16.9,'2026-04-05 13:36:13','MI2A000022',NULL),
(13,23,34,10.9,'2026-04-05 14:16:27','MI2A000023',NULL),
(14,24,25,16.9,'2026-04-05 14:52:16','MI2A000024',NULL),
(15,25,25,6.5,'2026-04-05 15:11:01','MI2A000025',NULL),
(16,26,25,16.9,'2026-04-05 15:12:01','MI2A000026',NULL),
(17,27,25,35.4,'2026-04-05 15:35:13','MI2A000027',NULL),
(18,27,25,18.5,'2026-04-05 15:43:28','MI2A000027',NULL),
(19,28,25,59,'2026-04-05 15:49:31','MI2A000028',NULL),
(20,29,34,10.9,'2026-04-05 20:20:12','MI2A000029',NULL),
(21,30,25,38.5,'2026-04-06 23:23:00','MI2A000030',NULL),
(22,31,25,28.8,'2026-04-07 08:56:22','MI2A000031',NULL),
(23,32,34,16.9,'2026-04-14 21:18:51','MI2A000032',NULL),
(24,33,35,33.4,'2026-04-21 11:47:28','MI2A000033',NULL),
(25,38,35,41.9,'2026-04-21 11:52:17','MI2A000038',NULL),
(26,39,35,43.4,'2026-04-21 11:57:17','MI2A000039',NULL),
(27,40,36,32,'2026-04-24 13:12:02','MI2A000040',NULL),
(28,41,25,23,'2026-05-05 08:57:24','MI2A000041',NULL),
(29,42,35,15.5,'2026-05-05 09:02:49','MI2A000042',NULL),
(30,43,34,16.9,'2026-05-05 10:11:51','MI2A000043',NULL),
(31,44,26,9,'2026-05-05 10:18:56','MI2A000044',NULL),
(32,45,26,9,'2026-05-05 10:24:38','MI2A000045',NULL),
(33,46,25,20.7,'2026-05-05 10:43:15','MI2A000046',NULL),
(34,47,33,14,'2026-05-05 11:04:26','MI2A000047',NULL),
(35,48,25,65.34,'2026-05-06 10:36:40','MI2A000048',NULL);
/*!40000 ALTER TABLE `Paiements` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping data for table `Produits`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `Produits` WRITE;
/*!40000 ALTER TABLE `Produits` DISABLE KEYS */;
INSERT INTO `Produits` VALUES
(1,'Onion Rings \"Tower\"','Entrées',6.5,'public/images/nourriture/onion_rings.png','8 à 10 beignets d\'oignons servis sur pique verticale. Sauce BBQ.','[{\"titre\":\"Sauce d\'accompagnement\",\"choix\":[\"Sauce BBQ\",\"Ketchup\",\"Mayonnaise\",\"Sans sauce\"]}]'),
(2,'Os à Moelle Rôti','Entrées',9,'public/images/nourriture/os_moelle.png','Coupe longitudinale, fleur de sel, pain de campagne grillé.','[{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Viande Halal\"]}]'),
(3,'Planche Mixte','Entrées',14,'public/images/nourriture/planche_mixte.png','Charcuterie (Rosette, Terrine) + Fromages (Cantal, Chèvre).','[{\"titre\":\"Préparation\",\"choix\":[\"Standard (avec Porc)\",\"Sans Porc (100% Bœuf/Volaille)\",\"100% Fromages (Végétarien)\"]}]'),
(4,'L\'Entrecôte XXL','Viandes',24.9,'public/images/nourriture/entrecote_xxl.png','350g - Charolais/Limousin. Persillée.','[{\"titre\":\"Cuisson\",\"choix\":[\"Bleu\",\"Saignant\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Viande Halal\"]}]'),
(5,'Le Pavé du Chef','Viandes',18.5,'public/images/nourriture/pave_rumsteak.png','200g - Cœur de Rumsteak. Tendre et maigre.','[{\"titre\":\"Cuisson\",\"choix\":[\"Bleu\",\"Saignant\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Viande Halal\"]}]'),
(6,'La Côte de Bœuf','Viandes',59,'public/images/nourriture/cote_de_boeuf.png','1 kg (pour 2 pers). Maturation min. 21 jours.','[{\"titre\":\"Cuisson\",\"choix\":[\"Bleu\",\"Saignant\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Viande Halal\"]}]'),
(7,'BBQ Ribs','Viandes',19.5,'public/images/nourriture/bbq_ribs.png','Travers de porc marinés 24h, cuisson lente 12h.','[{\"titre\":\"Sauce\",\"choix\":[\"Sauce BBQ classique\",\"Sauce BBQ Piquante\"]}]'),
(8,'Magret de Canard','Viandes',22,'public/images/nourriture/magret_canard.png','Entier (300g approx), grillé rosé, sauce miel.','[{\"titre\":\"Cuisson\",\"choix\":[\"Rosé\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Viande Halal\"]}]'),
(9,'Pavé de Saumon','Viandes',17.5,'public/images/nourriture/pave_saumon.png','180g, grillé unilatéral, citron vert.','[{\"titre\":\"Cuisson\",\"choix\":[\"Rosé à cœur\",\"Bien cuit\"]}]'),
(10,'L\'Andouillette','Viandes',16,'public/images/nourriture/andouillette.png','AAAAA, grillée forte à la moutarde.','[{\"titre\":\"Sauce\",\"choix\":[\"Moutarde à l\'ancienne\",\"Sauce au Poivre\",\"Sans sauce\"]}]'),
(11,'Le Grand Miam','Burgers',16.9,'public/images/nourriture/burger_grand_miam.png','Double Steak 150g, Cheddar, Sauce Maison.','[{\"titre\":\"Cuisson\",\"choix\":[\"Saignant\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Viande Halal\"]}]'),
(12,'Le Cheesy Tower','Burgers',15.5,'public/images/nourriture/burger_cheesy_tower.png','Steak 180g, Sauce Fromagère, Cheddar fondu.','[{\"titre\":\"Cuisson\",\"choix\":[\"Saignant\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Viande Halal\"]}]'),
(13,'Le Montagnard','Burgers',17.9,'public/images/nourriture/burger_montagnard.png','Steak 150g, Reblochon, Lardons, Galette PdeT.','[{\"titre\":\"Cuisson\",\"choix\":[\"Saignant\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"choix\":[\"Standard (avec Lardons)\",\"Sans Lardons\",\"Viande Halal (sans Lardons)\"]}]'),
(14,'Le Frenchy','Burgers',16.5,'public/images/nourriture/burger_frenchy.png','Steak 150g, Cantal jeune, Oignons confits.','[{\"titre\":\"Cuisson\",\"choix\":[\"Saignant\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Viande Halal\"]}]'),
(15,'Veggie Grill','Burgers',14.5,'public/images/nourriture/burger_veggie.png','Galette Haricots Rouges/Maïs ou Simili-carné.','[{\"titre\":\"Galette\",\"choix\":[\"Haricots Rouges/Maïs\",\"Simili-carné\"]}]'),
(16,'Profiteroles XXL','Desserts',9.5,'public/images/nourriture/profiteroles.png','1 Chou géant, Glace Vanille, Chocolat chaud versé à table.','[{\"titre\":\"Chocolat chaud\",\"choix\":[\"Versé sur le chou\",\"Servi à part\"]}]'),
(17,'Cookie Skillet','Desserts',8,'public/images/nourriture/cookie_skillet.png','Cuit minute dans un poêlon, mi-cuit à cœur.','[{\"titre\":\"Cuisson\",\"choix\":[\"Mi-cuit (fondant)\",\"Bien cuit\"]},{\"titre\":\"Glace\",\"choix\":[\"Vanille\",\"Chocolat\",\"Caramel\"]}]'),
(18,'Cheesecake NY','Desserts',7.5,'public/images/nourriture/cheesecake.png','Base Speculoos, coulis fruits rouges.','[{\"titre\":\"Coulis\",\"choix\":[\"Fruits rouges\",\"Caramel au beurre salé\",\"Chocolat\"]}]'),
(19,'Brioche Perdue','Desserts',6.9,'public/images/nourriture/brioche_perdue.png','Tranche épaisse, caramel beurre salé.','[{\"titre\":\"Accompagnement\",\"choix\":[\"Caramel beurre salé\",\"Chocolat\",\"Sans coulis\"]}]'),
(20,'Café Gourmand','Desserts',8.5,'public/images/nourriture/cafe_gourmand.png','Café + Mini Cookie + Mini Mousse + Mini Brioche.','[{\"titre\":\"Boisson Chaude\",\"choix\":[\"Café Expresso\",\"Café Allongé\",\"Café Décaféiné\",\"Thé\"]}]'),
(21,'Sodas','Boissons',3.9,'public/images/nourriture/sodas.png','Coca-Cola / Zéro, Fanta, Sprite (33cl)','[{\"titre\":\"Choix\",\"choix\":[\"Coca-Cola\",\"Coca-Cola Zéro\", \"Coca-Cola Cherry\",\"Fanta Orange\", \"Fanta Cherry\",\"Sprite\"]}]'),
(22,'Ice Tea','Boissons',3.9,'public/images/nourriture/icetea.png','Fuze Tea / Lipton (25cl)','[{\"titre\":\"Choix\",\"choix\":[\"Fuze Tea\",\"Lipton\"]}]'),
(23,'Limonade Artisanale','Boissons',4.5,'public/images/nourriture/limonade.png','\"La French\" (Citron ou Violette) - 33cl','[{\"titre\":\"Parfum\",\"choix\":[\"Citron\",\"Violette\"]}]'),
(24,'Jus de Fruits','Boissons',4,'public/images/nourriture/jus_fruits.png','Orange, Pomme, Ananas (25cl)','[{\"titre\":\"Parfum\",\"choix\":[\"Orange\",\"Pomme\",\"Ananas\"]}]'),
(25,'Eaux','Boissons',3.5,'public/images/nourriture/eau.png','Vittel, San Pellegrino (50cl / 1L)','[{\"titre\":\"Marque\",\"choix\":[\"Vittel\",\"San Pellegrino\"]},{\"titre\":\"Format\",\"choix\":[\"50cl - 3.50 €\",\"1L - 5.50 €\"]}]'),
(26,'Sirop à l\'eau','Boissons',2.5,'public/images/nourriture/sirop.png','Grenadine, Menthe, Fraise (25cl)','[{\"titre\":\"Parfum\",\"choix\":[\"Grenadine\",\"Menthe\",\"Fraise\"]}]'),
(27,'Bière Pression Blonde','Boissons',4,'public/images/nourriture/biere_blonde.png','Premium (25cl / 50cl)','[{\"titre\":\"Format\",\"choix\":[\"25cl - 4.00 €\",\"50cl (Pinte) - 7.50 €\"]}]'),
(28,'Bière Pression IPA','Boissons',5,'public/images/nourriture/biere_ipa.png','Craft (25cl / 50cl)','[{\"titre\":\"Format\",\"choix\":[\"25cl - 5.00 €\",\"50cl (Pinte) - 8.50 €\"]}]'),
(29,'Budweiser','Boissons',5.5,'public/images/nourriture/budweiser.png','Bouteille 33cl','[{\"titre\":\"Quantité\",\"choix\":[\"1 Bouteille\",\"Pack de 2 - 10.00 €\"]}]'),
(30,'Desperados','Boissons',6,'public/images/nourriture/desperados.png','Bouteille 33cl','[{\"titre\":\"Quantité\",\"choix\":[\"1 Bouteille\",\"Pack de 2 - 11.00 €\"]}]'),
(31,'Vin Rouge','Boissons',5,'public/images/nourriture/vin_rouge.png','Côtes du Rhône ou Bordeaux (Verre 12cl / Bouteille)','[{\"titre\":\"Type\",\"choix\":[\"Côtes du Rhône\",\"Bordeaux\"]},{\"titre\":\"Format\",\"choix\":[\"Verre 12cl - 5.00 €\",\"Bouteille - 24.00 €\"]}]'),
(32,'Vin Rosé','Boissons',5,'public/images/nourriture/vin_rose.png','Côte de Provence (Verre 12cl / Bouteille)','[{\"titre\":\"Parfum\",\"choix\":[\"Vanille\",\"Chocolat\",\"Fraise\"]}]'),
(33,'Smoothie \"Le Tropical\"','Boissons',6.5,'public/images/nourriture/smoothie_tropical.png','Mangue, Ananas, Passion (Mixé minute)','[{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Sans sucre ajouté\"]}]'),
(34,'Smoothie \"Red Kiss\"','Boissons',6.5,'public/images/nourriture/smoothie_red_kiss.png','Fraise, Framboise, Banane','[{\"titre\":\"Préparation\",\"choix\":[\"Standard\",\"Sans sucre ajouté\"]}]'),
(35,'Milkshake Classique US','Boissons',5.5,'public/images/nourriture/milkshake_classique.png','Vanille, Chocolat ou Fraise','[{\"titre\":\"Parfum\",\"choix\":[\"Vanille\",\"Chocolat\",\"Fraise\"]},{\"titre\":\"Chantilly\",\"choix\":[\"Avec Chantilly\",\"Sans Chantilly\"]}]'),
(36,'FREAKSHAKE \"Le Choco-Bomb\"','Boissons',9.9,'public/images/nourriture/freakshake_choco.png','Milkshake Nutella + Chantilly + Brownie entier + Coulis','[{\"titre\":\"Chantilly\",\"choix\":[\"Avec Chantilly\",\"Sans Chantilly\"]}]'),
(37,'FREAKSHAKE \"Cookie Monster\"','Boissons',9.9,'public/images/nourriture/freakshake_cookie.png','Milkshake Vanille + Chantilly + Éclats de Cookie + Caramel','[{\"titre\":\"Chantilly\",\"choix\":[\"Avec Chantilly\",\"Sans Chantilly\"]}]'),
(38,'Mojito','Boissons',8.5,'public/images/nourriture/mojito.png','Rhum, Menthe fraîche, Citron vert, Perrier','[{\"titre\":\"Sucre\",\"choix\":[\"Standard\",\"Peu sucré\"]}]'),
(39,'Spritz','Boissons',8.5,'public/images/nourriture/spritz.png','Aperol, Prosecco, Rondelle d\'orange','[{\"titre\":\"Glaçons\",\"choix\":[\"Avec glaçons\",\"Peu de glaçons\"]}]'),
(40,'Virgin Mojito','Boissons',6.5,'public/images/nourriture/virgin_mojito.png','Version sans alcool','[{\"titre\":\"Sucre\",\"choix\":[\"Standard\",\"Peu sucré\"]}]'),
(41,'Rio','Boissons',6.5,'public/images/nourriture/cocktail_rio.png','Jus d\'orange, Jus d\'ananas, Sirop de grenadine (Bicolore)','[{\"titre\":\"Glaçons\",\"choix\":[\"Avec glaçons\",\"Peu de glaçons\"]}]'),
(42,'Formule LUNCH EXPRESS','Menus',16.9,NULL,'Menu complet à composer','[{\"titre\":\"Plat\",\"choix\":[\"Burger Le Grand Miam\",\"Le Pavé du Chef\",\"Veggie Grill\"]},{\"titre\":\"Cuisson\",\"condition\":{\"Plat\":[\"Burger Le Grand Miam\",\"Le Pavé du Chef\"]},\"choix\":[\"Saignant\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"condition\":{\"Plat\":[\"Burger Le Grand Miam\",\"Le Pavé du Chef\"]},\"choix\":[\"Standard\",\"Viande Halal\"]},{\"titre\":\"Boisson\",\"choix\":[\"Coca-Cola (33cl)\",\"Coca Zéro (33cl)\",\"Fanta (33cl)\",\"Sprite (33cl)\",\"Ice Tea (33cl)\",\"Verre de vin (12cl)\",\"Café\"]}]'),
(43,'Menu LITTLE COWBOY','Menus',10.9,NULL,'Menu complet à composer','[{\"titre\":\"Plat\",\"choix\":[\"Mini Cheeseburger\",\"Nuggets de Poulet (x6)\"]},{\"titre\":\"Dessert\",\"choix\":[\"Sundae Vanille\",\"Compote de fruits\"]},{\"titre\":\"Boisson\",\"choix\":[\"Sirop à l\'eau\",\"Jus de pomme\"]}]'),
(44,'Menu GRILL MASTER','Menus',32,NULL,'Menu complet à composer','[{\"titre\":\"Entrée\",\"choix\":[\"Onion Rings\",\"Os à Moelle\",\"Œuf Mayo\"]},{\"titre\":\"Plat\",\"choix\":[\"Burger Le Grand Miam\",\"Burger Cheesy Tower\",\"Burger Montagnard\",\"Burger Frenchy\",\"BBQ Ribs\",\"Magret de Canard\",\"Le Pavé du Chef\",\"Pavé de Saumon\"]},{\"titre\":\"Cuisson\",\"condition\":{\"Plat\":[\"Burger Le Grand Miam\",\"Burger Cheesy Tower\",\"Burger Montagnard\",\"Burger Frenchy\",\"Le Pavé du Chef\",\"Magret de Canard\"]},\"choix\":[\"Bleu\",\"Saignant\",\"À point\",\"Bien cuit\"]},{\"titre\":\"Préparation\",\"condition\":{\"Plat\":[\"Burger Le Grand Miam\",\"Burger Cheesy Tower\",\"Burger Montagnard\",\"Burger Frenchy\",\"Le Pavé du Chef\"]},\"choix\":[\"Standard\",\"Viande Halal\"]},{\"titre\":\"Dessert\",\"choix\":[\"Cheesecake\",\"Brioche Perdue\",\"Coupe de Glace 3 boules\"]},{\"titre\":\"Boisson\",\"choix\":[\"Pinte de Bière\",\"Coca-Cola (50cl)\",\"Coca Zéro (50cl)\",\"Fanta (50cl)\",\"Sprite (50cl)\",\"Ice Tea (50cl)\"]}]'),
(45,'Sauce Supplémentaire','Menus',1.5,NULL,'Menu complet à composer','[{\"titre\":\"Sauce\",\"choix\":[\"Sauce BBQ\",\"Sauce Béarnaise\",\"Sauce au Poivre\",\"Sauce Roquefort\",\"Moutarde Ancienne\"]}]');
/*!40000 ALTER TABLE `Produits` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping data for table `Utilisateurs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `Utilisateurs` WRITE;
/*!40000 ALTER TABLE `Utilisateurs` DISABLE KEYS */;
INSERT INTO `Utilisateurs` VALUES
(25,'Dupont','Jean','client1@example.com','$2y$10$45Iyh6SzKDFLJGJgMw3vH.HPmNXb5vs3C9hvmtm1z4HTAdUopls8e','Client','0123456789','12 rue des Lilas, 95000 Cergy',4592,4592,'Actif'),
(26,'Martin','Sophie','client2@example.com','$2y$10$45Iyh6SzKDFLJGJgMw3vH.HPmNXb5vs3C9hvmtm1z4HTAdUopls8e','Client','0234567891','24 avenue des Roses, 95800 Cergy',180,180,'Actif'),
(27,'Petit','Marie','client3@example.com','$2y$10$45Iyh6SzKDFLJGJgMw3vH.HPmNXb5vs3C9hvmtm1z4HTAdUopls8e','Client','0345678912','8 boulevard des Chênes, 95000 Cergy',0,0,'Actif'),
(28,'Dubois','Pierre','client4@example.com','$2y$10$45Iyh6SzKDFLJGJgMw3vH.HPmNXb5vs3C9hvmtm1z4HTAdUopls8e','Client','0456789123','15 rue de la Paix, 95610 Eragny',0,0,'Actif'),
(29,'Leroy','Julie','client5@example.com','$2y$10$45Iyh6SzKDFLJGJgMw3vH.HPmNXb5vs3C9hvmtm1z4HTAdUopls8e','Client','0567891234','3 allée des Pins, 95280 Jouy-le-Moutier',0,0,'Actif'),
(30,'Admin','Principal','admin1@grandmiam.com','$2y$10$45Iyh6SzKDFLJGJgMw3vH.HPmNXb5vs3C9hvmtm1z4HTAdUopls8e','Administrateur','0678912345','',0,0,'Actif'),
(31,'Admin','Secondaire','admin2@grandmiam.com','$2y$10$45Iyh6SzKDFLJGJgMw3vH.HPmNXb5vs3C9hvmtm1z4HTAdUopls8e','Administrateur','0789123456','',0,0,'Actif'),
(32,'Chef','Principal','resto@grandmiam.com','$2y$10$45Iyh6SzKDFLJGJgMw3vH.HPmNXb5vs3C9hvmtm1z4HTAdUopls8e','Restaurateur','0891234567','',0,0,'Actif'),
(33,'Allen','Barry','livreur1@grandmiam.com','$2y$10$45Iyh6SzKDFLJGJgMw3vH.HPmNXb5vs3C9hvmtm1z4HTAdUopls8e','Livreur','0912345678','Cergy-Pontoise, Île-de-France, France',140,140,'Actif'),
(34,'BENSAID','Myriam','myriam.bensaid21@gmail.com','$2y$10$p8W194ANaV1En7DbPnPWr.ZQEBGBrkdzfi1axe9IW.KLBaV3Bhv8m','Client','0668399206','38 QUATER Boulevard Du Port\r\nRésidence Good Morning Cergy II',1050,1050,'Actif'),
(35,'Doe','John','jd@mail.com','$2y$10$.nCczQLN4QToZa2dC0vEAeegJDac7r/JxGHwN2JtPEfQxyuajW/yy','Client','','',1342,1342,'Actif'),
(36,'Ouarghi','Sheryne','ouarghisheryne@gmail.com','$2y$10$BRK4B2ElmvuQb/hhcPHXVOykjpmxg6OC6DnL/tSCcOa7apMHc2mwC','Client','0617677702','3 places des fédérés\r\nAppt 308C',320,320,'Bloqué');
/*!40000 ALTER TABLE `Utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-05-12 13:04:31
