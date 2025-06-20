-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: document_tracking
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `depart`
--

DROP TABLE IF EXISTS `depart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `depart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num_ordre` int(11) NOT NULL,
  `date_envoi` date NOT NULL,
  `destinataire` varchar(255) NOT NULL,
  `objet` text NOT NULL,
  `division` varchar(255) NOT NULL,
  `responsable` varchar(255) NOT NULL,
  `observations` text NOT NULL,
  `important` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_ordre` (`num_ordre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `depart`
--

LOCK TABLES `depart` WRITE;
/*!40000 ALTER TABLE `depart` DISABLE KEYS */;
INSERT INTO `depart` VALUES (1,90,'2025-04-01','Destinataire1','90','Division2','Responsable1','90',1),(4,20,'2025-05-27','ABH Sbou-Fès','10','DSIC','10','101010',1);
/*!40000 ALTER TABLE `depart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destinataires`
--

DROP TABLE IF EXISTS `destinataires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `destinataires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destinataires`
--

LOCK TABLES `destinataires` WRITE;
/*!40000 ALTER TABLE `destinataires` DISABLE KEYS */;
INSERT INTO `destinataires` VALUES (1,'Partis Politiques (الأحزاب السياسية)'),(2,'Syndicats (النقابات)'),(3,'Associations (الجمعيات)'),(4,'Coopératives (التعاونيات)'),(5,'Entreprises et societés (المقاولات والشركات)'),(6,'Citoyens (المواطنون)'),(7,'Groupe de population (مجموعة من الساكنة)'),(8,'Fonctionnaires (الموظفون)'),(9,'Avocats (المحامون)');
/*!40000 ALTER TABLE `destinataires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `divisions`
--

DROP TABLE IF EXISTS `divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `divisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `divisions`
--

LOCK TABLES `divisions` WRITE;
/*!40000 ALTER TABLE `divisions` DISABLE KEYS */;
INSERT INTO `divisions` VALUES (1,'Cabinet'),(2,'DAI'),(3,'DAS'),(4,'DAR'),(5,'DBM'),(6,'DCAE'),(7,'DCL'),(8,'DSIC'),(9,'DUE'),(10,'DRH'),(11,'Service juridique et contentieux'),(12,'Cellule DERRO'),(13,'Cellule RAMED'),(14,'Cellule Manarat'),(15,'Secrétariat Particulier (SG)'),(16,'DE'),(17,'SEC'),(18,'Cellule Foncière'),(19,'PN'),(20,'Cellule MRE'),(21,'CRI'),(22,'CSPS (Projets Structurants)'),(23,'CGRN (Risques Naturels)'),(24,'SPS'),(25,'AMO'),(29,'ibrahim');
/*!40000 ALTER TABLE `divisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_number` int(11) NOT NULL,
  `destinataire` varchar(255) NOT NULL,
  `date_envoi` date NOT NULL,
  `num_ordre` int(11) NOT NULL,
  `date_arrivee` date NOT NULL,
  `objet` text NOT NULL,
  `division` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_ordre` (`num_ordre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `division_fr` varchar(255) NOT NULL,
  `division_ar` varchar(255) NOT NULL,
  `location` varchar(100) NOT NULL,
  `level` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=458 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizations`
--

LOCK TABLES `organizations` WRITE;
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
INSERT INTO `organizations` VALUES (1,'Divisions de la province','أقسام العمالة','Al Hoceima','Local'),(2,'Cabinet','الديوان','Al Hoceima','Local'),(3,'DAI','قسم الشؤون الداخلية','Al Hoceima','Local'),(4,'DAS','قسم العمل الاجتماعي','Al Hoceima','Local'),(5,'DAR','قسم الشؤون القروية','Al Hoceima','Local'),(6,'DBM','قسم الميزانية والصفقات','Al Hoceima','Local'),(7,'DCAE','القسم الاقتصادي','Al Hoceima','Local'),(8,'DCL','قسم الجماعات المحلية','Al Hoceima','Local'),(9,'DSIC','قسم أنظمة الإعلام والتواصل','Al Hoceima','Local'),(10,'DUE','قسم التعمير والبيئة','Al Hoceima','Local'),(11,'DRH','قسم الموارد البشرية','Al Hoceima','Local'),(12,'Service juridique et contentieux','مصلحة الشكايات والمنازعات القانونية','Al Hoceima','Local'),(13,'Cellule DERRO','خلية الديرو','Al Hoceima','Local'),(14,'Cellule RAMED','خلية الراميد','Al Hoceima','Local'),(15,'Cellule Manarat','خلية منارة المتوسط','Al Hoceima','Local'),(16,'SEC','مصلحة الحالة المدنية','Al Hoceima','Local'),(17,'Secrétariat Particulier','الكتابة الخاصة','Al Hoceima','Local'),(18,'DE','قسم التجهيزات','Al Hoceima','Local'),(19,'Services techniques d\'Al Hoceima','المصالح التقنية بالإقليم','Al Hoceima','Provincial'),(20,'CGI-section Al Hoceima','الشركة العقارية العامة -فرع الحسيمة','Al Hoceima','Provincial'),(21,'ANAPEC','وكالة إنعاش الشغل','Al Hoceima','Provincial'),(22,'Haut Commissariat Au Plan (HCP)','التخطيط','Al Hoceima','Provincial'),(23,'ISTA','معهد التكنولوجيا التطبيقية','Al Hoceima','Provincial'),(24,'ONP d\'Al Hoceima-Délégation Régionale','مكتب الصيد البحري','Al Hoceima','Provincial'),(25,'ANP d\'Al Hoceima','قسم الميناء','Al Hoceima','Provincial'),(26,'Douanes','الجمارك','Al Hoceima','Provincial'),(27,'CNOPS d\'Al Hoceima','صندوق الضمان الاجتماعي','Al Hoceima','Provincial'),(28,'ITPM','معهد التكنولوجية للصيد البحري','Al Hoceima','Provincial'),(29,'CRI d\'Al Hoceima','الإسثمار','Al Hoceima','Provincial'),(30,'Al Omrane Al Hoceima','العمران','Al Hoceima','Provincial'),(31,'Agence Urbaine','الوكالة الحضرية','Al Hoceima','Provincial'),(32,'Conseil des Oulémas Local','المجلس العلمي المحلي','Al Hoceima','Provincial'),(33,'Maroc Télécom','إتصالات المغرب','Al Hoceima','Provincial'),(34,'ONEE-BE','المديرية الإقليمية للكهرباء','Al Hoceima','Provincial'),(35,'ONEE-BO','المديرية الإقليمية للماء','Al Hoceima','Provincial'),(36,'Nadir des Habous','نظارة الأوقاف','Al Hoceima','Provincial'),(37,'Service des Impôts','مندوبية الضرائب','Al Hoceima','Provincial'),(38,'Domaines de l\'Etat','مندوبية أملاك الدولة','Al Hoceima','Provincial'),(39,'Eaux & Forêts','مندوبية المياه والغابات','Al Hoceima','Provincial'),(40,'Trésorerie Provinciale','الخزينة الإقليمية','Al Hoceima','Provincial'),(41,'ABHL','اللوكوس','Al Hoceima','Provincial'),(42,'Coordination Provinciale de l\'Agence Développement Social','التنسيقية الإقليمية للتنمية الاجتماعية','Al Hoceima','Provincial'),(43,'ODECCO (Office Provincial de Développement de la Coopération)','مكتب تنمية التعاون','Al Hoceima','Provincial'),(44,'Délégation Provinciale du Tourisme','مندوبية السياحة','Al Hoceima','Provincial'),(45,'Direction Provinciale de l\'Artisanat','مندوبية الصناعة التقليـدية','Al Hoceima','Provincial'),(46,'Délégation Provinciale des Anciens Resistants','مندوبية المقاومة','Al Hoceima','Provincial'),(47,'Délégation Provinciale de l\'Entraide Nationale','مندوبية التعاون الوطني','Al Hoceima','Provincial'),(48,'Délégation Provinciale de la Pêche Maritime','مندوبية الصيد البحري','Al Hoceima','Provincial'),(49,'Délégation Provinciale de l\'Industrie et du Commerce','مندوبية الصناعة والتجارة','Al Hoceima','Provincial'),(50,'Délégation Provinciale de la Jeunesse et des Sports','مندوبية الشباب والرياضة','Al Hoceima','Provincial'),(51,'Délégation Provinciale des Affaires Islamiques','مندوبية الشؤون الإسلامية','Al Hoceima','Provincial'),(52,'Direction Provinciale de l\'Equipement, du Transport et de la Logistique','مندوبية التجهيز والنقل','Al Hoceima','Provincial'),(53,'Délégation Provinciale de la Culture','مندوبية الثقـافـة','Al Hoceima','Provincial'),(54,'Délégation Provinciale de l\'Agriculture','مندوبية الفلاحة','Al Hoceima','Provincial'),(55,'Direction Provinciale du Travail','مندوبية التشغيل','Al Hoceima','Provincial'),(56,'Direction Provinciale de l\'Education Nationale','التربية الوطنية','Al Hoceima','Provincial'),(57,'Délégation Provinciale de la Santé','مندوبية الصحة','Al Hoceima','Provincial'),(58,'Délégation Provinciale de l\'Habitat et de la Politique de la Ville','مندوبية الإسكان','Al Hoceima','Provincial'),(59,'Service de l\'Environnement','مصلحة البيئة','Al Hoceima','Provincial'),(60,'Service de la Conservation Foncière','المحافظة العقارية','Al Hoceima','Provincial'),(61,'Service du Cadastre','المسح العقاري','Al Hoceima','Provincial'),(62,'Services Sécuritaires d\'Al Hoceima','السلطة الأمنية بالإقليم','Al Hoceima','Provincial'),(63,'DST','إدارة مراقبة التراب الوطني','Al Hoceima','Provincial'),(64,'Sûreté Régionale','الأمن الجهوي بالحسيمة','Al Hoceima','Provincial'),(65,'Gendarmerie Royale','الدرك- القيادة الجهوية','Al Hoceima','Provincial'),(66,'Place d\'Armes','الحامية العسكرية-القيادة','Al Hoceima','Provincial'),(67,'Prison Locale','السجن المحلي','Al Hoceima','Provincial'),(68,'Protection Civile','الوقاية المدنية','Al Hoceima','Provincial'),(69,'Forces Auxiliaires','القوات المساعدة-القيادة الإقليمية','Al Hoceima','Provincial'),(70,'Autorité Judiciaire d\'Al Hoceima','السلطة القضائية بالإقليم','Al Hoceima','Provincial'),(71,'Cour d\'Appel d\'Al Hoceima','محكمة الاستئناف بالحسيمة','Al Hoceima','Provincial'),(72,'Tribunal de 1ère Instance d\'Al Hoceima','المحكمة الابتدائية بالحسيمة','Al Hoceima','Provincial'),(73,'Tribunal de 1ère Instance de Targuist','المحكمة الابتدائية بتارجيست','Al Hoceima','Provincial'),(74,'Pachaliks/Cercles d\'Al Hoceima','الباشويات والدوائر بالإقليم','Al Hoceima','Provincial'),(75,'Pachalik Al Hoceima','باشوية الحسيمة','Al Hoceima','Provincial'),(76,'Pachalik Ajdir','باشوية أجدير','Al Hoceima','Provincial'),(77,'Pachalik Imzouren','باشوية إمزورن','Al Hoceima','Provincial'),(78,'Pachalik Bni Bouâyach','باشوية بني بوعياش','Al Hoceima','Provincial'),(79,'Pachalik Targuist','باشوية تارجيست','Al Hoceima','Provincial'),(80,'Cercle Bni Ouariaghel Charqia','دائرة بني ورياغل الشرقية','Al Hoceima','Provincial'),(81,'Cercle Bni Ouariaghel Gharbia','دائرة بني ورياغل الغربية','Al Hoceima','Provincial'),(82,'Cercle Targuist','دائرة تارجيست','Al Hoceima','Provincial'),(83,'Cercle Ketama','دائرة كتامة','Al Hoceima','Provincial'),(84,'Cercle Bni Boufrah','دائرة بني بوفراح','Al Hoceima','Provincial'),(85,'Autorité Locale d\'Al Hoceima','السلطة المحلية بالإقليم','Al Hoceima','Provincial'),(86,'Al Hoceima - 1er Arrondissement','الحسيمة المقاطعة الأولى','Al Hoceima','Provincial'),(87,'Al Hoceima - 2ème Arrondissement','الحسيمة المقاطعة الثانية','Al Hoceima','Provincial'),(88,'Al Hoceima - 3ème Arrondissement','الحسيمة المقاطعة التالثة','Al Hoceima','Provincial'),(89,'Al Hoceima - 4ème Arrondissement','الحسيمة المقاطعة الرابعة','Al Hoceima','Provincial'),(90,'Imzouren - 1er Arrondissement','إمزورن المقاطعة الأولى','Al Hoceima','Provincial'),(91,'Imzouren - 2ème Arrondissement','إمزورن المقاطعة الثانية','Al Hoceima','Provincial'),(92,'Bni Bouâyach - 1er Arrondissement','بني بوعياش المقاطعة الأولى','Al Hoceima','Provincial'),(93,'Bni Bouâyach - 2ème Arrondissement','بني بوعياش المقاطعة الثانية','Al Hoceima','Provincial'),(94,'Caïdat Ait Youssef Ouali','أيت يوسف أوعلي','Al Hoceima','Provincial'),(95,'Caïdat Nekkor','قيادة النكور','Al Hoceima','Provincial'),(96,'Caïdat Imrabten','قيادة إمرابطن','Al Hoceima','Provincial'),(97,'Caïdat Arbaa Taourirt','قيادة أربعاء تاوريرت','Al Hoceima','Provincial'),(98,'Caïdat Bni Hadifa','قيادة بني حذيفة','Al Hoceima','Provincial'),(99,'Caïdat Bni Abdellah','قيادة بني عبد الله','Al Hoceima','Provincial'),(100,'Caïdat Izemmouren','قيادة إزمورن','Al Hoceima','Provincial'),(101,'Caïdat Rouadi','قيادة الرواضي','Al Hoceima','Provincial'),(102,'Caïdat Sidi Boutmime','قيادة سدي بوتميم','Al Hoceima','Provincial'),(103,'Caïdat Bni Ammart','قيادة بني عمارت','Al Hoceima','Provincial'),(104,'Caïdat Bni Bounssar','قيادة بني بونصار','Al Hoceima','Provincial'),(105,'Caïdat Ketama','قيادة كتامة','Al Hoceima','Provincial'),(106,'Caïdat Issaguen','قيادة إساكن','Al Hoceima','Provincial'),(107,'Caïdat Tabarrant','قيادة تبرانت','Al Hoceima','Provincial'),(108,'Caïdat Abdelghaya Souahel','قيادة عبد الغاية السواحل','Al Hoceima','Provincial'),(109,'Caïdat Bni Boufrah','قيادة بني بوفراح','Al Hoceima','Provincial'),(110,'Caïdat Bni Gmil','قيادة بني جميل','Al Hoceima','Provincial'),(111,'Communes d\'Al Hoceima','جماعات الحسيمة','Al Hoceima','Provincial'),(112,'Commune Al Hoceima','جماعة الحسيمة','Al Hoceima','Provincial'),(113,'Commune Imzouren','جماعة إمزورن','Al Hoceima','Provincial'),(114,'Commune Bni Bouayach','جماعة بني بوعياش','Al Hoceima','Provincial'),(115,'Commune Targuist','جماعة ترجيست','Al Hoceima','Provincial'),(116,'Commune Ajdir','جماعة أجدير','Al Hoceima','Provincial'),(117,'Commune Ait Youssef Ouali','جماعة آيت يوسف وعلي','Al Hoceima','Provincial'),(118,'Commune Louta','جماعة لوطا','Al Hoceima','Provincial'),(119,'Commune Nekor','جماعة النكور','Al Hoceima','Provincial'),(120,'Commune Tifarouine','جماعة تفروين','Al Hoceima','Provincial'),(121,'Commune Imrabten','جماعة امرابظن','Al Hoceima','Provincial'),(122,'Commune Arbiaa Taourirt','جماعة أربعاء تاوريرت','Al Hoceima','Provincial'),(123,'Commune Chakrane','جماعة شقران','Al Hoceima','Provincial'),(124,'Commune Bni Hadifa','جماعة بني جذيفة','Al Hoceima','Provincial'),(125,'Commune Zaouiat Sidi Abdelkader','جماعة زاوية سيدي عبد القادر','Al Hoceima','Provincial'),(126,'Commune Bni Abdellah','جماعة بني عبد الله','Al Hoceima','Provincial'),(127,'Commune Izemmouren','جماعة ازمورن','Al Hoceima','Provincial'),(128,'Commune Ait Kamra','جماعة آيت قمرة','Al Hoceima','Provincial'),(129,'Commune Rouadi','جماعة الرواضي','Al Hoceima','Provincial'),(130,'Commune Sidi Boutmim','جماعة سيدي بوتميم','Al Hoceima','Provincial'),(131,'Commune Bni Bchir','جماعة بني ابشير','Al Hoceima','Provincial'),(132,'Commune Zerkat','جماعة زرقت','Al Hoceima','Provincial'),(133,'Commune Bni Ammart','جماعة بني عمارت','Al Hoceima','Provincial'),(134,'Commune Sidi Bouzineb','جماعة سيدي بوزينب','Al Hoceima','Provincial'),(135,'Commune Bni Bounsar','جماعة بني بونصار','Al Hoceima','Provincial'),(136,'Commune Bni Ahmed Imogzen','جماعة بني احمد اموكزن','Al Hoceima','Provincial'),(137,'Commune Ketama','جماعة كتامة','Al Hoceima','Provincial'),(138,'Commune Tamsaout','جماعة تامساوت','Al Hoceima','Provincial'),(139,'Commune Issaguen','جاعة اساكن','Al Hoceima','Provincial'),(140,'Commune Moulay Ahmed Chrif','جماعة مولاي احمد الشريف','Al Hoceima','Provincial'),(141,'Commune Bni Bouchibet','جماعة بني بوشيبت','Al Hoceima','Provincial'),(142,'Commune Taghzout','جماعة تاغزوت','Al Hoceima','Provincial'),(143,'Commune Abdelghaya Souahel','جماعة عبد الغاية السواحل','Al Hoceima','Provincial'),(144,'Commune Bni Boufrah','جماعة بني بوفراح','Al Hoceima','Provincial'),(145,'Commune Snada','جماعة اسنادة','Al Hoceima','Provincial'),(146,'Commune Bni Guemil Mestassa','جماعة بني اجميل','Al Hoceima','Provincial'),(147,'Commune Bni Guemil Maksoline','جماعة بني اجميل مكصولين','Al Hoceima','Provincial'),(148,'Conseil Provincial','المجلس الإقليمي','Al Hoceima','Provincial'),(149,'Groupements de Communes','مجموعة الجماعات','Al Hoceima','Provincial'),(150,'Groupement Nkour Ghiss','مجموعة الجماعات النكور غيس','Al Hoceima','Provincial'),(151,'Groupement de communes \"Tanmia\"','مجموعة الجماعات التنمية','Al Hoceima','Provincial'),(152,'Groupement de communes \"Taâwoun\"','مجموعة الجماعات التعاون','Al Hoceima','Provincial'),(153,'Groupement de communes \"Tacharouk\"','مجموعة الجماعات التشارك','Al Hoceima','Provincial'),(154,'Pouvoir Exécutif (Gouvernement)','السلطة التنفيذية','Rabat','Central'),(155,'Ministère de l\'Agriculture, de la Pêche maritime, du Développement Rural et des Eaux et Forêts','وزارة الفلاحة والصيد البحري والتنمية القروية والمياه والغابات','Rabat','Central'),(156,'Ministère de l\'Aménagement du Territoire, de l\'Urbanisme, de l\'Habitat et de la Politique de la ville','وزارة اعداد التراب والتعميروالاسكان وسياسة المدينة','Rabat','Central'),(157,'Ministère de l\'Economie et des Finances','وزارة الاقتصاد والمالية','Rabat','Central'),(158,'Ministère de l\'Education Nationale, de la Formation Professionnelle, de l\'Enseignement Supérieur et de la Recherche Scientifique','وزارة التربية الوطنية والتكوين المهني والتعليم العالي والبحث العلمي','Rabat','Central'),(159,'Ministère de l\'Energie, des Mines et du Développement Durable','وزارة الطاقة والمعادن والتنمية المستدامة','Rabat','Central'),(160,'Ministère de l\'Equipement, du Transport, de la Logistique et de l\'Eau','وزارة التجهيز والنقل اللوجستيك والماء','Rabat','Central'),(161,'Ministère de l\'Industrie, de l\'Investissement, du Commerce et de l\'Economie Numérique','وزارة الصناعة والاستثمار والتجارة والاقتصاد الرقمي','Rabat','Central'),(162,'Ministère de l\'Intérieur','وزارة الداخلية','Rabat','Central'),(163,'Ministère de la Culture et de la Communication','وزارة الثقافة والتواصل','Rabat','Central'),(164,'Ministère de la Famille, de la Solidarité, de l\'Egalité et du Développement Social','وزارة الاسرة والتضامن والمساواة والتنمية الاجتماعية','Rabat','Central'),(165,'Ministère de la Jeunesse et des Sports','وزارة الشباب والرياضة','Rabat','Central'),(166,'Ministère de la Justice','وزارة العدل','Rabat','Central'),(167,'Ministère de la Santé','وزارة الصحة','Rabat','Central'),(168,'Ministère délégué chargé de l\'Administration de la Défense Nationale','الوزارة المنتدبة المكلفة بإدارة الدفاع الوطني','Rabat','Central'),(169,'Ministère délégué chargé de la Réforme de l\'Administration et de la Fonction Publique','الوزارة المنتدبة المكلفة بالإصلاح الإداري والوظيفة العمومية','Rabat','Central'),(170,'Ministère délégué chargé des Affaires Générales et de la Gouvernance','الوزارة المنتدبة المكلفة بالشؤون العامة والحكومة','Rabat','Central'),(171,'Ministère délégué chargé des Marocains Résidant à l\'Etranger et des Affaires de la Migration','الوزارة المنتدبة المكلفة بالمغاربة المقيمين بالخارج وشؤؤون الهجرة','Rabat','Central'),(172,'Ministère délégué chargé des Relations avec le Parlement et la Société civile, porte-parole du gouvernement','الوزارة المنتدبة المكلفة بالعلاقات مع البرلمان والمجتمع المدني الناطق الرسمي للحكومة','Rabat','Central'),(173,'Ministère des Affaires Etrangères et de la Coopération Internationale','الوزارة الشؤون الخارجية والتعاون الدولي','Rabat','Central'),(174,'Ministère des Droits de l\'Homme','وزارة حقوق الانسان','Rabat','Central'),(175,'Ministère des Habous et des Affaires Islamiques','وزارة الأحباس والشؤون الإسلامية','Rabat','Central'),(176,'Ministère du Tourisme, du Transport aérien, de l\'Artisanat et de l\'Economie Sociale','وزارة السياحة والنقل الجوي والصناعة التقليدية والإقتصاد الاجتماعي','Rabat','Central'),(177,'Chef du Gouvernement','رئيس الحكومة','Rabat','Central'),(178,'Secrétariat d\'Etat chargé de l\'Artisanat et de l\'Economie sociale','كتابة الدولة المكلفة بالصناعة التقليدية والإقتصاد الاجتماعي','Rabat','Central'),(179,'Secrétariat d\'Etat chargé de l\'Eau','كتابة الدولة المكلفة بالماء','Rabat','Central'),(180,'Secrétariat d\'Etat chargé de l\'Enseignement Supérieur et de la Recherche Scientifique','كتابة الدولة المكلفة المكلفة بالتعليم العالي والبحث العلمي','Rabat','Central'),(181,'Secrétariat d\'Etat chargé de l\'Habitat','كتابة الدولة المكلفة بالإسكان','Rabat','Central'),(182,'Secrétariat d\'Etat chargé de l\'Investissement','كتابة الدولة المكلفة بالإستثمار','Rabat','Central'),(183,'Secrétariat d\'Etat chargé de la Formation Professionnelle','كتابة الدولة المكلفة بالنكوين المهني','Rabat','Central'),(184,'Secrétariat d\'Etat chargé de la Pêche Maritime','كتابة الدولة المكلفة بالصيد البحري','Rabat','Central'),(185,'Secrétariat d\'Etat chargé du Développement Durable','كتابة الدولة المكلفة بالتنمية المستدامة','Rabat','Central'),(186,'Secrétariat d\'Etat chargé du Développement rural et des Eaux et Forêts','كتابة الدولة المكلفة بالتمية القروية والمياه والغابات','Rabat','Central'),(187,'Secrétariat d\'Etat chargé du Transport','كتابة الدولة المكلفة بالنقل','Rabat','Central'),(188,'Secrétariat d\'Etat chargé du Commerce Extérieur','كتابة الدولة المكلفة بالتجارة الخارجية','Rabat','Central'),(189,'Secrétariat d\'Etat chargé du Tourisme','كتابة الدولة المكلفة بالسياحة','Rabat','Central'),(190,'Secrétariat Général du gouvernement','الأمانة العامة للحكومة','Rabat','Central'),(191,'Pouvoir Législatif','السلطة التشريعية','Rabat','Central'),(192,'Parlement-Conseil des Députés','البرلمان - مجلس النواب','Rabat','Central'),(193,'Parlement-Conseil des Conseillers','البرلمان - مجلس المستشارين','Rabat','Central'),(194,'Services Techniques Régionaux','المصالح التقنية الجهوية','Rabat','Central'),(195,'APDN','وكالة تنمية أقاليم الشمال','Tanger','Régional'),(196,'Cour Régionale des Comptes','المجلس الجهوي للحسابات','Tanger','Régional'),(197,'Délégation Régionale du Tourisme','السياحة (المصلحة الجهوية)','Tanger','Régional'),(198,'Académie Régionale de l\'Education Nationale','الأكاديمية الجهوية للتربية والتكوين جهة طنجة-تطوان-الحسيمة','Tanger','Régional'),(199,'Direction Régionale de l\'Environnement','المديرية الجهوية للبيئة لجهة طنجة-تطوان-الحسيمة','Tanger','Régional'),(200,'Institution Médiateur (Délégation Régionale)','المندوبية الجهوية لمؤسسة وسيط المملكة جهة طنجة-تطوان-الحسيمة','Tanger','Régional'),(201,'Al Omrane','شركة العمران فاس','Fès','Régional'),(202,'Institut National de la Recherche Pêche Maritime','المعهد الوطني للبحث في الصيد البحري','Nador','Régional'),(203,'Chambre de L\'industrie, du Commerce et des Services','غرفة الصناعة والتجارة والخدمات طنجة-تطوان-الحسيمة','Tanger','Régional'),(204,'Chambre Agricole','الغرفة الفلاحية لجهة طنجة-تطوان-الحسيمة','Larache','Régional'),(205,'ABHL-Tétouan','وكالة الحوض المائي اللكوس - تطوان','Tétouan','Régional'),(206,'ABH Sbou-Fès','وكالة الحوض المائي - فاس','Fès','Régional'),(207,'Conservation Régionale du Patrimoine','المحافظة على المآثر التاريخية','Tanger','Régional'),(208,'Société Maroc Soir','شركة Maroc Soir','Tanger','Régional'),(209,'Impôts-Service Régional du Foncier','المديرية الجهوية للضرائب -المصلحة الجهوية للوعاء العقاري','Tétouan','Régional'),(210,'ONEE-Branche Eau','\"المديرية الجهوية للمنطقة الوسطى الشمالية للمكتب الوطني للكهرباء والماء ص ش\nقطاع الماء\"','Tanger','Régional'),(211,'ONEE-Branche Eléctricité','\"المديرية الجهوية للمنطقة الوسطى الشمالية للمكتب الوطني للكهرباء والماء ص ش\nقطاع الكهرباء\"','Tanger','Régional'),(212,'Office National Interprofessionnel des Céréales et Légumineuses','المكتب الوطني للحبوب والقطاني','Taza','Régional'),(213,'ONEE Central (Branche Eau)','المكتب الوطني للكهرباء والماء الصالح للشرب - قطاع الماء','Rabat','Central'),(214,'ONEE Central (Branche Electricité)','المكتب الوطني للكهرباء والماء الصالح للشرب -قطاع الكهرباء -','Casablanca','Central'),(215,'Agence Nationale d\'Assurance Médicale','الوكالة الوطنية للتأمين الصحي','Rabat','Central'),(216,'Direction de La Documentation Royale','مديرية الوثائق الملكية','Rabat','Central'),(217,'FRMF','الجامعة الملكية المغربية لكرة القدم','Rabat','Central'),(218,'Fondation Hassan II Agents d\'Autorité','مؤسسة الحسن الثاني للرعاية الاجتماعية لرجال السلطة','Rabat','Central'),(219,'Caisse Marocaine de Retraite','الصندوق المغربي للتقاعد','Rabat','Central'),(220,'Trésorier Ministèriel (MI)','الخازن الوزاري المعتمد لدى وزارة الداخلية','Rabat','Central'),(221,'CNOPS','الصندوق الوطني للضمان الاجتماعي','Rabat','Central'),(222,'MGPAP','التعاضدية','Rabat','Central'),(223,'ALEM','وكالة المساكن والتجهيزات العسكرية','Rabat','Central'),(224,'Wilayas des Régions','ولايات المملكة','Divers','Régional'),(225,'Wilaya de la Région Tanger-Tétouan-Al Hoceïma','ولاية جهة طنجة -تطوان- الحسيمة','Tanger','Régional'),(226,'Wilaya de la Région L\'Oriental','ولاية الجهة الشرقية','Oujda','Régional'),(227,'Wilaya de la Région Fès-Meknès','ولاية جهة فاس-مكناس','Fès','Régional'),(228,'Wilaya de la Région Rabat-Salé-Kénitra','ولاية جهة الرباط-سلا-القنيطرة','Rabat','Régional'),(229,'Wilaya de la Région Béni Mellal-Khénifra','ولاية جهة بني ملال-خنيفرة','Bni Mellal','Régional'),(230,'Wilaya de la Région Casablanca-Settat','ولاية جهة الدارالبيضاء -سطات','Casablanca','Régional'),(231,'Wilaya de la Région Marrakech-Safi','ولاية جهة مراكش-أسفي','Marrakech','Régional'),(232,'Wilaya de la Région Drâa-Tafilalet','ولاية جهة درعة-تافيلالت','Rachidia','Régional'),(233,'Wilaya de la Région Souss-Massa','ولاية جهة سوس-ماسة','Agadir','Régional'),(234,'Wilaya de la Région Guelmim-Oued Noun','ولاية جهة كلميم-واد النون','Guelmim','Régional'),(235,'Wilaya de la Région Laâyoune-Sakia El Hamra','ولاية جهة العيون-الساقية الحمراء','Laâyoune','Régional'),(236,'Wilaya de la Région Dakhla-Oued Ed Dahab','ولاية جهة الداخلة-واد الذهب','Oued Dahab','Régional'),(237,'Conseils des Régions','المجالس الجهوية بالمملكة','Divers','Régional'),(238,'Conseil Régional Tanger-Tétouan-Al Hoceïma','المجلس الإقليمي لجهة طنجة -تطوان- الحسيمة','Tanger','Provincial'),(239,'Conseil Régional L\'Oriental','المجلس الإقليمي للجهة الشرقية','Oujda','Provincial'),(240,'Conseil Régional Fès-Meknès','المجلس الإقليمي لجهة فاس-مكناس','Fès','Provincial'),(241,'Conseil Régional Rabat-Salé-Kénitra','المجلس الإقليمي لجهة الرباط -سلا- القنيطرة','Rabat','Provincial'),(242,'Conseil Régional Béni Mellal-Khénifra','المجلس الإقليمي لجهة بني ملال-خنيفرة','Bni Mellal','Provincial'),(243,'Conseil Régional Casablanca-Settat','المجلس الإقليمي لجهة الدارالبيضاء-سطات','Casablanca','Provincial'),(244,'Conseil Régional Marrakech-Safi','المجلس الإقليمي لجهة مراكش-أسفي','Marrakech','Provincial'),(245,'Conseil Régional Drâa-Tafilalet','المجلس الإقليمي لجهة درعة - تافيلالت','Rachidia','Provincial'),(246,'Conseil Régional Souss-Massa','المجلس الإقليمي لجهة سوس - ماسة','Agadir','Provincial'),(247,'Conseil Régional Guelmim-Oued Noun','المجلس الإقليمي لجهة كلميم-واد النون','Guelmim','Provincial'),(248,'Conseil Régional Laâyoune-Sakia El Hamra','المجلس الإقليمي لجهة العيون الساقية الحمراء','Laâyoune','Provincial'),(249,'Conseil Régional Dakhla-Oued Ed Dahab','المجلس الإقليمي لجهة الداخلة - واد الذهب','Oued Dahab','Provincial'),(250,'Provinces et Préfectures','العمالات والأقاليم','Divers','Provincial'),(251,'Préfecture de Tanger-Assilah','عمالة طنجة - أصيلة','Tanger','Provincial'),(252,'Préfecture de M\'diq-Fnideq','عمالة المضيق - الفنيدق','M\'Diq','Provincial'),(253,'Province de Tétouan','عمالة تطوان','Tétouan','Provincial'),(254,'Province d\'Al Hoceima','عمالة الحسيمة','Al Hoceima','Provincial'),(255,'Province de Fahs-Anjra','عمالة الفحص أنجرة','Fahs-Anjra','Provincial'),(256,'Province de Larache','عمالة العرائش','Larache','Provincial'),(257,'Province de Chefchaouen','عمالة شفشاون','Chefchaoun','Provincial'),(258,'Province d\'Ouezzane','عمالة وزان','Ouezzane','Provincial'),(259,'Préfecture d\'Oujda-Angad','عمالة وجدة أنجاد','Oujda','Provincial'),(260,'Province de Nador','عمالة الناظور','Nador','Provincial'),(261,'Province de Driouch','عمالة الدريوش','Driouch','Provincial'),(262,'Province de Jerada','عمالة جرادة','Jerada','Provincial'),(263,'Province de Berkane','عمالة بركان','Berkane','Provincial'),(264,'Province de Taourirt','عمالة تاوريرت','Taourirt','Provincial'),(265,'Province de Guercif','عمالة جرسيف','Guercif','Provincial'),(266,'Province de Figuig','عمالة فجيج','Figuig','Provincial'),(267,'Préfecture de Fès','عمالة فاس','Fès','Provincial'),(268,'Préfecture de Meknès','عمالة مكناس','Meknès','Provincial'),(269,'Province d\'El Hajeb','عمالة الحاجب','El Hajeb','Provincial'),(270,'Province d\'Ifrane','عمالة إفران','Ifrane','Provincial'),(271,'Province de Moulay Yaâcoub','عمالة مولاي يعقوب','Moulay Yaâcoub','Provincial'),(272,'Province de Séfrou','عمالة صفرو','Séfrou','Provincial'),(273,'Province de Boulemane','عمالة بولمان','Boulemane','Provincial'),(274,'Province de Taounate','عمالة تاونات','Taounate','Provincial'),(275,'Province de Taza','عمالة تازة','Taza','Provincial'),(276,'Préfecture de Rabat','عمالة الرباط','Rabat','Provincial'),(277,'Préfecture de Salé','عمالة سلا','Salé','Provincial'),(278,'Préfecture de Skhirate-Témara','عمالة الصخيرات -تمارة','Skhirate-Témara','Provincial'),(279,'Province de Kénitra','عمالة القنيطرة','Kénitra','Provincial'),(280,'Province de Khémisset','عمالة الخميسات','Khémisset','Provincial'),(281,'Province de Sidi Kacem','عمالة سدي قاسم','Sidi Kacem','Provincial'),(282,'Province de Sidi Slimane','عمالة سدي سليمان','Sidi Slimane','Provincial'),(283,'Province de Béni-Mellal','عمالة بني ملال','Bni Mellal','Provincial'),(284,'Province d\'Azilal','عمالة أزيلال','Azilal','Provincial'),(285,'Province de Fquih Ben Salah','عمالة الفقيه بن صالح','Fquih Ben Salah','Provincial'),(286,'Province de Khénifra','عمالة خنيفرة','Khénifra','Provincial'),(287,'Province de Khouribga','عمالة خريبكة','Khouribga','Provincial'),(288,'Préfecture de Casablanca','عمالة الدارالبيضاء','Casablanca','Provincial'),(289,'Préfecture de Mohammédia','عمالة المحمدية','Mohammédia','Provincial'),(290,'Province d\'El Jadida','عمالة الجديدة','El Jadida','Provincial'),(291,'Province de Nouaceur','عمالة النواصر','Nouacer','Provincial'),(292,'Province de Médiouna','عمالة مديونة','Médiouna','Provincial'),(293,'Province de Benslimane','عمالة بنسليمان','Benslimane','Provincial'),(294,'Province de Berrechid','عمالة برشيد','Berrachid','Provincial'),(295,'Province de Settat','عمالة سطات','Settat','Provincial'),(296,'Province de Sidi Bennour','عمالة سدي بنور','Sidi Bennour','Provincial'),(297,'Préfecture de Marrakech','عمالة مراكش','Marrakech','Provincial'),(298,'Province de Chichaoua','عمالة شيشاوة','Chichaoua','Provincial'),(299,'Province d\'Al Haouz','عمالة الحوز','Al Haouz','Provincial'),(300,'Province d\'El Kelaâ des Sraghna','عمالة السراغنة','El Kelaâ Des Sraghna','Provincial'),(301,'Province d\'Essaouira','عمالة الصويرة','Essaouira','Provincial'),(302,'Province de Rehamna','عمالة الرحامنة','Rehamna','Provincial'),(303,'Province de Safi','عمالة أسفي','Safi','Provincial'),(304,'Province de Youssoufia','عمالة اليوسفية','Youssoufia','Provincial'),(305,'Province d\'Errachidia','عمالة الراشدية','Rachidia','Provincial'),(306,'Province de Ouarzazate','عمالة ورززات','Ouarzazate','Provincial'),(307,'Province de Midelt','عمالة ميدلت','Midelt','Provincial'),(308,'Province de Tinghir','عمالة تنغير','Tinghir','Provincial'),(309,'Province de Zagora','عمالة زاكورة','Zagora','Provincial'),(310,'Préfecture d\'Agadir Ida-Outanane','عمالة إداوتنان','Agadir','Provincial'),(311,'Préfecture d\'Inezgane-Aït Melloul','عمالة أيت ملول','Inezgane','Provincial'),(312,'Province de Chtouka-Aït Baha','عمالة أيت باهة','Chtouka-Aït Baha','Provincial'),(313,'Province de Taroudant','عمالة تارودانت','Taroudant','Provincial'),(314,'Province de Tiznit','عمالة تزنيت','Tiznit','Provincial'),(315,'Province de Tata','عمالة طاطا','Tata','Provincial'),(316,'Province de Guelmim','عمالة كلميم','Guelmim','Provincial'),(317,'Province d\'Assa-Zag','عمالة أسا الزاك','Assa-Zag','Provincial'),(318,'Province de Tan-Tan','عمالة طانطان','Tan-Tan','Provincial'),(319,'Province de Sidi Ifni','عمالة سدي إفني','Sidi Ifni','Provincial'),(320,'Province de Laâyoune','عمالة العيون','Laâyoune','Provincial'),(321,'Province de Boujdour','عمالة بوجدور','Boujdour','Provincial'),(322,'Province de Tarfaya','عمال طرفاية','Tarfaya','Provincial'),(323,'Province d\'Es-Semara','عمالة السمارة','Smara','Provincial'),(324,'Province d\'Oued Ed Dahab','عمالة واد الذهب','Oued Dahab','Provincial'),(325,'Province d\'Aousserd','عمالة أوسرد','Aousserd','Provincial'),(326,'Entreprises et Sociétés','المقاولات والشركات','Divers','Autre'),(327,'Citoyens','المواطنون والمواطنات','Divers','Autre'),(328,'Coopératives','التعاونيات','Divers','Autre'),(329,'Partis Politiques','الأحزاب السياسية','Divers','Autre'),(330,'Syndicats','النقابات','Divers','Autre'),(331,'Associations','الجمعيات','Divers','Autre'),(332,'Cellule Foncière','خلية العقار','Al Hoceima','Local'),(333,'Délégation Provinciale de l\'Agence Nationale de Lutte contre l\'Analphabétisme','الوكالة الوطنية لمحاربة الأمية','Al Hoceima','Provincial'),(334,'Commune Fnideq','جماعة الفنيدق','Tétouan','Provincial'),(335,'Commune Bni Dracoul','جماعة بني دراكول','Taza','Provincial'),(336,'Casablanca-Arrondissement Sbata','الدارالبيضاء - مقاطعة اسباتة','Casablanca','Provincial'),(337,'Service du contrôle et de la Protection des végétaux','مصلحة مراقبة النباتات','Al Hoceima','Provincial'),(338,'ONSSA','المصلحة البيطرية','Al Hoceima','Provincial'),(339,'ONEE-Branche Eau','المكتب الوطني للماء والكهرباء-قطاع الماء','Fès','Régional'),(340,'Aéroport  Charif El Idrissi','مطار الشريف الإدريسي بالحسيمة','Al Hoceima','Provincial'),(341,'Avocats','المحامون','Divers','Autre'),(342,'Consulat Général du Royaume du Maroc à Bruxelles','القنصلية العامة للمملكة المغربية  ببروكسيل','Bruxelles','Autre'),(343,'Consulat Général du Royaume du Maroc à Toulouse','قنصلية المملكة المغربية بتولوز','Toulouse','Autre'),(344,'Commune de Martil','جماعة مرتيل','M\'Diq','Provincial'),(345,'Fondation Mohammed VI pour la Protection de L\'Environnement','مؤسسة محمد السادس لحماية البيئة','Rabat','Central'),(346,'Prison Centrale','السجن  المركزي','Kénitra','Provincial'),(347,'Conseil Provincial du Tourisme','المجلس الإقليمي للسياحة','Al Hoceima','Provincial'),(348,'Tribunal de 1ère instance Larache','المحكمة الإبتدائية','Larache','Provincial'),(349,'Cour d\'Appel Rabat','محكمة الإستئناف','Rabat','Régional'),(350,'Préfecture de Police-Oujda','ولاية الأمن','Oujda','Régional'),(351,'Association de l\'industrie hôtelière','جمعية  الصناعة الفندقية','Al Hoceima','Provincial'),(352,'Prison Agricole Zaio','السجن الفلاحي بزايو','Nador','Provincial'),(353,'Prison Loacle Ras El Ma-Fès','السجن المحلي رأس الماء','Fès','Provincial'),(354,'Consulat Général du Royaume du Maroc à Anvers','القنصلية العامة للمملكة المغربية أنفيرس','Anvers','Autre'),(355,'Cour d\'Appel du Commerce Fès','محكمة الاستئناف التجارية','Fès','Régional'),(356,'Tribunal de 1ère instance Taza','المحكمة الابتدائية','Taza','Provincial'),(357,'Tribunal de 1ère instance Tétouan','المحكمة الابتدائية','Tétouan','Provincial'),(358,'Cour de Cassation','محكمة النقض','Rabat','Central'),(359,'PN','الإنعاش الوطني (المندوبية الإقليمية)','Al Hoceima','Provincial'),(360,'Pachalik Tétouan','باشوية تطوان','Tétouan','Provincial'),(361,'Agence Nationale Conservation Foncière, Cadastre & Cartographie (ANCFCC)','المحافظة العامة للأملاك العقارية','Rabat','Central'),(362,'Commission Régionale des Droits de l\'Homme-Section Al Hoceima','اللجنة الجهوية لحقوق الإنسان-فرع الحسيمة','Al Hoceima','Provincial'),(363,'Conseil Régional des Architectes (Oriental-Al Hoceima)','المجلس الجهوي للمهندسين المعماريين-الشرق الحسيمة','Nador','Régional'),(364,'Ecole Nationale des Sciences Appliquées (ENSAH)','المدرسة الوطنية للعلوم التطبيقية','Al Hoceima','Provincial'),(365,'Haut Commissariat Au Plan (HCP) Tanger','المندوبية السامية للتخطيط','Tanger','Régional'),(366,'Conseil Régional des Architectes (Tanger)','المجلس الجهوي للمهندسين المعماريين-طنجة','Tanger','Régional'),(367,'Direction Régionale de la Culture','المديرية الجهوية للثقافة','Tanger','Régional'),(368,'Préfecture de Police-Fès','ولاية الأمن','Fès','Régional'),(369,'Service de la Conservation Foncière - Tétouan','مصلحة المحافظة العقارية','Tétouan','Provincial'),(370,'Forces Armées Royales','القوات المسلحة الملكية','Rabat','Central'),(371,'Consulat Général du Maroc à Barcelone','القنصلية العامة للمملكة المغربية ببرشلونة','Barcelone','Autre'),(372,'FEC (Fonds d\'Equipement Communal)','صندوق التجهيز الجماعي','Rabat','Central'),(373,'Tribunal Administratif Fès','المحكمة الإدارية','Fès','Régional'),(374,'Consulat Général du Maroc à Lille','القنصلية العامة للمملكة المغربية بليل','Lille','Autre'),(375,'Consulat Général du Maroc à Bilbao','القنصلية العامة للمملكة المغربية ببيلباو','Bilbao','Autre'),(376,'Consulat Général du Maroc à Dusseldorf','القنصلية العامة للمملكة المغربية بدوسلدورف','Dusseldorf','Autre'),(377,'Consulat Général du Maroc à Orléans','القنصلية العامة للمملكة المغربية أرليان','Orléans','Autre'),(378,'Consulat Général du Maroc à Marseille','القنصلية العامة للمملكة المغربية بمارسيليا','Marseille','Autre'),(379,'Caisse de Dépôt et de Gestion','صندوق الإيداع والتدبير','Rabat','Central'),(380,'Cour d\'Appel Administratif de Rabat','محكمة الاستئناف الإدارية بالرباط','Rabat','Régional'),(381,'Consulat Général du Maroc à Oran','القنصلية العامة للمملكة المغربية بوهران','Oran','Autre'),(382,'Consulat Général du Maroc à Madrid','القنصلية العامة للمملكة المغربية بمدريد','Madrid','Autre'),(383,'Facultée des Sciences de Tétouan','كلية العلوم بتطوان','Tétouan','Régional'),(384,'Tribunal de 1ère instance Séfrou','المحكمة الابتدائية','Séfrou','Provincial'),(385,'Institut Technique Agricole au Littoral boutaher à Taounate','المعهد التقني الفلاحي بساحل بوطاهر','Taounate','Provincial'),(386,'Prison Locale à Taounate','السجن المحلي','Taounate','Provincial'),(387,'OFPPT Al Hoceima','مكتب التكوين المهني','Al Hoceima','Provincial'),(388,'Corps de la paix des états unis','هيئة السلام للولايات المتحدة الامريكية','Rabat','Central'),(389,'tribunal 1° instance-Chefchaoun','المحكمة الابتدائية','Chefchaoun','Central'),(390,'directeur d\'agence nationale des ports','مدير الوكالة الوطنية للموانئ','Al Hoceima','Local'),(391,'Consulat Général de l\'Espagne à Nador','القنصلية العامة لإسبانيا بالناظور','Nador','Autre'),(392,'Délégation Régionale de CMR-Tétouan','المندوبية الجهوية للصندوق المغربي للتقاعد بتطوان','Tétouan','Régional'),(393,'Direction Régionale d\'Administration des Etablissements pénitentiaires et de la Réinsertion','المديرية الجهوية لإدارة السجون وإعادة الإدماج','Tanger','Régional'),(394,'SNTL','الشركة الوطنية للنقل والوسائل اللوجيستيكية','Rabat','Central'),(395,'Fondation Mohammed VI pour la Promotion des Oeuvres Sociales des Préposés Religieux - Al Hoceima','مؤسسة محمد السادس للنهوض بالأعمال الاجتماعية للقيمين الدينيين - الحسيمة','Al Hoceima','Provincial'),(396,'BANK AL-MAGHRIB','بنك المغرب','Al Hoceima','Provincial'),(397,'Prison Locale Toulal 1 Meknès','السجن المحلي تولال 1 بمكناس','Meknès','Provincial'),(398,'ONSSA Tanger','المكتب الوطني للسلامة الصحية للمنتجات الغذائية','Tanger','Régional'),(399,'Directeur Provincial du Département de L\'Energie','المدير الإقليمي للطاقة والمعادن','Al Hoceima','Provincial'),(400,'Chambre d\'Artisanat Région Tanger Tétouan AlHociema','غرفة الصناعة التقليدية','Tanger','Régional'),(401,'Chambre d\'Artisanat Région Tanger Tetoun AlHoceima','غرفة الصناعة التقليدية جهة طنجة تطوان الحسيمة','Tanger','Régional'),(402,'Centre d\'Information Adminitratif','مركز التكوين الإداري','Fès','Provincial'),(403,'FONDATION Marocaine Pour la Promotion de L\'enseignement Préscolaire','المؤسسة المغربية للنهوض بالتعليم الأولي','Al Hoceima','Provincial'),(404,'Œuvre de Mutualité Fonctionnaires et Agents Assimilés du Maroc \"OMFAM\"','الهيآت التعاضدية لموظفي الإدارات والمصالح العمومية بالمغرب','Casablanca','Central'),(405,'Le gouverneur, Directeur de la promotion national','العامل مدير الإنعاش الوطني','Rabat','Central'),(406,'Agence Régionale d\'Exécution des Projets','الوكالة الجهوية لتنفيذ المشاريع','Tanger','Régional'),(407,'tribunal de 1ère Instance targuist','وكيل الملك بالمحكمة الابتدائية بتارجيست','Targuist','Provincial'),(408,'Agence de développement social','وكالة التنمية الإجتماعية','Al Hoceima','Provincial'),(409,'Cour D\'appel de Tétouan','محكمة الإستئناف بتطوان','Tétouan','Régional'),(410,'Directeur Barid al Maghreb','مدير بريد المغرب','Al Hoceima','Provincial'),(411,'Consulat Général du Royaume du Maroc à Rotterdam','القنصلية العامة للمملكة المغربية روطردام','Pays bas','Autre'),(412,'consulat Général du Royaume du Maroc à Villemomble','القنصلية العامة للمملكة المغربية قيل مومبل','Villemomble','Autre'),(413,'Consulat Général du Royaume du Maroc à Amsterdam','القنصلية العامة للمملكة المغربية أمستردام','Amsterdam','Autre'),(414,'consulat Général du Royaume du Maroc à U.S.A','القنصلية العامة للمملكة المغربية نيويورك','U.S.A','Autre'),(415,'consulat Général du Royaume du Maroc à Montpellier','القنصلية العامة للمملكة المغربية مونبوليي','Montpellier','Autre'),(416,'consulat Général du Royaume du Maroc à Tarragona','القنصلية العامة للمملكة المغربية تاراغونا','Tarragona','Autre'),(417,'Le Directeur de la societe Nationale Des transports','مدير شركة الوطنية للنقل','Rabat','Central'),(418,'tribunal de 1ère Instance de Fès','المحكمة الإبتدائية بفاس','Fès','Autre'),(419,'Tribunal de 1ère Instance de Nador','المحكمة الإبتدائية بالناظور','Nador','Local'),(420,'ONEE','المكتب الوطني للكهرباء والماء الصالح للشرب بوجدة','Oujda','Local'),(421,'Consulat Général du Royaume du Maroc à Denbosch','القنصلية العامة للمملكة المغربية دين بوش','Denbosch','Autre'),(422,'OMFAM','الصندوق الوطني لمنظمات الاحتياط الاجتماعي','Casablanca','Autre'),(423,'Cour d\'appel de Kénitra','محكمة الإستئناف بالقنيطرة','Kénitra','Autre'),(424,'Cour d\'appel d\'oujda','محكمة الإستئناف بوجدة','Oujda','Autre'),(425,'Consulat Général du Royaume du Maroc à Utrecht','القنصلية العامة للمملكة المغربية أوتيخت','Pays bas','Autre'),(426,'Consulat Général du Ryaume du Maroc à Lyon','القنصلية العامة للمملكة المغربية ليون','France','Autre'),(427,'Commune Bab berrad','جماعة باب برد','Chefchaoun','Autre'),(428,'Consulat Général du Royaume du Maroc à Pontoise','القنصلية العامة للمملكة المغربية ببونطواز','France','Autre'),(429,'Consulat Général du Royaume du Maroc à Oslo','القنصلية العامة للمملكة المغربية أوسلو','Norvége','Autre'),(430,'Al Omrane Tanger','العمران بطنجة','Tanger','Régional'),(431,'Tribunal de 1ère Instance de Salé','المحكمة الابتدائية بسلا','Salé','Autre'),(432,'Office National Interprofessionnel des céréales et des Légumineuses à Nador','المكتب الوطني المهني للحبوب والقطاني بالناظور','Nador','Autre'),(433,'Tribunal de 1ére Instance Driouch','المحكمة الإبتدائية بالدريوش','Driouch','Autre'),(434,'Direction Régionale de la Santé à la région de Tanger- Tétouan-Al Hoceima','المديرية الجهوية للصحة لجهة طنجة-تطوان-الحسيمة','Tanger','Régional'),(435,'LePrésident du Conseil National de l\'Ordre National des Médecins','رئيس المجلس الوطني للأطباء بالرباط','Rabat','Central'),(436,'Caïdat Bni Taouzin et Tefarsit','قيادة بني توزين وتفرسيت','Driouch','Autre'),(437,'Le Président du Conseil National de L\'Ordre National des Médecins à Tanger','رئيس المجلس الوطني للأطباء لجهة طنجة -تطوان - الحسيمة بطنجة','Tanger','Régional'),(438,'Direction de Centre Régional D\'Investissement à Tanger','مدير المركز الجهوي للإستثمار لجهة طنجة - تطوان - الحسيمة - بطنجة','Tanger','Régional'),(439,'Consulat Général du Royaume du Maroc à Mallorca','القنصلية العامة للملكة المغربية بمايوركا','Mallorca','Autre'),(440,'Commune Tarfaya','رئيس جماعة طرفاية','Tarfaya','Autre'),(441,'SNRT','الشركة الوطنية للإذاعة والتلفزة','Al Hoceima','Local'),(442,'Consulat Général de Ryaume de Napoli','القنصل العام للمملكة المغربية بنابولي','Italy','Autre'),(443,'Consulat Général du Royaume du Maroc Liège','القنصلية العامة للمملكة المغربية بلييج','Belgigue','Autre'),(444,'Direction Régionale de Maroc Télécom','المديرية الجهوية لاتصالات المغرب بطنجة','Tanger','Régional'),(445,'Agence Nationale de Lutte Contre l\'Analphabétisme','الوكالة الوطنية لمحاربة الأمية','Al Hoceima','Provincial'),(446,'Consulat Général du Ryaume du Maroc à London','القنصلية العامة للمملكة المغربية لندن','Britain','Autre'),(447,'Centre De Formation Aminisrative Al Hoceima','مركز التكوين الإداري بالحسيمة','Al Hoceima','Provincial'),(448,'Directeur de l\'Institut National de la Recherche Agronomique','مدير المعهد الوطني للبحث الزراعي','Rabat','Central'),(449,'Le Directeur Regional De L\'Agence Nationale Des Equipements Publics Du Nord','المديرية الجهوية للوكالة الوطنية للتجهيزات العامة بالشمال','Tanger','Régional'),(450,'Institut National De Recherche Halieutique','المعهد الوطني للبحث في الصيد البحري','Casablanca','Central'),(451,'Consulat General du Royaume du Copenhagen','القنصلية العامة للمملكة المغربية بكوبنهاغن','Danemark','Autre'),(452,'Percepteur communal','القابض الجماعي','Al Hoceima','Provincial'),(453,'Délégation provincial de la transition energetique et du développement durable -Section de Transistion Energetique al Hoceima','المندوبية الإقليمية للانتقال الطاقي و التنمية المستدامة- قطاع الانتقال الطاقي بالحسيمة','Al Hoceima','Provincial'),(454,'Délégation provinciale de la transition energetique et du développement durable -Section de Transistion Energetique Al Hoceima','المندوبية الإقليمية للإنتقال الطاقي و التنمية المستدامة - قطاع الانتقال الطاقي','Al Hoceima','Provincial');
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_entries`
--

DROP TABLE IF EXISTS `personal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe_destinataire` varchar(255) DEFAULT NULL,
  `nom_complet` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `tel` varchar(50) DEFAULT NULL,
  `date_envoi` date DEFAULT NULL,
  `num_ordre` int(11) DEFAULT NULL,
  `date_arrivee` date DEFAULT NULL,
  `objet` text DEFAULT NULL,
  `division` varchar(255) DEFAULT NULL,
  `important` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_ordre` (`num_ordre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_entries`
--

LOCK TABLES `personal_entries` WRITE;
/*!40000 ALTER TABLE `personal_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personale`
--

DROP TABLE IF EXISTS `personale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe` varchar(50) NOT NULL,
  `nom_complet` varchar(100) NOT NULL,
  `province` varchar(50) NOT NULL,
  `adresse` text NOT NULL,
  `tel` varchar(20) NOT NULL,
  `date_envoi` date NOT NULL,
  `numero_ordre` int(11) NOT NULL,
  `date_arrive` date NOT NULL,
  `objet` text NOT NULL,
  `division` varchar(50) NOT NULL,
  `important` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_ordre` (`numero_ordre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personale`
--

LOCK TABLES `personale` WRITE;
/*!40000 ALTER TABLE `personale` DISABLE KEYS */;
INSERT INTO `personale` VALUES (2,'Syndicats (النقابات)','20','Tanger','20','0612345678','2025-05-06',20,'2025-05-06','20','Cabinet',1,'2025-05-06 13:05:59'),(4,'Partis Politiques (الأحزاب السياسية)','2','Al Hoceima','2','2','2025-05-24',3,'2025-05-19','1','2',1,'2025-05-06 17:32:04');
/*!40000 ALTER TABLE `personale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provinces`
--

DROP TABLE IF EXISTS `provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provinces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provinces`
--

LOCK TABLES `provinces` WRITE;
/*!40000 ALTER TABLE `provinces` DISABLE KEYS */;
/*!40000 ALTER TABLE `provinces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `signataire`
--

DROP TABLE IF EXISTS `signataire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signataire` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `signataire`
--

LOCK TABLES `signataire` WRITE;
/*!40000 ALTER TABLE `signataire` DISABLE KEYS */;
INSERT INTO `signataire` VALUES (1,'Chourak Farid Gouverneur'),(2,'Badraoui Mohammed SG'),(3,'El Hattach Abdeslam Chef DAE'),(4,'Ouami Abdelaziz Chef Cabinet'),(5,'El Kandoussi Abdelmajid Chef DAI'),(6,'Degouj Driss SG'),(7,'Dahou Hicham Chef DAI'),(8,'Zitouni Hassan Gouverneur');
/*!40000 ALTER TABLE `signataire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_number` int(11) NOT NULL,
  `destinataire` varchar(255) NOT NULL,
  `date_envoi` date NOT NULL,
  `num_ordre` int(11) NOT NULL,
  `date_arrivee` date NOT NULL,
  `objet` text NOT NULL,
  `division` varchar(255) NOT NULL,
  `important` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `destinataire_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_ordre` (`num_ordre`),
  UNIQUE KEY `num_ordre_2` (`num_ordre`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submissions`
--

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
INSERT INTO `submissions` VALUES (11,5,'Avocats (المحامون)','2025-05-12',5,'2025-05-26','5','DE',1,'2025-05-06 16:42:59',9,16),(13,1,'','2025-05-20',3,'2025-05-13','10','',1,'2025-05-06 16:47:38',3,6),(14,1200,'','1010-10-10',90,'2020-12-12','11','',1,'2025-05-12 11:12:36',38,15);
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `villes`
--

DROP TABLE IF EXISTS `villes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `villes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `villes`
--

LOCK TABLES `villes` WRITE;
/*!40000 ALTER TABLE `villes` DISABLE KEYS */;
INSERT INTO `villes` VALUES (1,'Al Hoceima'),(2,'Tanger'),(3,'Rabat'),(4,'Casablanca'),(5,'Fès'),(6,'Agadir'),(7,'Bni Mellal'),(8,'Divers'),(9,'Guelmim'),(10,'Laâyoune'),(11,'Marrakech'),(12,'Oued Dahab'),(13,'Oujda'),(14,'Rachidia'),(15,'Al Haouz'),(16,'Aousserd'),(17,'Assa-Zag'),(18,'Azilal'),(19,'Benslimane'),(20,'Berkane'),(21,'Berrachid'),(22,'Boujdour'),(23,'Boulemane'),(24,'Chefchaoun'),(25,'Chichaoua'),(26,'Chtouka-Aït Baha'),(27,'Driouch'),(28,'El Hajeb'),(29,'El Jadida'),(30,'El Kelaâ Des Sraghna'),(31,'Essaouira'),(32,'Fahs-Anjra'),(33,'Figuig'),(34,'Fquih Ben Salah'),(35,'Guercif'),(36,'Ifrane'),(37,'Inezgane'),(38,'Jerada'),(39,'Kénitra'),(40,'Khémisset'),(41,'Khénifra'),(42,'Khouribga'),(43,'Larache'),(44,'M\'Diq'),(45,'Médiouna'),(46,'Meknès'),(47,'Midelt'),(48,'Mohammédia'),(49,'Moulay Yaâcoub'),(50,'Nador'),(51,'Nouacer'),(52,'Ouarzazate'),(53,'Ouezzane'),(54,'Rehamna'),(55,'Safi'),(56,'Salé'),(57,'Séfrou'),(58,'Settat'),(59,'Sidi Bennour'),(60,'Sidi Ifni'),(61,'Sidi Kacem'),(62,'Sidi Slimane'),(63,'Skhirate-Témara'),(64,'Smara'),(65,'Tan-Tan'),(66,'Taounate'),(67,'Taourirt'),(68,'Tarfaya'),(69,'Taroudant'),(70,'Tata'),(71,'Taza'),(72,'Tétouan'),(73,'Tinghir'),(74,'Tiznit'),(75,'Youssoufia'),(76,'Zagora'),(77,'Bruxelles'),(78,'Toulouse'),(79,'Anvers'),(80,'Barcelone'),(81,'Madrid'),(82,'Lille'),(83,'Bilbao'),(84,'Dusseldorf'),(85,'Orléans'),(86,'Marseille'),(87,'Oran'),(88,'Targuist'),(89,'Bays bas'),(90,'Pays bas'),(91,'Villemomble'),(92,'Amsterdam'),(93,'U.S.A'),(94,'Montpellier'),(95,'Tarragona'),(96,'Denbosch'),(97,'يثىلا'),(98,'France'),(99,'Norvége'),(100,'Mallorca'),(101,'Italy'),(102,'Belgigue'),(103,'Bilgigue'),(104,'Britain'),(105,'Danemark'),(106,'TETOUAN'),(108,'tstssssss');
/*!40000 ALTER TABLE `villes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visa`
--

DROP TABLE IF EXISTS `visa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` int(11) DEFAULT NULL,
  `date_envoi` date DEFAULT NULL,
  `departement_origine` varchar(255) DEFAULT NULL,
  `departement_cible` varchar(255) DEFAULT NULL,
  `n_reception` int(11) DEFAULT NULL,
  `date_reception` date DEFAULT NULL,
  `num_ordre` int(11) DEFAULT NULL,
  `date_depart` date DEFAULT NULL,
  `objet` text DEFAULT NULL,
  `division` varchar(255) DEFAULT NULL,
  `signataire_province` varchar(255) DEFAULT NULL,
  `observations` varchar(255) DEFAULT NULL,
  `important` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_ordre` (`num_ordre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visa`
--

LOCK TABLES `visa` WRITE;
/*!40000 ALTER TABLE `visa` DISABLE KEYS */;
INSERT INTO `visa` VALUES (6,2,'0001-11-11','unknoen','unknoen',2,'0000-00-00',2,'0001-11-11','2','DSIC','unknoen','unknoen',1);
/*!40000 ALTER TABLE `visa` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-20 17:35:09
