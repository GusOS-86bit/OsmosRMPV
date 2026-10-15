
-- In order to become the first admin, you will have to do this manually in PHPMyAdmin users table , is_admin = 1 not 0 
-- every demo users password is osmos123

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `content_i_follow`;
CREATE TABLE `content_i_follow` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `content_id` bigint(20) NOT NULL,
  `content_type` varchar(30) NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_follow` (`user_id`,`content_id`,`content_type`),
  KEY `content_idx` (`content_type`,`content_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `content_i_follow` VALUES
(1,748617700973442,33391535090799345,'post',0,'2026-10-05 18:20:00'),
(2,473897691038,67822844686224760,'post',0,'2026-10-05 20:05:00'),
(3,748617700973442,719185617483662,'post',0,'2026-10-09 18:20:00'),
(4,473897691038,7105721622037,'post',0,'2026-10-10 09:15:00'),
(5,4903407816971,7105721622037,'post',0,'2026-10-10 09:40:00'),
(7,73558098367309,2724023069316,'post',0,'2026-10-11 16:05:00'),
(8,748617700973442,2724023069316,'post',0,'2026-10-11 16:30:00'),
(9,372154181591,2724023069316,'post',0,'2026-10-11 17:12:00'),
(10,73558098367309,9610383361689,'post',0,'2026-10-11 09:00:00'),
(11,748617700973442,7158062911982,'post',0,'2026-10-12 08:00:00'),
(12,473897691038,725465338730,'post',0,'2026-10-12 14:20:00'),
(13,4903407816971,725465338730,'post',0,'2026-10-12 14:45:00'),
(14,372154181591,15999331102094,'post',0,'2026-10-12 19:48:00'),
(15,748617700973442,15999331102094,'post',0,'2026-10-12 20:05:00'),
(17,748617700973442,842084841837436,'post',0,'2026-10-13 10:30:00'),
(18,473897691038,842084841837436,'post',0,'2026-10-13 11:05:00'),
(20,1897884319521,553687694969097,'post',0,'2026-10-13 12:10:00'),
(21,473897691038,85449930663125,'post',0,'2026-10-13 17:45:00'),
(22,4903407816971,169501544376,'post',0,'2026-10-13 20:55:00'),
(23,473897691038,169501544376,'post',0,'2026-10-13 21:10:00');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sender` bigint(20) NOT NULL,
  `receiver` bigint(20) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'pending',
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sender_receiver` (`sender`,`receiver`),
  KEY `receiver` (`receiver`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `friends` VALUES
(1,748617700973442,473897691038,'accepted','2026-10-06 09:00:00'),
(2,748617700973442,4903407816971,'accepted','2026-10-06 10:20:00'),
(3,748617700973442,73558098367309,'accepted','2026-10-06 13:00:00'),
(4,748617700973442,372154181591,'accepted','2026-10-08 08:45:00'),
(5,473897691038,4903407816971,'accepted','2026-10-06 11:00:00'),
(6,473897691038,1897884319521,'accepted','2026-10-08 18:00:00'),
(7,4903407816971,372154181591,'accepted','2026-10-08 09:30:00'),
(8,73558098367309,1897884319521,'accepted','2026-10-08 20:00:00'),
(9,1897884319521,748617700973442,'pending','2026-10-13 12:30:00');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `group_invites`;
CREATE TABLE `group_invites` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `groupid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `inviter` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_user` (`groupid`,`userid`),
  KEY `groupid` (`groupid`),
  KEY `userid` (`userid`),
  KEY `disabled` (`disabled`),
  KEY `inviter` (`inviter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `group_members`;
CREATE TABLE `group_members` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) NOT NULL,
  `groupid` bigint(20) NOT NULL,
  `role` varchar(10) NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_user` (`groupid`,`userid`),
  KEY `userid` (`userid`),
  KEY `groupid` (`groupid`),
  KEY `role` (`role`),
  KEY `disabled` (`disabled`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `group_members` VALUES
(1,473897691038,464532042534,'moderator',0),
(2,4903407816971,464532042534,'member',0),
(3,372154181591,464532042534,'member',0),
(4,748617700973442,3613257718561,'member',0),
(5,1897884319521,3613257718561,'member',0),
(6,372154181591,3613257718561,'member',0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `group_requests`;
CREATE TABLE `group_requests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `groupid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_user` (`groupid`,`userid`),
  KEY `groupid` (`groupid`),
  KEY `userid` (`userid`),
  KEY `disabled` (`disabled`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `group_requests` VALUES
(1,464532042534,73558098367309,0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `contentid` bigint(20) NOT NULL,
  `likes` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_contentid` (`type`,`contentid`),
  KEY `contentid` (`contentid`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `likes` VALUES
(1,'post',719185617483662,'[{\"user_id\":\"473897691038\",\"date\":\"2026-10-09 19:00:00\"},{\"user_id\":\"4903407816971\",\"date\":\"2026-10-09 19:30:00\"},{\"user_id\":\"73558098367309\",\"date\":\"2026-10-09 21:00:00\"}]'),
(2,'post',2724023069316,'[{\"user_id\":\"748617700973442\",\"date\":\"2026-10-11 16:28:00\"},{\"user_id\":\"473897691038\",\"date\":\"2026-10-11 16:50:00\"},{\"user_id\":\"372154181591\",\"date\":\"2026-10-11 17:10:00\"},{\"user_id\":\"4903407816971\",\"date\":\"2026-10-11 18:00:00\"}]'),
(3,'post',15999331102094,'[{\"user_id\":\"748617700973442\",\"date\":\"2026-10-12 20:00:00\"}]'),
(4,'post',3185550526910215,'[{\"user_id\":\"372154181591\",\"date\":\"2026-10-12 20:20:00\"}]'),
(5,'post',842084841837436,'[{\"user_id\":\"473897691038\",\"date\":\"2026-10-13 11:00:00\"},{\"user_id\":\"4903407816971\",\"date\":\"2026-10-13 12:40:00\"},{\"user_id\":\"73558098367309\",\"date\":\"2026-10-13 15:15:00\"},{\"user_id\":\"372154181591\",\"date\":\"2026-10-13 18:30:00\"}]'),
(6,'post',85449930663125,'[{\"user_id\":\"748617700973442\",\"date\":\"2026-10-13 18:10:00\"},{\"user_id\":\"4903407816971\",\"date\":\"2026-10-13 19:02:00\"}]'),
(7,'post',169501544376,'[{\"user_id\":\"473897691038\",\"date\":\"2026-10-13 21:05:00\"},{\"user_id\":\"748617700973442\",\"date\":\"2026-10-13 21:40:00\"}]'),
(8,'post',7158062911982,'[{\"user_id\":\"473897691038\",\"date\":\"2026-10-12 08:30:00\"},{\"user_id\":\"4903407816971\",\"date\":\"2026-10-12 09:10:00\"}]');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `msgid` varchar(60) NOT NULL,
  `sender` bigint(20) NOT NULL,
  `receiver` bigint(20) NOT NULL,
  `message` text DEFAULT NULL,
  `file` varchar(500) DEFAULT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_sender` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_receiver` tinyint(1) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `msgid` (`msgid`),
  KEY `sender` (`sender`),
  KEY `receiver` (`receiver`),
  KEY `seen` (`seen`),
  KEY `deleted_sender` (`deleted_sender`),
  KEY `deleted_receiver` (`deleted_receiver`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `messages` VALUES
(1,'ABBteTaHWpZur83bQ6tnNfKgDPLs7TL5nNYPWQJJ',372154181591,748617700973442,'Paldies vēlreiz par palīdzību ar sesijām!','',1,0,0,'2026-10-12 20:25:00'),
(2,'mdd2UKCmNQWY7b7DaraPfqKeC3Fh6Vegv5pnWZ8K',473897691038,748617700973442,'Sveiks! Vai vari rīt atsūtīt savu ER diagrammu?','',1,0,0,'2026-10-13 18:02:00'),
(3,'mdd2UKCmNQWY7b7DaraPfqKeC3Fh6Vegv5pnWZ8K',748617700973442,473897691038,'Jā, protams. Vakarā pabeigšu un atsūtīšu.','',1,0,0,'2026-10-13 18:05:00'),
(4,'mdd2UKCmNQWY7b7DaraPfqKeC3Fh6Vegv5pnWZ8K',473897691038,748617700973442,'Paldies! Tad varēšu salīdzināt ar savu.','',1,0,0,'2026-10-13 18:06:00'),
(5,'mdd2UKCmNQWY7b7DaraPfqKeC3Fh6Vegv5pnWZ8K',748617700973442,473897691038,'Te būs, paskaties!','uploads/748617700973442/32ba228bf8d717f.jpg',0,0,0,'2026-10-13 21:30:00'),
(6,'mCAgG3eadV6BJFADwcvUaV7gTqqpR8kxU5pvhRPZ',4903407816971,748617700973442,'Vai rītdienas konsultācija notiks 214. kabinetā?','',0,0,0,'2026-10-13 21:52:00');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `notification_seen`;
CREATE TABLE `notification_seen` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) NOT NULL,
  `notification_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_notification` (`userid`,`notification_id`),
  KEY `userid` (`userid`),
  KEY `notification_id` (`notification_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `notification_seen` VALUES
(7,372154181591,7),
(5,473897691038,5),
(4,4903407816971,4),
(6,73558098367309,6),
(1,748617700973442,1),
(2,748617700973442,2),
(3,748617700973442,3),
(8,748617700973442,8),
(9,748617700973442,9);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `activity` varchar(30) NOT NULL,
  `content_id` bigint(20) NOT NULL,
  `content_owner` bigint(20) NOT NULL,
  `content_type` varchar(30) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `content_id` (`content_id`),
  KEY `owner_date_idx` (`content_owner`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `notifications` VALUES
(1,473897691038,'like',719185617483662,748617700973442,'post','2026-10-09 19:00:00'),
(2,4903407816971,'like',719185617483662,748617700973442,'post','2026-10-09 19:30:00'),
(3,73558098367309,'like',719185617483662,748617700973442,'post','2026-10-09 21:00:00'),
(4,473897691038,'tag',7105721622037,4903407816971,'post','2026-10-10 09:15:00'),
(5,4903407816971,'comment',7105721622037,473897691038,'comment','2026-10-10 09:40:00'),
(6,748617700973442,'comment',2724023069316,73558098367309,'comment','2026-10-11 16:30:00'),
(7,748617700973442,'comment',15999331102094,372154181591,'comment','2026-10-12 20:05:00'),
(8,372154181591,'tag',70533670360002,748617700973442,'post','2026-10-12 20:21:00'),
(9,372154181591,'like',3185550526910215,748617700973442,'comment','2026-10-12 20:20:00'),
(10,473897691038,'comment',842084841837436,748617700973442,'comment','2026-10-13 11:05:00'),
(11,473897691038,'like',842084841837436,748617700973442,'post','2026-10-13 11:00:00'),
(12,4903407816971,'like',842084841837436,748617700973442,'post','2026-10-13 12:40:00'),
(13,1897884319521,'friend_request',1897884319521,748617700973442,'profile','2026-10-13 12:30:00'),
(14,73558098367309,'like',842084841837436,748617700973442,'post','2026-10-13 15:15:00'),
(15,372154181591,'like',842084841837436,748617700973442,'post','2026-10-13 18:30:00'),
(16,748617700973442,'like',85449930663125,473897691038,'post','2026-10-13 18:10:00'),
(17,473897691038,'like',169501544376,4903407816971,'post','2026-10-13 21:05:00'),
(18,473897691038,'comment',169501544376,4903407816971,'comment','2026-10-13 21:10:00');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` bigint(19) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(19) NOT NULL,
  `user_id` bigint(19) NOT NULL,
  `owner` bigint(19) NOT NULL DEFAULT 0,
  `post` text NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `comments` int(11) NOT NULL DEFAULT 0,
  `likes` int(11) NOT NULL DEFAULT 0,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `has_image` tinyint(1) NOT NULL DEFAULT 0,
  `is_profile_image` tinyint(1) NOT NULL,
  `is_cover_image` tinyint(1) NOT NULL,
  `parent` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `comments` (`comments`),
  KEY `likes` (`likes`),
  KEY `date` (`date`),
  KEY `has_image` (`has_image`),
  KEY `is_profile_image` (`is_profile_image`),
  KEY `is_cover_image` (`is_cover_image`),
  KEY `owner` (`owner`),
  FULLTEXT KEY `post` (`post`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `posts` VALUES
(1,33391535090799345,748617700973442,0,'','uploads/748617700973442/e946f0d4cd22fa7.jpg',0,0,'2026-10-05 15:20:00',1,1,0,0),
(2,67822844686224760,473897691038,0,'','uploads/473897691038/3bf57e5e78d0d64.jpg',0,0,'2026-10-05 17:05:00',1,0,1,0),
(3,719185617483662,748617700973442,0,'Beidzot pabeidzu Osmos datubāzes shēmu. Tagad lietotājiem būs draugi, grupas un privātās ziņas! #php #mysql','uploads/748617700973442/58b54f1afb16cfb.jpg',0,3,'2026-10-09 15:20:00',1,0,0,0),
(4,7105721622037,473897691038,0,'Kurš rīt nāks uz konsultāciju par #mysql vaicājumiem? @janis_k, tu taču solīji paskaidrot JOIN.','',2,0,'2026-10-10 06:15:00',0,0,0,0),
(5,12920526320847139,4903407816971,0,'Būšu! Paņemšu līdzi piezīmes no pagājušās nedēļas.','',0,0,'2026-10-10 06:40:00',0,0,0,7105721622037),
(6,44472717830475838,473897691038,0,'Super, tad tiekamies 214. kabinetā.','',0,0,'2026-10-10 07:02:00',0,0,0,7105721622037),
(7,2724023069316,73558098367309,0,'Skolas pārgājiens uz Siguldu bija lielisks! Paldies visiem, kas piedalījās.','uploads/73558098367309/11789a24f979e13.jpg',2,4,'2026-10-11 13:05:00',1,0,0,0),
(8,976728865667484,748617700973442,0,'Skaisti kadri! Nākamreiz jāiet uz Turaidu.','',0,0,'2026-10-11 13:30:00',0,0,0,2724023069316),
(9,67903953045542637,372154181591,0,'Piekrītu, rudenī tur ir visskaistāk.','',0,0,'2026-10-11 14:12:00',0,0,0,2724023069316),
(10,9610383361689,73558098367309,3613257718561,'Sestdien basketbola turnīrs sporta zālē. Pieteikties līdz piektdienai! #sports','',0,0,'2026-10-11 06:00:00',0,0,0,0),
(11,7158062911982,748617700973442,464532042534,'Sveiki visi! Šajā grupā dalāmies ar materiāliem kvalifikācijas eksāmenam. #eksamens','',0,2,'2026-10-12 05:00:00',0,0,0,0),
(12,725465338730,473897691038,464532042534,'Uzdevumi nākamajai nedēļai: ER diagramma, datu plūsmu diagramma un testēšanas tabulas.','',1,0,'2026-10-12 11:20:00',0,0,0,0),
(13,9031377288952,4903407816971,464532042534,'Paldies, Anna!','',0,0,'2026-10-12 11:45:00',0,0,0,725465338730),
(14,15999331102094,372154181591,0,'Vai kāds var palīdzēt ar #php sesijām? Pēc pieslēgšanās lietotājs uzreiz tiek izmests ārā.','',2,1,'2026-10-12 16:48:00',0,0,0,0),
(15,3185550526910215,748617700973442,0,'Pārbaudi, vai session_start() tiek izsaukts pirms jebkādas HTML izvades. Man bija tieši tāda pati kļūda.','',0,1,'2026-10-12 17:05:00',0,0,0,15999331102094),
(16,70533670360002,372154181591,0,'Strādā! Paldies, @gustavs','',0,0,'2026-10-12 17:21:00',0,0,0,15999331102094),
(17,842084841837436,748617700973442,0,'Šodien pievienoju draudzības pieprasījumus un privātuma iestatījumus. Kods ir bildē. #php','uploads/748617700973442/3cfb253a9143335.jpg',2,4,'2026-10-13 07:30:00',1,0,0,0),
(18,6837748734873160,473897691038,0,'Izskatās ļoti labi! Vai privātumu var mainīt jebkurā brīdī?','',0,0,'2026-10-13 08:05:00',0,0,0,842084841837436),
(19,557252205548,748617700973442,0,'Jā, iestatījumos sadaļā Privacy.','',0,0,'2026-10-13 08:20:00',0,0,0,842084841837436),
(20,553687694969097,1897884319521,0,'Rīt 10:00 kontroldarbs programmēšanā. Atkārtojam regulārās izteiksmes! #regex #php','',0,0,'2026-10-13 09:10:00',0,0,0,0),
(21,85449930663125,473897691038,0,'Noderīga regulārā izteiksme lietotājvārdiem: ^[a-z][a-z0-9_]{2,19}$ #regex','',0,2,'2026-10-13 14:45:00',0,0,0,0),
(22,169501544376,4903407816971,0,'Kurš vēl gatavojas eksāmenam? Varam sanākt bibliotēkā rīt pēc stundām. #eksamens','',1,2,'2026-10-13 17:55:00',0,0,0,0),
(23,99610540142092,473897691038,0,'Es nāku!','',0,0,'2026-10-13 18:10:00',0,0,0,169501544376);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` bigint(19) NOT NULL,
  `owner` bigint(19) NOT NULL DEFAULT 0,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` varchar(6) DEFAULT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'profile',
  `group_type` varchar(8) NOT NULL DEFAULT 'public',
  `privacy` varchar(10) NOT NULL DEFAULT 'public',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `blocked` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(100) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(1000) DEFAULT '',
  `cover_image` varchar(1000) NOT NULL DEFAULT '',
  `about` varchar(1024) DEFAULT NULL,
  `tag_name` varchar(100) NOT NULL,
  `online` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`),
  UNIQUE KEY `tag_name_unique` (`tag_name`),
  KEY `first_name` (`first_name`),
  KEY `last_name` (`last_name`),
  KEY `gender` (`gender`),
  KEY `date` (`date`),
  KEY `tag_name` (`tag_name`),
  KEY `type` (`type`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `users` VALUES
(1,748617700973442,0,'Gustavs','Siliņš','Male','profile','public','public',1,0,'$2y$10$ABRs2XrdKrOwYAygjM3fu.DDoGEOhcLpMPEQh4i2mmKHbLPntwCoK','2026-10-05 15:02:11','uploads/748617700973442/e946f0d4cd22fa7.jpg','uploads/748617700973442/898924364ef3657.jpg','Programmēšanas tehniķa audzēknis. Interesē tīmekļa izstrāde un datubāzes.','gustavs',1791920139),
(2,473897691038,0,'Anna','Bērziņa','Female','profile','public','public',0,0,'$2y$10$ABRs2XrdKrOwYAygjM3fu.DDoGEOhcLpMPEQh4i2mmKHbLPntwCoK','2026-10-05 16:40:03','uploads/473897691038/7b9af0e1662501e.jpg','uploads/473897691038/3bf57e5e78d0d64.jpg','Mīlu datubāzes, labu kafiju un sakārtotu kodu.','anna_b',1791920079),
(3,4903407816971,0,'Jānis','Kalniņš','Male','profile','public','public',0,0,'$2y$10$ABRs2XrdKrOwYAygjM3fu.DDoGEOhcLpMPEQh4i2mmKHbLPntwCoK','2026-10-06 05:15:42','uploads/4903407816971/4520bf8a879fc97.jpg','','','janis_k',1791909324),
(4,73558098367309,0,'Līga','Ozola','Female','profile','public','public',0,0,'$2y$10$ABRs2XrdKrOwYAygjM3fu.DDoGEOhcLpMPEQh4i2mmKHbLPntwCoK','2026-10-06 09:31:09','uploads/73558098367309/624cd8b82687f85.jpg','','Skolas sporta komandas kapteine.','liga_o',1791919524),
(5,372154181591,0,'Mārtiņš','Liepiņš','Male','profile','public','public',0,0,'$2y$10$ABRs2XrdKrOwYAygjM3fu.DDoGEOhcLpMPEQh4i2mmKHbLPntwCoK','2026-10-07 13:50:27','uploads/372154181591/fc9c490727e985d.jpg','','','martins_l',1791920139),
(6,1897884319521,0,'Elīza','Krūmiņa','Female','profile','public','friends',0,0,'$2y$10$ABRs2XrdKrOwYAygjM3fu.DDoGEOhcLpMPEQh4i2mmKHbLPntwCoK','2026-10-07 18:05:55','','','Šeit raksta tikai draugiem.','eliza_k',1791912924),
(7,898904586618,0,'Roberts','Zariņš','Male','profile','public','public',0,1,'$2y$10$ABRs2XrdKrOwYAygjM3fu.DDoGEOhcLpMPEQh4i2mmKHbLPntwCoK','2026-10-08 07:12:30','','','','roberts_z',1791574524),
(8,464532042534,748617700973442,'Programmēšana 9. grupa','','','group','private','public',0,0,'','2026-10-08 16:00:00','','uploads/464532042534/27d353dafef1cd2.jpg','Materiāli, uzdevumi un jautājumi par programmēšanu un kvalifikācijas eksāmenu.','programmesana9',0),
(9,3613257718561,73558098367309,'Skolas sports','','','group','public','public',0,0,'','2026-10-09 04:30:00','','','Sporta pasākumi, treniņi un turnīri.','skolassports',0);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
