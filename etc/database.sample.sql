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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--
-- ORDER BY:  `id`

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` (`id`, `parent_type`, `parent_id`, `user_id`, `created_datetime`, `edited_count`, `edited_datetime`, `content`) VALUES (1,2,1,1,'2018-02-13 05:48:14',10,'2018-10-26 00:08:42','And don\'t even think about being first to comment. ;)');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pages that contain miscellaneous documentation';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--
-- ORDER BY:  `id`

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` (`id`, `user_id`, `created_datetime`, `options_bitmask`, `edited_count`, `edited_datetime`, `title`, `content`) VALUES (1,1,'2018-02-13 04:51:50',3,15,'2019-12-28 18:39:37','Lorem Ipsum','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus nec dolor orci. Mauris condimentum quam tristique condimentum vestibulum. Duis ut justo urna. Proin in molestie ipsum, ut volutpat tortor. Suspendisse venenatis accumsan elit vel faucibus. Nulla tempus magna elit, a dictum purus tristique ut. Aenean dapibus ut libero vel luctus. Nam a leo sit amet turpis convallis condimentum.\r\n\r\nPraesent nec augue ut tellus viverra accumsan et nec ante. Fusce malesuada auctor efficitur. Nam sit amet eros mauris. Morbi enim magna, laoreet vitae ultricies quis, varius id odio. Sed vehicula sollicitudin tincidunt. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas enim mi, dictum non odio in, cursus vestibulum neque. Aenean luctus quam eu aliquet posuere. Etiam consectetur quis mi et luctus.\r\n\r\nQuisque in lectus viverra, tincidunt ex ac, ultricies turpis. Praesent sit amet nunc metus. Pellentesque luctus ultricies ipsum vel aliquet. Vestibulum quis purus elementum arcu commodo pellentesque. Fusce vulputate porta magna, ut cursus magna elementum sed. Donec nec cursus est. Vivamus lorem tortor, volutpat vitae pretium nec, maximus vitae turpis.\r\n\r\nFusce commodo nisl viverra velit bibendum lobortis. Nunc ante massa, tincidunt vitae felis et, porta lacinia tellus. Nunc quis tempus nunc, vel pretium leo. Suspendisse tempor luctus libero, vitae porttitor ipsum rutrum sit amet. Morbi orci libero, condimentum sit amet consectetur ut, tempor posuere urna. Curabitur eu ligula non massa consectetur porttitor. Suspendisse imperdiet luctus nunc eu consectetur. Nunc dignissim ligula id fermentum consequat. Maecenas vehicula est a elit rhoncus ultrices. Etiam tincidunt in arcu non tempor. Suspendisse rutrum vulputate volutpat. Donec ex purus, scelerisque et sollicitudin vitae, ullamcorper in libero. Fusce ut urna ut augue mattis consectetur in fermentum diam. Aenean tempor sagittis egestas. Vivamus hendrerit feugiat ultrices. Nunc vel auctor metus.\r\n\r\nNulla sed suscipit lectus. Donec eget lacinia nisi, sit amet molestie lectus. Quisque ac posuere leo. Donec consectetur, nunc nec fermentum commodo, neque purus eleifend ipsum, sed laoreet nunc mauris sed sapien. Nullam vitae augue nec tortor pharetra ultrices pellentesque vitae sapien. Sed at purus lectus. Quisque iaculis semper tortor convallis rutrum. Vestibulum ante nisi, condimentum quis viverra ac, varius non sem. Vivamus posuere interdum imperdiet. Sed maximus odio augue, in pharetra ex ultrices sit amet. Proin elementum sed magna quis ornare. Aenean neque neque, porttitor vel lorem scelerisque, vestibulum sollicitudin sapien. Phasellus egestas ullamcorper porta. Mauris arcu lorem, efficitur eu viverra non, efficitur at ex.');
INSERT INTO `documents` (`id`, `user_id`, `created_datetime`, `options_bitmask`, `edited_count`, `edited_datetime`, `title`, `content`) VALUES (2,1,'2018-02-14 03:31:04',3,7,'2019-12-28 18:39:49','Markdown Test','# <a name=\"top\"></a>Markdown Test Page\r\n\r\n* [Headings](#Headings)\r\n* [Paragraphs](#Paragraphs)\r\n* [Blockquotes](#Blockquotes)\r\n* [Lists](#Lists)\r\n* [Horizontal rule](#Horizontal)\r\n* [Table](#Table)\r\n* [Code](#Code)\r\n* [Inline elements](#Inline)\r\n\r\n***\r\n\r\n# <a name=\"Headings\"></a>Headings\r\n\r\n# Heading one\r\n\r\nSint sit cillum pariatur eiusmod nulla pariatur ipsum. Sit laborum anim qui mollit tempor pariatur nisi minim dolor. Aliquip et adipisicing sit sit fugiat commodo id sunt. Nostrud enim ad commodo incididunt cupidatat in ullamco ullamco Lorem cupidatat velit enim et Lorem. Ut laborum cillum laboris fugiat culpa sint irure do reprehenderit culpa occaecat. Exercitation esse mollit tempor magna aliqua in occaecat aliquip veniam reprehenderit nisi dolor in laboris dolore velit.\r\n\r\n## Heading two\r\n\r\nAute officia nulla deserunt do deserunt cillum velit magna. Officia veniam culpa anim minim dolore labore pariatur voluptate id ad est duis quis velit dolor pariatur enim. Incididunt enim excepteur do veniam consequat culpa do voluptate dolor fugiat ad adipisicing sit. Labore officia est adipisicing dolore proident eiusmod exercitation deserunt ullamco anim do occaecat velit. Elit dolor consectetur proident sunt aliquip est do tempor quis aliqua culpa aute. Duis in tempor exercitation pariatur et adipisicing mollit irure tempor ut enim esse commodo laboris proident. Do excepteur laborum anim esse aliquip eu sit id Lorem incididunt elit irure ea nulla dolor et. Nulla amet fugiat qui minim deserunt enim eu cupidatat aute officia do velit ea reprehenderit.\r\n\r\n### Heading three\r\n\r\nVoluptate cupidatat cillum elit quis ipsum eu voluptate fugiat consectetur enim. Quis ut voluptate culpa ex anim aute consectetur dolore proident voluptate exercitation eiusmod. Esse in do anim magna minim culpa sint. Adipisicing ipsum consectetur proident ullamco magna sit amet aliqua aute fugiat laborum exercitation duis et.\r\n\r\n#### Heading four\r\n\r\nCommodo fugiat aliqua minim quis pariatur mollit id tempor. Non occaecat minim esse enim aliqua adipisicing nostrud duis consequat eu adipisicing qui. Minim aliquip sit excepteur ipsum consequat laborum pariatur excepteur. Veniam fugiat et amet ad elit anim laborum duis mollit occaecat et et ipsum et reprehenderit. Occaecat aliquip dolore adipisicing sint labore occaecat officia fugiat. Quis adipisicing exercitation exercitation eu amet est laboris sunt nostrud ipsum reprehenderit ullamco. Enim sint ut consectetur id anim aute voluptate exercitation mollit dolore magna magna est Lorem. Ut adipisicing adipisicing aliqua ullamco voluptate labore nisi tempor esse magna incididunt.\r\n\r\n##### Heading five\r\n\r\nVeniam enim esse amet veniam deserunt laboris amet enim consequat. Minim nostrud deserunt cillum consectetur commodo eu enim nostrud ullamco occaecat excepteur. Aliquip et ut est commodo enim dolor amet sint excepteur. Amet ad laboris laborum deserunt sint sunt aliqua commodo ex duis deserunt enim est ex labore ut. Duis incididunt velit adipisicing non incididunt adipisicing adipisicing. Ad irure duis nisi tempor eu dolor fugiat magna et consequat tempor eu ex dolore. Mollit esse nisi qui culpa ut nisi ex proident culpa cupidatat cillum culpa occaecat anim. Ut officia sit ea nisi ea excepteur nostrud ipsum et nulla.\r\n\r\n###### Heading six\r\n\r\nLorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\r\n\r\n\r\n[[Top]](#top)\r\n\r\n# <a name=\"Paragraphs\"></a>Paragraphs\r\n\r\nIncididunt ex adipisicing ea ullamco consectetur in voluptate proident fugiat tempor deserunt reprehenderit ullamco id dolore laborum. Do laboris laboris minim incididunt qui consectetur exercitation adipisicing dolore et magna consequat magna anim sunt. Officia fugiat Lorem sunt pariatur incididunt Lorem reprehenderit proident irure. Dolore ipsum aliqua mollit ad officia fugiat sit eu aliquip cupidatat ipsum duis laborum laborum fugiat esse. Voluptate anim ex dolore deserunt ea ex eiusmod irure. Occaecat excepteur aliqua exercitation aliquip dolor esse eu eu.\r\n\r\nOfficia dolore laborum aute incididunt commodo nisi velit est est elit et dolore elit exercitation. Enim aliquip magna id ipsum aliquip consectetur ad nulla quis. Incididunt pariatur dolor consectetur cillum enim velit cupidatat laborum quis ex.\r\n\r\nOfficia irure in non voluptate adipisicing sit amet tempor duis dolore deserunt enim ut. Reprehenderit incididunt in ad anim et deserunt deserunt Lorem laborum quis. Enim aute anim labore proident laboris voluptate elit excepteur in. Ex labore nulla velit officia ullamco Lorem Lorem id do. Dolore ullamco ipsum magna dolor pariatur voluptate ipsum id occaecat ipsum. Dolore tempor quis duis commodo quis quis enim.\r\n\r\n[[Top]](#top)\r\n\r\n# <a name=\"Blockquotes\"></a>Blockquotes\r\n\r\nAd nisi laborum aute cupidatat magna deserunt eu id laboris id. Aliquip nulla cupidatat sint ex Lorem mollit laborum dolor amet est ut esse aute. Nostrud ex consequat id incididunt proident ipsum minim duis aliqua ut ex et ad quis. Laborum sint esse cillum anim nulla cillum consectetur aliqua sit. Nisi excepteur cillum labore amet excepteur commodo enim occaecat consequat ipsum proident exercitation duis id in.\r\n\r\n> Ipsum et cupidatat mollit exercitation enim duis sunt irure aliqua reprehenderit mollit. Pariatur Lorem pariatur laboris do culpa do elit irure. Eiusmod amet nulla voluptate velit culpa et aliqua ad reprehenderit sit ut.\r\n\r\nLabore ea magna Lorem consequat aliquip consectetur cillum duis dolore. Et veniam dolor qui incididunt minim amet laboris sit. Dolore ad esse commodo et dolore amet est velit ut nisi ea. Excepteur ea nulla commodo dolore anim dolore adipisicing eiusmod labore id enim esse quis mollit deserunt est. Minim ea culpa voluptate nostrud commodo proident in duis aliquip minim.\r\n\r\n> Qui est sit et reprehenderit aute est esse enim aliqua id aliquip ea anim. Pariatur sint reprehenderit mollit velit voluptate enim consectetur sint enim. Quis exercitation proident elit non id qui culpa dolore esse aliquip consequat.\r\n\r\nIpsum excepteur cupidatat sunt minim ad eiusmod tempor sit.\r\n\r\n> Deserunt excepteur adipisicing culpa pariatur cillum laboris ullamco nisi fugiat cillum officia. In cupidatat nulla aliquip tempor ad Lorem Lorem quis voluptate officia consectetur pariatur ex in est duis. Mollit id esse est elit exercitation voluptate nostrud nisi laborum magna dolore dolore tempor in est consectetur.\r\n\r\nAdipisicing voluptate ipsum culpa voluptate id aute laboris labore esse fugiat veniam ullamco occaecat do ut. Tempor et esse reprehenderit veniam proident ipsum irure sit ullamco et labore ea excepteur nulla labore ut. Ex aute minim quis tempor in eu id id irure ea nostrud dolor esse.\r\n\r\n[[Top]](#top)\r\n\r\n# <a name=\"Lists\"></a>Lists\r\n\r\n### Ordered List\r\n\r\n1. Longan\r\n2. Lychee\r\n3. Excepteur ad cupidatat do elit laborum amet cillum reprehenderit consequat quis.\r\n    Deserunt officia esse aliquip consectetur duis ut labore laborum commodo aliquip aliquip velit pariatur dolore.\r\n4. Marionberry\r\n5. Melon\r\n    - Cantaloupe\r\n    - Honeydew\r\n    - Watermelon\r\n6. Miracle fruit\r\n7. Mulberry\r\n\r\n### Unordered List\r\n\r\n- Olive\r\n- Orange\r\n    - Blood orange\r\n    - Clementine\r\n- Papaya\r\n- Ut aute ipsum occaecat nisi culpa Lorem id occaecat cupidatat id id magna laboris ad duis. Fugiat cillum dolore veniam nostrud proident sint consectetur eiusmod irure adipisicing.\r\n- Passionfruit\r\n\r\n[[Top]](#top)\r\n\r\n# <a name=\"Horizontal\"></a>Horizontal rule\r\n\r\nIn dolore velit aliquip labore mollit minim tempor veniam eu veniam ad in sint aliquip mollit mollit. Ex occaecat non deserunt elit laborum sunt tempor sint consequat culpa culpa qui sit. Irure ad commodo eu voluptate mollit cillum cupidatat veniam proident amet minim reprehenderit.\r\n\r\n***\r\n\r\nIn laboris eiusmod reprehenderit aliquip sit proident occaecat. Non sit labore anim elit veniam Lorem minim commodo eiusmod irure do minim nisi. Dolor amet cillum excepteur consequat sint non sint.\r\n\r\n[[Top]](#top)\r\n\r\n# <a name=\"Table\"></a>Table\r\n\r\nDuis sunt ut pariatur reprehenderit mollit mollit magna dolore in pariatur nulla commodo sit dolor ad fugiat. Laboris amet ea occaecat duis eu enim exercitation deserunt ea laborum occaecat reprehenderit. Et incididunt dolor commodo consequat mollit nisi proident non pariatur in et incididunt id. Eu ut et Lorem ea ex magna minim ipsum ipsum do.\r\n\r\n| Table Heading 1 | Table Heading 2 | Center align    | Right align     | Table Heading 5 |\r\n| :-------------- | :-------------- | :-------------: | --------------: | :-------------- |\r\n| Item 1          | Item 2          | Item 3          | Item 4          | Item 5          |\r\n| Item 1          | Item 2          | Item 3          | Item 4          | Item 5          |\r\n| Item 1          | Item 2          | Item 3          | Item 4          | Item 5          |\r\n| Item 1          | Item 2          | Item 3          | Item 4          | Item 5          |\r\n| Item 1          | Item 2          | Item 3          | Item 4          | Item 5          |\r\n\r\nMinim id consequat adipisicing cupidatat laborum culpa veniam non consectetur et duis pariatur reprehenderit eu ex consectetur. Sunt nisi qui eiusmod ut cillum laborum Lorem officia aliquip laboris ullamco nostrud laboris non irure laboris. Cillum dolore labore Lorem deserunt mollit voluptate esse incididunt ex dolor.\r\n\r\n[[Top]](#top)\r\n\r\n# <a name=\"Code\"></a>Code\r\n\r\n## Inline code\r\n\r\nAd amet irure est magna id mollit Lorem in do duis enim. Excepteur velit nisi magna ea pariatur pariatur ullamco fugiat deserunt sint non sint. Duis duis est `code in text` velit velit aute culpa ex quis pariatur pariatur laborum aute pariatur duis tempor sunt ad. Irure magna voluptate dolore consectetur consectetur irure esse. Anim magna `<strong>in culpa qui officia</strong>` dolor eiusmod esse amet aute cupidatat aliqua do id voluptate cupidatat reprehenderit amet labore deserunt.\r\n\r\n## Highlighted\r\n\r\nEt fugiat ad nisi amet magna labore do cillum fugiat occaecat cillum Lorem proident. In sint dolor ullamco ad do adipisicing amet id excepteur Lorem aliquip sit irure veniam laborum duis cillum. Aliqua occaecat minim cillum deserunt magna sunt laboris do do irure ea nostrud consequat ut voluptate ex.\r\n\r\n```go\r\npackage main\r\n\r\nimport (\r\n    \"fmt\"\r\n    \"net/http\"\r\n)\r\n\r\nfunc handler(w http.ResponseWriter, r *http.Request) {\r\n    fmt.Fprintf(w, \"Hi there, I love %s!\", r.URL.Path[1:])\r\n}\r\n\r\nfunc main() {\r\n    http.HandleFunc(\"/\", handler)\r\n    http.ListenAndServe(\":8080\", nil)\r\n}\r\n```\r\n\r\nEx amet id ex aliquip id do laborum excepteur exercitation elit sint commodo occaecat nostrud est. Nostrud pariatur esse veniam laborum non sint magna sit laboris minim in id. Aliqua pariatur pariatur excepteur adipisicing irure culpa consequat commodo et ex id ad.\r\n\r\n[[Top]](#top)\r\n\r\n# <a name=\"Inline\"></a>Inline elements\r\n\r\nSint ea anim ipsum ad commodo cupidatat do **exercitation** incididunt et minim ad labore sunt. Minim deserunt labore laboris velit nulla incididunt ipsum nulla. Ullamco ad laborum ea qui et anim in laboris exercitation tempor sit officia laborum reprehenderit culpa velit quis. **Consequat commodo** reprehenderit duis [irure](#!) esse esse exercitation minim enim Lorem dolore duis irure. Nisi Lorem reprehenderit ea amet excepteur dolor excepteur magna labore proident voluptate ipsum. Reprehenderit ex esse deserunt aliqua ea officia mollit Lorem nulla magna enim. Et ad ipsum labore enim ipsum **cupidatat consequat**. Commodo non ea cupidatat magna deserunt dolore ipsum velit nulla elit veniam nulla eiusmod proident officia.\r\n\r\n![Super wide](http://placekitten.com/1280/800)\r\n\r\n*Proident sit veniam in est proident officia adipisicing* ea tempor cillum non cillum velit deserunt. Voluptate laborum incididunt sit consectetur Lorem irure incididunt voluptate nostrud. Commodo ut eiusmod tempor cupidatat esse enim minim ex anim consequat. Mollit sint culpa qui laboris quis consectetur ad sint esse. Amet anim anim minim ullamco et duis non irure. Sit tempor adipisicing ea laboris `culpa ex duis sint` anim aute reprehenderit id eu ea. Aute [excepteur proident](#!) Lorem minim adipisicing nostrud mollit ad ut voluptate do nulla esse occaecat aliqua sint anim.\r\n\r\n![Not so big](http://placekitten.com/480/400)\r\n\r\nIncididunt in culpa cupidatat mollit cillum qui proident sit. In cillum aliquip incididunt voluptate magna amet cupidatat cillum pariatur sint aliqua est _enim **anim** voluptate_. Magna aliquip proident incididunt id duis pariatur eiusmod incididunt commodo culpa dolore sit. Culpa do nostrud elit ad exercitation anim pariatur non minim nisi **adipisicing sunt _officia_**. Do deserunt magna mollit Lorem commodo ipsum do cupidatat mollit enim ut elit veniam ea voluptate.\r\n\r\n[![Box](//img.youtube.com/vi/3OuHuaSquP4/0.jpg)](//youtu.be/3OuHuaSquP4)\r\n\r\nReprehenderit non eu quis in ad elit esse qui aute id [incididunt](#!) dolore cillum. Esse laboris consequat dolor anim exercitation tempor aliqua deserunt velit magna laboris. Culpa culpa minim duis amet mollit do quis amet commodo nulla irure.');
INSERT INTO `documents` (`id`, `user_id`, `created_datetime`, `options_bitmask`, `edited_count`, `edited_datetime`, `title`, `content`) VALUES (3,1,'2019-02-11 19:46:14',3,0,NULL,'Software design pattern','In software engineering, a software design pattern is a general, reusable solution to a commonly occurring problem within a given context in software design. It is not a finished design that can be transformed directly into source or machine code. It is a description or template for how to solve a problem that can be used in many different situations. Design patterns are formalized best practices that the programmer can use to solve common problems when designing an application or system.\r\n\r\nObject-oriented design patterns typically show relationships and interactions between classes or objects, without specifying the final application classes or objects that are involved. Patterns that imply mutable state may be unsuited for functional programming languages, some patterns can be rendered unnecessary in languages that have built-in support for solving the problem they are trying to solve, and object-oriented patterns are not necessarily suitable for non-object-oriented languages.\r\n\r\nDesign patterns may be viewed as a structured approach to computer programming intermediate between the levels of a programming paradigm and a concrete algorithm.\r\n');
INSERT INTO `documents` (`id`, `user_id`, `created_datetime`, `options_bitmask`, `edited_count`, `edited_datetime`, `title`, `content`) VALUES (4,1,'2019-02-11 19:46:57',3,1,'2019-12-28 18:24:11','Factory method pattern','In class-based programming, the factory method pattern is a creational pattern that uses factory methods to deal with the problem of creating objects without having to specify the exact class of the object that will be created. This is done by creating objects by calling a factory method—either specified in an interface and implemented by child classes, or implemented in a base class and optionally overridden by derived classes—rather than by calling a constructor.');
INSERT INTO `documents` (`id`, `user_id`, `created_datetime`, `options_bitmask`, `edited_count`, `edited_datetime`, `title`, `content`) VALUES (5,1,'2019-02-11 19:47:20',3,0,NULL,'Object pool pattern','The object pool pattern is a software creational design pattern that uses a set of initialized objects kept ready to use – a \"pool\" – rather than allocating and destroying them on demand. A client of the pool will request an object from the pool and perform operations on the returned object. When the client has finished, it returns the object to the pool rather than destroying it; this can be done manually or automatically.\r\n\r\nObject pools are primarily used for performance: in some circumstances, object pools significantly improve performance. Object pools complicate object lifetime, as objects obtained from and returned to a pool are not actually created or destroyed at this time, and thus require care in implementation.');
INSERT INTO `documents` (`id`, `user_id`, `created_datetime`, `options_bitmask`, `edited_count`, `edited_datetime`, `title`, `content`) VALUES (6,1,'2019-02-11 19:47:46',3,2,'2019-12-28 18:40:22','Prototype pattern','The prototype pattern is a creational design pattern in software development. It is used when the type of objects to create is determined by a prototypical instance, which is cloned to produce new objects. This pattern is used to:\r\n\r\n* avoid subclasses of an object creator in the client application, like the factory method pattern does.\r\n* avoid the inherent cost of creating a new object in the standard way (e.g., using the \'new\' keyword) when it is prohibitively expensive for a given application.\r\n\r\nTo implement the pattern, declare an abstract base class that specifies a pure virtual clone() method. Any class that needs a \"polymorphic constructor\" capability derives itself from the abstract base class, and implements the clone() operation.\r\n\r\nThe client, instead of writing code that invokes the \"new\" operator on a hard-coded class name, calls the clone() method on the prototype, calls a factory method with a parameter designating the particular concrete derived class desired, or invokes the clone() method through some mechanism provided by another design pattern. ');
INSERT INTO `documents` (`id`, `user_id`, `created_datetime`, `options_bitmask`, `edited_count`, `edited_datetime`, `title`, `content`) VALUES (7,1,'2019-02-11 19:49:01',3,0,NULL,'Messaging pattern','In software architecture, a messaging pattern is a network-oriented architectural pattern which describes how two different parts of a message passing system connect and communicate with each other.\r\n\r\nIn telecommunications, a message exchange pattern (MEP) describes the pattern of messages required by a communications protocol to establish or use a communication channel. There are two major message exchange patterns — a request–response pattern, and a one-way pattern. For example, HTTP is a request–response pattern protocol, and UDP is a one-way pattern.');
INSERT INTO `documents` (`id`, `user_id`, `created_datetime`, `options_bitmask`, `edited_count`, `edited_datetime`, `title`, `content`) VALUES (8,1,'2019-02-11 19:49:31',3,0,NULL,'File transfer','File transfer is the transmission of a computer file through a communication channel from one computer system to another. Typically, file transfer is mediated by a communications protocol. In the history of computing, a large number of file transfer protocols have been designed for different contexts.');
INSERT INTO `documents` (`id`, `user_id`, `created_datetime`, `options_bitmask`, `edited_count`, `edited_datetime`, `title`, `content`) VALUES (9,1,'2019-02-11 19:49:45',3,0,NULL,'Sideloading','Sideloading is a term used mostly on the Internet, similar to \"upload\" and \"download\", but in reference to the process of transferring files between two local devices, in particular between a computer and a mobile device such as a mobile phone, smartphone, PDA, tablet, portable media player or e-reader.\r\n\r\nSideloading typically refers to media file transfer to a mobile device via USB, Bluetooth, WiFi or by writing to a memory card for insertion into the mobile device.\r\n\r\nWhen referring to Android apps, \"sideloading\" typically means installing an application package in APK format onto an Android device. Such packages are usually downloaded from websites other than Google play, usually through a computer. Side loading of apps is only possible if the user has allowed \"Unknown Sources\" in their Security Settings.');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_posts`
--
-- ORDER BY:  `id`

LOCK TABLES `news_posts` WRITE;
/*!40000 ALTER TABLE `news_posts` DISABLE KEYS */;
INSERT INTO `news_posts` (`id`, `created_datetime`, `user_id`, `edited_count`, `edited_datetime`, `category_id`, `options_bitmask`, `title`, `content`) VALUES (1,'2018-02-13 04:27:51',1,0,NULL,5,3,'Welcome to BNETDocs Dev!','This is the first news post in the BNETDocs Development environment. This environment allows BNETDocs Staff to test things out before publishing them to the production environment.\r\n\r\nIf you got here by mistake, simply [click here](https://www.bnetdocs.org/news) to get things back to normal. Otherwise, I don\'t know why you\'re reading this.');
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
