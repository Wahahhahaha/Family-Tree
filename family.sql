/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.4.12-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: permat43_ranji
-- ------------------------------------------------------
-- Server version	11.4.12-MariaDB

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
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`context`)),
  `ip_adress` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=352 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES
(1,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293647276090049,\\\"longitude\\\":104.00905110646363,\\\"accuracy\\\":156},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:24:14','2026-05-24 09:24:14','1.129364727609','104.00905110646'),
(2,'DELETE.management.backup.reset',1,'\"{\\\"path\\\":\\\"management\\\\\\/backup-database\\\\\\/reset\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":{\\\"_method\\\":\\\"DELETE\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:24:16','2026-05-24 09:24:16','',''),
(3,'POST.management.setting.website.update',1,'\"{\\\"path\\\":\\\"management\\\\\\/setting\\\\\\/website\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"systemname\\\":\\\"Sepeda\\\",\\\"systemmanager\\\":\\\"ayam\\\",\\\"systemcontact\\\":\\\"123\\\",\\\"systemaddress\\\":\\\"adadeg\\\",\\\"systemlogo\\\":{}},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:25:32','2026-05-24 09:25:32','',''),
(4,'POST.management.setting.landing.update',1,'\"{\\\"path\\\":\\\"management\\\\\\/setting\\\\\\/landing\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"family_name\\\":\\\"Koto Balai Nan di Bawah\\\",\\\"head_of_family_name\\\":\\\"Arthur Miller\\\",\\\"description\\\":\\\"Keluarga\\\",\\\"head_of_family_message\\\":\\\"Se;amat datang\\\",\\\"created_by_name\\\":\\\"123\\\",\\\"approved_by_name\\\":\\\"456\\\",\\\"designed_by_name\\\":\\\"789\\\",\\\"acknowledged_by_name\\\":\\\"012\\\",\\\"head_of_family_photo\\\":{},\\\"created_by_photo\\\":{},\\\"approved_by_photo\\\":{},\\\"designed_by_photo\\\":{},\\\"acknowledged_by_photo\\\":{}},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:27:06','2026-05-24 09:27:06','',''),
(5,'POST.management.setting.landing.update',1,'\"{\\\"path\\\":\\\"management\\\\\\/setting\\\\\\/landing\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"family_name\\\":\\\"Koto Balai Nan di Bawah\\\",\\\"head_of_family_name\\\":\\\"Arthur Miller\\\",\\\"description\\\":\\\"Keluarga\\\",\\\"head_of_family_message\\\":\\\"Se;amat datang\\\",\\\"created_by_name\\\":\\\"123\\\",\\\"approved_by_name\\\":\\\"456\\\",\\\"designed_by_name\\\":\\\"789\\\",\\\"acknowledged_by_name\\\":\\\"012\\\",\\\"head_of_family_photo\\\":{},\\\"created_by_photo\\\":{},\\\"approved_by_photo\\\":{},\\\"designed_by_photo\\\":{},\\\"acknowledged_by_photo\\\":{}},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:27:30','2026-05-24 09:27:30','',''),
(6,'POST.management.setting.landing.update',1,'\"{\\\"path\\\":\\\"management\\\\\\/setting\\\\\\/landing\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"family_name\\\":\\\"Koto Balai Nan di Bawah\\\",\\\"head_of_family_name\\\":\\\"Arthur Miller\\\",\\\"description\\\":\\\"Keluarga\\\",\\\"head_of_family_message\\\":\\\"Se;amat datang\\\",\\\"created_by_name\\\":\\\"123\\\",\\\"approved_by_name\\\":\\\"456\\\",\\\"designed_by_name\\\":\\\"789\\\",\\\"acknowledged_by_name\\\":\\\"012\\\",\\\"head_of_family_photo\\\":{},\\\"created_by_photo\\\":{},\\\"approved_by_photo\\\":{},\\\"designed_by_photo\\\":{},\\\"acknowledged_by_photo\\\":{}},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:29:46','2026-05-24 09:29:46','',''),
(7,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293670140788994,\\\"longitude\\\":104.00904218923104,\\\"accuracy\\\":156},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:34:17','2026-05-24 09:34:17','1.1293670140789','104.00904218923'),
(8,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:36:52','2026-05-24 09:36:52','',''),
(9,'POST.login.otp.send',0,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/send\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"email\\\":\\\"yenatricesn@gmail.com\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:38:15','2026-05-24 09:38:15','',''),
(10,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:39:39','2026-05-24 09:39:39','',''),
(11,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293648588528589,\\\"longitude\\\":104.00904860328092,\\\"accuracy\\\":126},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:45:06','2026-05-24 09:45:06','1.1293648588529','104.00904860328'),
(12,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:46:11','2026-05-24 09:46:11','',''),
(13,'POST.login.otp.send',0,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/send\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"email\\\":\\\"yenatricesn@gmail.com\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:46:28','2026-05-24 09:46:28','',''),
(14,'POST.login/otp/verify',0,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/verify\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"email\\\":\\\"yenatricesn@gmail.com\\\",\\\"otp\\\":\\\"38934\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:50:22','2026-05-24 09:50:22','',''),
(15,'POST.login.otp.send',0,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/send\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"email\\\":\\\"yenatricesn@gmail.com\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:53:56','2026-05-24 09:53:56','',''),
(16,'POST.login.otp.send',0,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/send\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"email\\\":\\\"yenatricesn@gmail.com\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:55:05','2026-05-24 09:55:05','',''),
(17,'POST.login/otp/verify',0,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/verify\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"email\\\":\\\"yenatricesn@gmail.com\\\",\\\"otp\\\":\\\"201708\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:55:15','2026-05-24 09:55:15','',''),
(18,'POST.login/otp/verify',1,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/verify\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"email\\\":\\\"yenatricesn@gmail.com\\\",\\\"otp\\\":\\\"985690\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:56:11','2026-05-24 09:56:11','',''),
(19,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293738932140764,\\\"longitude\\\":104.00904763326116,\\\"accuracy\\\":135},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:56:12','2026-05-24 09:56:12','1.1293738932141','104.00904763326'),
(20,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/1\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"partner\\\",\\\"target_member_id\\\":\\\"1\\\",\\\"name\\\":\\\"Madaline Day\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"2007-04-24\\\",\\\"birthplace\\\":\\\"Repudiandae voluptat\\\",\\\"bloodtype\\\":\\\"Illo exercitation re\\\",\\\"phonenumber\\\":\\\"+1 (469) 501-4715\\\",\\\"email\\\":\\\"bete@mailinator.com\\\",\\\"job\\\":\\\"Fuga Maxime volupta\\\",\\\"education_status\\\":\\\"Omnis incididunt eli\\\",\\\"address\\\":\\\"Voluptates quidem ip\\\"},\\\"status\\\":422}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:56:18','2026-05-24 09:56:18','',''),
(21,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/1\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"partner\\\",\\\"target_member_id\\\":\\\"1\\\",\\\"name\\\":\\\"Vernon Bray\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2002-08-18\\\",\\\"birthplace\\\":\\\"Deserunt quibusdam h\\\",\\\"bloodtype\\\":\\\"Sint asperiores ull\\\",\\\"phonenumber\\\":\\\"+1 (614) 681-9557\\\",\\\"email\\\":\\\"nuzyrijyc@mailinator.com\\\",\\\"job\\\":\\\"Magnam dolorum est\\\",\\\"education_status\\\":\\\"Dolor quasi voluptat\\\",\\\"address\\\":\\\"Enim est laborum Do\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:56:48','2026-05-24 09:56:48','',''),
(22,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/1\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"1\\\",\\\"name\\\":\\\"Isadora Martinez\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2016-10-18\\\",\\\"other_parent_id\\\":\\\"33\\\",\\\"birthplace\\\":\\\"Eos iusto quibusdam\\\",\\\"bloodtype\\\":\\\"Voluptatem autem cum\\\",\\\"address\\\":\\\"Culpa dolore aliqua\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:56:56','2026-05-24 09:56:56','',''),
(23,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/34\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"partner\\\",\\\"target_member_id\\\":\\\"34\\\",\\\"name\\\":\\\"Zena Slater\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"1971-01-20\\\",\\\"birthplace\\\":\\\"Sit voluptatum quod\\\",\\\"bloodtype\\\":\\\"Facere aliquip facer\\\",\\\"phonenumber\\\":\\\"+1 (835) 165-2664\\\",\\\"email\\\":\\\"bojuje@mailinator.com\\\",\\\"job\\\":\\\"Id dolor iste tempo\\\",\\\"education_status\\\":\\\"Odit commodo sit al\\\",\\\"address\\\":\\\"Sint dolorem ipsa\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:57:11','2026-05-24 09:57:11','',''),
(24,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/34\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"34\\\",\\\"name\\\":\\\"Tanisha Hicks\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"1977-06-22\\\",\\\"other_parent_id\\\":\\\"35\\\",\\\"birthplace\\\":\\\"Reprehenderit nobis\\\",\\\"bloodtype\\\":\\\"In dolor voluptatum\\\",\\\"address\\\":\\\"Id doloribus consequ\\\"},\\\"status\\\":422}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:57:18','2026-05-24 09:57:18','',''),
(25,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/34\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"34\\\",\\\"name\\\":\\\"Tanisha Hicks\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"2013-06-22\\\",\\\"other_parent_id\\\":\\\"35\\\",\\\"birthplace\\\":\\\"Reprehenderit nobis\\\",\\\"bloodtype\\\":\\\"In dolor voluptatum\\\",\\\"address\\\":\\\"Id doloribus consequ\\\"},\\\"status\\\":422}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:57:24','2026-05-24 09:57:24','',''),
(26,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/34\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"34\\\",\\\"name\\\":\\\"Tanisha Hicks\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"2017-06-22\\\",\\\"other_parent_id\\\":\\\"35\\\",\\\"birthplace\\\":\\\"Reprehenderit nobis\\\",\\\"bloodtype\\\":\\\"In dolor voluptatum\\\",\\\"address\\\":\\\"Id doloribus consequ\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:57:32','2026-05-24 09:57:32','',''),
(27,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/1\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"1\\\",\\\"name\\\":\\\"Illana Hawkins\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2025-08-26\\\",\\\"other_parent_id\\\":\\\"33\\\",\\\"birthplace\\\":\\\"Aute voluptate verit\\\",\\\"bloodtype\\\":\\\"Commodi exercitation\\\",\\\"address\\\":\\\"Magna anim dolorem p\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:57:42','2026-05-24 09:57:42','',''),
(28,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/1\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"1\\\",\\\"name\\\":\\\"Jason Mosley\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"2002-06-03\\\",\\\"other_parent_id\\\":\\\"33\\\",\\\"birthplace\\\":\\\"Placeat quia sunt\\\",\\\"bloodtype\\\":\\\"Quasi velit lorem eu\\\",\\\"address\\\":\\\"Quae ad eaque labore\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:57:47','2026-05-24 09:57:47','',''),
(29,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"37\\\",\\\"name\\\":\\\"Remedios Berger\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"1987-09-06\\\",\\\"other_parent_id\\\":null,\\\"birthplace\\\":\\\"Nesciunt blanditiis\\\",\\\"bloodtype\\\":\\\"Explicabo Sit pers\\\",\\\"address\\\":\\\"Ipsa delectus iure\\\"},\\\"status\\\":422}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:57:56','2026-05-24 09:57:56','',''),
(30,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"37\\\",\\\"name\\\":\\\"Remedios Berger\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2026-09-06\\\",\\\"other_parent_id\\\":null,\\\"birthplace\\\":\\\"Nesciunt blanditiis\\\",\\\"bloodtype\\\":\\\"Explicabo Sit pers\\\",\\\"address\\\":\\\"Ipsa delectus iure\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 09:58:04','2026-05-24 09:58:04','',''),
(31,'PUT.management.datauser.update',1,'\"{\\\"path\\\":\\\"management\\\\\\/data-user\\\\\\/update\\\\\\/1\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"_method\\\":\\\"PUT\\\",\\\"username\\\":\\\"superadmin\\\",\\\"email\\\":\\\"yenatricesn@gmail.com\\\",\\\"phonenumber\\\":\\\"082170976500\\\",\\\"levelid\\\":\\\"1\\\"},\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:00:55','2026-05-24 10:00:55','',''),
(32,'PUT.management.datauser.update',1,'\"{\\\"path\\\":\\\"management\\\\\\/data-user\\\\\\/update\\\\\\/1\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"_method\\\":\\\"PUT\\\",\\\"username\\\":\\\"superadmin\\\",\\\"email\\\":\\\"yenatricesn@gmail.com\\\",\\\"phonenumber\\\":\\\"082170976500\\\",\\\"levelid\\\":\\\"1\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:02:16','2026-05-24 10:02:16','',''),
(33,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:02:21','2026-05-24 10:02:21','',''),
(34,'POST.login.otp.send',0,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/send\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"phone\\\":\\\"082170976500\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:03:55','2026-05-24 10:03:55','',''),
(35,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 10:08:43','2026-05-24 10:08:43','',''),
(36,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1294298706290888,\\\"longitude\\\":104.00902165065767,\\\"accuracy\\\":155},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:10:16','2026-05-24 10:10:16','1.1294298706291','104.00902165066'),
(37,'POST.login.otp.send',0,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/send\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"phone\\\":\\\"082170976500\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:10:18','2026-05-24 10:10:18','',''),
(38,'POST.password.forgot.send_otp',0,'\"{\\\"path\\\":\\\"forgot-password\\\\\\/phone\\\\\\/send-otp\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"phone\\\":\\\"082170976500\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:13:40','2026-05-24 10:13:40','',''),
(39,'familymember.updated',1,'{\"id\":33,\"name\":\"Vernon Bray\",\"class\":\"FamilyMember\"}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 03:14:16','2026-05-24 03:14:16',NULL,NULL),
(40,'POST.password.forgot.send_otp',0,'\"{\\\"path\\\":\\\"forgot-password\\\\\\/phone\\\\\\/send-otp\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"phone\\\":\\\"082170976500\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:15:20','2026-05-24 10:15:20','',''),
(41,'POST.password.forgot.verify_otp',0,'\"{\\\"path\\\":\\\"forgot-password\\\\\\/phone\\\\\\/verify\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"phone\\\":\\\"082170976500\\\",\\\"otp\\\":\\\"561104\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:15:51','2026-05-24 10:15:51','',''),
(42,'POST.password.forgot.update',0,'\"{\\\"path\\\":\\\"forgot-password\\\\\\/change-password\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:16:02','2026-05-24 10:16:02','',''),
(43,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:16:08','2026-05-24 10:16:08','',''),
(44,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 10:18:55','2026-05-24 10:18:55','',''),
(45,'POST.login',38,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"vernon417\\\"},\\\"status\\\":302}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 10:19:03','2026-05-24 10:19:03','',''),
(46,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 10:19:22','2026-05-24 10:19:22','',''),
(47,'POST.login',2,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"budi684\\\"},\\\"status\\\":302}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 10:19:32','2026-05-24 10:19:32','',''),
(48,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 10:20:32','2026-05-24 10:20:32','',''),
(49,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 10:20:40','2026-05-24 10:20:40','',''),
(50,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1294285,\\\"longitude\\\":104.009113,\\\"accuracy\\\":500},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:21:08','2026-05-24 10:21:08','1.1294285','104.009113'),
(51,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293186232620345,\\\"longitude\\\":104.0091288669519,\\\"accuracy\\\":127},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:31:52','2026-05-24 10:31:52','1.129318623262','104.00912886695'),
(52,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/34\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"34\\\",\\\"name\\\":\\\"Aretha Carney\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2025-10-21\\\",\\\"other_parent_id\\\":\\\"35\\\",\\\"birthplace\\\":\\\"Minus blanditiis in\\\",\\\"bloodtype\\\":\\\"Similique eu necessi\\\",\\\"address\\\":\\\"Exercitationem conse\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:32:15','2026-05-24 10:32:15','',''),
(53,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293635088333376,\\\"longitude\\\":104.0090384619372,\\\"accuracy\\\":151},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 10:45:14','2026-05-24 10:45:14','1.1293635088333','104.00903846194'),
(54,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 10:46:53','2026-05-24 10:46:53','',''),
(55,'POST.login',2,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"budi684\\\"},\\\"status\\\":302}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 10:47:04','2026-05-24 10:47:04','',''),
(56,'CREATE.user',1,'{\"id\":46,\"changes\":[]}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:04:17','2026-05-24 04:04:17',NULL,NULL),
(57,'familymember.created',1,'{\"id\":41,\"name\":\"Accusantium quia rep\",\"class\":\"FamilyMember\"}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:04:17','2026-05-24 04:04:17',NULL,NULL),
(58,'relation.created',1,'{\"id\":9,\"name\":null,\"class\":\"Relation\"}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:04:17','2026-05-24 04:04:17',NULL,NULL),
(59,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293778879420497,\\\"longitude\\\":104.00904755520408,\\\"accuracy\\\":137},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 11:04:17','2026-05-24 11:04:17','1.129377887942','104.0090475552'),
(60,'CREATE.user',1,'{\"id\":47,\"changes\":[]}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:05:20','2026-05-24 04:05:20',NULL,NULL),
(61,'familymember.created',1,'{\"id\":42,\"name\":\"Mollit culpa eligend\",\"class\":\"FamilyMember\"}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:05:20','2026-05-24 04:05:20',NULL,NULL),
(62,'relation.created',1,'{\"id\":10,\"name\":null,\"class\":\"Relation\"}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:05:20','2026-05-24 04:05:20',NULL,NULL),
(63,'DELETE.user',1,'{\"id\":47,\"changes\":[]}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:05:45','2026-05-24 04:05:45',NULL,NULL),
(64,'CREATE.user',1,'{\"id\":48,\"changes\":[]}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:06:13','2026-05-24 04:06:13',NULL,NULL),
(65,'familymember.created',1,'{\"id\":43,\"name\":\"Est dignissimos non\",\"class\":\"FamilyMember\"}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:06:14','2026-05-24 04:06:14',NULL,NULL),
(66,'relation.created',1,'{\"id\":11,\"name\":null,\"class\":\"Relation\"}','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 04:06:14','2026-05-24 04:06:14',NULL,NULL),
(67,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.129362970635174,\\\"longitude\\\":104.00903722278966,\\\"accuracy\\\":149},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 11:14:53','2026-05-24 11:14:53','1.1293629706352','104.00903722279'),
(68,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 11:18:43','2026-05-24 11:18:43','',''),
(69,'POST.family.member.add_relative',2,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"partner\\\",\\\"target_member_id\\\":\\\"37\\\",\\\"name\\\":\\\"Rebecca Pittman\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"1987-12-16\\\",\\\"birthplace\\\":\\\"Quia est quis quia e\\\",\\\"bloodtype\\\":\\\"Velit aliquam aut n\\\",\\\"phonenumber\\\":\\\"+1 (596) 636-8945\\\",\\\"email\\\":\\\"sasi@mailinator.com\\\",\\\"job\\\":\\\"Quo et reprehenderit\\\",\\\"education_status\\\":\\\"Saepe doloremque mag\\\",\\\"address\\\":\\\"Consequuntur quae as\\\"},\\\"status\\\":200}\"','223.255.224.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-05-24 11:26:48','2026-05-24 11:26:48','',''),
(70,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','182.2.6.73','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 11:59:12','2026-05-24 11:59:12','',''),
(71,'POST.login',0,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"budi684\\\"},\\\"status\\\":302}\"','182.2.6.73','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 12:04:55','2026-05-24 12:04:55','',''),
(72,'POST.login',2,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"budi684\\\"},\\\"status\\\":302}\"','182.2.6.73','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 12:05:03','2026-05-24 12:05:03','',''),
(73,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','182.2.6.73','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 12:25:09','2026-05-24 12:25:09','',''),
(74,'POST.login',38,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"vernon417\\\"},\\\"status\\\":302}\"','182.2.6.73','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 12:25:29','2026-05-24 12:25:29','',''),
(75,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','182.2.6.73','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36','2026-05-24 12:47:29','2026-05-24 12:47:29','',''),
(76,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1416781,\\\"longitude\\\":103.9982796,\\\"accuracy\\\":7.105999946594238},\\\"status\\\":200}\"','182.2.6.73','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36','2026-05-24 12:47:36','2026-05-24 12:47:36','1.1416781','103.9982796'),
(77,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/1\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Budi Santosos\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":null,\\\"job\\\":\\\"Pensiunan\\\",\\\"email\\\":\\\"budi.santoso@example.com\\\",\\\"phonenumber\\\":null,\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1','2026-05-24 13:37:41','2026-05-24 13:37:41','',''),
(78,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 13:38:48','2026-05-24 13:38:48','',''),
(79,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 13:39:03','2026-05-24 13:39:03','',''),
(80,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":404}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 13:39:08','2026-05-24 13:39:08','',''),
(81,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/44\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 13:39:17','2026-05-24 13:39:17','',''),
(82,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/49\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 13:39:33','2026-05-24 13:39:33','',''),
(83,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/47\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 13:39:36','2026-05-24 13:39:36','',''),
(84,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:01:34','2026-05-24 14:01:34','',''),
(85,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:01:44','2026-05-24 14:01:44','',''),
(86,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":404}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:01:45','2026-05-24 14:01:45','',''),
(87,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":404}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:01:48','2026-05-24 14:01:48','',''),
(88,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/44\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:02:21','2026-05-24 14:02:21','',''),
(89,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/49\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:02:23','2026-05-24 14:02:23','',''),
(90,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:07:27','2026-05-24 14:07:27','',''),
(91,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:07:32','2026-05-24 14:07:32','',''),
(92,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/44\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:09:02','2026-05-24 14:09:02','',''),
(93,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/49\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:09:13','2026-05-24 14:09:13','',''),
(94,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:26:30','2026-05-24 14:26:30','',''),
(95,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:27:05','2026-05-24 14:27:05','',''),
(96,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/44\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:27:06','2026-05-24 14:27:06','',''),
(97,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/49\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:27:08','2026-05-24 14:27:08','',''),
(98,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:32:05','2026-05-24 14:32:05','',''),
(99,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/1\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Budi Santosos\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":null,\\\"job\\\":\\\"Pensiunan\\\",\\\"email\\\":\\\"budi.santoso@example.com\\\",\\\"phonenumber\\\":null,\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:38:46','2026-05-24 14:38:46','',''),
(100,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/1\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Budi Santosoooo\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":null,\\\"job\\\":\\\"Pensiunan\\\",\\\"email\\\":\\\"budi.santoso@example.com\\\",\\\"phonenumber\\\":null,\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1','2026-05-24 14:39:19','2026-05-24 14:39:19','',''),
(101,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"partner\\\",\\\"target_member_id\\\":\\\"38\\\",\\\"name\\\":\\\"Dillon Anderson\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2023-01-08\\\",\\\"birthplace\\\":\\\"Dignissimos corrupti\\\",\\\"bloodtype\\\":\\\"A\\\",\\\"phonenumber\\\":\\\"+1 (738) 452-3418\\\",\\\"email\\\":\\\"napope@mailinator.com\\\",\\\"job\\\":\\\"Laborum et qui conse\\\",\\\"education_status\\\":\\\"Consequatur velit s\\\",\\\"address\\\":\\\"Qui voluptas sit re\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:40:53','2026-05-24 14:40:53','',''),
(102,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"38\\\",\\\"name\\\":\\\"Carter Merritt\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"2009-01-10\\\",\\\"other_parent_id\\\":\\\"45\\\",\\\"birthplace\\\":\\\"Non soluta fugiat la\\\",\\\"bloodtype\\\":\\\"A\\\",\\\"address\\\":\\\"Sit inventore sint\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:41:02','2026-05-24 14:41:02','',''),
(103,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:41:30','2026-05-24 14:41:30','',''),
(104,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:41:50','2026-05-24 14:41:50','',''),
(105,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/44\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:41:53','2026-05-24 14:41:53','',''),
(106,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/49\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:41:54','2026-05-24 14:41:54','',''),
(107,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/35\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Zena Slater\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":\\\"Odit commodo sit al\\\",\\\"job\\\":\\\"Id dolor iste tempo\\\",\\\"email\\\":\\\"bojuje@mailinator.com\\\",\\\"phonenumber\\\":\\\"+1 (835) 165-2664\\\",\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:43:40','2026-05-24 14:43:40','',''),
(108,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:45:33','2026-05-24 14:45:33','',''),
(109,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:46:32','2026-05-24 14:46:32','',''),
(110,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:53:04','2026-05-24 14:53:04','',''),
(111,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/43\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:53:09','2026-05-24 14:53:09','',''),
(112,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/43\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":404}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:53:10','2026-05-24 14:53:10','',''),
(113,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/50\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:53:12','2026-05-24 14:53:12','',''),
(114,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/51\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:53:14','2026-05-24 14:53:14','',''),
(115,'PUT.management.datauser.update',1,'\"{\\\"path\\\":\\\"management\\\\\\/data-user\\\\\\/update\\\\\\/38\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"_method\\\":\\\"PUT\\\",\\\"username\\\":\\\"vernon417\\\",\\\"email\\\":\\\"nuzyrijyc@mailinator.com\\\",\\\"phonenumber\\\":\\\"+1 (614) 681-9557\\\",\\\"levelid\\\":\\\"2\\\",\\\"roleid\\\":\\\"2\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:54:41','2026-05-24 14:54:41','',''),
(116,'PUT.management.datauser.update',1,'\"{\\\"path\\\":\\\"management\\\\\\/data-user\\\\\\/update\\\\\\/38\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"_method\\\":\\\"PUT\\\",\\\"username\\\":\\\"vernon417\\\",\\\"email\\\":\\\"nuzyrijyc@mailinator.com\\\",\\\"phonenumber\\\":\\\"+1 (614) 681\\\",\\\"levelid\\\":\\\"2\\\",\\\"roleid\\\":\\\"2\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:54:50','2026-05-24 14:54:50','',''),
(117,'POST.family.leader.store',1,'\"{\\\"path\\\":\\\"leader-setting\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"owner_userid\\\":\\\"38\\\",\\\"heir_memberid\\\":\\\"1\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:55:04','2026-05-24 14:55:04','',''),
(118,'POST.login',38,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"vernon417\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:55:31','2026-05-24 14:55:31','',''),
(119,'POST.family.leader.pin.create',38,'\"{\\\"path\\\":\\\"leader-setting\\\\\\/pin\\\\\\/create\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"owner_userid\\\":\\\"38\\\",\\\"pin\\\":\\\"1234\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:55:43','2026-05-24 14:55:43','',''),
(120,'POST.family.leader.store',38,'\"{\\\"path\\\":\\\"leader-setting\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"owner_userid\\\":\\\"38\\\",\\\"heir_memberid\\\":\\\"1\\\",\\\"pin\\\":\\\"1234\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:55:48','2026-05-24 14:55:48','',''),
(121,'POST.family.leader.pin.reset',1,'\"{\\\"path\\\":\\\"leader-setting\\\\\\/pin\\\\\\/reset\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"owner_userid\\\":\\\"38\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:55:59','2026-05-24 14:55:59','',''),
(122,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"37\\\",\\\"name\\\":\\\"Wynne Norton\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2016-12-29\\\",\\\"other_parent_id\\\":\\\"44\\\",\\\"birthplace\\\":\\\"Elit sit eveniet a\\\",\\\"bloodtype\\\":\\\"AB+\\\",\\\"address\\\":\\\"Non ut harum dolor r\\\"},\\\"status\\\":422}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:56:21','2026-05-24 14:56:21','',''),
(123,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"37\\\",\\\"name\\\":\\\"Wynne Norton\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2026-05-20\\\",\\\"other_parent_id\\\":\\\"44\\\",\\\"birthplace\\\":\\\"Elit sit eveniet a\\\",\\\"bloodtype\\\":\\\"AB+\\\",\\\"address\\\":\\\"Non ut harum dolor r\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:56:34','2026-05-24 14:56:34','',''),
(124,'POST.management.datauser.store',1,'\"{\\\"path\\\":\\\"management\\\\\\/data-user\\\\\\/store\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"povowibet\\\",\\\"email\\\":\\\"tuvohowe@mailinator.com\\\",\\\"phonenumber\\\":\\\"0886857\\\",\\\"levelid\\\":\\\"1\\\",\\\"roleid\\\":\\\"2\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:56:57','2026-05-24 14:56:57','',''),
(125,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/35\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Zena Slater\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":\\\"Odit commodo sit al\\\",\\\"job\\\":\\\"Id dolor iste tempo\\\",\\\"email\\\":\\\"bojuje@mailinator.com\\\",\\\"phonenumber\\\":\\\"+1 (835) 165-2664\\\",\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:59:13','2026-05-24 14:59:13','',''),
(126,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/35\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Zena Slater\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":\\\"Odit commodo sit al\\\",\\\"job\\\":\\\"Id dolor iste tempo\\\",\\\"email\\\":\\\"bojuje@mailinator.com\\\",\\\"phonenumber\\\":\\\"+1 (835) 165-2664\\\",\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 14:59:30','2026-05-24 14:59:30','',''),
(127,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/35\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Zena Slater\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":\\\"Odit commodo sit al\\\",\\\"job\\\":\\\"Id dolor iste tempo\\\",\\\"email\\\":\\\"bojuje@mailinator.com\\\",\\\"phonenumber\\\":\\\"+1 (835) 165-2664\\\",\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:03:27','2026-05-24 15:03:27','',''),
(128,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/40\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Aretha Carney\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":null,\\\"job\\\":null,\\\"email\\\":null,\\\"phonenumber\\\":null,\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:03:49','2026-05-24 15:03:49','',''),
(129,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/40\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Aretha Carney\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":null,\\\"job\\\":null,\\\"email\\\":null,\\\"phonenumber\\\":null,\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:07:08','2026-05-24 15:07:08','',''),
(130,'UPDATE.user',1,'{\"id\":46,\"changes\":{\"password\":\"$2y$12$mFQq43YvongmsFowoz4fJugT5oeCbWHL\\/s6TT49LowEYRkRXN.pjq\"}}','180.252.48.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 08:08:23','2026-05-24 08:08:23',NULL,NULL),
(131,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/34\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Isadora Martinez\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":null,\\\"job\\\":null,\\\"email\\\":null,\\\"phonenumber\\\":null,\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:08:39','2026-05-24 15:08:39','',''),
(132,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:14:28','2026-05-24 15:14:28','',''),
(133,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:15:11','2026-05-24 15:15:11','',''),
(134,'POST.password.forgot.send_otp',0,'\"{\\\"path\\\":\\\"forgot-password\\\\\\/phone\\\\\\/send-otp\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"phone\\\":\\\"082170976500\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:15:23','2026-05-24 15:15:23','',''),
(135,'POST.password.forgot.verify_otp',0,'\"{\\\"path\\\":\\\"forgot-password\\\\\\/phone\\\\\\/verify\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"phone\\\":\\\"082170976500\\\",\\\"otp\\\":\\\"521674\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:15:40','2026-05-24 15:15:40','',''),
(136,'POST.login.otp.send',0,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/send\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"email\\\":\\\"yenatricesn@gmail.com\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:16:09','2026-05-24 15:16:09','',''),
(137,'POST.login/otp/verify',1,'\"{\\\"path\\\":\\\"login\\\\\\/otp\\\\\\/verify\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"email\\\":\\\"yenatricesn@gmail.com\\\",\\\"otp\\\":\\\"317096\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:17:41','2026-05-24 15:17:41','',''),
(138,'POST.management.setting.website.update',1,'\"{\\\"path\\\":\\\"management\\\\\\/setting\\\\\\/website\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"systemname\\\":\\\"Sepeda\\\",\\\"systemmanager\\\":\\\"ayam\\\",\\\"systemcontact\\\":\\\"123\\\",\\\"systemaddress\\\":\\\"adadeg\\\",\\\"systemlogo\\\":{}},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:18:29','2026-05-24 15:18:29','',''),
(139,'POST.management.datamaster.social.store',1,'\"{\\\"path\\\":\\\"management\\\\\\/data-master\\\\\\/social\\\\\\/store\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"socialname\\\":\\\"Youtube\\\",\\\"prefix\\\":null},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:19:20','2026-05-24 15:19:20','',''),
(140,'DELETE.management.datamaster.social.delete',1,'\"{\\\"path\\\":\\\"management\\\\\\/data-master\\\\\\/social\\\\\\/delete\\\\\\/1\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":{\\\"_method\\\":\\\"DELETE\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:19:27','2026-05-24 15:19:27','',''),
(141,'POST.family.event.store',1,'\"{\\\"path\\\":\\\"events\\\\\\/store\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"title\\\":\\\"Dignissimos irure su\\\",\\\"description\\\":\\\"Incidunt explicabo\\\",\\\"event_date\\\":\\\"3000-02-03T14:23\\\",\\\"location\\\":\\\"Culpa voluptatem di\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:20:23','2026-05-24 15:20:23','',''),
(142,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:21:38','2026-05-24 15:21:38','',''),
(143,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:22:40','2026-05-24 15:22:40','',''),
(144,'POST.chatbot.ask',1,'\"{\\\"path\\\":\\\"chatbot\\\\\\/ask\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"message\\\":\\\"resep kue stoberi\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:22:54','2026-05-24 15:22:54','',''),
(145,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/45\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:28:54','2026-05-24 15:28:54','',''),
(146,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/43\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:31:29','2026-05-24 15:31:29','',''),
(147,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/51\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:31:34','2026-05-24 15:31:34','',''),
(148,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/50\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:31:34','2026-05-24 15:31:34','',''),
(149,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/45\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:31:44','2026-05-24 15:31:44','',''),
(150,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"partner\\\",\\\"target_member_id\\\":\\\"38\\\",\\\"name\\\":\\\"Astra Mosley\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"1988-01-08\\\",\\\"birthplace\\\":\\\"Adipisicing voluptas\\\",\\\"bloodtype\\\":\\\"AB+\\\",\\\"phonenumber\\\":\\\"+1 (466) 672-6572\\\",\\\"email\\\":\\\"siretyje@mailinator.com\\\",\\\"job\\\":\\\"Non dolore deleniti\\\",\\\"education_status\\\":\\\"Praesentium dolor Na\\\",\\\"address\\\":\\\"Ab sit et ut laboru\\\"},\\\"status\\\":422}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:31:51','2026-05-24 15:31:51','',''),
(151,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"partner\\\",\\\"target_member_id\\\":\\\"38\\\",\\\"name\\\":\\\"Blake Ayala\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2021-09-25\\\",\\\"birthplace\\\":\\\"Rerum asperiores pra\\\",\\\"bloodtype\\\":\\\"A-\\\",\\\"phonenumber\\\":\\\"+1 (434) 474-4005\\\",\\\"email\\\":\\\"zehyv@mailinator.com\\\",\\\"job\\\":\\\"Perspiciatis evenie\\\",\\\"education_status\\\":\\\"Aut lorem est a lab\\\",\\\"address\\\":\\\"Magnam cum nulla ill\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:32:01','2026-05-24 15:32:01','',''),
(152,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293743591960708,\\\"longitude\\\":104.0090471145239,\\\"accuracy\\\":131},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:33:56','2026-05-24 15:33:56','1.1293743591961','104.00904711452'),
(153,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/37\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:34:44','2026-05-24 15:34:44','',''),
(154,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/35\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Zena Slater\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":\\\"Odit commodo sit al\\\",\\\"job\\\":\\\"Id dolor iste tempo\\\",\\\"email\\\":\\\"bojuje@mailinator.com\\\",\\\"phonenumber\\\":\\\"+1 (835) 165-2664\\\",\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:36:06','2026-05-24 15:36:06','',''),
(155,'POST.login',2,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"budi684\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:38:29','2026-05-24 15:38:29','',''),
(156,'POST.family.livelocation.update',2,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293610534490302,\\\"longitude\\\":104.00905324399645,\\\"accuracy\\\":117},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:38:30','2026-05-24 15:38:30','1.129361053449','104.009053244'),
(157,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:38:36','2026-05-24 15:38:36','',''),
(158,'POST.login',45,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"aretha969\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:39:00','2026-05-24 15:39:00','',''),
(159,'POST.family.livelocation.update',45,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293791264617703,\\\"longitude\\\":104.00904879372379,\\\"accuracy\\\":137},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:39:02','2026-05-24 15:39:02','1.1293791264618','104.00904879372'),
(160,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/48\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:40:07','2026-05-24 15:40:07','',''),
(161,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"partner\\\",\\\"target_member_id\\\":\\\"38\\\",\\\"name\\\":\\\"Preston Wolfe\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"1985-02-06\\\",\\\"birthplace\\\":\\\"Est consequatur Et\\\",\\\"bloodtype\\\":\\\"O\\\",\\\"phonenumber\\\":\\\"+1 (416) 524-1985\\\",\\\"email\\\":\\\"tani@mailinator.com\\\",\\\"job\\\":\\\"Et qui nulla ipsa i\\\",\\\"education_status\\\":\\\"Eius atque magna ven\\\",\\\"address\\\":\\\"Est qui ab magni ip\\\"},\\\"status\\\":422}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:40:14','2026-05-24 15:40:14','',''),
(162,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"partner\\\",\\\"target_member_id\\\":\\\"38\\\",\\\"name\\\":\\\"Colorado Buchanan\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2007-01-03\\\",\\\"birthplace\\\":\\\"Quibusdam ut sit co\\\",\\\"bloodtype\\\":\\\"B-\\\",\\\"phonenumber\\\":\\\"+1 (362) 794-9656\\\",\\\"email\\\":\\\"quxejypek@mailinator.com\\\",\\\"job\\\":\\\"In minim deserunt di\\\",\\\"education_status\\\":\\\"Anim distinctio Ill\\\",\\\"address\\\":\\\"Omnis harum delectus\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:40:24','2026-05-24 15:40:24','',''),
(163,'DELETE.family.member.delete',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/46\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:41:18','2026-05-24 15:41:18','',''),
(164,'PUT.family.member.update_basic',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/49\\\\\\/update-basic\\\",\\\"method\\\":\\\"PUT\\\",\\\"input\\\":{\\\"name\\\":\\\"Colorado Buchanansss\\\",\\\"life_status\\\":\\\"alive\\\",\\\"deaddate\\\":null,\\\"grave_location_url\\\":null,\\\"bloodtype\\\":null,\\\"education_status\\\":\\\"Anim distinctio Ill\\\",\\\"job\\\":\\\"In minim deserunt di\\\",\\\"email\\\":\\\"quxejypek@mailinator.com\\\",\\\"phonenumber\\\":\\\"+1 (362) 794-9656\\\",\\\"country\\\":null,\\\"_method\\\":\\\"PUT\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:41:24','2026-05-24 15:41:24','',''),
(165,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1293648459723196,\\\"longitude\\\":104.00904247735346,\\\"accuracy\\\":137},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:44:57','2026-05-24 15:44:57','1.1293648459723','104.00904247735'),
(166,'POST.chatbot.ask',1,'\"{\\\"path\\\":\\\"chatbot\\\\\\/ask\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"message\\\":\\\"berikan resep kue stoberi\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:45:09','2026-05-24 15:45:09','',''),
(167,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"38\\\",\\\"name\\\":\\\"Jonas Burks\\\",\\\"gender\\\":\\\"male\\\",\\\"birthdate\\\":\\\"2024-11-26\\\",\\\"other_parent_id\\\":\\\"49\\\",\\\"birthplace\\\":\\\"Blanditiis iusto dol\\\",\\\"bloodtype\\\":\\\"A+\\\",\\\"address\\\":\\\"Omnis officia sint\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:46:19','2026-05-24 15:46:19','',''),
(168,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"38\\\",\\\"name\\\":\\\"Nolan Dillon\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"1991-02-09\\\",\\\"other_parent_id\\\":\\\"49\\\",\\\"birthplace\\\":\\\"Eius et consequatur\\\",\\\"bloodtype\\\":\\\"O\\\",\\\"address\\\":\\\"Laudantium culpa o\\\"},\\\"status\\\":422}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:46:28','2026-05-24 15:46:28','',''),
(169,'POST.family.member.add_relative',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/38\\\\\\/add-relative\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"relation_type\\\":\\\"child\\\",\\\"target_member_id\\\":\\\"38\\\",\\\"name\\\":\\\"Nolan Dillon\\\",\\\"gender\\\":\\\"female\\\",\\\"birthdate\\\":\\\"2025-02-09\\\",\\\"other_parent_id\\\":\\\"49\\\",\\\"birthplace\\\":\\\"Eius et consequatur\\\",\\\"bloodtype\\\":\\\"O\\\",\\\"address\\\":\\\"Laudantium culpa o\\\"},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 15:46:36','2026-05-24 15:46:36','',''),
(170,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.129385,\\\"longitude\\\":104.0090485,\\\"accuracy\\\":160},\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-24 16:51:03','2026-05-24 16:51:03','1.129385','104.0090485'),
(171,'UPDATE.user',1,'{\"id\":1,\"changes\":{\"username\":\"superadminawwaaaaaaawaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa\"}}','180.252.49.116','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-25 04:46:20','2026-05-25 04:46:20',NULL,NULL),
(172,'UPDATE.user',1,'{\"id\":1,\"changes\":{\"username\":\"superadmin\"}}','180.252.49.116','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-25 04:46:25','2026-05-25 04:46:25',NULL,NULL),
(173,'familymember.updated',1,'{\"id\":1,\"name\":\"Budi Santosoooo\",\"class\":\"FamilyMember\"}','180.252.49.116','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-25 04:47:36','2026-05-25 04:47:36',NULL,NULL),
(174,'familymember.updated',1,'{\"id\":1,\"name\":\"Budi Santosoooo\",\"class\":\"FamilyMember\"}','180.252.49.116','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-25 04:47:41','2026-05-25 04:47:41',NULL,NULL),
(175,'UPDATE.user',1,'{\"id\":40,\"changes\":{\"levelid\":1}}','180.252.49.116','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-25 04:47:50','2026-05-25 04:47:50',NULL,NULL),
(176,'UPDATE.user',1,'{\"id\":40,\"changes\":{\"levelid\":2}}','180.252.49.116','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-25 04:47:53','2026-05-25 04:47:53',NULL,NULL),
(177,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','36.68.180.146','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-26 06:35:42','2026-05-26 06:35:42','',''),
(178,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-26 19:21:18','2026-05-26 19:21:18','',''),
(179,'POST.login',0,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:02:09','2026-05-28 14:02:09','',''),
(180,'POST.login',0,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"admin\\\"},\\\"status\\\":302}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:02:17','2026-05-28 14:02:17','',''),
(181,'POST.login',0,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:02:52','2026-05-28 14:02:52','',''),
(182,'POST.login',0,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\",\\\"g-recaptcha-response\\\":\\\"0cAFcWeA6onQV6nBwufpNJj78fDwiH4Lcik7nvLr87P-q1vA0A2UwunLzaJylIXd1pHdWbsbiyVyRdqgJvesh32SZ8CGOhqjvK9CQkmBdQl5RhlJNJn2gLN4_i6xkOqdl1iZcuO-alf3hSWC3kWtU7I8b2olUd49ktzR-BtSx55SuuAEDnCiBD2dki3SAyIAeTgOwVPf3JPe_g48YNVKsMsEmS417LXBrnjDZyXRtS_tKRDZyy7-f-cp6j5Y4YYaJJiaonpwto6qxJ65skW5TN1ifRfWK52wPmANvYvCye_iMzw7SiyX9qSJtOcwT2lYb_RPbeqNu-9gJ2mEoxwuwg5k4nWygRAAjpg-7DpdM-VWod2tS9TMQPjGhZE13A_nHxeml-vk_ozE_CSVp5oFnL8pQp43jJQSCWJTf5kPLUiWJ1twq-9nPk3dXAFLe5Tf1cuk3JEeS0nuMCVbMxoEt9ec8v_I5zq71HyKrIIw3o4RMZcH3yPvlr1ZQJZSaCQysbFyqygFsCGoxR53f6QaJTbMVDWxtsMDIuPoXxoP3irJHh1aVF3QGOfP98m_l2Bzs0dt21wvC32pb6RnqDFklbvkYTIwwaYihOhPu3vKw5W2pDed4I5HEtSLHAHGIBsbTnw3yANMbGxI45nvqYSMtqfAbXTk-0MwKHY7oBVvxMXBhzSa5gamlRQBWGpeOc_6ww-0joEskGpXdwd0TKcniSSsNzX_bQ845WSWNjDuMjM1H8k9b2WMLgbFzD-c-0BZMttkN8X3cIrCdgqtr5uwMTyKuZUJ50Ow9MT-ltzDXWJxZp6j2gMdrri4AcYUgYe83uGIF6axmQvCwcN7QOky-_uzoTV8i4VJ3kfZFlLy9QI8ysP6lH2jOlywOtd3Fqqa-jWz4lRZz6tBSAI0nBq2i5u9KHClRaKfiFpsMxUKebs-6Q1VukzqnK3Gw2-V8cTc1fQrc0qF5tfKSq7yWWPizwyTECb97QhUAAh_iYCw89Ki4OV5j4BUZpYUFeOOEf3NnYbOZS9HXQUiRuS6HKbJqvOTeGJSnoCNikoec_FKUer95hmk88cumngOOZ_h810L6tLGfbBHIGfvUZNtPOruk9Pox1r4WzcR8uOIzBOgldY9lERurNnalvLqIvVVRtd6Uh6tMTqyxvp4omqhe228YylAV1OFD1Je9R25IuckORqj-5eNl80g7TxZJ5yw_XlCd5dMZ37v_tpUyvCEzroZ1L7zGhGH5JmVwTdXjcr2aEVUD3hLxYC4HpjujE6cb5cC_niAmt3ByfFhzxRqFjIVewBIoLqq0QPd-e5LiCWBv8XPjQTS9Pq8aLFvHLibb-Q8XNjVU6EiX86Dtz-CoLVG5XNbPYqvky5RKzr7c5ro2hPyPEqUe0M2sR0SJKmNFjYNBTYlqesAFF17c1YgzOCzUsj69eU2lIbSvgKOpBWurkbj3eD17F6tpscxXqkclh6wd94hs89ZnOT2meVcWLqdEh4doRc-NjaW51czjfAltPqUTVF5nLFD4mgDS4Ywa5wyn8pmCTFYjLC6qMDWn0j0uR6hwSKb6Scp8ttoMYQMPTtSzmzcpdOvPgbmWbiWii1XjgK1byVV2UnBFeSVeeDDo2jkpGN_D7sgJgKVQ541IPG26OFsjiXfomc2YVGrE6AExrmN55H--R8htw5CAJgpXo4p38h0N5U951k4EjE4eE6-_oamHmO3muanKOHr0CkDQt4uETLk9y5d9YL2t_0-7PCpwOiIz4LGLL5st5Tzu7QsXnTDsSg9u2HavOGFWRKCjlgi7ClU1wvFuYb8eMHpVz5-BqXrTAGBtCyLrGnjVU31dvBFeJTCcvNKv4Z9PqIZs311p_toVJuaCABS7-ZuOpZOdR7c_sWN3jlrNTUfj8lkyGuES8jPVfKx7s9BnppZ9c5jqlpSr0oSDNp2sMqZs_npXJTV3qC3RbCM8zazGk6ds-JP-mW-u32tQRJacjKBiKETkC-XNVGU6zJEpQC4HgqupN9XVIg58eFnbGVEoKa6Dd6mm9fE0n8BrQscr73bqxYVbS1IEFN_fX9ljjz7lGvL_wLS47UVtFSw\\\"},\\\"status\\\":302}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:03:33','2026-05-28 14:03:33','',''),
(183,'POST.login',0,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\",\\\"g-recaptcha-response\\\":\\\"0cAFcWeA5tIkkd12qjU_SUukyNNjgr7r4g6KY4jO0JAbHJOhwmDM2lmCm0Py1SzEceAbMESak4RRUfnZcFxaUGEXXXDuTiAsyfIvSIxfz0xSVugMjmoi-Z9CIyel8rfnhbuQ1pQYUm4Xjj1AbOinvLQ59kIZQMBe_5EF_8X5C-6_7V-HujHq01N0mC5plUNcaApzvVjJUxuBEdGV5x0DsI0SakPRV0I6RhAiH7FuOvSg3FpXZhm2i6pEejL4ZA4efT3RJ71YU-7CRTgkiIxHDwp_EgjCYHkckJXFnudg4Ef3O4Ko5SyNJUm71FhMnDOeNGoB1XNn888p49k9Nq0L6FQ9KMcBbVspHsZiIxNJhAD8cW1Ba887rfpj1nkDrKRJ9XSUNP8ByBMCbIIZeJfp4NWocXeunKROOsSMzj7DulaOTzDr9EcSX6n-FUe0gftpsDoVN3Dq4KFW3Tu4tHNvaxncaG9wkCLAr5AczTKv2Fkf0jBVwvUg_IqEl6GyjbWVIkHdfBesSQcRk76NDrlP6wk3d8vqdaTGNNKCjEi3KPX5W9UY3xmjV4canww8L5fY05UgvguosVVZi9hKU_vXRXxzZCp3dTs_bI4Sz2hNW_1qYsZHxn800EmI1ae9goAgqVThZMiLypc7T0SA7AnEnPT00P768vad8hBW4HjvlWZXow7kjASBpqNBpJC-PxMUJKPeqJqjmZR8ib9I56cZB2BKC5KcdathHO24MfzUxWM6TEunAhwF0QUVlcPiP9q4yJYyU7xiftD5X75WWR8hnnSUv5AvPEaIQdQQlK_uvzWgyZe5YIbOx7tNRGDgROrR-uOXuAKKmC_tzfMzUJ_NZAMUGTOfmnEhc1eR9EFAVJ0c_4_kiJYuUMK4mFalS6ZlevNL0VD_6pW170K6N5bh_sR8LQkEHUpU4OGIVOcp9xkZiTKVX0dPbQTh_e-tecvoMrBYzrohlyhqVdmAIS0_g7DmnZsyROftnnBwg1UPwwMIU7WiSHnwTVGJGeUKYXQ-sBRjZwL5LuLnQw9fGzHfesY0BB2ziGRaWjBDJ2GsdhR1xfRmg22eP4sJ7ERRNPJbiQcqjhotOaIaWhjUTACiFmftOtBesw4w97VJW7o-SwV_01N5z69SsUJnWHQRnEcTXWKqCOWxCtMnpweM253l683wESjr6b6LFAY0TVk_qa0vGNoZO-mDlP0D6hfj1GOO_Ao2osgYXJOVb0uDoVLzCZc2mfO5AqUA1AFVItYBz0ckW57ByiDmzuYqUzb-W_ZM_aoe4zI0RW54KLRrx5c3VP6QAzf778geRgtTqm63vpXdgSYAPPsKuECzgg8pYyWQTFW477BElZSo6WBZj31h9wI-8HW-f04qOHdfqNKjtojQByvqRUwB5ILTR61dfjXbDqHrMLFpqjuAh1hXj6NDbLVgez39_WiqpaKmNQxxEU7bj0bUE_ke4abOxuwJQ0E5SJZK4B0T7x-9kRq4xEy4BoTn1om2VU4XbogjtNdWLfGgSSFxhWIJWY3pfozwc2cUOSglmfSR2WqutsmEfk6k3uVN51LVIT9b6A4fp--rE_mwxM2o3pUsIyu3HjuSuQ-XqTfkIxUVunSnjlMlGqpd3mdShxwN607IePGASVFne-6olGBCmZj-M4rd6IV3ogi2A3onK1zppI9DKqUej44ei09YmLHY8T4JCN77EEX-aGz8cAisfJRXfcFwk_gc27awF-lw1z2dsnGxxtl-24YH26QHgMRgHgzmMxwQ3cWvTXEfolQJTcmfL1rxJPkV_TqfR6AVfFJYg7-T-Z8OMaihfFVXpYxBg5O5tUbYAA_YHmCbt6qruHnTUcddQTmS_M4ppcSXbtt-C8TDM-tTBaQG-kb4z7K1vF7Q1FfaOJTEWl4paswAgUt8rtFGXJbxbJlOj-m1bjxtxIGf1IpGZ8LDbjgw-ugncyl_8gfs-0TB94ApzMTUFBkrDKog2xelWYt467jWtrI185V345j_e1vNRill0L2A2PabZDdoksze5QVQGyyJJKHlAONzAL23ZnJjAFtM_suXi1MSVXvyoGGwTJ-m0TixUrZt2Vxi40_0ukHbjwdZEuE8SGS2c2xqijDpFlBqUZBSt365tj\\\"},\\\"status\\\":302}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:03:41','2026-05-28 14:03:41','',''),
(184,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\",\\\"g-recaptcha-response\\\":\\\"0cAFcWeA7dCKj_dFUNtdrNxkLw9EfyYBnqEZfEy0pvpulf4s8Cb5ojO58gJskQ8X3XDr6duIG7FfM7k5cVyShKAiA19K-p7Oc9KiHtaYmCc8Lat4Jh-jrHv4Q8U6ZiK1HY4-96MI0LAPRke48yNzzk7tHrhVWlSs15UP4mvo1sVb5rEu4ZsVMktIHg5yexxGTkQLmQqMLZAOzUQXF7wq-LNC8nf-OzBc9Bs6jN537Hu9y1HF5VH66kggxMqR2XbpnaN4cP8RaUG1DcTWVpYQo_GFAnB9sQ-Z2KINWtlR-wXC7GTbzwvp3Nz651icwbEIgDNa-O4jx6LdFBrLHihUN7QIUCpre0yfAhOPkyzQE4yKj75irk1SzTOx-f8KCDFfX2sjpW1L6lFwCzwgzQiLDFNHDNVhNWit3-1UHPfIUpCP-t1DK2ldse6NXNBiGnI7BZqWkTr2eW-Krb_Q4J-YoWpDaKOvZfAoMKAWxvsI0KSxLhy89oW0qDqfcIxeqNHkjujYrYf_CxGc6KSYgbwr4R7WLJ0iaGvHnAJlStLw__mQ9twyuU0IUxVMB6Fz8xOlga5Tk-0mVwSwx6-k2JuagUdgXTZNk9T6Uei5g6WBb1ASxsOIkXuLu1-UtpI7KK_9e_zg6TUin-R3iH-Fs9qq-3CRVD-iKpJewU_Znfg6U-a89mfzUoWglZfcITrCCsJ7DYN59K8zx44S4e9dA43OpsDbPo8UgPgiZ-_9lt-mOAsYM-4x9YCfIwlLIU4mG12zuQoZAKrSpV545bMNYpaLR054M3reLHgtlBHkY_hskobc8LSDL97MYP-ZkQdLvXtHj-3_1mfEN6hlakntEfiMWr8tHNhP_wuIwubExQnY-mSJO_zMQvN0wvLOBAlQLh2_ITKzhiYxgxcZb9-krs5CAx3T2hTJHh7w7hEk0Pyw-BvnidQIndZ0P7qbpg6EKtqsjLANCfosC1jU106xkCoIrL56B6hkWlqOokLg28XbA9Zl_CRUZn-wKi9o0ICupGvXXkpKxGI8STTYPwmsHFK3dvtCqi7AZoL_NncJuzZXId3ixQEfjD7cpR-WjtU3EEF2EPHQa_KPxNxcSab5lp-2u1rRv8XR1HeY41QvcCjFmncjVSWzLKSJLiH3aOYLUgsD16zaNqkLj89bse9kLZ2yspFhAKM5NUT-9Q872MHYEccRNt0xkXPdKUkAWzBw9M4czfwgD_6L3i1Tp0j25XdM2JtRLpPpQX5gLUpjhkqkdeQGRgN1PoClarjVwiQreehXX6etUK-71oXqHaDoC1ULO8QbS8FvOGvbZnVfxJJ824oXKCMsZePehw4sXwhuOq6uB987ZiljyZvH70myPGqOCIVRkAAvCYxb9Z75pE7LmZLQm2j_2F9BJLh3jM2az9umbT9Cbv9qMDcqMPzNUE9yHz7Y_jtx-ZquoSS1Edz-hGo70bmac0VULt21JrXOgyd_tNfBlcJNhrHdoPgv34aHct0sXKCKohQbLPbk5HyRnRy7aE8iCYwJ1Vkdx5eAXOQGT3LszOpOEiSyIF85yJiPYWnxf8spv4Oo8nkaIPOTFXvka5frd-una7VQhZZX_NZiVjpVBjWR-pIPQXO0B0bLoB6BvsssH3Fx_5Sv5Yx8ZILBMc-Pw9wcp-E82Nc701WnWmAJZPP_PRDTLy0txgTkMmN8rthI4gR4DBugkTNRUpX3H2JB5NcGV8SrZgrZaqwkSf7ZWw4RVbRwNr4fvJuFMr6i1j-26bR4QFby-iuYxW2w5YNG9px8uBOIGbIoGrZxNf7L7oV-sw-R1LB3KTkqzhjqLQXijZHZTxha6sbz1wDOUczHQomxJLhdBjNNRFlv5xyznj1eOR5wq4QH_tRZ-zlGNzXKF8x7qzdXCmQ1XyrzcCV7fnvXNJ0N7iIZ5k7KYNTuO0pmW5KFi0dm2a2AxwjXqRBt0g6FSfraXT_8VVtn-QxVqURcD3uiO5wJ0dT7YD3sz5XOrualfZiJNgxUzA70qL0uOtz7jGlY0xtTK55JUVBVFcS6Bl5cHU6hl9UBb1IySvjYNo6LZTuq60LQbzqQtDnKHZ6ne-9yhuegJYfrqY7QLGuEltKe3O2FWhn15iiazeC407rkXpT7_10JtqBXGyY9soWLr-oA\\\"},\\\"status\\\":302}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:05:37','2026-05-28 14:05:37','',''),
(185,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1246958385115966,\\\"longitude\\\":104.04260135331626,\\\"accuracy\\\":35},\\\"status\\\":200}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:05:38','2026-05-28 14:05:38','1.1246958385116','104.04260135332'),
(186,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1246958385115966,\\\"longitude\\\":104.04260135331626,\\\"accuracy\\\":35},\\\"status\\\":200}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:05:38','2026-05-28 14:05:38','1.1246958385116','104.04260135332'),
(187,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:06:23','2026-05-28 14:06:23','',''),
(188,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:06:40','2026-05-28 14:06:40','',''),
(189,'POST.family.member.mark_deceased',1,'\"{\\\"path\\\":\\\"family\\\\\\/member\\\\\\/49\\\\\\/mark-deceased\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"deaddate\\\":\\\"2026-01-01\\\",\\\"grave_location_url\\\":null,\\\"death_date\\\":\\\"2026-01-01\\\"},\\\"status\\\":200}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:12:44','2026-05-28 14:12:44','',''),
(190,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','223.255.224.102','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36','2026-05-28 14:14:02','2026-05-28 14:14:02','',''),
(191,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1246124057481064,\\\"longitude\\\":104.04257063704209,\\\"accuracy\\\":35},\\\"status\\\":200}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:17:48','2026-05-28 14:17:48','1.1246124057481','104.04257063704'),
(192,'POST.family.livelocation.update',1,'\"{\\\"path\\\":\\\"live-location\\\\\\/update\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"latitude\\\":1.1246124057481064,\\\"longitude\\\":104.04257063704209,\\\"accuracy\\\":35},\\\"status\\\":200}\"','119.110.85.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 14:17:48','2026-05-28 14:17:48','1.1246124057481','104.04257063704'),
(193,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 15:13:43','2026-05-28 15:13:43','',''),
(194,'POST.logout',0,'\"{\\\"path\\\":\\\"logout\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 15:23:11','2026-05-28 15:23:11','',''),
(195,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 15:25:29','2026-05-28 15:25:29','',''),
(196,'POST.family.gallery.album.store',1,'\"{\\\"path\\\":\\\"gallery\\\\\\/album\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"title\\\":\\\"Laut\\\",\\\"description\\\":null},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 15:40:05','2026-05-28 15:40:05','',''),
(197,'POST.family.gallery.photo.store',1,'\"{\\\"path\\\":\\\"gallery\\\\\\/photo\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"title\\\":\\\"Ikan\\\",\\\"album_id\\\":\\\"1\\\",\\\"photo\\\":{}},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 15:40:34','2026-05-28 15:40:34','',''),
(198,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','182.2.4.252','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 00:25:00','2026-05-29 00:25:00','',''),
(199,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','182.2.5.170','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 00:40:55','2026-05-29 00:40:55','',''),
(200,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','182.2.5.170','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 02:35:04','2026-05-29 02:35:04','',''),
(201,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 07:15:02','2026-05-29 07:15:02','',''),
(202,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/51\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 07:15:59','2026-05-29 07:15:59','',''),
(203,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 07:27:37','2026-05-29 07:27:37','',''),
(204,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/42\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":404}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 07:27:40','2026-05-29 07:27:40','',''),
(205,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/44\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":500}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 07:34:37','2026-05-29 07:34:37','',''),
(206,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/49\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 07:36:37','2026-05-29 07:36:37','',''),
(207,'POST.management.recycle_bin.restore',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/restore\\\\\\/user\\\\\\/52\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 07:36:39','2026-05-29 07:36:39','',''),
(208,'DELETE.management.recycle_bin.delete',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/delete\\\\\\/user\\\\\\/54\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 07:50:39','2026-05-29 07:50:39','',''),
(209,'DELETE.management.recycle_bin.delete',1,'\"{\\\"path\\\":\\\"management\\\\\\/recycle-bin\\\\\\/delete\\\\\\/user\\\\\\/50\\\",\\\"method\\\":\\\"DELETE\\\",\\\"input\\\":[],\\\"status\\\":200}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 07:50:42','2026-05-29 07:50:42','',''),
(210,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 11:42:00','2026-05-29 11:42:00','',''),
(211,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','223.255.224.98','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36','2026-06-05 08:43:55','2026-06-05 08:43:55','',''),
(212,'POST.login',0,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-07 17:55:11','2026-06-07 17:55:11','',''),
(213,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-07 17:55:14','2026-06-07 17:55:14','',''),
(214,'system.updated',1,'{\"id\":1,\"name\":null,\"class\":\"System\"}','61.94.160.181','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','2026-06-07 19:30:04','2026-06-07 19:30:04',NULL,NULL),
(215,'landingpagesetting.updated',1,'{\"id\":1,\"name\":null,\"class\":\"LandingPageSetting\"}','61.94.160.181','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','2026-06-07 19:30:04','2026-06-07 19:30:04',NULL,NULL),
(216,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','202.67.47.29','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-06-08 12:13:15','2026-06-08 12:13:15','',''),
(217,'POST.login',1,'\"{\\\"path\\\":\\\"login\\\",\\\"method\\\":\\\"POST\\\",\\\"input\\\":{\\\"username\\\":\\\"superadmin\\\"},\\\"status\\\":302}\"','223.255.224.104','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-06-13 07:48:57','2026-06-13 07:48:57','',''),
(218,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 03:50:22','2026-06-13 03:50:22',NULL,NULL),
(219,'PUT: family-members.update',1,'{\"name\":\"Budi Santosoooo\",\"birthdate\":\"1940-01-01\",\"birthplace\":\"Solo\",\"bloodtype\":null,\"job\":\"Pensiunan\",\"education_status\":null,\"email\":\"budi.santoso@example.com\",\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 03:52:16','2026-06-13 03:52:16',NULL,NULL),
(220,'POST: POST login',1,'{\"username\":\"superadmin\"}','223.255.224.104','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-06-13 04:04:46','2026-06-13 04:04:46',NULL,NULL),
(221,'POST: chatbot.respond',1,'{\"message\":\"hi\",\"history\":[{\"role\":\"assistant\",\"content\":\"Hello! I am your family digital assistant. How can I help you today?\"}]}','223.255.224.104','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-06-13 04:09:52','2026-06-13 04:09:52',NULL,NULL),
(222,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:26:42','2026-06-13 14:26:42',NULL,NULL),
(223,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"created_by_name\":\"123\",\"created_by_photo\":null,\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":null,\"head_of_family_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:29:16','2026-06-13 14:29:16',NULL,NULL),
(224,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"created_by_name\":\"123\",\"created_by_photo\":null,\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":null,\"head_of_family_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:41:49','2026-06-13 14:41:49',NULL,NULL),
(225,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"created_by_name\":\"123\",\"created_by_photo\":null,\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":null,\"head_of_family_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:41:52','2026-06-13 14:41:52',NULL,NULL),
(226,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"created_by_name\":\"123\",\"created_by_photo\":null,\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":null,\"head_of_family_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:47:46','2026-06-13 14:47:46',NULL,NULL),
(227,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"created_by_name\":\"123\",\"created_by_photo\":null,\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":null,\"head_of_family_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:50:30','2026-06-13 14:50:30',NULL,NULL),
(228,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"created_by_name\":\"123\",\"created_by_photo\":null,\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":null,\"head_of_family_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:50:32','2026-06-13 14:50:32',NULL,NULL),
(229,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"created_by_name\":\"123\",\"created_by_photo\":null,\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":null,\"head_of_family_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:52:36','2026-06-13 14:52:36',NULL,NULL),
(230,'landingpagesetting.updated',1,'{\"id\":1,\"name\":null,\"class\":\"LandingPageSetting\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:53:33','2026-06-13 14:53:33',NULL,NULL),
(231,'landingpagesetting.updated',1,'{\"id\":1,\"name\":null,\"class\":\"LandingPageSetting\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 14:54:00','2026-06-13 14:54:00',NULL,NULL),
(232,'POST: POST login',0,'{\"username\":\"superadmin@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 19:23:29','2026-06-13 19:23:29',NULL,NULL),
(233,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 19:23:38','2026-06-13 19:23:38',NULL,NULL),
(234,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"head_of_family_photo\":null,\"created_by_name\":\"123\",\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":null,\"created_by_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 19:43:22','2026-06-13 19:43:22',NULL,NULL),
(235,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"head_of_family_photo\":null,\"created_by_name\":\"123\",\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":null,\"created_by_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 19:43:24','2026-06-13 19:43:24',NULL,NULL),
(236,'POST: system.landing.update',1,'{\"family_name\":\"Koto Balai Nan di Bawah\",\"description\":\"Keluarga\",\"head_of_family_name\":\"Budi Santosoooo\",\"head_of_family_message\":\"Se;amat datang\",\"head_of_family_photo\":null,\"created_by_name\":\"123\",\"created_by_photo\":null,\"designed_by_name\":\"789\",\"designed_by_photo\":null,\"approved_by_name\":\"456\",\"approved_by_photo\":null,\"acknowledged_by_name\":\"012\",\"acknowledged_by_photo\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 19:44:20','2026-06-13 19:44:20',NULL,NULL),
(237,'POST: gallery.photo.upload',1,'{\"album_id\":\"1\",\"title\":\"adsa\",\"caption\":\"sdsd\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 19:44:58','2026-06-13 19:44:58',NULL,NULL),
(238,'POST: gallery.photo.upload',1,'{\"title\":\"d\",\"caption\":\"s\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 19:49:19','2026-06-13 19:49:19',NULL,NULL),
(239,'PUT: family-members.update',1,'{\"name\":\"Budi Santosoooo\",\"birthdate\":\"1940-01-01\",\"birthplace\":\"Solo\",\"bloodtype\":null,\"job\":\"Pensiunan\",\"education_status\":null,\"email\":\"budi.santoso@example.com\",\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":\"Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 19:59:40','2026-06-13 19:59:40',NULL,NULL),
(240,'PUT: family-members.update',1,'{\"name\":\"Isadora Martinez\",\"birthdate\":\"2016-10-18\",\"birthplace\":\"Eos iusto quibusdam\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 20:00:01','2026-06-13 20:00:01',NULL,NULL),
(241,'PUT: family-members.update',1,'{\"name\":\"Aretha Carney\",\"birthdate\":\"2025-10-21\",\"birthplace\":\"Minus blanditiis in\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"34\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 20:00:17','2026-06-13 20:00:17',NULL,NULL),
(242,'POST: POST login',43,'{\"username\":\"jason297\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 20:32:51','2026-06-13 20:32:51',NULL,NULL),
(243,'POST: family-members.request-deletion',43,'{\"reason\":\"asd\",\"document\":{}}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 20:38:58','2026-06-13 20:38:58',NULL,NULL),
(244,'PUT: family-members.update',1,'{\"name\":\"Remedios Berger\",\"birthdate\":\"2026-09-06\",\"birthplace\":\"Nesciunt blanditiis\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"37\",\"secondary_parent_id\":\"44\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 21:46:35','2026-06-13 21:46:35',NULL,NULL),
(245,'PUT: family-members.update',1,'{\"name\":\"Remedios Berger\",\"birthdate\":\"2026-09-06\",\"birthplace\":\"Nesciunt blanditiis\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"37\",\"secondary_parent_id\":\"44\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 21:47:11','2026-06-13 21:47:11',NULL,NULL),
(246,'PUT: family-members.update',1,'{\"name\":\"Remedios Berger\",\"birthdate\":\"2026-09-06\",\"birthplace\":\"Nesciunt\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"37\",\"secondary_parent_id\":\"44\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 21:55:04','2026-06-13 21:55:04',NULL,NULL),
(247,'PUT: family-members.update',1,'{\"name\":\"Remedios\",\"birthdate\":\"2026-09-06\",\"birthplace\":\"Nesciunt\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"37\",\"secondary_parent_id\":\"44\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 21:55:19','2026-06-13 21:55:19',NULL,NULL),
(248,'PUT: family-members.update',1,'{\"name\":\"Remedios\",\"birthdate\":\"2026-09-06\",\"birthplace\":\"Nesciunt\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"37\",\"secondary_parent_id\":\"44\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 21:55:21','2026-06-13 21:55:21',NULL,NULL),
(249,'PUT: family-members.update',1,'{\"name\":\"Remedios\",\"birthdate\":\"2026-09-06\",\"birthplace\":\"Nesciunt blanditiis\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"37\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 22:00:33','2026-06-13 22:00:33',NULL,NULL),
(250,'PUT: family-members.update',1,'{\"name\":\"Remedios\",\"birthdate\":\"2026-09-06\",\"birthplace\":\"Nesciunt blanditiis\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"37\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 22:00:35','2026-06-13 22:00:35',NULL,NULL),
(251,'PUT: family-members.update',1,'{\"name\":\"Illana\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 22:15:04','2026-06-13 22:15:04',NULL,NULL),
(252,'PUT: family-members.update',43,'{\"name\":\"Jason Mosley\",\"birthdate\":\"2002-06-03\",\"birthplace\":\"Placeat quia sunt\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 22:34:43','2026-06-13 22:34:43',NULL,NULL),
(253,'PUT: family-members.update',43,'{\"name\":\"Jason Mosley\",\"birthdate\":\"2002-06-03\",\"birthplace\":\"Placeat quia sunt\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 22:34:54','2026-06-13 22:34:54',NULL,NULL),
(254,'PUT: family-members.update',43,'{\"name\":\"Jason\",\"birthdate\":\"2002-06-03\",\"birthplace\":\"Placeat quia sunt\",\"bloodtype\":null,\"job\":null,\"education_status\":\"sd\",\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 22:35:21','2026-06-13 22:35:21',NULL,NULL),
(255,'PUT: family-members.update',43,'{\"name\":\"Jasonsd\",\"birthdate\":\"2002-06-03\",\"birthplace\":\"Placeat quia sunt\",\"bloodtype\":null,\"job\":null,\"education_status\":\"sd\",\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":\"Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 22:35:34','2026-06-13 22:35:34',NULL,NULL),
(256,'PUT: family-members.update',43,'{\"name\":\"Zena Slater\",\"birthdate\":\"2024-12-12\",\"birthplace\":\"Sit voluptatum quod\",\"bloodtype\":null,\"job\":\"Id dolor iste tempo\",\"education_status\":\"Odit commodo sit al\",\"email\":\"bojuje@mailinator.com\",\"phonenumber\":\"+1 (835) 165-2664\",\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 23:11:42','2026-06-13 23:11:42',NULL,NULL),
(257,'PUT: family-members.update',43,'{\"name\":\"Budi Santoso\",\"birthdate\":\"1940-01-01\",\"birthplace\":\"Solo\",\"bloodtype\":null,\"job\":\"Pensiunan\",\"education_status\":null,\"email\":\"budi.santoso@example.com\",\"phonenumber\":null,\"address\":\"Indonesia, Indonesia, Indonesia\",\"address_detail\":\"Indonesia, Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 23:12:09','2026-06-13 23:12:09',NULL,NULL),
(258,'PUT: family-members.update',43,'{\"name\":\"Zena\",\"birthdate\":null,\"birthplace\":\"Sit voluptatum quod\",\"bloodtype\":null,\"job\":\"Id dolor iste tempo\",\"education_status\":\"Odit commodo sit al\",\"email\":\"bojuje@mailinator.com\",\"phonenumber\":\"+1 (835) 165-2664\",\"address\":\"Indonesia, Indonesia\",\"address_detail\":\"Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 23:34:33','2026-06-13 23:34:33',NULL,NULL),
(259,'PUT: family-members.update',43,'{\"name\":\"Aretha Carney\",\"birthdate\":null,\"birthplace\":\"Minus\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":\"Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"34\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-13 23:35:02','2026-06-13 23:35:02',NULL,NULL),
(260,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 00:49:50','2026-06-14 00:49:50',NULL,NULL),
(261,'PUT: family-members.update',1,'{\"name\":\"Illana ijdijf\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":\"Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 00:50:44','2026-06-14 00:50:44',NULL,NULL),
(262,'PUT: family-members.update',1,'{\"name\":\"Illana ijdijf\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia, Indonesia\",\"address_detail\":\"Indonesia, Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":\"2026-12-12\",\"grave_location_url\":\"sdhsifhidhfihdih\",\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 00:51:10','2026-06-14 00:51:10',NULL,NULL),
(263,'PUT: family-members.update',1,'{\"name\":\"Illana ijdijf\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia, Indonesia\",\"address_detail\":\"Indonesia, Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":\"2026-12-12\",\"grave_location_url\":\"sdhsifhidhfihdih\",\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 00:51:23','2026-06-14 00:51:23',NULL,NULL),
(264,'PUT: family-members.update',1,'{\"name\":\"Zena\",\"birthdate\":null,\"birthplace\":\"Sit voluptatum quod\",\"bloodtype\":null,\"job\":\"Id dolor iste tempo\",\"education_status\":\"Odit commodo sit al\",\"email\":\"bojuje@mailinator.com\",\"phonenumber\":\"+1 (835) 165-2664\",\"address\":\"Indonesia, Indonesia, Indonesia\",\"address_detail\":\"Indonesia, Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 00:53:07','2026-06-14 00:53:07',NULL,NULL),
(265,'PUT: family-members.update',1,'{\"name\":\"Zena ytufhfhg\",\"birthdate\":null,\"birthplace\":\"Sit voluptatum quod\",\"bloodtype\":null,\"job\":\"Id dolor iste tempo\",\"education_status\":\"Odit commodo sit al\",\"email\":\"bojuje@mailinator.com\",\"phonenumber\":\"+1 (835) 165-2664\",\"address\":\"Indonesia, Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":\"Indonesia\",\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"married\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 00:53:22','2026-06-14 00:53:22',NULL,NULL),
(266,'POST: backup.run',1,'[]','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 00:59:42','2026-06-14 00:59:42',NULL,NULL),
(267,'POST: backup.run',1,'[]','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 00:59:52','2026-06-14 00:59:52',NULL,NULL),
(268,'PUT: family-members.update',1,'{\"name\":\"Tempore suscipit is\",\"birthdate\":\"1996-06-19\",\"birthplace\":\"Tempore mollit dolo\",\"bloodtype\":\"Aut rerum deserunt i\",\"job\":\"Minus minima officia\",\"education_status\":\"Cumque alias dolores\",\"email\":\"xyjepetum@mailinator.com\",\"phonenumber\":\"Natus architecto neq\",\"address\":\"Aut consequatur qui , Iraq\",\"address_detail\":\"Aut consequatur qui\",\"country\":\"Iraq\",\"province\":null,\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"single\",\"gender\":\"male\",\"deaddate\":\"2026-03-10\",\"grave_location_url\":\"https:\\/\\/nathalia.rplkodingan.com\",\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 01:01:13','2026-06-14 01:01:13',NULL,NULL),
(269,'PUT: family-members.update',1,'{\"name\":\"Tempore suscipit is\",\"birthdate\":\"1996-06-19\",\"birthplace\":\"Tempore mollit dolo\",\"bloodtype\":\"Aut rerum deserunt i\",\"job\":\"Minus minima officia\",\"education_status\":\"Cumque alias dolores\",\"email\":\"xyjepetum@mailinator.com\",\"phonenumber\":\"Natus architecto neq\",\"address\":\"Aut consequatur qui , Iraq\",\"address_detail\":\"Aut consequatur qui\",\"country\":\"Iraq\",\"province\":null,\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"single\",\"gender\":\"male\",\"deaddate\":\"2026-03-10\",\"grave_location_url\":\"https:\\/\\/nathalia.rplkodingan.com\",\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 01:01:22','2026-06-14 01:01:22',NULL,NULL),
(270,'POST: chatbot.respond',1,'{\"message\":\"who is budi\",\"history\":[{\"role\":\"assistant\",\"content\":\"Hello! I am your family digital assistant. How can I help you today?\"}]}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 01:01:53','2026-06-14 01:01:53',NULL,NULL),
(271,'PUT: family-members.update',1,'{\"name\":\"Zena ytufhfhg\",\"birthdate\":null,\"birthplace\":\"Sit voluptatum quod\",\"bloodtype\":null,\"job\":\"Id dolor iste tempo\",\"education_status\":\"Odit commodo sit al\",\"email\":\"bojuje@mailinator.com\",\"phonenumber\":\"+1 (835) 165-2664\",\"address\":\"Indonesia, Indonesia, Indonesia\",\"address_detail\":\"Indonesia, Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"married\",\"gender\":\"female\",\"deaddate\":\"2026-12-12\",\"grave_location_url\":\"sddf\",\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 01:03:17','2026-06-14 01:03:17',NULL,NULL),
(272,'PUT: family-members.update',1,'{\"name\":\"Zena y\",\"birthdate\":null,\"birthplace\":\"Sit voluptatum quod\",\"bloodtype\":null,\"job\":\"Id dolor iste tempo\",\"education_status\":\"Odit commodo sit al\",\"email\":\"bojuje@mailinator.com\",\"phonenumber\":\"+1 (835) 165-2664\",\"address\":\"Indonesia, Indonesia, Indonesia\",\"address_detail\":\"Indonesia, Indonesia\",\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"married\",\"gender\":\"female\",\"deaddate\":\"2026-12-12\",\"grave_location_url\":\"dssadsdas\",\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 01:11:03','2026-06-14 01:11:03',NULL,NULL),
(273,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 11:33:33','2026-06-14 11:33:33',NULL,NULL),
(274,'POST: POST login',1,'{\"username\":\"superadmin\"}','182.2.5.165','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-14 20:42:01','2026-06-14 20:42:01',NULL,NULL),
(275,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 01:58:30','2026-06-15 01:58:30',NULL,NULL),
(276,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 09:03:11','2026-06-15 09:03:11',NULL,NULL),
(277,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 15:20:45','2026-06-15 15:20:45',NULL,NULL),
(278,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 16:41:31','2026-06-15 16:41:31',NULL,NULL),
(279,'PUT: family-members.update',1,'{\"name\":\"Illana\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 18:52:36','2026-06-15 18:52:36',NULL,NULL),
(280,'PUT: family-members.update',1,'{\"name\":\"Illana\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 19:06:09','2026-06-15 19:06:09',NULL,NULL),
(281,'PUT: family-members.update',1,'{\"name\":\"Illana\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null,\"_method\":\"PUT\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 19:52:38','2026-06-15 19:52:38',NULL,NULL),
(282,'PUT: family-members.update',1,'{\"name\":\"Illana\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null,\"_method\":\"PUT\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 19:54:11','2026-06-15 19:54:11',NULL,NULL),
(283,'POST: family-members.update',1,'{\"name\":\"Illana\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 20:05:57','2026-06-15 20:05:57',NULL,NULL),
(284,'POST: family-members.update',1,'{\"name\":\"Illana\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Aute voluptate verit\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia, Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 20:20:49','2026-06-15 20:20:49',NULL,NULL),
(285,'POST: family-members.store',1,'{\"name\":\"Est eiusmod consequ\",\"birthdate\":\"2017-01-01\",\"birthplace\":\"Voluptatibus volupta\",\"bloodtype\":\"Elit natus in nostr\",\"job\":\"Minim quibusdam et i\",\"education_status\":\"Eum ducimus reprehe\",\"email\":\"foxijy@mailinator.com\",\"phonenumber\":\"Sint ipsum voluptat\",\"address\":null,\"address_detail\":\"Id Nam et maxime no\",\"country\":\"Israel\",\"province\":\"Sulawesi Barat\",\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"widowed\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"1\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 22:00:49','2026-06-15 22:00:49',NULL,NULL),
(286,'POST: family-members.store',1,'{\"name\":\"Mollit distinctio I\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Ut dolore error aliq\",\"bloodtype\":\"Et molestias eos et\",\"job\":\"Quae laborum Minim\",\"education_status\":\"Laboris harum iusto\",\"email\":\"wybe@mailinator.com\",\"phonenumber\":\"Commodo repellendus\",\"address\":null,\"address_detail\":\"Debitis alias recusa\",\"country\":\"Nigeria\",\"province\":\"Riau\",\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"1\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 22:01:13','2026-06-15 22:01:13',NULL,NULL),
(287,'POST: family-members.store',1,'{\"name\":\"Mollit distinctio I\",\"birthdate\":\"2025-08-26\",\"birthplace\":\"Ut dolore error aliq\",\"bloodtype\":\"Et molestias eos et\",\"job\":\"Quae laborum Minim\",\"education_status\":\"Laboris harum iusto\",\"email\":\"wybe@mailinator.com\",\"phonenumber\":\"Commodo repellendus\",\"address\":null,\"address_detail\":\"Debitis alias recusa\",\"country\":\"Nigeria\",\"province\":\"Riau\",\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"1\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 22:01:19','2026-06-15 22:01:19',NULL,NULL),
(288,'POST: family-members.store',1,'{\"name\":\"Distinctio Ut quia\",\"birthdate\":\"1977-03-24\",\"birthplace\":\"Voluptate aut conseq\",\"bloodtype\":\"Laborum sequi non ni\",\"job\":\"Duis odit sed amet\",\"education_status\":\"Officia cumque est\",\"email\":\"puxu@mailinator.com\",\"phonenumber\":\"Aliqua Eius quidem\",\"address\":null,\"address_detail\":\"Qui eu aut dicta par\",\"country\":\"Guatemala\",\"province\":\"Kalimantan Tengah\",\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"divorced\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"1\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 22:20:54','2026-06-15 22:20:54',NULL,NULL),
(289,'POST: family-members.store',1,'{\"name\":\"ayammm\",\"birthdate\":\"1976-02-02\",\"birthplace\":\"Tempora fugiat dolo\",\"bloodtype\":\"Ipsa consequatur S\",\"job\":\"Harum fugiat natus\",\"education_status\":\"Quis quia veniam qu\",\"email\":\"boquho@mailinator.com\",\"phonenumber\":\"Et repudiandae qui a\",\"address\":null,\"address_detail\":\"Deserunt cum officia\",\"country\":\"Italy\",\"province\":\"Gorontalo\",\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"widowed\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"1\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 22:21:27','2026-06-15 22:21:27',NULL,NULL),
(290,'POST: family-members.store',1,'{\"name\":\"ayammm\",\"birthdate\":\"1976-02-02\",\"birthplace\":\"Tempora fugiat dolo\",\"bloodtype\":\"Ipsa consequatur S\",\"job\":\"Harum fugiat natus\",\"education_status\":\"Quis quia veniam qu\",\"email\":\"boquho@mailinator.com\",\"phonenumber\":\"Et repudiandae qui a\",\"address\":null,\"address_detail\":\"Deserunt cum officia\",\"country\":\"Italy\",\"province\":\"Gorontalo\",\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"widowed\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"1\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 22:21:36','2026-06-15 22:21:36',NULL,NULL),
(291,'POST: family-members.store',1,'{\"name\":\"Sed nihil obcaecati\",\"birthdate\":\"2022-12-03\",\"birthplace\":\"Ea minima aspernatur\",\"bloodtype\":\"Error ullamco distin\",\"job\":\"Necessitatibus dicta\",\"education_status\":\"Dolor sit porro pari\",\"email\":\"ziwutuk@mailinator.com\",\"phonenumber\":\"Consequatur ut sed c\",\"address\":null,\"address_detail\":\"Commodo voluptatem\",\"country\":\"Gabon\",\"province\":\"Maluku\",\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"1\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 22:57:18','2026-06-15 22:57:18',NULL,NULL),
(292,'POST: family-members.store',1,'{\"name\":\"Sed nihil obcaecati\",\"birthdate\":\"2022-12-03\",\"birthplace\":\"Ea minima aspernatur\",\"bloodtype\":\"A\",\"job\":\"Necessitatibus dicta\",\"education_status\":\"Dolor sit porro pari\",\"email\":\"ziwutuk@mailinator.com\",\"phonenumber\":\"Consequatur ut sed c\",\"address\":null,\"address_detail\":\"Commodo voluptatem\",\"country\":\"Gabon\",\"province\":\"Maluku\",\"city\":null,\"life_status\":\"deceased\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"1\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 22:57:25','2026-06-15 22:57:25',NULL,NULL),
(293,'POST: family-members.store',1,'{\"name\":\"Sed nihil obcaecati\",\"birthdate\":\"2022-12-03\",\"birthplace\":\"Ea minima aspernatur\",\"bloodtype\":\"A\",\"job\":\"Necessitatibus dicta\",\"education_status\":\"Dolor sit porro pari\",\"email\":\"ziwutuk@mailinator.com\",\"phonenumber\":\"Consequatur ut sed c\",\"address\":null,\"address_detail\":\"Commodo voluptatem\",\"country\":\"Gabon\",\"province\":\"Maluku\",\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"1\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 22:57:34','2026-06-15 22:57:34',NULL,NULL),
(294,'DELETE: family-members.destroy',1,'[]','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:03:57','2026-06-15 23:03:57',NULL,NULL),
(295,'DELETE: family-members.destroy',1,'[]','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:04:14','2026-06-15 23:04:14',NULL,NULL),
(296,'POST: family-members.store',1,'{\"name\":\"ami\",\"birthdate\":\"2026-12-12\",\"birthplace\":\"asas\",\"bloodtype\":\"A\",\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":null,\"address_detail\":\"asd\",\"country\":\"Indonesia\",\"province\":\"Kepulauan Riau\",\"city\":\"City in Kepulauan Riau 1\",\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"51\",\"second_parent_id\":null,\"relation_type\":\"parent\",\"primary_parent_id\":null,\"secondary_parent_id\":null,\"social_media\":[{\"socialid\":null,\"link\":\"asdfgh\"}]}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:05:14','2026-06-15 23:05:14',NULL,NULL),
(297,'POST: family-members.store',1,'{\"name\":\"ami\",\"birthdate\":\"2026-06-12\",\"birthplace\":\"asas\",\"bloodtype\":\"A\",\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":null,\"address_detail\":\"asd\",\"country\":\"Indonesia\",\"province\":\"Kepulauan Riau\",\"city\":\"City in Kepulauan Riau 1\",\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"51\",\"second_parent_id\":null,\"relation_type\":\"parent\",\"primary_parent_id\":null,\"secondary_parent_id\":null,\"social_media\":[{\"socialid\":null,\"link\":\"asdfgh\"}]}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:05:23','2026-06-15 23:05:23',NULL,NULL),
(298,'POST: family-members.store',1,'{\"name\":\"ami\",\"birthdate\":\"2026-06-12\",\"birthplace\":\"asas\",\"bloodtype\":\"A\",\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":null,\"address_detail\":\"asd\",\"country\":\"Indonesia\",\"province\":\"Kepulauan Riau\",\"city\":\"City in Kepulauan Riau 1\",\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"51\",\"second_parent_id\":null,\"relation_type\":\"parent\",\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:05:30','2026-06-15 23:05:30',NULL,NULL),
(299,'POST: master-data.social-media.store',1,'{\"socialname\":\"Youtube\",\"prefix\":\"https:\\/\\/youtube.com\",\"socialicon\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:07:23','2026-06-15 23:07:23',NULL,NULL),
(300,'POST: recycle-bin.restore',1,'{\"type\":\"socialmedia\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:07:28','2026-06-15 23:07:28',NULL,NULL),
(301,'PUT: master-data.social-media.update',1,'{\"socialname\":\"Youtube\",\"prefix\":\"https:\\/\\/youtube.com\",\"socialicon\":\"https:\\/\\/cdn.jsdelivr.net\\/npm\\/simple-icons@v11\\/icons\\/youtube.svg\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:07:44','2026-06-15 23:07:44',NULL,NULL),
(302,'POST: master-data.social-media.store',1,'{\"socialname\":\"Facebook\",\"prefix\":\"https:\\/\\/facebook.com\",\"socialicon\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:07:57','2026-06-15 23:07:57',NULL,NULL),
(303,'PUT: master-data.social-media.update',1,'{\"socialname\":\"Facebook\",\"prefix\":\"https:\\/\\/facebook.com\",\"socialicon\":\"https:\\/\\/cdn.jsdelivr.net\\/npm\\/simple-icons@v11\\/icons\\/facebook.svg\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:08:11','2026-06-15 23:08:11',NULL,NULL),
(304,'POST: POST login',2,'{\"username\":\"budi684\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-15 23:24:05','2026-06-15 23:24:05',NULL,NULL),
(305,'PUT: family-members.update',1,'{\"name\":\"Vernon\",\"birthdate\":\"2002-08-18\",\"birthplace\":\"Deserunt quibusdam h\",\"bloodtype\":null,\"job\":\"Magnam dolorum est\",\"education_status\":\"Dolor quasi voluptat\",\"email\":\"nuzyrijyc@mailinator.com\",\"phonenumber\":\"+1 (614) 681\",\"address\":null,\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":null,\"secondary_parent_id\":null}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 00:07:04','2026-06-16 00:07:04',NULL,NULL),
(306,'PUT: family-members.update',1,'{\"name\":\"Isadora Martinez\",\"birthdate\":\"2016-10-18\",\"birthplace\":\"Eos iusto quibusdam\",\"bloodtype\":null,\"job\":null,\"education_status\":null,\"email\":null,\"phonenumber\":null,\"address\":\"Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"female\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"1\",\"secondary_parent_id\":\"33\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 00:18:19','2026-06-16 00:18:19',NULL,NULL),
(307,'POST: family-members.store',1,'{\"name\":\"misty plume\",\"birthdate\":\"2000-12-12\",\"birthplace\":\"ad\",\"bloodtype\":\"B+\",\"job\":\"as\",\"education_status\":\"asd\",\"email\":null,\"phonenumber\":null,\"address\":null,\"address_detail\":\"cv\",\"country\":\"Indonesia\",\"province\":\"Kepulauan Riau\",\"city\":\"Batam\",\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"42\",\"second_parent_id\":null,\"relation_type\":\"child\",\"primary_parent_id\":\"42\",\"secondary_parent_id\":null,\"social_media\":[{\"socialid\":\"1\",\"link\":\"hsd\"}]}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 00:19:49','2026-06-16 00:19:49',NULL,NULL),
(308,'PUT: family-members.update',1,'{\"name\":\"misty plume\",\"birthdate\":\"2000-12-12\",\"birthplace\":\"ad\",\"bloodtype\":\"B+\",\"job\":\"as\",\"education_status\":\"asd\",\"email\":null,\"phonenumber\":null,\"address\":\"cv, Batam, Kepulauan Riau, Indonesia\",\"address_detail\":null,\"country\":\"Indonesia\",\"province\":null,\"city\":null,\"life_status\":\"alive\",\"marital_status\":\"single\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"PUT\",\"related_to\":null,\"second_parent_id\":null,\"relation_type\":null,\"primary_parent_id\":\"42\",\"secondary_parent_id\":null,\"social_media\":[{\"ownid\":\"1\",\"socialid\":\"1\",\"link\":\"https:\\/\\/www.youtube.com\\/@4leaf-music\"}]}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 00:20:19','2026-06-16 00:20:19',NULL,NULL),
(309,'POST: backup.run',1,'[]','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 01:13:38','2026-06-16 01:13:38',NULL,NULL),
(310,'POST: backup.run',1,'[]','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 01:13:47','2026-06-16 01:13:47',NULL,NULL),
(311,'POST: backup.run',1,'[]','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 01:39:10','2026-06-16 01:39:10',NULL,NULL),
(312,'POST: logout',0,'[]','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 01:48:55','2026-06-16 01:48:55',NULL,NULL),
(313,'PUT: users.update',1,'{\"username\":\"Nathalia\",\"name\":\"Luiz Goverment\",\"email\":\"owllfiewolfie@gmail.com\",\"phonenumber\":\"2844400011678\",\"roleid\":\"1\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 01:49:17','2026-06-16 01:49:17',NULL,NULL),
(314,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 01:49:47','2026-06-16 01:49:47',NULL,NULL),
(315,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 01:51:24','2026-06-16 01:51:24',NULL,NULL),
(316,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 02:17:10','2026-06-16 02:17:10',NULL,NULL),
(317,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 02:50:15','2026-06-16 02:50:15',NULL,NULL),
(318,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 02:50:29','2026-06-16 02:50:29',NULL,NULL),
(319,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 08:10:27','2026-06-16 08:10:27',NULL,NULL),
(320,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 08:10:29','2026-06-16 08:10:29',NULL,NULL),
(321,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 12:45:58','2026-06-16 12:45:58',NULL,NULL),
(322,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 14:09:07','2026-06-16 14:09:07',NULL,NULL),
(323,'POST: POST login',1,'{\"username\":\"superadmin\"}','182.2.4.127','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 17:38:39','2026-06-16 17:38:39',NULL,NULL),
(324,'POST: family-members.store',1,'{\"name\":\"lili sasa\",\"birthdate\":\"2026-06-12\",\"birthplace\":\"amerika\",\"bloodtype\":\"B+\",\"job\":\"dfghjkl\",\"education_status\":\"wsdfyui\",\"email\":null,\"phonenumber\":null,\"address\":null,\"address_detail\":\"sdfghjk\",\"country\":\"Indonesia\",\"province\":\"Kepulauan Bangka Belitung\",\"city\":\"Bangka Barat\",\"life_status\":\"alive\",\"marital_status\":\"married\",\"gender\":\"male\",\"deaddate\":null,\"grave_location_url\":null,\"_method\":\"POST\",\"related_to\":\"39\",\"second_parent_id\":null,\"relation_type\":\"spouse\",\"primary_parent_id\":null,\"secondary_parent_id\":null}','182.2.4.127','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 17:41:04','2026-06-16 17:41:04',NULL,NULL),
(325,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 17:57:12','2026-06-16 17:57:12',NULL,NULL),
(326,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 17:57:17','2026-06-16 17:57:17',NULL,NULL),
(327,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 17:57:19','2026-06-16 17:57:19',NULL,NULL),
(328,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 18:41:27','2026-06-16 18:41:27',NULL,NULL),
(329,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 18:45:35','2026-06-16 18:45:35',NULL,NULL),
(330,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 18:45:42','2026-06-16 18:45:42',NULL,NULL),
(331,'POST: profile.employer.update',1,'{\"username\":\"superadmin\",\"name\":\"superadmin\",\"email\":\"yenatricesn@gmail.com\",\"phonenumber\":\"082170976500\"}','182.2.4.127','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 18:50:06','2026-06-16 18:50:06',NULL,NULL),
(332,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 18:58:18','2026-06-16 18:58:18',NULL,NULL),
(333,'POST: logout',0,'[]','182.2.4.127','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 18:58:42','2026-06-16 18:58:42',NULL,NULL),
(334,'POST: login.otp.send',0,'{\"email\":\"atricesn@gmail.com\"}','182.2.5.53','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 18:59:00','2026-06-16 18:59:00',NULL,NULL),
(335,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 18:59:14','2026-06-16 18:59:14',NULL,NULL),
(336,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:11:32','2026-06-16 19:11:32',NULL,NULL),
(337,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:11:40','2026-06-16 19:11:40',NULL,NULL),
(338,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:11:44','2026-06-16 19:11:44',NULL,NULL),
(339,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:11:57','2026-06-16 19:11:57',NULL,NULL),
(340,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:22:18','2026-06-16 19:22:18',NULL,NULL),
(341,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:29:45','2026-06-16 19:29:45',NULL,NULL),
(342,'POST: POST login',2,'{\"username\":\"budi684\"}','182.2.5.53','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 19:42:39','2026-06-16 19:42:39',NULL,NULL),
(343,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:42:50','2026-06-16 19:42:50',NULL,NULL),
(344,'POST: login.otp.verify',0,'{\"email\":\"yenatricesn@gmail.com\",\"otp\":\"744920\",\"step\":\"otp\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:43:14','2026-06-16 19:43:14',NULL,NULL),
(345,'POST: login.otp.verify',0,'{\"email\":\"yenatricesn@gmail.com\",\"otp\":\"744920\",\"step\":\"otp\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:43:17','2026-06-16 19:43:17',NULL,NULL),
(346,'POST: login.otp.verify',0,'{\"email\":\"yenatricesn@gmail.com\",\"otp\":\"744920\",\"step\":\"otp\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:45:49','2026-06-16 19:45:49',NULL,NULL),
(347,'POST: login.otp.verify',0,'{\"email\":\"yenatricesn@gmail.com\",\"otp\":\"744920\",\"step\":\"otp\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:47:48','2026-06-16 19:47:48',NULL,NULL),
(348,'POST: login.otp.send',0,'{\"email\":\"yenatricesn@gmail.com\",\"otp\":\"744920\",\"step\":\"email\"}','182.2.4.127','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:47:54','2026-06-16 19:47:54',NULL,NULL),
(349,'POST: login.otp.verify',0,'{\"email\":\"yenatricesn@gmail.com\",\"otp\":\"88955\",\"step\":\"otp\"}','182.2.5.53','Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-06-16 19:48:25','2026-06-16 19:48:25',NULL,NULL),
(350,'POST: login.otp.verify',1,'{\"email\":\"yenatricesn@gmail.com\",\"otp\":\"889554\",\"step\":\"otp\"}','182.2.5.53','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-16 19:49:08','2026-06-16 19:49:08',NULL,NULL),
(351,'POST: POST login',1,'{\"username\":\"superadmin\"}','202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-28 12:23:18','2026-07-28 12:23:18',NULL,NULL);
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES
('family-tree-cache-2bee2e0f3bc2a479f0b5742cf491d453','i:1;',1784442323),
('family-tree-cache-2bee2e0f3bc2a479f0b5742cf491d453:timer','i:1784442323;',1784442323),
('family-tree-cache-928396fb7b24a3ce899dcda191503915','i:1;',1781787138),
('family-tree-cache-928396fb7b24a3ce899dcda191503915:timer','i:1781787138;',1781787138),
('family-tree-cache-ee6ff04fa0482f16f4751a7e486cfb14','i:1;',1784442309),
('family-tree-cache-ee6ff04fa0482f16f4751a7e486cfb14:timer','i:1784442309;',1784442309),
('family-tree-cache-f76128f05ba38e3dfbc78ae5e943e963','i:1;',1781348856),
('family-tree-cache-f76128f05ba38e3dfbc78ae5e943e963:timer','i:1781348856;',1781348856),
('family-tree-cache-superadmin@gmail.com|202.43.231.185','i:1;',1784442309),
('family-tree-cache-superadmin@gmail.com|202.43.231.185:timer','i:1784442309;',1784442309);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employer`
--

DROP TABLE IF EXISTS `employer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employer` (
  `employerid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pending_email` varchar(255) DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_token_expires_at` timestamp NULL DEFAULT NULL,
  `phonenumber` varchar(255) NOT NULL,
  `pending_phonenumber` varchar(255) DEFAULT NULL,
  `phone_verification_otp_hash` varchar(255) DEFAULT NULL,
  `phone_verification_otp_expires_at` timestamp NULL DEFAULT NULL,
  `roleid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`employerid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employer`
--

LOCK TABLES `employer` WRITE;
/*!40000 ALTER TABLE `employer` DISABLE KEYS */;
INSERT INTO `employer` VALUES
(1,'superadmin','yenatricesn@gmail.com',NULL,NULL,NULL,'082170976500',NULL,NULL,NULL,1,1,NULL,'2026-05-24 06:25:07'),
(2,'Luiz Goverment','owllfiewolfie@gmail.com',NULL,NULL,NULL,'2844400011678',NULL,NULL,NULL,2,19,NULL,'2026-05-24 06:25:07'),
(3,'Budi Santoso','budi.santoso@example.com',NULL,NULL,NULL,'',NULL,NULL,NULL,2,2,NULL,'2026-05-24 06:25:07'),
(5,'luis anomali','satukaki@gmail.com',NULL,NULL,NULL,'121212121212',NULL,NULL,NULL,2,37,NULL,'2026-05-24 06:25:07'),
(6,'povowibet','tuvohowe@mailinator.com',NULL,NULL,NULL,'0886857',NULL,NULL,NULL,2,53,NULL,'2026-05-24 14:56:57');
/*!40000 ALTER TABLE `employer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_responses`
--

DROP TABLE IF EXISTS `event_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `member_id` int(11) NOT NULL,
  `status` enum('going','not_going','maybe') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_responses_event_id_member_id_unique` (`event_id`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_responses`
--

LOCK TABLES `event_responses` WRITE;
/*!40000 ALTER TABLE `event_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'planning',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES
(1,'Dignissimos irure su','Incidunt explicabo','3000-02-03 14:23:00','Culpa voluptatem di','planning',1,'2026-05-24 15:20:23','2026-05-24 15:20:23',NULL),
(2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa','aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa','2026-05-29 00:00:00',NULL,'planning',1,'2026-05-25 04:46:50','2026-05-25 04:46:50',NULL);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_gallery_albums`
--

DROP TABLE IF EXISTS `family_gallery_albums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_gallery_albums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `created_by_userid` int(10) unsigned NOT NULL,
  `updated_by_userid` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_gallery_albums_title_index` (`title`),
  KEY `family_gallery_albums_created_by_userid_index` (`created_by_userid`),
  KEY `family_gallery_albums_updated_by_userid_index` (`updated_by_userid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_gallery_albums`
--

LOCK TABLES `family_gallery_albums` WRITE;
/*!40000 ALTER TABLE `family_gallery_albums` DISABLE KEYS */;
INSERT INTO `family_gallery_albums` VALUES
(1,'Laut',NULL,1,NULL,'2026-05-28 15:40:05','2026-05-28 15:40:05');
/*!40000 ALTER TABLE `family_gallery_albums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_gallery_photos`
--

DROP TABLE IF EXISTS `family_gallery_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_gallery_photos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(10) unsigned NOT NULL,
  `uploader_userid` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_album_uploaded_index` (`album_id`,`uploaded_at`),
  KEY `family_gallery_photos_album_id_index` (`album_id`),
  KEY `family_gallery_photos_uploader_userid_index` (`uploader_userid`),
  KEY `family_gallery_photos_uploaded_at_index` (`uploaded_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_gallery_photos`
--

LOCK TABLES `family_gallery_photos` WRITE;
/*!40000 ALTER TABLE `family_gallery_photos` DISABLE KEYS */;
INSERT INTO `family_gallery_photos` VALUES
(1,1,1,'Ikan',NULL,'https://ranji.rplsph.my.id/uploads/gallery/1779982834_cropped_image.png','1779982834_cropped_image.png','image/png',1529873,'2026-05-28 15:40:34','2026-05-28 15:40:34','2026-05-28 15:40:34'),
(2,1,1,'adsa','sdsd','https://ranji.rplsph.my.id/gallery/1781405098_cropped-memory.jpg','cropped-memory.jpg','image/jpeg',60745,'2026-06-13 19:44:58','2026-06-13 19:44:58','2026-06-13 19:44:58'),
(3,1,1,'d','s','https://ranji.rplsph.my.id/uploads/gallery/1781405359_cropped-memory.jpg','cropped-memory.jpg','image/jpeg',203738,'2026-06-13 19:49:19','2026-06-13 19:49:19','2026-06-13 19:49:19');
/*!40000 ALTER TABLE `family_gallery_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_medical_histories`
--

DROP TABLE IF EXISTS `family_medical_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_medical_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `family_member_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `medical_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by_userid` bigint(20) unsigned DEFAULT NULL,
  `updated_by_userid` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_date_index` (`family_member_id`,`medical_date`),
  KEY `family_medical_histories_family_member_id_index` (`family_member_id`),
  KEY `family_medical_histories_user_id_index` (`user_id`),
  KEY `family_medical_histories_medical_date_index` (`medical_date`),
  KEY `family_medical_histories_created_by_userid_index` (`created_by_userid`),
  KEY `family_medical_histories_updated_by_userid_index` (`updated_by_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_medical_histories`
--

LOCK TABLES `family_medical_histories` WRITE;
/*!40000 ALTER TABLE `family_medical_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `family_medical_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_member`
--

DROP TABLE IF EXISTS `family_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_member` (
  `memberid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pending_email` varchar(255) DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_token_expires_at` timestamp NULL DEFAULT NULL,
  `phonenumber` varchar(255) DEFAULT NULL,
  `pending_phonenumber` varchar(255) DEFAULT NULL,
  `phone_verification_otp_hash` varchar(255) DEFAULT NULL,
  `phone_verification_otp_expires_at` timestamp NULL DEFAULT NULL,
  `gender` varchar(255) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `birthplace` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `bloodtype` varchar(11) DEFAULT NULL,
  `job` varchar(255) DEFAULT NULL,
  `education_status` varchar(255) DEFAULT NULL,
  `life_status` varchar(255) NOT NULL,
  `marital_status` varchar(255) DEFAULT NULL,
  `deaddate` date DEFAULT NULL,
  `grave_location_url` text DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `userid` int(11) NOT NULL,
  `roleid` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`memberid`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_member`
--

LOCK TABLES `family_member` WRITE;
/*!40000 ALTER TABLE `family_member` DISABLE KEYS */;
INSERT INTO `family_member` VALUES
(1,'Budi Santoso','budi.santoso@example.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'male','1940-01-01','Solo','Indonesia, Indonesia, Indonesia',NULL,'Pensiunan',NULL,'alive','married',NULL,NULL,'https://ranji.rplsph.my.id/uploads/profile-pictures/1781405980_001968500_1455438595-chi-success-kid-meme-dad-kidney-removebg-preview.png',2,NULL,'2026-05-19 00:17:54',NULL),
(33,'Vernon','nuzyrijyc@mailinator.com',NULL,NULL,NULL,'+1 (614) 681',NULL,NULL,NULL,'female','2002-08-18','Deserunt quibusdam h','Indonesia',NULL,'Magnam dolorum est','Dolor quasi voluptat','alive','single',NULL,NULL,'https://ranji.kotobalai.my.id/uploads/profile-photos/XYYIIss3ZuEbprcVqcB0XU1jCt4dDtE0IvGp9PxT.jpg',38,2,'2026-05-24 09:56:48',NULL),
(34,'Isadora Martinez',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'female','2016-10-18','Eos iusto quibusdam','Indonesia',NULL,NULL,NULL,'alive','single',NULL,NULL,'https://ranji.rplsph.my.id/uploads/profile-pictures/1781406001_001968500_1455438595-chi-success-kid-meme-dad-kidney-removebg-preview.png',39,3,'2026-05-24 09:56:56',NULL),
(35,'Zena ytufhfhg','bojuje@mailinator.com',NULL,NULL,NULL,'+1 (835) 165-2664',NULL,NULL,NULL,'female',NULL,'Sit voluptatum quod','Indonesia, Indonesia',NULL,'Id dolor iste tempo','Odit commodo sit al','alive','married',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',40,3,'2026-05-24 09:57:11',NULL),
(36,'Tanisha Hicks',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'male','2017-06-22','Reprehenderit nobis',NULL,NULL,NULL,NULL,'alive',NULL,NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',41,3,'2026-05-24 09:57:32',NULL),
(37,'Illana ijdijf',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'female','2025-08-26','Aute voluptate verit','Indonesia, Indonesia',NULL,NULL,NULL,'alive','single',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-female.svg',42,3,'2026-05-24 09:57:42',NULL),
(38,'Jasonsd',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'male','2002-06-03','Placeat quia sunt','Indonesia, Indonesia',NULL,NULL,'sd','alive','single',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',43,3,'2026-05-24 09:57:47',NULL),
(39,'Remedios Berger',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'female','2026-09-06','Nesciunt blanditiis',NULL,NULL,NULL,NULL,'alive','married',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-female.svg',44,3,'2026-05-24 09:58:04',NULL),
(40,'Aretha Carney',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'female',NULL,'Minus','Indonesia, Indonesia',NULL,NULL,NULL,'alive','single',NULL,NULL,'https://ranji.rplsph.my.id/uploads/profile-pictures/1781406017_POS-DFD 1.drawio.png',45,3,'2026-05-24 10:32:15',NULL),
(41,'Accusantium quia rep','pubebu@mailinator.com',NULL,NULL,NULL,'Exercitationem quod',NULL,NULL,NULL,'male','2002-04-28',NULL,NULL,'A','Est et voluptates fa','Nesciunt consequatu','alive','single',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',46,NULL,'2026-05-24 11:04:17','2026-06-15 23:04:14'),
(42,'Mollit culpa eligend','joletibyly@mailinator.com',NULL,NULL,NULL,'Et animi provident',NULL,NULL,NULL,'male','1979-07-23',NULL,NULL,'AB-','Doloribus laborum V','Expedita ipsa esse','alive','married',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',47,NULL,'2026-05-24 11:05:20',NULL),
(43,'Est dignissimos non','juzipam@mailinator.com',NULL,NULL,NULL,'Inventore delectus',NULL,NULL,NULL,'male','2010-07-06',NULL,NULL,'B-','Duis voluptate sint','Ipsum non duis dolo','alive','divorced',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',48,NULL,'2026-05-24 11:06:14',NULL),
(44,'Rebecca Pittman','sasi@mailinator.com',NULL,NULL,NULL,'+1 (596) 636-8945',NULL,NULL,NULL,'male','1987-12-16','Quia est quis quia e',NULL,NULL,'Quo et reprehenderit','Saepe doloremque mag','alive',NULL,NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',49,3,'2026-05-24 11:26:48',NULL),
(46,'Carter Merritt',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'male','2009-01-10','Non soluta fugiat la',NULL,NULL,NULL,NULL,'alive',NULL,NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',51,3,'2026-05-24 14:41:02',NULL),
(47,'Wynne Norton',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'female','2026-05-20','Elit sit eveniet a',NULL,NULL,NULL,NULL,'alive',NULL,NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-female.svg',52,3,'2026-05-24 14:56:34',NULL),
(49,'Colorado Buchanan','quxejypek@mailinator.com',NULL,NULL,NULL,'+1 (362) 794-9656',NULL,NULL,NULL,'female','2007-01-03','Quibusdam ut sit co',NULL,NULL,'In minim deserunt di','Anim distinctio Ill','deceased',NULL,'2026-01-01',NULL,'https://ranji.rplsph.my.id/images/avatar-female.svg',55,3,'2026-05-24 15:40:24',NULL),
(50,'Jonas Burks',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'male','2024-11-26','Blanditiis iusto dol',NULL,NULL,NULL,NULL,'alive',NULL,NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',56,3,'2026-05-24 15:46:19',NULL),
(51,'Nolan Dillon',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'female','2025-02-09','Eius et consequatur',NULL,NULL,NULL,NULL,'alive',NULL,NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-female.svg',57,3,'2026-05-24 15:46:36',NULL),
(52,'Sed nihil obcaecati','ziwutuk@mailinator.com',NULL,NULL,NULL,'Consequatur ut sed c',NULL,NULL,NULL,'male','2022-12-03','Ea minima aspernatur','Commodo voluptatem, Maluku, Gabon','A','Necessitatibus dicta','Dolor sit porro pari','alive','married',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',58,NULL,'2026-06-15 22:57:34','2026-06-15 23:03:57'),
(53,'ami',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'female','2026-06-12','asas','asd, City in Kepulauan Riau 1, Kepulauan Riau, Indonesia','A',NULL,NULL,'alive','single',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-female.svg',59,NULL,'2026-06-15 23:05:30',NULL),
(54,'misty plume',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'male','2000-12-12','ad','cv, Batam, Kepulauan Riau, Indonesia','B+','as','asd','alive','single',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',60,NULL,'2026-06-16 00:19:49',NULL),
(55,'lili sasa',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'male','2026-06-12','amerika','sdfghjk, Bangka Barat, Kepulauan Bangka Belitung, Indonesia','B+','dfghjkl','wsdfyui','alive','married',NULL,NULL,'https://ranji.rplsph.my.id/images/avatar-male.svg',61,NULL,'2026-06-16 17:41:04',NULL);
/*!40000 ALTER TABLE `family_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_timelines`
--

DROP TABLE IF EXISTS `family_timelines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_timelines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `family_member_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_timelines_family_member_id_index` (`family_member_id`),
  KEY `family_timelines_event_date_index` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_timelines`
--

LOCK TABLES `family_timelines` WRITE;
/*!40000 ALTER TABLE `family_timelines` DISABLE KEYS */;
/*!40000 ALTER TABLE `family_timelines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES
(1,'default','{\"uuid\":\"d77eb23f-61fd-435b-a182-127758e7aa79\",\"displayName\":\"App\\\\Jobs\\\\ProcessDatabaseBackup\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessDatabaseBackup\",\"command\":\"O:30:\\\"App\\\\Jobs\\\\ProcessDatabaseBackup\\\":0:{}\",\"batchId\":null},\"createdAt\":1781423982,\"delay\":null}',0,NULL,1781423982,1781423982),
(2,'default','{\"uuid\":\"e96875b8-12d7-4245-902d-3e619ad968df\",\"displayName\":\"App\\\\Jobs\\\\ProcessDatabaseBackup\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessDatabaseBackup\",\"command\":\"O:30:\\\"App\\\\Jobs\\\\ProcessDatabaseBackup\\\":0:{}\",\"batchId\":null},\"createdAt\":1781423992,\"delay\":null}',0,NULL,1781423992,1781423992),
(3,'default','{\"uuid\":\"e29987d2-6ef0-448d-8ecb-19b11c4187ae\",\"displayName\":\"App\\\\Jobs\\\\ProcessDatabaseBackup\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessDatabaseBackup\",\"command\":\"O:30:\\\"App\\\\Jobs\\\\ProcessDatabaseBackup\\\":0:{}\",\"batchId\":null},\"createdAt\":1781597618,\"delay\":null}',0,NULL,1781597618,1781597618),
(4,'default','{\"uuid\":\"db670a46-ea40-455c-ba87-d7d8f2221505\",\"displayName\":\"App\\\\Jobs\\\\ProcessDatabaseBackup\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessDatabaseBackup\",\"command\":\"O:30:\\\"App\\\\Jobs\\\\ProcessDatabaseBackup\\\":0:{}\",\"batchId\":null},\"createdAt\":1781597627,\"delay\":null}',0,NULL,1781597627,1781597627);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `landing_page_settings`
--

DROP TABLE IF EXISTS `landing_page_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `landing_page_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `family_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `head_of_family_name` varchar(255) DEFAULT NULL,
  `head_of_family_message` text DEFAULT NULL,
  `head_of_family_photo` varchar(255) DEFAULT NULL,
  `created_by_name` varchar(255) DEFAULT NULL,
  `created_by_photo` varchar(255) DEFAULT NULL,
  `designed_by_name` varchar(255) DEFAULT NULL,
  `designed_by_photo` varchar(255) DEFAULT NULL,
  `approved_by_name` varchar(255) DEFAULT NULL,
  `approved_by_photo` varchar(255) DEFAULT NULL,
  `acknowledged_by_name` varchar(255) DEFAULT NULL,
  `acknowledged_by_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `landing_page_settings`
--

LOCK TABLES `landing_page_settings` WRITE;
/*!40000 ALTER TABLE `landing_page_settings` DISABLE KEYS */;
INSERT INTO `landing_page_settings` VALUES
(1,'Koto Balai Nan di Bawah','Keluarga','Budi Santosoooo','Se;amat datang','https://ranji.kotobalai.my.id/uploads/landing-page/JnoYmPxi3oqeKiCU8FUmuKm4p1Ds0fjPSHAmovtk.jpg','123','https://ranji.rplsph.my.id/uploads/system/1781405004_created_by_photo_created_by_photo.jpg','789','https://ranji.kotobalai.my.id/uploads/landing-page/B7AkRxA4QzkYpLMKcSbQ9xIRAgKz31Kb3Xnt6N51.jpg','456','https://ranji.kotobalai.my.id/uploads/landing-page/TxgrZk6SpusvZQIeHvhBel0CKyYPDxXLiG47PmyH.jpg','012','https://ranji.rplsph.my.id/uploads/system/1781405060_acknowledged_by_photo_acknowledged_by_photo.jpg','2026-05-24 09:27:06','2026-06-13 14:54:00');
/*!40000 ALTER TABLE `landing_page_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leader_succession_histories`
--

DROP TABLE IF EXISTS `leader_succession_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leader_succession_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_userid` bigint(20) unsigned NOT NULL,
  `leader_memberid` int(10) unsigned DEFAULT NULL,
  `leader_name` varchar(191) DEFAULT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'manual',
  `changed_by_userid` bigint(20) unsigned DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leader_succession_histories_owner_userid_index` (`owner_userid`),
  KEY `leader_succession_histories_leader_memberid_index` (`leader_memberid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leader_succession_histories`
--

LOCK TABLES `leader_succession_histories` WRITE;
/*!40000 ALTER TABLE `leader_succession_histories` DISABLE KEYS */;
INSERT INTO `leader_succession_histories` VALUES
(1,38,1,'Budi Santosoooo','superadmin_override',1,'2026-05-24 14:55:04','2026-05-24 14:55:04','2026-05-24 14:55:04'),
(2,38,1,'Budi Santosoooo','manual_setting',38,'2026-05-24 14:55:48','2026-05-24 14:55:48','2026-05-24 14:55:48');
/*!40000 ALTER TABLE `leader_succession_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leader_succession_settings`
--

DROP TABLE IF EXISTS `leader_succession_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leader_succession_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_userid` bigint(20) unsigned NOT NULL,
  `heir_memberid` int(10) unsigned DEFAULT NULL,
  `pin_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leader_succession_settings_owner_userid_unique` (`owner_userid`),
  KEY `leader_succession_settings_heir_memberid_index` (`heir_memberid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leader_succession_settings`
--

LOCK TABLES `leader_succession_settings` WRITE;
/*!40000 ALTER TABLE `leader_succession_settings` DISABLE KEYS */;
INSERT INTO `leader_succession_settings` VALUES
(1,38,1,NULL,'2026-05-24 14:55:04','2026-05-24 14:55:59');
/*!40000 ALTER TABLE `leader_succession_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `letters`
--

DROP TABLE IF EXISTS `letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `letters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `unlock_type` varchar(20) NOT NULL DEFAULT 'immediate',
  `unlock_value` int(10) unsigned DEFAULT NULL,
  `unlock_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letters`
--

LOCK TABLES `letters` WRITE;
/*!40000 ALTER TABLE `letters` DISABLE KEYS */;
/*!40000 ALTER TABLE `letters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `level`
--

DROP TABLE IF EXISTS `level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `level` (
  `levelid` int(11) NOT NULL AUTO_INCREMENT,
  `levelname` varchar(255) NOT NULL,
  PRIMARY KEY (`levelid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `level`
--

LOCK TABLES `level` WRITE;
/*!40000 ALTER TABLE `level` DISABLE KEYS */;
INSERT INTO `level` VALUES
(1,'Employer'),
(2,'Family Member');
/*!40000 ALTER TABLE `level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `live_locations`
--

DROP TABLE IF EXISTS `live_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `live_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) unsigned NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `accuracy` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `live_locations_userid_unique` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `live_locations`
--

LOCK TABLES `live_locations` WRITE;
/*!40000 ALTER TABLE `live_locations` DISABLE KEYS */;
INSERT INTO `live_locations` VALUES
(1,1,1.1246406,104.0425755,35.00,'2026-05-24 09:24:13','2026-05-28 14:17:49'),
(2,2,1.1293611,104.0090532,117.00,'2026-05-24 15:38:30','2026-05-24 15:38:32'),
(3,45,1.1293459,104.0090009,127.00,'2026-05-24 15:39:02','2026-05-24 15:42:10');
/*!40000 ALTER TABLE `live_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_articles`
--

DROP TABLE IF EXISTS `member_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_articles` (
  `member_id` int(11) NOT NULL,
  `biography` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_articles`
--

LOCK TABLES `member_articles` WRITE;
/*!40000 ALTER TABLE `member_articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000001_create_cache_table',1),
(2,'0001_01_01_000002_create_jobs_table',1),
(3,'2026_05_11_125627_create_new_auth_tables',1),
(4,'2026_05_11_125648_create_new_family_tables',1),
(5,'2026_05_11_125700_create_new_feature_tables',1),
(6,'2026_05_13_150757_add_prefix_to_socialmedia_table',1),
(7,'2026_05_14_040705_add_soft_deletes_to_social_media_table',1),
(8,'2026_05_14_075712_add_soft_deletes_to_relationship_table',1),
(9,'2026_05_15_142718_add_status_to_events_table',1),
(10,'2026_05_16_024227_make_document_path_nullable_in_relationship_validations',1),
(11,'2026_05_16_025021_make_profile_fields_nullable_in_family_member_table',1),
(12,'2026_05_16_051356_add_roleid_to_family_member_table',1),
(13,'2026_05_16_063215_add_remember_token_to_user_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ownsocial`
--

DROP TABLE IF EXISTS `ownsocial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ownsocial` (
  `ownid` int(11) NOT NULL AUTO_INCREMENT,
  `socialid` int(11) NOT NULL,
  `memberid` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`ownid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ownsocial`
--

LOCK TABLES `ownsocial` WRITE;
/*!40000 ALTER TABLE `ownsocial` DISABLE KEYS */;
INSERT INTO `ownsocial` VALUES
(2,1,54,'https://www.youtube.com/@4leaf-music');
/*!40000 ALTER TABLE `ownsocial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relationship`
--

DROP TABLE IF EXISTS `relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `relationship` (
  `relationid` int(11) NOT NULL AUTO_INCREMENT,
  `memberid` int(11) NOT NULL,
  `relatedmemberid` int(11) NOT NULL,
  `relationtype` varchar(255) NOT NULL,
  `child_parenting_mode` varchar(255) NOT NULL DEFAULT 'with_current_partner',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`relationid`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relationship`
--

LOCK TABLES `relationship` WRITE;
/*!40000 ALTER TABLE `relationship` DISABLE KEYS */;
INSERT INTO `relationship` VALUES
(1,1,33,'partner','with_current_partner',NULL),
(3,34,35,'partner','with_current_partner',NULL),
(4,34,36,'child','single_parent',NULL),
(7,37,39,'child','single_parent',NULL),
(9,35,41,'child','single_parent','2026-06-15 23:04:14'),
(10,34,42,'child','single_parent',NULL),
(11,34,43,'child','single_parent',NULL),
(12,37,44,'partner','with_current_partner',NULL),
(14,38,46,'child','single_parent',NULL),
(15,37,47,'child','single_parent',NULL),
(17,38,49,'partner','with_current_partner',NULL),
(18,38,50,'child','single_parent',NULL),
(19,38,51,'child','single_parent',NULL),
(24,1,38,'child','with_current_partner',NULL),
(25,34,40,'child','with_current_partner',NULL),
(26,1,37,'child','with_current_partner',NULL),
(27,1,52,'child','with_current_partner','2026-06-15 23:03:57'),
(28,33,52,'child','with_current_partner','2026-06-15 23:03:57'),
(29,53,51,'child','with_current_partner',NULL),
(30,38,53,'partner','with_current_partner',NULL),
(31,1,34,'child','with_current_partner',NULL),
(32,33,34,'child','with_current_partner',NULL),
(34,42,54,'child','with_current_partner',NULL),
(35,39,55,'partner','with_current_partner',NULL);
/*!40000 ALTER TABLE `relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relationship_validations`
--

DROP TABLE IF EXISTS `relationship_validations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `relationship_validations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `requested_by_member_id` bigint(20) unsigned DEFAULT NULL,
  `action_type` enum('divorce','delete_child','delete_partner') NOT NULL,
  `target_member_id` bigint(20) unsigned DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_status_created_index` (`status`,`created_at`),
  KEY `family_action_status_index` (`action_type`,`status`),
  KEY `relationship_validations_requested_by_member_id_index` (`requested_by_member_id`),
  KEY `relationship_validations_action_type_index` (`action_type`),
  KEY `relationship_validations_target_member_id_index` (`target_member_id`),
  KEY `relationship_validations_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relationship_validations`
--

LOCK TABLES `relationship_validations` WRITE;
/*!40000 ALTER TABLE `relationship_validations` DISABLE KEYS */;
/*!40000 ALTER TABLE `relationship_validations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `roleid` int(11) NOT NULL AUTO_INCREMENT,
  `rolename` varchar(255) NOT NULL,
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES
(1,'Superadmin'),
(2,'Admin');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('0Q3C9pCUiZa7ElqmVlkZwrwDVFkBiHyuWmt2BEbz',NULL,'34.243.218.145','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM3lUYVd5SUlKbUFEWDhOOUdaOW5TNnFsTWkzOG1DQTNPSTZtV0xjVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnJhbmppLnJwbHNwaC5teS5pZCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1785125167),
('5V1F5A5GQfgvAhTZbXj3kPQGLMO5EihC5CMZSNtY',NULL,'66.132.172.214','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','eyJfdG9rZW4iOiJzV09WOFdNdVVLNWg5ckhpRTMzRmxxMmRLbmJyQnhnWVBQeGhUaFZ0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784758421),
('6rKU2pOmHXZmWCvLwdxVGpV8OmVGKuzoDup7G6Sk',NULL,'198.235.24.16','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','eyJfdG9rZW4iOiIycGh2S0JwV0J2eE9hZ3dRTVFHMTViM1QydWlCNFFWbTBIeHNKR0JFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3JhbmppLmtvdG9iYWxhaS5teS5pZCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1784973060),
('6uuuPITy4ABfr3xCrlzOELmo9DCLOkqScQ4b9MlJ',NULL,'44.220.233.148','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJwWEhWMkZFWklINzE2SDRkQTdnMVBETUY2MjdRaHN0R1RZbHZ6elZLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cucmFuamkua290b2JhbGFpLm15LmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1784854953),
('8a8g0ymDMVGeBlg9agkFyBtEizsY7qwW7mcdBTDt',NULL,'54.164.167.77','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNEhPenJtSG96OHgzU0ladkhWZ1NFZFV6Mk1IdUhOTVRNV2RPSkt6YSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnJhbmppLnJwbHNwaC5teS5pZCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1784858987),
('9VQfNo03wPHZ7Y6nq0wsNwYXc6fzD404VOgwbMUt',NULL,'66.132.172.202','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYlRxVWtPdmdSVTNicXZJWkFCV3Q1STNwTlltbmJsaUdxWllUSkpxWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly93d3cucmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1784728136),
('APLrtXq1EqhRrxb5ve3ImWLk4v0Hs78FBAGv6Lgc',NULL,'54.157.149.144','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMTNBeXFYeTNOeWlpVW9uMTJib3k3dU5MT1BHbW9zSmhJcmtoV1EwQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly9yYW5qaS5ycGxzcGgubXkuaWQiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1785113130),
('AsqkixlYxnYp51lFXF0RAxVAvJiN0Ji6hP0zZqfo',NULL,'44.252.99.30','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','eyJfdG9rZW4iOiIxOUtMcnphUUdVREN5MlVrQlNHMUF4VVFTVmkzTENkZjk5WGRUOXA4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cucmFuamkua290b2JhbGFpLm15LmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1784898016),
('avEetoo4lBQX4fdLACbLJVHoV9N2MmT6FOekImaO',NULL,'205.210.31.163','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','eyJfdG9rZW4iOiJqZWVKcWlzNGNCcHNuU1FuRU80VVB4bDF2SVVLOEl6bjdkSzlRRmdJIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784889469),
('Bdm7McIZvEsyFKHzVNvzZCh2j46lBxI5KOEm41y3',NULL,'66.132.195.112','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','eyJfdG9rZW4iOiJRMkN3UjYwZ0xTYnk0NGcyYUR3bThFMGtlUUFpSjNZYlBlWW1hNEZGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3d3dy5yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784728724),
('bK6pnsiTRuoxye2ijT7kfUNycI5hFZKBgmb0ZBgY',NULL,'144.126.210.203','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJiNFNLTXFUWHNNMzhRQVIySlpTMkV5bllPTE1IaG05dW1pbTdBOWNiIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3JhbmppLmtvdG9iYWxhaS5teS5pZCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1784723772),
('DQUsMpAHpXWQl6jxD3aUrhMoXkY15lMCIHuZpGCK',NULL,'198.235.24.113','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','eyJfdG9rZW4iOiJJandxc1JkYkhLMktEc05HVWVNR21Cb2VDWGNhM1RxOWJUdDRMQkVnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3JhbmppLmtvdG9iYWxhaS5teS5pZCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1784890175),
('efuX50xRImMg9eaS6qJjcd3pq15tnMP0IOAWU0f4',NULL,'51.102.72.130','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJaNmRta2VwVFZScTJtdzgxbGp6b28yRnI4VHVGeGo2UWE3Q0FweEd2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784886248),
('GFcvdbrxjIUuO52Dre0Rwano0ymIIwPb3ODUjC0P',NULL,'74.7.227.22','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.4; +https://openai.com/gptbot)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiajFuTnA2UHlwZnhBSjloTE9ZS0MwanZMN2pYeXA4N2NzZlJGVlBRZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnJhbmppLnJwbHNwaC5teS5pZCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1784742336),
('gK1SloEK4j2wIkS5WHQvSiFfNVcWJ5acOyhpOajM',NULL,'34.195.23.187','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 clst-pab','eyJfdG9rZW4iOiJLQzZtZ0F0STJCV3FrbnphZnZIVjh2aGtHVHNlZkgzWWF2N3VqWFhvIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3d3dy5yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1785105817),
('GVRz70fAVFLVqtjdRi235QWEqJJOhepyX27gzwqu',NULL,'24.199.123.204','Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaW9GYUtLYWhCZmhBOFRKTThEQzVCYUJHYVJZZDJxU0VrcUNtTFlqRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly9yYW5qaS5ycGxzcGgubXkuaWQiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1785054264),
('H1OzPpzPQLLkJXdoNQYd8raEywlP5UU2YN238VEo',NULL,'182.2.5.134','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNWNucUlDN1MyTTZPS1BoRDJEVFZ0UHJlU1BPUllHSUoyS2Vwdm5HNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vcmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1784766341),
('HahCs1hPzV5s5G7ohvxNBb2Va4oOn8XnJw4ShmUn',NULL,'144.126.210.203','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJQcnZvSXZKdDJINEk1SFN4a09LYURYMk93R0EyOWJYRTIxMk54OEllIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784723772),
('Hljf1gT7lOgtVlSGRkBCjAyGbrjK0JA5JUXC8UNL',NULL,'3.65.34.43','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRFg2aHNmV0dHSXgxRW9NaTN4MnNrV3Z3MlZmTVJIRGRhNkZiMkxEdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vcmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1785115126),
('inWbx3kUnOm6vtqtsIxpc2UtLYWfALNz6DSEKhIb',NULL,'205.210.31.21','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVzhVZjJGakJVeGs0MHh3NUpOOG43YWhWWjlWdG5rdnhwdzVBNU53QyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly9yYW5qaS5ycGxzcGgubXkuaWQiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1784973211),
('jmFYyvSLZs36lyD5fFspMtWbbi2aHMUwsGSDJM2M',NULL,'159.89.183.33','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI2clJkT3ZuMWlOYWtGek93MXBtdnVmU3luNm5Ha1dDQmpaM2V3b25aIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3d3dy5yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784771143),
('kasXfZfughXkeMAICM8chmd79x66TPfRr52TeZNR',NULL,'34.23.17.50','Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)','YTozOntzOjY6Il90b2tlbiI7czo0MDoieFR4RzkyclJvblpTZlBFMjBGVWZZSG5CRTQ2Z2dxNHkzNkRjdVhibSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly93d3cucmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1785160877),
('kR0f5R67HrQCqqUeqGGJ5Owp0aZ92oTHeobEDIip',NULL,'188.165.193.149','tranco-crawler/1.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNFRDYnpiUzlLSmRwaUVPUHdCenA0S3Z4d2FVMGZtMXJncWdkaVp4QyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vcmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1784857030),
('lD2XTkVRKD94S6uoUJC9mVcvZ2qk9dTInb3fVDf1',NULL,'54.173.131.118','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU0VPY000SDZ4YnlacjZUOEdhTWZienFPUElSSWJobHEzT3dZSG9vZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly93d3cucmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1785102770),
('lNiwhuqGlKFgXe63AoeDMVUNlP2oK7dnQaMn5NbA',NULL,'66.132.195.106','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','eyJfdG9rZW4iOiI1dnIwWW5xdTB6cHRBU0UyRlluZzRGalBGQ3U2emdCdWJvUXBxWUtJIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3JhbmppLmtvdG9iYWxhaS5teS5pZCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1784758590),
('LpCAyObHaRK05cS2SnhLbJ4Vzp2p4tC6Gbq0pvA0',NULL,'66.132.195.112','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','eyJfdG9rZW4iOiJpSGkyTzJDVjc4ZjlFMndJaUJtOWs2SmRoWWl1SzZIMDFuSWJVcnZnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3d3dy5yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784728699),
('My3ksUEYmgUcSlu9CurD1YdBWhbpIp0mxyjqEAiY',1,'202.43.231.185','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSWV6Z0RHQnZ1MHQ0aUNhNmI5Vmo0RUtzeWZCVnM5UWFCeURHWGV1QSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vcmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1785267248),
('PpgULWW7sGYfmI8llLfAGYsHbo5PlVLqMCoWWVe8',NULL,'185.247.137.112','Mozilla/5.0 (compatible; InternetMeasurement/1.0; +https://internet-measurement.com/)','eyJfdG9rZW4iOiIzUGxhNjc2UWZlNjFVWGdrVTdqUk1WRnpGRWVYTmVQc2t5c3dhOHZBIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cucmFuamkua290b2JhbGFpLm15LmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1784856414),
('PtNUKjOk7fqIS6VXTx6UN5k8l31hC5v5fABSYw98',NULL,'34.83.163.148','Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZEVlTE5TdzlsSmRYc2I4bUNXa3Jpc05HOWRvbzBTU0V3ek5RVkF1ciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly9yYW5qaS5ycGxzcGgubXkuaWQiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1785161455),
('q35fRRn8FyE3Fp8TIyji9bPigBMcnCCokQ7JW2eE',NULL,'74.7.242.0','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.4; +https://openai.com/gptbot)','eyJfdG9rZW4iOiJtd2ZPeTVCNmtaVXRoUmkzR3pCSUdSd3NGNmIyUEpSTjVPNVUyb1VHIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784844434),
('qGnwFBIIbRdxVBhJGJgjZJj5lBD2X2c1l5JwJJl2',NULL,'74.7.227.181','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.4; +https://openai.com/gptbot)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOXFjcmRzVVo0R2hwZUNNYWNjeTJndHRCQzR3c0NrQnNDQjRRUVVvMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vcmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1784754823),
('qVgD2KMMOsnoezqBer0477D2c1UE6duZNt98Um3B',NULL,'195.96.139.224','Mozilla/5.0 (compatible; InternetMeasurement/1.0; +https://internet-measurement.com/)','eyJfdG9rZW4iOiJwQk5TdFhDWkpBSFBsYzFUb3lWdlZrSmNHdm1SOHlTelIzZHBCVXFzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3d3dy5yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784856221),
('qX4gVI4nEHO4ilrnDRbmUjuuPRo8dyEGVlXvGkNn',NULL,'66.132.195.106','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','eyJfdG9rZW4iOiJzSVE2aWpOdVVFQloxenluRjlOdTNWS1NIYzJiMGs0SWNmU0VXMGZRIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3JhbmppLmtvdG9iYWxhaS5teS5pZCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1784758598),
('sec7LG9bMR9XXbmQaau9w592KiQ5Fd56sGNrRBOg',NULL,'147.90.209.74','Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1','eyJfdG9rZW4iOiIwZms5Z2NGdnFhcHdDYVlOWTNsTUNZYnBLUlpsRXNHRmszeHJ3czFDIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3JhbmppLmtvdG9iYWxhaS5teS5pZCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1784967834),
('tBTHuPmIvthl53C4Erafc14N7TWz8bDNytWRnUpO',NULL,'35.221.24.38','Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT0pCYWpxVEdjWkVxNXFzQTFoeWEwV0cyTlFINDFpbGw4TVVvUnlKMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly93d3cucmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1785260191),
('tjn7vLGkT0yWSbPnBR1vBtLDsK97xfY10JOVcoyQ',NULL,'198.235.24.173','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','eyJfdG9rZW4iOiJFQnZPd2N5Qk0yWmVYZHIwaWgyYzhtZWNuamJqY2VqVlc0N0s4UHY5IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3JhbmppLmtvdG9iYWxhaS5teS5pZCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1784746653),
('U86Lm4FocNhrflNPJCyiin3uVuoJldvJuAsjv9zr',NULL,'34.182.30.254','Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNk5QSkxlYW4zQTJHTGtjUTJRWkdwcEdpQmcxeU52Nk5GS0tXZXYxaSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly93d3cucmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1785251395),
('UqjhU3BExiJbxDxWeQTZRH6ibk8ramns736nJAcK',NULL,'147.185.132.237','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','eyJfdG9rZW4iOiJUNjhlaFNkQ0U2Q09LSUtqRnJSdWNjbE4wRFdaUklQY0tQYnd3cFRwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cucmFuamkua290b2JhbGFpLm15LmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1784898624),
('v7SqGp2fuYH6svwh0mlaQGNI6OChRvRGO9O2UGwH',NULL,'66.132.172.202','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoielNRMVpqV3pSZjRNUDRTOHMyOTc0a1Z6d1N1aGFBZkRSNU95TTlKNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly93d3cucmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1784728099),
('VxDuygz7AO0NBFZFZyT50LNd3YfZUxjhrs3hovkg',NULL,'24.199.123.204','Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNTI5b0F5RjhzcTJYVHpIMzdPVnJoY3RiSEhRSlhpeWZxWTJMZmhHcSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vcmFuamkucnBsc3BoLm15LmlkIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1785054264),
('Vy9po5OjUxiz55u7anLEeuspb61mENPoWq465mi2',NULL,'188.165.193.149','tranco-crawler/1.0','eyJfdG9rZW4iOiJFV1ZLdGxBWE1wUWJneE00cTlQbnA5Umd3U2tjblpzOE84d0ZYdzNqIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784857031),
('W8vNLNVUmgkQZN0z7Gui3ZIPwngYrcDvSlLb3zDt',NULL,'66.132.172.214','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','eyJfdG9rZW4iOiJ0VXozUm9oMk8yYVc1SFVWaWM1QTZEWnJhWTJaUkl6a0Q0RW5hNzQzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9yYW5qaS5rb3RvYmFsYWkubXkuaWQiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784758382),
('XSj3bqpFfJIVa1uUm9oPoonWNBLpmGkro4vsfP0g',NULL,'205.210.31.38','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','eyJfdG9rZW4iOiJBS0NSQU1ON1VJN1llTkVWWUZYUk84dzd5QWZLeGVzUXE0OGR1Z1NPIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cucmFuamkua290b2JhbGFpLm15LmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1785066152),
('YIT0tdUTqZTyO6QhiNk71kXTXBpqsm6MK1BKlgl6',NULL,'159.89.183.33','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJVWTRmRE9WU09EOVZrWm1PZmR0cHVhaWhESzNFU21hRFBPRmlIVzZPIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cucmFuamkua290b2JhbGFpLm15LmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1784771143);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `socialmedia`
--

DROP TABLE IF EXISTS `socialmedia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `socialmedia` (
  `socialid` int(11) NOT NULL AUTO_INCREMENT,
  `socialname` varchar(255) DEFAULT NULL,
  `prefix` varchar(255) DEFAULT NULL,
  `socialicon` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by_userid` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`socialid`),
  UNIQUE KEY `socialname` (`socialname`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `socialmedia`
--

LOCK TABLES `socialmedia` WRITE;
/*!40000 ALTER TABLE `socialmedia` DISABLE KEYS */;
INSERT INTO `socialmedia` VALUES
(1,'Youtube','https://youtube.com','https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/youtube.svg','2026-05-24 15:19:18','2026-06-15 23:07:43',NULL,NULL),
(2,'Facebook','https://facebook.com','https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/facebook.svg','2026-06-15 23:07:57','2026-06-15 23:08:11',NULL,NULL);
/*!40000 ALTER TABLE `socialmedia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system` (
  `systemid` int(11) NOT NULL AUTO_INCREMENT,
  `systemname` varchar(255) NOT NULL,
  `systemlogo` varchar(255) NOT NULL,
  `systemcontact` varchar(255) NOT NULL,
  `systemmanager` varchar(255) NOT NULL,
  `systemaddress` varchar(255) NOT NULL,
  PRIMARY KEY (`systemid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES
(1,'Sepeda','https://ranji.kotobalai.my.id/uploads/system/hI90FAv2aMSIRRYrNwShoEUhox0vm2S53TSaaySQ.jpg','123','ayam','adadeg');
/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translation_cache`
--

DROP TABLE IF EXISTS `translation_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `translation_cache` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(120) NOT NULL,
  `source_locale` varchar(12) NOT NULL,
  `target_locale` varchar(12) NOT NULL,
  `source_hash` varchar(64) NOT NULL,
  `source_text` longtext NOT NULL,
  `translated_text` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `translation_cache_unique` (`cache_key`,`source_locale`,`target_locale`,`source_hash`),
  KEY `translation_cache_lookup_index` (`cache_key`,`target_locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translation_cache`
--

LOCK TABLES `translation_cache` WRITE;
/*!40000 ALTER TABLE `translation_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `translation_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `levelid` int(11) NOT NULL,
  `reset_password_token` varchar(255) DEFAULT NULL,
  `reset_password_token_expired` datetime DEFAULT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES
(1,'superadmin','$2y$12$Kt2ogmDA8ppUdk1ioNTr7OlfOyobAYjFgNejPDDNl34p0Srwdivo2',NULL,NULL,1,NULL,NULL),
(2,'budi684','$2y$12$eDthgqYGaZOzvk7CkkQAL.dVO5i6tSagB8mYl.theLZfm7rQGgX/e',NULL,NULL,1,NULL,NULL),
(19,'Nathalia','$2y$12$zYvow0hwm0RIEmvky4cn5.Vgqablwb/sPVs774xzSYXASPIxblOhi',NULL,NULL,1,NULL,NULL),
(37,'luis anomali','$2y$12$0dUnu.UN/P6VWSGjjOHA6.XXMDyG86sUx4mt5OV509OahIg9TAEuG',NULL,NULL,1,NULL,NULL),
(38,'vernon417','$2y$12$2YupGq8xx13MGQx36rehOOyOQqV./8BqSJ0hPHLLmGLBkvnMCYZxW',NULL,NULL,2,NULL,NULL),
(39,'isadora360','$2y$12$T1jXjfNPZa2xxP8TvPhK6.Y4F30mB73rNDbRNcK5OyfVWC63WhV/K',NULL,NULL,2,NULL,NULL),
(40,'zena139','$2y$12$yirhFyRVfRrKi7902Ryh3u27KzX/3rjxEw//45HB39AcGXo.gCSqy',NULL,NULL,2,NULL,NULL),
(41,'tanisha374','$2y$12$nYcASqZ9/qxgM/BlKaErwOi0KX/jvdeTuUfPFbsx4fhEl5s/ggs3a',NULL,NULL,2,NULL,NULL),
(42,'illana806','$2y$12$KUcx0zjibXVctxL7syxm7uHb6t/lNHs7HH8Sklk4GxKi9r0S6VBqO',NULL,NULL,2,NULL,NULL),
(43,'jason297','$2y$12$8542LE7oEnGMpQdjUkyCE.b6k6S/SsAjTE67XDM5oCmfGEPjxkRIW',NULL,NULL,2,NULL,NULL),
(44,'remedios963','$2y$12$hESnaC8AM3mNSvoN0LLUguv29BxTKalUi3IOLxJ28dM/5aGAU8X/S',NULL,NULL,2,NULL,NULL),
(45,'aretha969','$2y$12$8nW3CHYoHIVSP8Lty7LUz.0l1hZZqg3fKnt874FOt9jYZlm5HYGTi',NULL,NULL,2,NULL,NULL),
(46,'accusantium864','$2y$12$mFQq43YvongmsFowoz4fJugT5oeCbWHL/s6TT49LowEYRkRXN.pjq',NULL,'2026-06-15 23:04:14',2,NULL,NULL),
(47,'mollit129','$2y$12$ku57VmB/aKg.JJFcd6x4FubicK1k3eOCGb4F8BLbpWpTAoMaV92OW',NULL,NULL,2,NULL,NULL),
(48,'est683','$2y$12$RjHteBscomkgEwige1Lx2eONbyahub1y/nyxU4O14ad14XxFte8pm',NULL,NULL,2,NULL,NULL),
(49,'rebecca805','$2y$12$CEByZJMTxA.bwES7CfcwmuOgvcZrz4JttylHp7mx6nZtr1u02d6dK',NULL,NULL,2,NULL,NULL),
(51,'carter493','$2y$12$mPfYODysIDh7YOGfo3ZirOTQkkqF5.p.xdp3dlx0IEA7sdmiV8V3u',NULL,NULL,2,NULL,NULL),
(52,'wynne988','$2y$12$bH.cum5PqQYtGpEQTZmqe.3PVuQ69ZUhpvzxRq8BjlDI8/BYukHxm',NULL,NULL,2,NULL,NULL),
(53,'povowibet','$2y$12$dpR6kBhyZb2xGqP4VUauKOCv6r6AThlfDDzohj3JCJ3dOP9YziZq2',NULL,NULL,1,NULL,NULL),
(55,'colorado961','$2y$12$2qV1rbjX1cOZVD3aBYjQouSgYWqgspS9WJFyUU3O8WGnbdTNh5eLK',NULL,NULL,2,NULL,NULL),
(56,'jonas555','$2y$12$7VCW.jX8O3ty.1agDT7b5.IjZEIObJGWYQf1WPerv4QFemxHk1rGq',NULL,NULL,2,NULL,NULL),
(57,'nolan993','$2y$12$61JLZWq143aBqXBozlV1Je22Dbod.ZoqWArDQftxT3um/KkiQbpCS',NULL,NULL,2,NULL,NULL),
(58,'sed191','$2y$12$qR/sT8bgWhhWVJUhVvRK7.m934puSSzu0Lih2RsTJijl6MsA/2yie',NULL,'2026-06-15 23:03:57',1,NULL,NULL),
(59,'ami447','$2y$12$AGZ0Ks2VLnFgb34U2WQ1euJX/e/MNRnnCmkbBwHY3978yQ9My.tT2',NULL,NULL,1,NULL,NULL),
(60,'misty184','$2y$12$x57/RQs4JYI.DzxisM34s.XpAD2lkWE35zUPCVAwCGDO19gxWTf8u',NULL,NULL,1,NULL,NULL),
(61,'lili345','$2y$12$WEAaRKOroqX9vlmufhnw8u/H4cl8JA6IR0ApgIsw5fiiaonKDmACy',NULL,NULL,1,NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-07-29  2:34:11
