-- MySQL dump 10.8
--
-- Host: localhost    Database: xmldir
-- ------------------------------------------------------
-- Server version	4.1.7-Max

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` varchar(36) NOT NULL default '0',
  `display_name` varchar(100) NOT NULL default '',
  `member_of` varchar(100) NOT NULL default '',
  `lname` varchar(100) NOT NULL default '',
  `fname` varchar(100) NOT NULL default '',
  `company` varchar(100) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  `office_phone` varchar(100) default '',
  `home_phone` varchar(100) default '',
  `cell_phone` varchar(100) default '',
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `owner` varchar(100) NOT NULL default '',
  `custom_phone` varchar(100) default '',
  `custom_number` varchar(100) default '',
  `sup_prefix_office` tinyint(4) default '0',
  `sup_prefix_home` tinyint(4) default '0',
  `sup_prefix_cell` tinyint(4) default '0',
  `sup_prefix_other` tinyint(4) default '0',
  `sup_prefix_custom` tinyint(4) default '0',
  `other_phone` varchar(100) default ''
) ENGINE=MyISAM;

--
-- Dumping data for table `contacts`
--


LOCK TABLES `contacts` WRITE;
INSERT INTO `contacts` VALUES ('5b50b5d1-254fd-fcd38-9010-42ea620509','Simon, Barbara - XYZ Company','23f32c21-a1bf7-3e152-a7b8-42ea55c0b1','Simon','Barbara','XYZ Company','President','215-555-5555','','215-555-5556','2005-12-30 11:21:46','0','Office in NJ','555-555-5557',0,1,0,0,1,''),('cba70325-c1749-b2134-a307-42ea62ab68','Smith, Abby - XYZ Company','23f32c21-a1bf7-3e152-a7b8-42ea55c0b1','Smith','Abby','XYZ Company','Treasurer','215-555-5555','','215-555-5557','2005-07-29 13:08:16','0','Create Custom','',0,0,0,0,0,''),('cb3648ac-06209-3fb31-046f-42ea634959','Fire','6fbd03fe-207ba-39338-3072-42ea5ae8bb','','','Fire','','215-555-5555','','','2005-07-29 13:14:24','0','Create Custom','',0,0,0,0,0,''),('d978bc85-3513c-fd6d5-e04c-43b3246f85','Dominos','6d89c587-2a112-f8841-3765-42ea5bd0da','','','Dominos','','6102531600','','','2005-12-28 18:49:18','0','Create Custom','',0,0,0,0,0,'');
UNLOCK TABLES;

--
-- Table structure for table `global_pref`
--

DROP TABLE IF EXISTS `global_pref`;
CREATE TABLE `global_pref` (
  `preference` varchar(11) NOT NULL default '',
  `ph_sec` char(3) NOT NULL default '',
  `prefix` varchar(100) NOT NULL default '',
  `ph_prfx` varchar(100) NOT NULL default '',
  `memo_ob` varchar(20) NOT NULL default '',
  `ob_sec` char(3) NOT NULL default ''
) ENGINE=MyISAM;

--
-- Dumping data for table `global_pref`
--


LOCK TABLES `global_pref` WRITE;
INSERT INTO `global_pref` VALUES ('primary','Yes','9','Yes','Date','Yes');
UNLOCK TABLES;

--
-- Table structure for table `memos`
--

DROP TABLE IF EXISTS `memos`;
CREATE TABLE `memos` (
  `id` varchar(100) NOT NULL default '',
  `msg` blob NOT NULL,
  `date` int(11) NOT NULL default '0',
  `access` varchar(100) NOT NULL default '',
  `sender` varchar(100) NOT NULL default '',
  `receiver` varchar(100) NOT NULL default '',
  `title` varchar(100) NOT NULL default ''
) ENGINE=MyISAM;

--
-- Dumping data for table `memos`
--


LOCK TABLES `memos` WRITE;
INSERT INTO `memos` VALUES ('2f4e6029-fc80b-27e51-0c2e-42d2ea2ffd','Be careful with this system as we\'re using it for both development and internal XML services. -CM',1121118895,'Public','corey','','System Usage');
UNLOCK TABLES;

--
-- Table structure for table `object`
--

DROP TABLE IF EXISTS `object`;
CREATE TABLE `object` (
  `id` varchar(36) NOT NULL default '0',
  `type` varchar(100) NOT NULL default '',
  `member_of` varchar(100) default '',
  `title` varchar(100) NOT NULL default '',
  `href` varchar(255) default '',
  `style` varchar(100) default '',
  `access` varchar(100) default ''
) ENGINE=MyISAM;

--
-- Dumping data for table `object`
--


LOCK TABLES `object` WRITE;
INSERT INTO `object` VALUES ('4599213d-667ba-b125c-62d9-42ea5b2c7c','Category','87d90b57-f65af-93bec-9c76-42ea59531c','Mid-Priced','','Seperate','Public'),('3b89fe22-a8a93-eda7d-f830-42ea5c3cca','Category','87d90b57-f65af-93bec-9c76-42ea59531c','Upscale','','Seperate','Public'),('dc919e54-606b4-649dc-7851-43b55f4884','Category','b2e12023-8aa36-037a0-40af-43b55ff4bd','East Coast','','Seperate','Public'),('0','Container',NULL,'Main',NULL,'','Public'),('88cb2ca6-38090-215e6-bf67-43b2cf465a','Container','','','','',''),('6147064f-3d686-14f96-0fed-43aaccc0a7','','','','','',''),('5a355dc1-380f8-e6ec6-305c-43aacce1a3','Category','29238bbe-df21a-f8900-e47e-42f22c49a6','Accounts','','Seperate','Public'),('8ef5254f-638f3-a62fd-68f2-42ea5b5c5b','Category','9bdbc1df-ef051-d9109-270a-42ea59f801','Misc.','','Seperate','Public'),('6d89c587-2a112-f8841-3765-42ea5bd0da','Category','87d90b57-f65af-93bec-9c76-42ea59531c','Delivery','','Seperate','Public'),('6cc609e9-c17a8-02067-d673-42ea5b7a3b','Category','9bdbc1df-ef051-d9109-270a-42ea59f801','Hardware','','Seperate','Public'),('c68f6166-68a72-a12cc-b025-42ea5bcc29','Category','9bdbc1df-ef051-d9109-270a-42ea59f801','Peripherals','','Seperate','Public'),('17b8b068-8bc40-a77cc-bc30-42ea5b3d38','Category','1','Government Accounts','','Seperate','Public'),('ec893b13-f36af-3401c-c82a-42ea5b9966','Category','1','Non - Profit Accounts','','Seperate','Public'),('597eb950-b2ed1-47c0f-f665-42ea5b843d','Category','9bdbc1df-ef051-d9109-270a-42ea59f801','Software','','Seperate','Public'),('e916dfb1-83e14-c5f60-37c1-42ea5bfaeb','Category','1','Small Business Accounts','','Seperate','Public'),('ceb1b5fe-9d8fb-105cd-a31f-42ea5bdb07','Category','1','Healthcare Accounts','','Seperate','Public'),('6fbd03fe-207ba-39338-3072-42ea5ae8bb','Category','0','Emergency','','Seperate','Public'),('87d90b57-f65af-93bec-9c76-42ea59531c','Container','0','Restaurants','','','Public'),('53d3ee0b-1f2b3-21ecd-ceb4-42ea595a97','Container','0','News Headlines','','','Public'),('23f32c21-a1bf7-3e152-a7b8-42ea55c0b1','Category','0','Employees','','Seperate','Public'),('29238bbe-df21a-f8900-e47e-42f22c49a6','Container','0','Customers','','','Public'),('9bdbc1df-ef051-d9109-270a-42ea59f801','Container','0','Vendors','','','Public'),('9df94f3d-34d41-16b36-944d-43aad1476a','Link','53d3ee0b-1f2b3-21ecd-ceb4-42ea595a97','WSJ','http://xmlsvc.csma.biz/xmlrssparse.php?feed=http://online.wsj.com/xml/rss/0,,3_7011,00.xml','','Public'),('710b8958-1e416-a6a7d-20c5-43b2c71269','Link','53d3ee0b-1f2b3-21ecd-ceb4-42ea595a97','CNet Headlines','http://xmlsvc.csma.biz/xmlrssparse.php?feed=http://news.com.com/2547-1_3-0-5.xml','','Public'),('b2e12023-8aa36-037a0-40af-43b55ff4bd','Container','29238bbe-df21a-f8900-e47e-42f22c49a6','Realty','','','Public'),('a87064fa-68d59-6e517-548c-43b55fd3a4','Category','b2e12023-8aa36-037a0-40af-43b55ff4bd','West Coast','','Seperate','Public');
UNLOCK TABLES;

--
-- Table structure for table `phone`
--

DROP TABLE IF EXISTS `phone`;
CREATE TABLE `phone` (
  `id` varchar(36) NOT NULL default '0',
  `MAC` varchar(100) NOT NULL default '',
  `date` varchar(100) NOT NULL default '',
  `number` varchar(100) NOT NULL default '',
  `away_msg` varchar(100) NOT NULL default '',
  `fname` varchar(100) NOT NULL default '',
  `lname` varchar(100) NOT NULL default '',
  `status` int(1) NOT NULL default '0',
  `access_lvl` varchar(100) default ''
) ENGINE=MyISAM;

--
-- Dumping data for table `phone`
--


LOCK TABLES `phone` WRITE;
INSERT INTO `phone` VALUES ('38ceaef5-376ed-098c2-4236-43b563060c','SEP00131A6FD5E5','','','','User','Sample',0,'Unrestricted');
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` varchar(36) NOT NULL default '',
  `username` varchar(100) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `account_type` varchar(100) NOT NULL default ''
) ENGINE=MyISAM;

--
-- Dumping data for table `users`
--


LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES ('0','admin','a74ad8dfacd4f985eb3977517615ce25','','Admin'),('639e8ade-13225-cad00-ddeb-43b55e6719','joe','8ff32489f92f33416694be8fdc2d4c22','joe1234@hostXYZ.com','User');
UNLOCK TABLES;

