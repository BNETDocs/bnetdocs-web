-- MySQL dump 10.16  Distrib 10.1.45-MariaDB, for Linux (x86_64)
--
-- Host: mariadb.localdomain    Database: bnetdocs_phoenix_dev_backup
-- ------------------------------------------------------
-- Server version	10.3.27-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `bnetdocs_phoenix_dev_backup`
--

/*!40000 DROP DATABASE IF EXISTS `bnetdocs_phoenix_dev_backup`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `bnetdocs_phoenix_dev_backup` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `bnetdocs_phoenix_dev_backup`;

--
-- Table structure for table `change_log`
--

DROP TABLE IF EXISTS `change_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `change_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned NOT NULL,
  `parent_type` bigint(20) unsigned NOT NULL,
  `created_datetime` datetime NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `change_type` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `change_log`
--
-- ORDER BY:  `id`

LOCK TABLES `change_log` WRITE;
/*!40000 ALTER TABLE `change_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `change_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `change_log_items`
--

DROP TABLE IF EXISTS `change_log_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `change_log_items` (
  `id` bigint(20) unsigned NOT NULL,
  `field_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_value_double` double DEFAULT NULL,
  `field_value_int` bigint(20) DEFAULT NULL,
  `field_value_string` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`,`field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `change_log_items`
--
-- ORDER BY:  `id`,`field_name`

LOCK TABLES `change_log_items` WRITE;
/*!40000 ALTER TABLE `change_log_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `change_log_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_type` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Author of the comment',
  `created_datetime` datetime NOT NULL,
  `edited_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `edited_datetime` datetime DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `created_datetime` (`created_datetime`),
  KEY `idx_parent` (`parent_type`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--
-- ORDER BY:  `id`

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_structure_skeleton`
--

DROP TABLE IF EXISTS `data_structure_skeleton`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_structure_skeleton` (
  `data_structure_id` bigint(20) unsigned NOT NULL,
  `offset` int(10) unsigned NOT NULL,
  `type_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `const_value_uint` bigint(20) unsigned DEFAULT NULL,
  `const_value_int` bigint(20) DEFAULT NULL,
  `const_value_string` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  KEY `idx_data_structure_id` (`data_structure_id`),
  KEY `idx_offset` (`offset`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_structure_skeleton`
--

LOCK TABLES `data_structure_skeleton` WRITE;
/*!40000 ALTER TABLE `data_structure_skeleton` DISABLE KEYS */;
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (0,0,0,'Padding Byte',255,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (0,1,0,'Message Id',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (0,2,1,'Message Length',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (0,4,9,'Message Data',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (1,0,1,'Message Length',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (1,2,0,'Message Id',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (1,3,9,'Message Data',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (2,0,1,'Message Length',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (2,2,0,'Message Id',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (2,3,9,'Message Data',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (3,0,0,'Message Id',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (3,1,9,'Message Data',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (4,0,0,'Message Length',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (4,1,9,'Message Data',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (5,0,0,'Message Length High Bits',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (5,1,0,'Message Length Low Bits',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (5,2,9,'Message Data',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (6,0,0,'Protocol Version',1,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (6,1,0,'Message Id',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (6,2,1,'Message Length',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (6,4,9,'Message Data',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (7,0,0,'Padding Byte',247,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (7,1,0,'Message Id',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (7,2,1,'Message Length',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (7,4,9,'Message Data',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (8,0,1,'Checksum',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (8,2,1,'Header Length',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (8,4,1,'Seq1',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (8,6,1,'Seq2',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (8,8,0,'CLS',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (8,9,0,'Command',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (8,10,0,'PlayerID',NULL,NULL,NULL);
INSERT INTO `data_structure_skeleton` (`data_structure_id`, `offset`, `type_id`, `name`, `const_value_uint`, `const_value_int`, `const_value_string`) VALUES (8,11,0,'Resend',NULL,NULL,NULL);
/*!40000 ALTER TABLE `data_structure_skeleton` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_structure_types`
--

DROP TABLE IF EXISTS `data_structure_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_structure_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bit_size_min` int(10) unsigned NOT NULL,
  `bit_size_max` int(10) unsigned DEFAULT NULL,
  `big_endian` tinyint(1) DEFAULT NULL,
  `signed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_structure_types`
--
-- ORDER BY:  `id`

LOCK TABLES `data_structure_types` WRITE;
/*!40000 ALTER TABLE `data_structure_types` DISABLE KEYS */;
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (0,'uint8',8,8,0,0);
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (1,'uint16',16,16,0,0);
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (2,'uint32',32,32,0,0);
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (3,'uint64',64,64,0,0);
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (4,'int8',8,8,0,1);
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (5,'int16',16,16,0,1);
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (6,'int32',32,32,0,1);
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (7,'int64',64,64,0,1);
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (8,'string',8,NULL,NULL,NULL);
INSERT INTO `data_structure_types` (`id`, `name`, `bit_size_min`, `bit_size_max`, `big_endian`, `signed`) VALUES (9,'void',0,NULL,NULL,NULL);
/*!40000 ALTER TABLE `data_structure_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_structures`
--

DROP TABLE IF EXISTS `data_structures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_structures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `parent_offset` int(10) unsigned DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_structures`
--
-- ORDER BY:  `id`

LOCK TABLES `data_structures` WRITE;
/*!40000 ALTER TABLE `data_structures` DISABLE KEYS */;
INSERT INTO `data_structures` (`id`, `name`, `parent_id`, `parent_offset`, `description`) VALUES (0,'BNCS Header',NULL,NULL,NULL);
INSERT INTO `data_structures` (`id`, `name`, `parent_id`, `parent_offset`, `description`) VALUES (1,'BNLS Header',NULL,NULL,NULL);
INSERT INTO `data_structures` (`id`, `name`, `parent_id`, `parent_offset`, `description`) VALUES (2,'MCP Header',NULL,NULL,NULL);
INSERT INTO `data_structures` (`id`, `name`, `parent_id`, `parent_offset`, `description`) VALUES (3,'D2GS Uncompressed Header',NULL,NULL,NULL);
INSERT INTO `data_structures` (`id`, `name`, `parent_id`, `parent_offset`, `description`) VALUES (4,'D2GS Compressed Header',NULL,NULL,NULL);
INSERT INTO `data_structures` (`id`, `name`, `parent_id`, `parent_offset`, `description`) VALUES (5,'D2GS Compressed Header Extended',NULL,NULL,NULL);
INSERT INTO `data_structures` (`id`, `name`, `parent_id`, `parent_offset`, `description`) VALUES (6,'Botnet Header',NULL,NULL,NULL);
INSERT INTO `data_structures` (`id`, `name`, `parent_id`, `parent_offset`, `description`) VALUES (7,'W3GS Header',NULL,NULL,NULL);
INSERT INTO `data_structures` (`id`, `name`, `parent_id`, `parent_offset`, `description`) VALUES (8,'Storm UDP Packet',NULL,NULL,NULL);
/*!40000 ALTER TABLE `data_structures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Author of the document',
  `created_datetime` datetime NOT NULL,
  `options_bitmask` bigint(20) unsigned NOT NULL DEFAULT 0,
  `edited_count` bigint(20) unsigned NOT NULL DEFAULT 0,
  `edited_datetime` datetime DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `created_datetime` (`created_datetime`) USING BTREE,
  KEY `options_bitmask` (`options_bitmask`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pages that contain miscellaneous documentation';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--
-- ORDER BY:  `id`

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_log`
--

DROP TABLE IF EXISTS `event_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_type_id` bigint(20) unsigned NOT NULL,
  `event_datetime` datetime NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Author of the event if applicable',
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Author''s IP address if applicable',
  `meta_data` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_type_id` (`event_type_id`) USING BTREE,
  KEY `event_datetime` (`event_datetime`) USING BTREE,
  KEY `user_id` (`user_id`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores events that occurred';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_log`
--
-- ORDER BY:  `id`

LOCK TABLES `event_log` WRITE;
/*!40000 ALTER TABLE `event_log` DISABLE KEYS */;
INSERT INTO `event_log` (`id`, `event_type_id`, `event_datetime`, `user_id`, `ip_address`, `meta_data`) VALUES (0,0,'2021-03-09 20:04:50',NULL,NULL,'Initial event log');
/*!40000 ALTER TABLE `event_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_categories`
--

DROP TABLE IF EXISTS `news_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sort_id` bigint(20) unsigned NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sort_id` (`sort_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_categories`
--
-- ORDER BY:  `id`

LOCK TABLES `news_categories` WRITE;
/*!40000 ALTER TABLE `news_categories` DISABLE KEYS */;
INSERT INTO `news_categories` (`id`, `sort_id`, `filename`, `label`) VALUES (0,3,'blizzard.png','Blizzard');
INSERT INTO `news_categories` (`id`, `sort_id`, `filename`, `label`) VALUES (1,4,'battlenet.png','Battle.net');
INSERT INTO `news_categories` (`id`, `sort_id`, `filename`, `label`) VALUES (2,6,'warcraft.png','Warcraft');
INSERT INTO `news_categories` (`id`, `sort_id`, `filename`, `label`) VALUES (3,5,'diablo.png','Diablo');
INSERT INTO `news_categories` (`id`, `sort_id`, `filename`, `label`) VALUES (4,7,'starcraft.png','Starcraft');
INSERT INTO `news_categories` (`id`, `sort_id`, `filename`, `label`) VALUES (5,1,'bnetdocs.png','BNETDocs');
INSERT INTO `news_categories` (`id`, `sort_id`, `filename`, `label`) VALUES (6,2,'bnls.png','BNLS');
INSERT INTO `news_categories` (`id`, `sort_id`, `filename`, `label`) VALUES (7,8,'sc2.png','Starcraft II');
/*!40000 ALTER TABLE `news_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_posts`
--

DROP TABLE IF EXISTS `news_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_datetime` datetime NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Author of the news post',
  `edited_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `edited_datetime` datetime DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `options_bitmask` bigint(20) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `created_datetime` (`created_datetime`) USING BTREE,
  KEY `category_id` (`category_id`) USING BTREE,
  KEY `options_bitmask` (`options_bitmask`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_posts`
--
-- ORDER BY:  `id`

LOCK TABLES `news_posts` WRITE;
/*!40000 ALTER TABLE `news_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packet_used_by`
--

DROP TABLE IF EXISTS `packet_used_by`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packet_used_by` (
  `id` bigint(20) unsigned NOT NULL,
  `bnet_product_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`,`bnet_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packet_used_by`
--
-- ORDER BY:  `id`,`bnet_product_id`

LOCK TABLES `packet_used_by` WRITE;
/*!40000 ALTER TABLE `packet_used_by` DISABLE KEYS */;
/*!40000 ALTER TABLE `packet_used_by` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packets`
--

DROP TABLE IF EXISTS `packets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Author of the packet',
  `created_datetime` datetime NOT NULL,
  `options_bitmask` bigint(20) unsigned NOT NULL DEFAULT 0,
  `edited_count` bigint(20) unsigned NOT NULL DEFAULT 0,
  `edited_datetime` datetime DEFAULT NULL,
  `packet_transport_layer_id` bigint(20) unsigned NOT NULL,
  `packet_application_layer_id` bigint(20) unsigned NOT NULL,
  `packet_direction_id` bigint(20) unsigned NOT NULL,
  `packet_id` tinyint(3) unsigned NOT NULL,
  `packet_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `packet_format` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `packet_remarks` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `packet_id` (`packet_id`),
  KEY `packet_name` (`packet_name`),
  KEY `options_bitmask` (`options_bitmask`) USING BTREE,
  KEY `packet_direction_id` (`packet_direction_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `created_datetime` (`created_datetime`) USING BTREE,
  KEY `packet_transport_layer_id` (`packet_transport_layer_id`) USING BTREE,
  KEY `packet_application_layer_id` (`packet_application_layer_id`) USING BTREE,
  FULLTEXT KEY `fulltext_packet_search` (`packet_remarks`,`packet_format`,`packet_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packets`
--
-- ORDER BY:  `id`

LOCK TABLES `packets` WRITE;
/*!40000 ALTER TABLE `packets` DISABLE KEYS */;
/*!40000 ALTER TABLE `packets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `sort` int(10) unsigned NOT NULL,
  `bnls_product_id` int(10) unsigned NOT NULL,
  `bnet_product_id` int(10) unsigned NOT NULL,
  `bnet_product_raw` binary(4) NOT NULL,
  `version_byte` int(10) unsigned NOT NULL,
  `label` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`bnet_product_id`),
  UNIQUE KEY `bnet_product_raw` (`bnet_product_raw`),
  UNIQUE KEY `bnls_product_id` (`bnls_product_id`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Products & Product Names';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--
-- ORDER BY:  `bnet_product_id`

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (7,4,1144144982,0x44324456,14,'Diablo II');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (8,5,1144150096,0x44325850,14,'Diablo II Lord of Destruction');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (5,9,1146246220,0x4452544C,42,'Diablo Retail');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (6,10,1146308690,0x44534852,42,'Diablo Shareware');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (4,6,1246975058,0x4A535452,169,'Starcraft Japanese');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (2,2,1397053520,0x53455850,211,'Starcraft Broodwar');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (3,11,1397966930,0x53534852,165,'Starcraft Shareware');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (1,1,1398030674,0x53544152,211,'Starcraft Original');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (9,3,1462911566,0x5732424E,79,'Warcraft II BNE');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (12,12,1462977613,0x5733444D,1,'Warcraft III Demo');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (11,8,1462982736,0x57335850,27,'Warcraft III The Frozen Throne');
INSERT INTO `products` (`sort`, `bnls_product_id`, `bnet_product_id`, `bnet_product_raw`, `version_byte`, `label`) VALUES (10,7,1463898675,0x57415233,27,'Warcraft III Reign of Chaos');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relation_verbs`
--

DROP TABLE IF EXISTS `relation_verbs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relation_verbs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `verb_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opposite_verb_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relation_verbs`
--
-- ORDER BY:  `id`

LOCK TABLES `relation_verbs` WRITE;
/*!40000 ALTER TABLE `relation_verbs` DISABLE KEYS */;
INSERT INTO `relation_verbs` (`id`, `verb_label`, `opposite_verb_id`) VALUES (0,'relates to',0);
INSERT INTO `relation_verbs` (`id`, `verb_label`, `opposite_verb_id`) VALUES (1,'duplicates',2);
INSERT INTO `relation_verbs` (`id`, `verb_label`, `opposite_verb_id`) VALUES (2,'is duplicated by',1);
INSERT INTO `relation_verbs` (`id`, `verb_label`, `opposite_verb_id`) VALUES (3,'replaces',4);
INSERT INTO `relation_verbs` (`id`, `verb_label`, `opposite_verb_id`) VALUES (4,'is replaced by',3);
/*!40000 ALTER TABLE `relation_verbs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relations`
--

DROP TABLE IF EXISTS `relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_source_type` bigint(20) unsigned NOT NULL,
  `link_source_id` bigint(20) unsigned NOT NULL,
  `link_target_type` bigint(20) unsigned NOT NULL,
  `link_target_id` bigint(20) unsigned NOT NULL,
  `linked_datetime` datetime NOT NULL,
  `link_verb_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relations`
--
-- ORDER BY:  `id`

LOCK TABLES `relations` WRITE;
/*!40000 ALTER TABLE `relations` DISABLE KEYS */;
/*!40000 ALTER TABLE `relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `server_types`
--

DROP TABLE IF EXISTS `server_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Application names for server classification';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `server_types`
--
-- ORDER BY:  `id`

LOCK TABLES `server_types` WRITE;
/*!40000 ALTER TABLE `server_types` DISABLE KEYS */;
INSERT INTO `server_types` (`id`, `label`) VALUES (0,'Generic Service');
INSERT INTO `server_types` (`id`, `label`) VALUES (1,'Primary Website Service');
INSERT INTO `server_types` (`id`, `label`) VALUES (2,'Ancillary Website Service');
INSERT INTO `server_types` (`id`, `label`) VALUES (10,'Battle.net v1 Game Client Service (BNCS) / Blizzard Classic');
INSERT INTO `server_types` (`id`, `label`) VALUES (11,'Battle.net v1 Game Client Service (BNCS) / Blizzard Classic Remastered');
INSERT INTO `server_types` (`id`, `label`) VALUES (12,'Battle.net v1 Game Client Service (BNCS) / Partnered Communities');
INSERT INTO `server_types` (`id`, `label`) VALUES (13,'Battle.net v1 Game Client Service (BNCS) / Communities');
INSERT INTO `server_types` (`id`, `label`) VALUES (20,'Battle.net Logon Service (BNLS) for Bots');
INSERT INTO `server_types` (`id`, `label`) VALUES (21,'Valhalla Legends Botnet Service');
INSERT INTO `server_types` (`id`, `label`) VALUES (30,'Battle.net v1 Remastered Service');
INSERT INTO `server_types` (`id`, `label`) VALUES (31,'Battle.net v1 Remastered Service Public Test Realm');
INSERT INTO `server_types` (`id`, `label`) VALUES (40,'Battle.net v2 Game Service');
INSERT INTO `server_types` (`id`, `label`) VALUES (41,'Battle.net v2 Game Service Public Test Realm');
INSERT INTO `server_types` (`id`, `label`) VALUES (42,'Battle.net v2 Logon Service');
INSERT INTO `server_types` (`id`, `label`) VALUES (43,'Battle.net v2 Logon Service Public Test Realm');
INSERT INTO `server_types` (`id`, `label`) VALUES (44,'Battle.net v2 Patch Service');
INSERT INTO `server_types` (`id`, `label`) VALUES (45,'Battle.net v2 Patch Service Public Test Realm');
INSERT INTO `server_types` (`id`, `label`) VALUES (100,'Other Website Service');
INSERT INTO `server_types` (`id`, `label`) VALUES (101,'TeamSpeak 3 Service');
/*!40000 ALTER TABLE `server_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Owner of the server',
  `type_id` bigint(20) unsigned NOT NULL,
  `created_datetime` datetime NOT NULL,
  `updated_datetime` datetime DEFAULT NULL,
  `status_bitmask` tinyint(3) unsigned NOT NULL COMMENT 'Bitfield;0=offline,1=online,2=disabled',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `type_id` (`type_id`) USING BTREE,
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='List of network applications to track the online status of';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servers`
--
-- ORDER BY:  `id`

LOCK TABLES `servers` WRITE;
/*!40000 ALTER TABLE `servers` DISABLE KEYS */;
/*!40000 ALTER TABLE `servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag_relations`
--

DROP TABLE IF EXISTS `tag_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_relations` (
  `tag_id` bigint(20) unsigned NOT NULL,
  `object_type` bigint(20) unsigned NOT NULL,
  `object_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`object_type`,`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_relations`
--
-- ORDER BY:  `tag_id`,`object_type`,`object_id`

LOCK TABLES `tag_relations` WRITE;
/*!40000 ALTER TABLE `tag_relations` DISABLE KEYS */;
INSERT INTO `tag_relations` (`tag_id`, `object_type`, `object_id`) VALUES (0,2,1);
INSERT INTO `tag_relations` (`tag_id`, `object_type`, `object_id`) VALUES (2,2,1);
INSERT INTO `tag_relations` (`tag_id`, `object_type`, `object_id`) VALUES (8,3,174);
INSERT INTO `tag_relations` (`tag_id`, `object_type`, `object_id`) VALUES (13,3,174);
INSERT INTO `tag_relations` (`tag_id`, `object_type`, `object_id`) VALUES (15,3,174);
INSERT INTO `tag_relations` (`tag_id`, `object_type`, `object_id`) VALUES (20,3,174);
INSERT INTO `tag_relations` (`tag_id`, `object_type`, `object_id`) VALUES (21,3,174);
INSERT INTO `tag_relations` (`tag_id`, `object_type`, `object_id`) VALUES (26,3,174);
/*!40000 ALTER TABLE `tag_relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--
-- ORDER BY:  `id`

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (0,'bnetdocs','BNETDocs',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (1,'bnetdocs-labs','BNETDocs: Labs',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (2,'bnetdocs-phoenix','BNETDocs: Phoenix',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (3,'bnetdocs-redux','BNETDocs: Redux',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (4,'bnls','BNLS',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (5,'brood-war','StarCraft: Brood War',15);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (6,'broodwar','StarCraft: Brood War',15);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (7,'defunct','Defunct',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (8,'diablo','Diablo',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (9,'research-needed','Research Needed',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (10,'sc','StarCraft',13);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (11,'sc2','StarCraft II',16);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (12,'scbw','StarCraft: Brood War',15);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (13,'starcraft','StarCraft',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (14,'starcraft-2','StarCraft II',16);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (15,'starcraft-broodwar','StarCraft: Brood War',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (16,'starcraft-ii','StarCraft II',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (17,'warcraft','WarCraft',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (18,'warcraft-2','WarCraft II',20);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (19,'warcraft-3','WarCraft III',21);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (20,'warcraft-ii','WarCraft II',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (21,'warcraft-iii','WarCraft III',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (22,'wc3','WarCraft III',21);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (23,'world-of-warcraft','World of WarCraft',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (24,'wc','WarCraft',17);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (25,'botnet-protocol','BotNet Protocol',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (26,'diablo-ii','Diablo II',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (27,'diablo-2','Diablo II',26);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (28,'diablo-iii','Diablo III',NULL);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (29,'diablo-3','Diablo III',28);
INSERT INTO `tags` (`id`, `name`, `description`, `alias_id`) VALUES (30,'d2','Diablo II',26);
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_profiles` (
  `user_id` bigint(20) unsigned NOT NULL,
  `discord_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reddit_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `steam_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biography` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profiles`
--
-- ORDER BY:  `user_id`

LOCK TABLES `user_profiles` WRITE;
/*!40000 ALTER TABLE `user_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sessions` (
  `id` char(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `ip_address` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_datetime` datetime NOT NULL,
  `expires_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_idx` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--
-- ORDER BY:  `id`

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `username` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `display_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_datetime` datetime NOT NULL,
  `verified_datetime` datetime DEFAULT NULL,
  `verifier_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_salt` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `options_bitmask` bigint(20) unsigned NOT NULL DEFAULT 0,
  `timezone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User metadata for the BNETDocs website';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--
-- ORDER BY:  `id`

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `email`, `username`, `display_name`, `created_datetime`, `verified_datetime`, `verifier_token`, `password_hash`, `password_salt`, `options_bitmask`, `timezone`) VALUES (1,'nobody@example.com','nobody',NULL,'2021-03-09 20:04:50',NULL,NULL,NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'bnetdocs_phoenix_dev_backup'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-03-09 20:04:50
