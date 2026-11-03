-- =====================================================================
-- SMCBI School Clinic Management System - database export (clinicdb)
-- IT 315 Final Examination submission
--
-- HOW TO IMPORT (XAMPP):
--   1. Start Apache and MySQL in the XAMPP Control Panel.
--   2. Open http://localhost/phpmyadmin and create a database named  clinicdb
--   3. Select clinicdb -> Import -> choose this file -> Import.
--   4. Open http://localhost/school-clinic/
--   5. Sign in: clinic@smcbi.edu.ph / Admin@12345
--      (the system asks for a new password right after the first sign-in)
--
-- Contents: all 11 tables (current schema, version 5), the default
-- administrator account, and 40 sample medicines/supplies with their
-- stock transactions. No patient records are included.
-- (Importing is optional: the tables are also created automatically
--  the first time the system opens an empty clinicdb database.)
-- =====================================================================

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: clinicdb
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
-- Table structure for table `app_meta`
--

DROP TABLE IF EXISTS `app_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_meta` (
  `k` varchar(50) NOT NULL,
  `v` varchar(255) NOT NULL,
  PRIMARY KEY (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_meta`
--

LOCK TABLES `app_meta` WRITE;
/*!40000 ALTER TABLE `app_meta` DISABLE KEYS */;
INSERT INTO `app_meta` VALUES ('schema_version','5');
/*!40000 ALTER TABLE `app_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(150) DEFAULT NULL,
  `event` varchar(60) NOT NULL,
  `outcome` varchar(20) NOT NULL DEFAULT 'success',
  `details` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(45) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clinic_visits`
--

DROP TABLE IF EXISTS `clinic_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinic_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `attended_by` int(11) NOT NULL,
  `visit_date` datetime NOT NULL DEFAULT current_timestamp(),
  `chief_complaint` text NOT NULL,
  `temperature` varchar(20) DEFAULT NULL,
  `blood_pressure` varchar(30) DEFAULT NULL,
  `pulse_rate` varchar(20) DEFAULT NULL,
  `assessment` text DEFAULT NULL,
  `treatment_given` text DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `referred_to_hospital` enum('Yes','No') NOT NULL DEFAULT 'No',
  `guardian_notified` enum('Yes','No') NOT NULL DEFAULT 'No',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clinic_visits`
--

LOCK TABLES `clinic_visits` WRITE;
/*!40000 ALTER TABLE `clinic_visits` DISABLE KEYS */;
/*!40000 ALTER TABLE `clinic_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `category` enum('Medicine','First Aid Supply','Medical Equipment','Clinic Supply') NOT NULL,
  `unit` varchar(50) NOT NULL,
  `current_quantity` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 10,
  `expiration_date` date DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
INSERT INTO `inventory_items` VALUES (1,'MED-001','Paracetamol 500mg Tablet','Medicine','tablet',100,20,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(2,'MED-002','Paracetamol Syrup 250mg/5mL','Medicine','bottle',20,5,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(3,'MED-003','Ibuprofen 200mg Tablet','Medicine','tablet',50,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(4,'MED-004','Mefenamic Acid 500mg Capsule','Medicine','capsule',50,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(5,'MED-005','Cetirizine 10mg Tablet','Medicine','tablet',50,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(6,'MED-006','Loratadine 10mg Tablet','Medicine','tablet',50,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(7,'MED-007','Oral Rehydration Salts (ORS)','Medicine','sachet',100,20,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(8,'MED-008','Antacid Tablet','Medicine','tablet',50,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(9,'MED-009','Dimenhydrinate 50mg Tablet','Medicine','tablet',30,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(10,'MED-010','Loperamide 2mg Capsule','Medicine','capsule',30,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(11,'MED-011','Ambroxol Syrup','Medicine','bottle',20,5,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(12,'MED-012','Lagundi Tablet','Medicine','tablet',50,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(13,'MED-013','Calamine Lotion','Medicine','bottle',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(14,'MED-014','Hydrocortisone 1% Cream','Medicine','tube',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(15,'MED-015','Clotrimazole 1% Cream','Medicine','tube',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(16,'MED-016','Povidone-Iodine Solution','Medicine','bottle',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(17,'MED-017','Hydrogen Peroxide 3% Solution','Medicine','bottle',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(18,'MED-018','Normal Saline Solution','Medicine','bottle',20,5,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(19,'MED-019','70% Isopropyl Alcohol','Medicine','bottle',15,5,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(20,'MED-020','Burn Ointment','Medicine','tube',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(21,'MED-021','Salbutamol Inhaler','Medicine','inhaler',5,2,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(22,'MED-022','Glucose Powder','Medicine','sachet',20,5,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(23,'MED-023','Eye Wash Solution','Medicine','bottle',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(24,'MED-024','Activated Charcoal Tablet','Medicine','tablet',20,5,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(25,'MED-025','Vitamin C 500mg Tablet','Medicine','tablet',100,20,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(26,'MED-026','Zinc Sulfate Tablet','Medicine','tablet',30,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(27,'MED-027','Bismuth Subsalicylate Tablet','Medicine','tablet',30,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(28,'MED-028','Mentholated Topical Balm','Medicine','jar',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(29,'MED-029','Antiseptic Wound Wash','Medicine','bottle',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(30,'MED-030','Cold Compress Gel Pack','Medicine','pack',15,5,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(31,'SUP-001','Adhesive Bandage Strip','First Aid Supply','piece',100,20,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(32,'SUP-002','Sterile Gauze Pad','First Aid Supply','piece',100,20,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(33,'SUP-003','Rolled Gauze Bandage','First Aid Supply','roll',30,10,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(34,'SUP-004','Medical Adhesive Tape','First Aid Supply','roll',20,5,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(35,'SUP-005','Cotton Balls','First Aid Supply','pack',20,5,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(36,'SUP-006','Alcohol Swab','First Aid Supply','piece',100,20,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(37,'SUP-007','Disposable Gloves','First Aid Supply','box',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(38,'SUP-008','Surgical Mask','First Aid Supply','box',10,3,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(39,'SUP-009','Digital Thermometer','Medical Equipment','piece',5,2,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04'),(40,'SUP-010','Tongue Depressor','Clinic Supply','piece',100,20,'2028-10-06','School Clinic Supplier','Demo inventory - confirm with the school nurse.','2026-10-06 15:02:04');
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_transactions`
--

DROP TABLE IF EXISTS `inventory_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_item_id` int(11) NOT NULL,
  `transaction_type` enum('Stock In','Stock Out','Visit Usage','Adjustment') NOT NULL,
  `transaction_reason` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transactions`
--

LOCK TABLES `inventory_transactions` WRITE;
/*!40000 ALTER TABLE `inventory_transactions` DISABLE KEYS */;
INSERT INTO `inventory_transactions` VALUES (1,1,'Stock In','Initial Stock',100,'Initial clinic stock',1,'2026-10-06 23:02:04'),(2,2,'Stock In','Initial Stock',20,'Initial clinic stock',1,'2026-10-06 23:02:04'),(3,3,'Stock In','Initial Stock',50,'Initial clinic stock',1,'2026-10-06 23:02:04'),(4,4,'Stock In','Initial Stock',50,'Initial clinic stock',1,'2026-10-06 23:02:04'),(5,5,'Stock In','Initial Stock',50,'Initial clinic stock',1,'2026-10-06 23:02:04'),(6,6,'Stock In','Initial Stock',50,'Initial clinic stock',1,'2026-10-06 23:02:04'),(7,7,'Stock In','Initial Stock',100,'Initial clinic stock',1,'2026-10-06 23:02:04'),(8,8,'Stock In','Initial Stock',50,'Initial clinic stock',1,'2026-10-06 23:02:04'),(9,9,'Stock In','Initial Stock',30,'Initial clinic stock',1,'2026-10-06 23:02:04'),(10,10,'Stock In','Initial Stock',30,'Initial clinic stock',1,'2026-10-06 23:02:04'),(11,11,'Stock In','Initial Stock',20,'Initial clinic stock',1,'2026-10-06 23:02:04'),(12,12,'Stock In','Initial Stock',50,'Initial clinic stock',1,'2026-10-06 23:02:04'),(13,13,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(14,14,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(15,15,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(16,16,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(17,17,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(18,18,'Stock In','Initial Stock',20,'Initial clinic stock',1,'2026-10-06 23:02:04'),(19,19,'Stock In','Initial Stock',15,'Initial clinic stock',1,'2026-10-06 23:02:04'),(20,20,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(21,21,'Stock In','Initial Stock',5,'Initial clinic stock',1,'2026-10-06 23:02:04'),(22,22,'Stock In','Initial Stock',20,'Initial clinic stock',1,'2026-10-06 23:02:04'),(23,23,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(24,24,'Stock In','Initial Stock',20,'Initial clinic stock',1,'2026-10-06 23:02:04'),(25,25,'Stock In','Initial Stock',100,'Initial clinic stock',1,'2026-10-06 23:02:04'),(26,26,'Stock In','Initial Stock',30,'Initial clinic stock',1,'2026-10-06 23:02:04'),(27,27,'Stock In','Initial Stock',30,'Initial clinic stock',1,'2026-10-06 23:02:04'),(28,28,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(29,29,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(30,30,'Stock In','Initial Stock',15,'Initial clinic stock',1,'2026-10-06 23:02:04'),(31,31,'Stock In','Initial Stock',100,'Initial clinic stock',1,'2026-10-06 23:02:04'),(32,32,'Stock In','Initial Stock',100,'Initial clinic stock',1,'2026-10-06 23:02:04'),(33,33,'Stock In','Initial Stock',30,'Initial clinic stock',1,'2026-10-06 23:02:04'),(34,34,'Stock In','Initial Stock',20,'Initial clinic stock',1,'2026-10-06 23:02:04'),(35,35,'Stock In','Initial Stock',20,'Initial clinic stock',1,'2026-10-06 23:02:04'),(36,36,'Stock In','Initial Stock',100,'Initial clinic stock',1,'2026-10-06 23:02:04'),(37,37,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(38,38,'Stock In','Initial Stock',10,'Initial clinic stock',1,'2026-10-06 23:02:04'),(39,39,'Stock In','Initial Stock',5,'Initial clinic stock',1,'2026-10-06 23:02:04'),(40,40,'Stock In','Initial Stock',100,'Initial clinic stock',1,'2026-10-06 23:02:04');
/*!40000 ALTER TABLE `inventory_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `username` (`username`,`attempted_at`),
  KEY `ip` (`ip`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_key` varchar(191) NOT NULL,
  `notification_type` enum('out_of_stock','critical_stock','low_stock','near_expiry','expired') NOT NULL,
  `priority` enum('critical','warning','info') NOT NULL DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `inventory_item_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_key` (`notification_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` varchar(50) NOT NULL,
  `patient_type` enum('student','personnel') NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) NOT NULL,
  `sex` enum('Male','Female') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `grade_department` varchar(120) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `guardian_name` varchar(150) DEFAULT NULL,
  `guardian_contact` varchar(30) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `emergency_contact_name` varchar(150) DEFAULT NULL,
  `emergency_contact_number` varchar(30) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `portal_code` char(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portal_attempts`
--

DROP TABLE IF EXISTS `portal_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portal_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` varchar(50) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`,`attempted_at`),
  KEY `ip` (`ip`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portal_attempts`
--

LOCK TABLES `portal_attempts` WRITE;
/*!40000 ALTER TABLE `portal_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `portal_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','nurse') NOT NULL DEFAULT 'nurse',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Administrator','clinic@smcbi.edu.ph','$2y$10$nKrJRHCCGUnG5v0q90xJVursL0qqckpzgB5Hdr7llL7rbbYrtipFK','admin','active',1,'2026-11-03 12:18:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visit_items`
--

DROP TABLE IF EXISTS `visit_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visit_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clinic_visit_id` int(11) NOT NULL,
  `inventory_item_id` int(11) NOT NULL,
  `quantity_used` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visit_items`
--

LOCK TABLES `visit_items` WRITE;
/*!40000 ALTER TABLE `visit_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `visit_items` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
