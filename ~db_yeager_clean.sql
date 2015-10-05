-- MySQL dump 10.13  Distrib 5.1.41, for Win32 (ia32)
--
-- Host: localhost    Database: db_yeager_clean
-- ------------------------------------------------------
-- Server version	5.1.41

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
-- Table structure for table `yg_koala_sequencer`
--

DROP TABLE IF EXISTS `yg_koala_sequencer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_koala_sequencer` (
  `COUNTER` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_koala_sequencer`
--

LOCK TABLES `yg_koala_sequencer` WRITE;
/*!40000 ALTER TABLE `yg_koala_sequencer` DISABLE KEYS */;
INSERT INTO `yg_koala_sequencer` VALUES (1000);
/*!40000 ALTER TABLE `yg_koala_sequencer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_tags_lnk_cb`
--

DROP TABLE IF EXISTS `yg_tags_lnk_cb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_tags_lnk_cb` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `TAGID` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  `OVERSION` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OID` (`OID`,`TAGID`,`OVERSION`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_tags_lnk_cb`
--

LOCK TABLES `yg_tags_lnk_cb` WRITE;
/*!40000 ALTER TABLE `yg_tags_lnk_cb` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_tags_lnk_cb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_tags_lnk_mailings`
--

DROP TABLE IF EXISTS `yg_tags_lnk_mailings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_tags_lnk_mailings` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `TAGID` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  `OVERSION` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OID` (`OID`,`TAGID`,`OVERSION`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_tags_lnk_mailings`
--

LOCK TABLES `yg_tags_lnk_mailings` WRITE;
/*!40000 ALTER TABLE `yg_tags_lnk_mailings` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_tags_lnk_mailings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_tags_lnk_files`
--

DROP TABLE IF EXISTS `yg_tags_lnk_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_tags_lnk_files` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `TAGID` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  `OVERSION` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OID` (`OID`,`TAGID`,`OVERSION`),
  KEY `TAGID` (`TAGID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_tags_lnk_files`
--

LOCK TABLES `yg_tags_lnk_files` WRITE;
/*!40000 ALTER TABLE `yg_tags_lnk_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_tags_lnk_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_tags_lnk_pages`
--

DROP TABLE IF EXISTS `yg_tags_lnk_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_tags_lnk_pages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITEID` int(11) NOT NULL DEFAULT '0',
  `OID` int(11) NOT NULL DEFAULT '0',
  `TAGID` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  `OVERSION` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OID` (`OID`,`TAGID`,`OVERSION`,`SITEID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_tags_lnk_pages`
--

LOCK TABLES `yg_tags_lnk_pages` WRITE;
/*!40000 ALTER TABLE `yg_tags_lnk_pages` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_tags_lnk_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_tags_properties`
--

DROP TABLE IF EXISTS `yg_tags_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_tags_properties` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTID` int(11) NOT NULL DEFAULT '0',
  `NAME` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `OBJECTID` (`OBJECTID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_tags_properties`
--

LOCK TABLES `yg_tags_properties` WRITE;
/*!40000 ALTER TABLE `yg_tags_properties` DISABLE KEYS */;
INSERT INTO `yg_tags_properties` VALUES (1,1,'Tags');
/*!40000 ALTER TABLE `yg_tags_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_tags_tree`
--

DROP TABLE IF EXISTS `yg_tags_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_tags_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LFT` int(11) NOT NULL DEFAULT '0',
  `RGT` int(11) NOT NULL DEFAULT '0',
  `VERSIONPUBLISHED` int(11) NOT NULL DEFAULT '0',
  `MOVED` int(11) NOT NULL DEFAULT '0',
  `TITLE` text,
  `LEVEL` int(11) NOT NULL DEFAULT '0',
  `PARENT` int(11) NOT NULL DEFAULT '0',
  `PNAME` text,
  PRIMARY KEY (`ID`),
  KEY `LFT` (`LFT`,`RGT`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_tags_tree`
--

LOCK TABLES `yg_tags_tree` WRITE;
/*!40000 ALTER TABLE `yg_tags_tree` DISABLE KEYS */;
INSERT INTO `yg_tags_tree` VALUES (1,1,2,0,0,'',1,0,'');
/*!40000 ALTER TABLE `yg_tags_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_contentblocks_lnk_entrymasks`
--

DROP TABLE IF EXISTS `yg_contentblocks_lnk_entrymasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_contentblocks_lnk_entrymasks` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ENTRYMASK` int(11) NOT NULL DEFAULT '0',
  `CBID` int(11) NOT NULL DEFAULT '0',
  `CBVERSION` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `CBID` (`CBID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_contentblocks_lnk_entrymasks`
--

LOCK TABLES `yg_contentblocks_lnk_entrymasks` WRITE;
/*!40000 ALTER TABLE `yg_contentblocks_lnk_entrymasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_contentblocks_lnk_entrymasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_contentblocks_lnk_entrymasks_c`
--

DROP TABLE IF EXISTS `yg_contentblocks_lnk_entrymasks_c`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_contentblocks_lnk_entrymasks_c` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FORMFIELD` int(11) NOT NULL,
  `ENTRYMASKFORMFIELD` int(11) NOT NULL,
  `LNK` int(11) NOT NULL,
  `VALUE01` text,
  `VALUE02` text,
  `VALUE03` text,
  `VALUE04` text,
  `VALUE05` text,
  `VALUE06` text,
  `VALUE07` text,
  `VALUE08` text,
  PRIMARY KEY (`ID`),
  KEY `LNK` (`LNK`),
  FULLTEXT KEY `FTEXT` (`VALUE01`,`VALUE02`,`VALUE03`,`VALUE04`,`VALUE05`,`VALUE06`,`VALUE07`,`VALUE08`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_contentblocks_lnk_entrymasks_c`
--

LOCK TABLES `yg_contentblocks_lnk_entrymasks_c` WRITE;
/*!40000 ALTER TABLE `yg_contentblocks_lnk_entrymasks_c` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_contentblocks_lnk_entrymasks_c` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_contentblocks_lnk_files`
--

DROP TABLE IF EXISTS `yg_contentblocks_lnk_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_contentblocks_lnk_files` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CBID` int(11) NOT NULL DEFAULT '0',
  `CBVERSION` int(11) NOT NULL DEFAULT '0',
  `COVID` int(11) NOT NULL DEFAULT '0',
  `MID` int(11) NOT NULL DEFAULT '0',
  `ACTIVE` int(11) NOT NULL DEFAULT '0',
  `DELETED` int(11) NOT NULL DEFAULT '0',
  `ALIAS` varchar(255) NOT NULL DEFAULT '',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  `TEMPLATECONTENTAREA` varchar(85) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `CBID` (`CBID`,`CBVERSION`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_contentblocks_lnk_files`
--

LOCK TABLES `yg_contentblocks_lnk_files` WRITE;
/*!40000 ALTER TABLE `yg_contentblocks_lnk_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_contentblocks_lnk_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_contentblocks_permissions`
--

DROP TABLE IF EXISTS `yg_contentblocks_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_contentblocks_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OID` (`OID`,`USERGROUPID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_contentblocks_permissions`
--

LOCK TABLES `yg_contentblocks_permissions` WRITE;
/*!40000 ALTER TABLE `yg_contentblocks_permissions` DISABLE KEYS */;
INSERT INTO `yg_contentblocks_permissions` VALUES (1,1,1,1,1,1,1,1,1,1,0),(2,1,2,1,0,0,0,0,0,0,0),(3,2,1,1,1,1,1,1,1,1,0),(4,2,2,1,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `yg_contentblocks_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_contentblocks_properties`
--

DROP TABLE IF EXISTS `yg_contentblocks_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_contentblocks_properties` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTID` int(11) NOT NULL DEFAULT '0',
  `FOLDER` int(11) NOT NULL DEFAULT '0',
  `TEXT` text,
  `TEXTPREVIEW` text,
  `VERSION` int(11) NOT NULL DEFAULT '0',
  `APPROVED` smallint(6) NOT NULL DEFAULT '0',
  `CREATEDBY` int(11) NOT NULL DEFAULT '0',
  `CHANGEDBY` int(11) NOT NULL DEFAULT '0',
  `HASCHANGED` int(11) NOT NULL DEFAULT '0',
  `LOCKED` int(11) NOT NULL DEFAULT '0',
  `LOCKUID` text NOT NULL,
  `TOKEN` text NOT NULL,
  `DELETED` int(11) NOT NULL DEFAULT '0',
  `COMMENTSTATUS` int(11) NOT NULL DEFAULT '1',
  `COMMENTSTATUS_AUTO` int(11) NOT NULL DEFAULT '1',
  `CREATEDTS` int(11) NOT NULL DEFAULT '0',
  `CHANGEDTS` int(11) NOT NULL DEFAULT '0',
  `EMBEDDED` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OBJECTID` (`OBJECTID`),
  KEY `VERSION` (`VERSION`),
  KEY `OBJECTID_2` (`OBJECTID`,`VERSION`),
  FULLTEXT KEY `NAME` (`TEXT`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_contentblocks_properties`
--

LOCK TABLES `yg_contentblocks_properties` WRITE;
/*!40000 ALTER TABLE `yg_contentblocks_properties` DISABLE KEYS */;
INSERT INTO `yg_contentblocks_properties` VALUES (1,1,1,NULL,NULL,1,1,1,1,0,0,'','',0,0,1,0,0,1),(2,2,1,'',NULL,0,0,1,1,1,0,'','',0,1,1,1305291458,1305291483,1);
/*!40000 ALTER TABLE `yg_contentblocks_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_contentblocks_props`
--

DROP TABLE IF EXISTS `yg_contentblocks_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_contentblocks_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `VISIBLE` int(11) NOT NULL DEFAULT '1',
  `READONLY` int(11) NOT NULL DEFAULT '0',
  `TYPE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_contentblocks_props`
--

LOCK TABLES `yg_contentblocks_props` WRITE;
/*!40000 ALTER TABLE `yg_contentblocks_props` DISABLE KEYS */;
INSERT INTO `yg_contentblocks_props` VALUES (1,'TXT_NAME','NAME',0,1,'TEXT',1),(2,'TXT_DESCRIPTION','DESCRIPTION',1,1,'TEXTAREA',2);
/*!40000 ALTER TABLE `yg_contentblocks_props` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_contentblocks_propslv`
--

DROP TABLE IF EXISTS `yg_contentblocks_propslv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_contentblocks_propslv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `VALUE` varchar(50) NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `LISTORDER` (`LISTORDER`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_contentblocks_propslv`
--

LOCK TABLES `yg_contentblocks_propslv` WRITE;
/*!40000 ALTER TABLE `yg_contentblocks_propslv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_contentblocks_propslv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_contentblocks_propsv`
--

DROP TABLE IF EXISTS `yg_contentblocks_propsv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_contentblocks_propsv` (
  `OID` int(11) NOT NULL DEFAULT '0',
  `NAME` text,
  `DESCRIPTION` text,
  PRIMARY KEY (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_contentblocks_propsv`
--

LOCK TABLES `yg_contentblocks_propsv` WRITE;
/*!40000 ALTER TABLE `yg_contentblocks_propsv` DISABLE KEYS */;
INSERT INTO `yg_contentblocks_propsv` VALUES (1,'Contentblocks',NULL),(2,'__BLIND__',NULL);
/*!40000 ALTER TABLE `yg_contentblocks_propsv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_contentblocks_tree`
--

DROP TABLE IF EXISTS `yg_contentblocks_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_contentblocks_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LFT` int(11) NOT NULL DEFAULT '0',
  `RGT` int(11) NOT NULL DEFAULT '0',
  `VERSIONPUBLISHED` int(11) NOT NULL DEFAULT '0',
  `MOVED` int(11) NOT NULL DEFAULT '0',
  `TITLE` text,
  `LEVEL` int(11) NOT NULL DEFAULT '0',
  `PARENT` int(11) NOT NULL DEFAULT '0',
  `PNAME` text,
  PRIMARY KEY (`ID`),
  KEY `LFT` (`LFT`,`RGT`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_contentblocks_tree`
--

LOCK TABLES `yg_contentblocks_tree` WRITE;
/*!40000 ALTER TABLE `yg_contentblocks_tree` DISABLE KEYS */;
INSERT INTO `yg_contentblocks_tree` VALUES (1,1,4,999999,0,'',1,0,'Contentblocks'),(2,2,3,0,0,'',2,1,'__BLIND__');
/*!40000 ALTER TABLE `yg_contentblocks_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_entrymasks_lnk_formfields`
--

DROP TABLE IF EXISTS `yg_entrymasks_lnk_formfields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_entrymasks_lnk_formfields` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FORMFIELD` int(11) NOT NULL,
  `ENTRYMASK` int(11) NOT NULL,
  `ORDER` int(11) NOT NULL DEFAULT '9999',
  `NAME` text NOT NULL,
  `IDENTIFIER` varchar(50) NOT NULL DEFAULT '',
  `PRESET` text NOT NULL,
  `WIDTH` text NOT NULL,
  `MAXLENGTH` text NOT NULL,
  `CONFIG` text NOT NULL,
  `CUSTOM` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ENTRYMASK` (`ENTRYMASK`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_entrymasks_lnk_formfields`
--

LOCK TABLES `yg_entrymasks_lnk_formfields` WRITE;
/*!40000 ALTER TABLE `yg_entrymasks_lnk_formfields` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_entrymasks_lnk_formfields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_entrymasks_lnk_formfields_lv`
--

DROP TABLE IF EXISTS `yg_entrymasks_lnk_formfields_lv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_entrymasks_lnk_formfields_lv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LID` int(11) NOT NULL,
  `VALUE` varchar(50) NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `LISTORDER` (`LISTORDER`,`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_entrymasks_lnk_formfields_lv`
--

LOCK TABLES `yg_entrymasks_lnk_formfields_lv` WRITE;
/*!40000 ALTER TABLE `yg_entrymasks_lnk_formfields_lv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_entrymasks_lnk_formfields_lv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_entrymasks_permissions`
--

DROP TABLE IF EXISTS `yg_entrymasks_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_entrymasks_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_entrymasks_permissions`
--

LOCK TABLES `yg_entrymasks_permissions` WRITE;
/*!40000 ALTER TABLE `yg_entrymasks_permissions` DISABLE KEYS */;
INSERT INTO `yg_entrymasks_permissions` VALUES (1,1,1,1,1,1,1,1,0,0,0),(2,1,2,1,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `yg_entrymasks_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_entrymasks_properties`
--

DROP TABLE IF EXISTS `yg_entrymasks_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_entrymasks_properties` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTID` int(11) NOT NULL DEFAULT '0',
  `FOLDER` int(11) NOT NULL DEFAULT '0',
  `TYPE` int(11) NOT NULL DEFAULT '0',
  `CODE` varchar(50) NOT NULL DEFAULT 'NONE',
  `NAME` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `OBJECTID` (`OBJECTID`),
  KEY `NAME` (`NAME`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_entrymasks_properties`
--

LOCK TABLES `yg_entrymasks_properties` WRITE;
/*!40000 ALTER TABLE `yg_entrymasks_properties` DISABLE KEYS */;
INSERT INTO `yg_entrymasks_properties` VALUES (1,1,1,0,'NONE','Entrymasks');
/*!40000 ALTER TABLE `yg_entrymasks_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_entrymasks_tree`
--

DROP TABLE IF EXISTS `yg_entrymasks_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_entrymasks_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LFT` int(11) NOT NULL DEFAULT '0',
  `RGT` int(11) NOT NULL DEFAULT '0',
  `VERSIONPUBLISHED` int(11) NOT NULL DEFAULT '0',
  `MOVED` int(11) NOT NULL DEFAULT '0',
  `TITLE` text,
  `LEVEL` int(11) NOT NULL DEFAULT '0',
  `PARENT` int(11) NOT NULL DEFAULT '0',
  `PNAME` text,
  PRIMARY KEY (`ID`),
  KEY `LFT` (`LFT`,`RGT`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_entrymasks_tree`
--

LOCK TABLES `yg_entrymasks_tree` WRITE;
/*!40000 ALTER TABLE `yg_entrymasks_tree` DISABLE KEYS */;
INSERT INTO `yg_entrymasks_tree` VALUES (1,1,2,0,0,'',1,0,'Formfields');
/*!40000 ALTER TABLE `yg_entrymasks_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_comments`
--

DROP TABLE IF EXISTS `yg_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_comments` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `COMMENT` text NOT NULL,
  `PARENT` int(11) NOT NULL,
  `APPROVED` int(11) NOT NULL DEFAULT '0',
  `SPAM` int(11) NOT NULL DEFAULT '0',
  `USERID` int(11) NOT NULL,
  `USERNAME` text NOT NULL,
  `USEREMAIL` text NOT NULL,
  `CREATEDTS` int(11) NOT NULL,
  `CHANGEDTS` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_comments`
--

LOCK TABLES `yg_comments` WRITE;
/*!40000 ALTER TABLE `yg_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_comments_lnk_cb`
--

DROP TABLE IF EXISTS `yg_comments_lnk_cb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_comments_lnk_cb` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `COMMENTID` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OID` (`OID`,`COMMENTID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_comments_lnk_cb`
--

LOCK TABLES `yg_comments_lnk_cb` WRITE;
/*!40000 ALTER TABLE `yg_comments_lnk_cb` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_comments_lnk_cb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_comments_lnk_files`
--

DROP TABLE IF EXISTS `yg_comments_lnk_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_comments_lnk_files` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `COMMENTID` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OID` (`OID`,`COMMENTID`),
  KEY `COMMENTID` (`COMMENTID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_comments_lnk_files`
--

LOCK TABLES `yg_comments_lnk_files` WRITE;
/*!40000 ALTER TABLE `yg_comments_lnk_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_comments_lnk_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_comments_lnk_pages_1`
--

DROP TABLE IF EXISTS `yg_comments_lnk_pages_1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_comments_lnk_pages_1` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `COMMENTID` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OID` (`OID`,`COMMENTID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_comments_lnk_pages_1`
--

LOCK TABLES `yg_comments_lnk_pages_1` WRITE;
/*!40000 ALTER TABLE `yg_comments_lnk_pages_1` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_comments_lnk_pages_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_comments_settings`
--

DROP TABLE IF EXISTS `yg_comments_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_comments_settings` (
  `ALLOW_HTML` smallint(1) NOT NULL DEFAULT '0',
  `FORCE_APPROVAL` smallint(1) NOT NULL DEFAULT '0',
  `FORCE_AUTHENTICATION` smallint(1) NOT NULL DEFAULT '0',
  `SE_RANK_DENIAL` smallint(1) NOT NULL DEFAULT '0',
  `BLACKLIST` text NOT NULL,
  `SPAMLIST` text NOT NULL,
  `AUTOCLOSE_AFTER_DAYS` int(11) NOT NULL DEFAULT '0',
  `MINIMUM_INTERVAL` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_comments_settings`
--

LOCK TABLES `yg_comments_settings` WRITE;
/*!40000 ALTER TABLE `yg_comments_settings` DISABLE KEYS */;
INSERT INTO `yg_comments_settings` VALUES (1,1,1,1,'','',0,0);
/*!40000 ALTER TABLE `yg_comments_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_cron`
--

DROP TABLE IF EXISTS `yg_cron`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_cron` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTTYPE` int(11) NOT NULL,
  `OBJECTID` int(11) NOT NULL,
  `ACTIONCODE` text COLLATE utf8_unicode_ci NOT NULL,
  `TIMESTAMP` bigint(20) NOT NULL,
  `EXPIRES` bigint(20) NOT NULL,
  `PARAMETERS` text COLLATE utf8_unicode_ci NOT NULL,
  `USERID` int(11) NOT NULL,
  `STATUS` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_cron`
--

LOCK TABLES `yg_cron` WRITE;
/*!40000 ALTER TABLE `yg_cron` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_cron` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_ext_defaultCblockListView_colistview_props`
--

DROP TABLE IF EXISTS `yg_ext_defaultCblockListView_colistview_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_ext_defaultCblockListView_colistview_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `VISIBLE` int(11) NOT NULL DEFAULT '1',
  `READONLY` int(11) NOT NULL DEFAULT '0',
  `TYPE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_ext_defaultCblockListView_colistview_props`
--

LOCK TABLES `yg_ext_defaultCblockListView_colistview_props` WRITE;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_colistview_props` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_colistview_props` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_ext_defaultCblockListView_colistview_propslv`
--

DROP TABLE IF EXISTS `yg_ext_defaultCblockListView_colistview_propslv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_ext_defaultCblockListView_colistview_propslv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `VALUE` varchar(50) NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `LISTORDER` (`LISTORDER`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_ext_defaultCblockListView_colistview_propslv`
--

LOCK TABLES `yg_ext_defaultCblockListView_colistview_propslv` WRITE;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_colistview_propslv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_colistview_propslv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_ext_defaultCblockListView_colistview_propsv`
--

DROP TABLE IF EXISTS `yg_ext_defaultCblockListView_colistview_propsv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_ext_defaultCblockListView_colistview_propsv` (
  `OID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_ext_defaultCblockListView_colistview_propsv`
--

LOCK TABLES `yg_ext_defaultCblockListView_colistview_propsv` WRITE;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_colistview_propsv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_colistview_propsv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_ext_defaultCblockListView_props`
--

DROP TABLE IF EXISTS `yg_ext_defaultCblockListView_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_ext_defaultCblockListView_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `VISIBLE` int(11) NOT NULL DEFAULT '1',
  `READONLY` int(11) NOT NULL DEFAULT '0',
  `TYPE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_ext_defaultCblockListView_props`
--

LOCK TABLES `yg_ext_defaultCblockListView_props` WRITE;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_props` DISABLE KEYS */;
INSERT INTO `yg_ext_defaultCblockListView_props` VALUES (1,'TXT_EX_DEFAULT_CONTENTBLOCK','DEFAULT_CO',1,0,'CBLOCK',1);
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_props` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_ext_defaultCblockListView_propslv`
--

DROP TABLE IF EXISTS `yg_ext_defaultCblockListView_propslv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_ext_defaultCblockListView_propslv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `VALUE` varchar(50) NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `LISTORDER` (`LISTORDER`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_ext_defaultCblockListView_propslv`
--

LOCK TABLES `yg_ext_defaultCblockListView_propslv` WRITE;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_propslv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_propslv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_ext_defaultCblockListView_propsv`
--

DROP TABLE IF EXISTS `yg_ext_defaultCblockListView_propsv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_ext_defaultCblockListView_propsv` (
  `OID` int(11) NOT NULL DEFAULT '0',
  `DEFAULT_CO` text,
  PRIMARY KEY (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_ext_defaultCblockListView_propsv`
--

LOCK TABLES `yg_ext_defaultCblockListView_propsv` WRITE;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_propsv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_ext_defaultCblockListView_propsv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_extensions`
--

DROP TABLE IF EXISTS `yg_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_extensions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODE` text COLLATE utf8_unicode_ci NOT NULL,
  `PATH` text COLLATE utf8_unicode_ci NOT NULL,
  `NAME` text COLLATE utf8_unicode_ci NOT NULL,
  `DEVELOPERNAME` text COLLATE utf8_unicode_ci NOT NULL,
  `VERSION` text COLLATE utf8_unicode_ci NOT NULL,
  `DESCRIPTION` text COLLATE utf8_unicode_ci NOT NULL,
  `URL` text COLLATE utf8_unicode_ci NOT NULL,
  `TYPE` int(11) NOT NULL,
  `INSTALLED` int(11) NOT NULL,
  `INTERNAL` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_extensions`
--

LOCK TABLES `yg_extensions` WRITE;
/*!40000 ALTER TABLE `yg_extensions` DISABLE KEYS */;
INSERT INTO `yg_extensions` VALUES (1,'defaultCblockListView','defaultCblockListView','Default cblockListView','Next Tuesday GmbH','1.0','Default cblockListView Extension','http://www.yeager.cm/',4,1,1);
/*!40000 ALTER TABLE `yg_extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_extensions_lnk_cblocks`
--

DROP TABLE IF EXISTS `yg_extensions_lnk_cblocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_extensions_lnk_cblocks` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `CBID` int(11) NOT NULL DEFAULT '0',
  `CBVERSION` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_extensions_lnk_cblocks`
--

LOCK TABLES `yg_extensions_lnk_cblocks` WRITE;
/*!40000 ALTER TABLE `yg_extensions_lnk_cblocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_extensions_lnk_cblocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_extensions_lnk_files`
--

DROP TABLE IF EXISTS `yg_extensions_lnk_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_extensions_lnk_files` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `FILEID` int(11) NOT NULL DEFAULT '0',
  `FILEVERSION` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_extensions_lnk_files`
--

LOCK TABLES `yg_extensions_lnk_files` WRITE;
/*!40000 ALTER TABLE `yg_extensions_lnk_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_extensions_lnk_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_extensions_lnk_mailings`
--

DROP TABLE IF EXISTS `yg_extensions_lnk_mailings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_extensions_lnk_mailings` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `MAILINGID` int(11) NOT NULL DEFAULT '0',
  `MAILINGVERSION` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_extensions_lnk_mailings`
--

LOCK TABLES `yg_extensions_lnk_mailings` WRITE;
/*!40000 ALTER TABLE `yg_extensions_lnk_mailings` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_extensions_lnk_mailings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_extensions_lnk_pages`
--

DROP TABLE IF EXISTS `yg_extensions_lnk_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_extensions_lnk_pages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `SITEID` int(11) NOT NULL DEFAULT '1',
  `PAGEID` int(11) NOT NULL DEFAULT '0',
  `PAGEVERSION` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_extensions_lnk_pages`
--

LOCK TABLES `yg_extensions_lnk_pages` WRITE;
/*!40000 ALTER TABLE `yg_extensions_lnk_pages` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_extensions_lnk_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_formfields`
--

DROP TABLE IF EXISTS `yg_formfields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_formfields` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TYPE` varchar(50) NOT NULL,
  `DESCRIPTION` varchar(120) NOT NULL,
  `NAME` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `TYPE` (`TYPE`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_formfields`
--

LOCK TABLES `yg_formfields` WRITE;
/*!40000 ALTER TABLE `yg_formfields` DISABLE KEYS */;
INSERT INTO `yg_formfields` VALUES (1,'TEXT','',''),(2,'TEXTAREA','',''),(3,'WYSIWYG','',''),(4,'CHECKBOX','',''),(5,'LINK','',''),(6,'FILE','',''),(7,'CO','',''),(8,'TAG','',''),(9,'LIST','',''),(10,'PASSWORD','',''),(11,'DATE','',''),(12,'DATETIME','',''),(13,'HEADLINE','',''),(14,'CUTLINE','',''),(15,'PAGE','',''),(16,'FILEFOLDER','','');
/*!40000 ALTER TABLE `yg_formfields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_history`
--

DROP TABLE IF EXISTS `yg_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_history` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SOURCEID` varchar(20) NOT NULL,
  `OID` int(11) NOT NULL DEFAULT '0',
  `DATETIME` int(11) DEFAULT NULL,
  `TEXT` text NOT NULL,
  `UID` int(11) NOT NULL DEFAULT '0',
  `TYPE` int(11) NOT NULL,
  `TARGETID` int(11) NOT NULL,
  `OLDVALUE` text NOT NULL,
  `NEWVALUE` text NOT NULL,
  `SITEID` int(11) NOT NULL,
  `FROM` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OID` (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_history`
--

LOCK TABLES `yg_history` WRITE;
/*!40000 ALTER TABLE `yg_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_jsqueue`
--

DROP TABLE IF EXISTS `yg_jsqueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_jsqueue` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SOURCEID` varchar(20) NOT NULL,
  `OID` int(11) NOT NULL DEFAULT '0',
  `DATETIME` int(11) DEFAULT NULL,
  `TEXT` text NOT NULL,
  `UID` int(11) NOT NULL DEFAULT '0',
  `TYPE` int(11) NOT NULL,
  `TARGETID` int(11) NOT NULL,
  `OLDVALUE` text NOT NULL,
  `NEWVALUE` text NOT NULL,
  `SITEID` int(11) NOT NULL,
  `FROM` int(11) DEFAULT '0',
  `VALUE1` text NOT NULL,
  `VALUE2` text NOT NULL,
  `VALUE3` text NOT NULL,
  `VALUE4` text NOT NULL,
  `VALUE5` text NOT NULL,
  `VALUE6` text NOT NULL,
  `VALUE7` text NOT NULL,
  `VALUE8` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `OID` (`OID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_jsqueue`
--

LOCK TABLES `yg_jsqueue` WRITE;
/*!40000 ALTER TABLE `yg_jsqueue` DISABLE KEYS */;
INSERT INTO `yg_jsqueue` VALUES (1,'0',0,1234567890,'',1,1,0,'','',1,0,'','','','','','','','');
/*!40000 ALTER TABLE `yg_jsqueue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_languages`
--

DROP TABLE IF EXISTS `yg_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_languages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text NOT NULL,
  `CODE` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_languages`
--

LOCK TABLES `yg_languages` WRITE;
/*!40000 ALTER TABLE `yg_languages` DISABLE KEYS */;
INSERT INTO `yg_languages` VALUES (1,'English','EN'),(2,'Deutsch','DE');
/*!40000 ALTER TABLE `yg_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_mailing_lnk_cb`
--

DROP TABLE IF EXISTS `yg_mailing_lnk_cb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_lnk_cb` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CBID` int(11) NOT NULL DEFAULT '0',
  `CBVERSION` int(11) NOT NULL DEFAULT '0',
  `CBPID` int(11) NOT NULL DEFAULT '0',
  `PID` int(11) NOT NULL DEFAULT '0',
  `PVERSION` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  `TEMPLATECONTENTAREA` varchar(85) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `CBID` (`CBID`,`CBVERSION`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_mailing_lnk_cb`
--

LOCK TABLES `yg_mailing_lnk_cb` WRITE;
/*!40000 ALTER TABLE `yg_mailing_lnk_cb` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_mailing_lnk_cb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_mailing_lnk_usergroups`
--

DROP TABLE IF EXISTS `yg_mailing_lnk_usergroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_lnk_usergroups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NID` int(11) NOT NULL,
  `NVERSION` int(11) NOT NULL,
  `RID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_mailing_lnk_usergroups`
--

LOCK TABLES `yg_mailing_lnk_usergroups` WRITE;
/*!40000 ALTER TABLE `yg_mailing_lnk_usergroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_mailing_lnk_usergroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_mailing_permissions`
--

DROP TABLE IF EXISTS `yg_mailing_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OID` (`OID`,`USERGROUPID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_mailing_permissions`
--

LOCK TABLES `yg_mailing_permissions` WRITE;
/*!40000 ALTER TABLE `yg_mailing_permissions` DISABLE KEYS */;
INSERT INTO `yg_mailing_permissions` VALUES (1,1,1,1,1,1,1,1,0,0,1),(2,1,2,1,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `yg_mailing_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_mailing_properties`
--

DROP TABLE IF EXISTS `yg_mailing_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_properties` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTID` int(11) NOT NULL DEFAULT '0',
  `VERSION` int(11) NOT NULL DEFAULT '0',
  `APPROVED` smallint(6) NOT NULL DEFAULT '0',
  `CREATEDBY` int(11) NOT NULL DEFAULT '0',
  `CHANGEDBY` int(11) NOT NULL DEFAULT '0',
  `HASCHANGED` int(11) NOT NULL DEFAULT '0',
  `TEMPLATEID` int(11) NOT NULL DEFAULT '0',
  `COMMENTSTATUS` int(11) NOT NULL DEFAULT '1',
  `COMMENTSTATUS_AUTO` int(11) NOT NULL DEFAULT '1',
  `NAVIGATION` int(11) NOT NULL DEFAULT '0',
  `ACTIVE` int(11) NOT NULL DEFAULT '0',
  `HIDDEN` int(11) NOT NULL DEFAULT '0',
  `LOCKED` int(11) NOT NULL DEFAULT '0',
  `LOCKUID` text NOT NULL,
  `TOKEN` text NOT NULL,
  `DELETED` int(11) NOT NULL DEFAULT '0',
  `CREATEDTS` int(11) NOT NULL DEFAULT '0',
  `CHANGEDTS` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OBJECTID` (`OBJECTID`,`VERSION`),
  KEY `VERSION` (`VERSION`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_mailing_properties`
--

LOCK TABLES `yg_mailing_properties` WRITE;
/*!40000 ALTER TABLE `yg_mailing_properties` DISABLE KEYS */;
INSERT INTO `yg_mailing_properties` VALUES (1,1,0,1,1,1,0,1,0,1,0,1,0,0,'1','',0,0,0);
/*!40000 ALTER TABLE `yg_mailing_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_mailing_props`
--

DROP TABLE IF EXISTS `yg_mailing_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `VISIBLE` int(11) NOT NULL DEFAULT '1',
  `READONLY` int(11) NOT NULL DEFAULT '0',
  `TYPE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;




--
-- Dumping data for table `yg_mailing_props`
--

LOCK TABLES `yg_mailing_props` WRITE;
/*!40000 ALTER TABLE `yg_mailing_props` DISABLE KEYS */;
INSERT INTO `yg_mailing_props` VALUES (1,'TXT_NAME','NAME',1,1,'TEXT',1),(2,'TXT_TITLE','TITLE',1,1,'TEXT',2),(3,'TXT_DESCRIPTION','DESCRIPTION',1,1,'TEXTAREA',3),(4,'FROM_EMAIL','FROM_EMAIL',1,1,'TEXT',4),(5,'FROM_NAME','FROM_NAME',1,1,'TEXT',5),(6,'SUBJECT','SUBJECT',1,1,'TEXT',6),(7,'FALLBACK_TEXT','FALLBACK_TEXT',1,1,'TEXTAREA',7),(8, 'FROM_REPLYTO', 'FROM_REPLYTO', 1, 1, 'TEXT', 8),(9, 'FROM_SENDER', 'FROM_SENDER', 1, 1, 'TEXT', 9),(10, 'ENCODING', 'ENCODING', 1, 1, 'TEXT', 10);
/*!40000 ALTER TABLE `yg_mailing_props` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `yg_mailing_propslv`
--

DROP TABLE IF EXISTS `yg_mailing_propslv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_propslv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `VALUE` varchar(50) NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `LISTORDER` (`LISTORDER`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_mailing_propslv`
--

LOCK TABLES `yg_mailing_propslv` WRITE;
/*!40000 ALTER TABLE `yg_mailing_propslv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_mailing_propslv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_mailing_propsv`
--

DROP TABLE IF EXISTS `yg_mailing_propsv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_propsv` (
  `OID` int(11) NOT NULL DEFAULT '0',
  `NAME` text,
  `TITLE` text,
  `DESCRIPTION` text,
  `FROM_EMAIL` text,
  `FROM_NAME` text,
  `SUBJECT` text,
  `FALLBACK_TEXT` text,
  PRIMARY KEY (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_mailing_propsv`
--

LOCK TABLES `yg_mailing_propsv` WRITE;
/*!40000 ALTER TABLE `yg_mailing_propsv` DISABLE KEYS */;
INSERT INTO `yg_mailing_propsv` VALUES (1,'Mailings',NULL,NULL,NULL,NULL,NULL,NULL);
ALTER TABLE  `yg_mailing_propsv` ADD  `FROM_REPLYTO` TEXT NOT NULL;
ALTER TABLE  `yg_mailing_propsv` ADD  `FROM_SENDER` TEXT NOT NULL;
ALTER TABLE  `yg_mailing_propsv` ADD  `ENCODING` TEXT NOT NULL;
/*!40000 ALTER TABLE `yg_mailing_propsv` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `yg_mailing_settings`
--

DROP TABLE IF EXISTS `yg_mailing_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_settings` (
  `ID` int(11) NOT NULL,
  `DEFAULTTEMPLATE` int(11) NOT NULL,
  `TEMPLATEROOT` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_mailing_settings`
--

LOCK TABLES `yg_mailing_settings` WRITE;
/*!40000 ALTER TABLE `yg_mailing_settings` DISABLE KEYS */;
INSERT INTO `yg_mailing_settings` VALUES (1,0,0);
/*!40000 ALTER TABLE `yg_mailing_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_mailing_status`
--

DROP TABLE IF EXISTS `yg_mailing_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_status` (
  `OID` int(11) NOT NULL AUTO_INCREMENT,
  `STATUS` text NOT NULL,
  `UID` int(11) NOT NULL,
  PRIMARY KEY (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_mailing_status`
--

LOCK TABLES `yg_mailing_status` WRITE;
/*!40000 ALTER TABLE `yg_mailing_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_mailing_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_mailing_tree`
--

DROP TABLE IF EXISTS `yg_mailing_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_mailing_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LFT` int(11) NOT NULL DEFAULT '0',
  `RGT` int(11) NOT NULL DEFAULT '0',
  `VERSIONPUBLISHED` int(11) NOT NULL DEFAULT '0',
  `MOVED` int(11) NOT NULL DEFAULT '0',
  `TITLE` text,
  `LEVEL` int(11) NOT NULL DEFAULT '0',
  `PARENT` int(11) NOT NULL DEFAULT '0',
  `PNAME` text,
  PRIMARY KEY (`ID`),
  KEY `LFT` (`LFT`,`RGT`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_mailing_tree`
--

LOCK TABLES `yg_mailing_tree` WRITE;
/*!40000 ALTER TABLE `yg_mailing_tree` DISABLE KEYS */;
INSERT INTO `yg_mailing_tree` VALUES (1,1,2,999999,0,'',1,0,'mailings');
/*!40000 ALTER TABLE `yg_mailing_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_files_permissions`
--

DROP TABLE IF EXISTS `yg_files_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_files_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OID` (`OID`,`USERGROUPID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_files_permissions`
--

LOCK TABLES `yg_files_permissions` WRITE;
/*!40000 ALTER TABLE `yg_files_permissions` DISABLE KEYS */;
INSERT INTO `yg_files_permissions` VALUES (1,1,1,1,1,1,1,1,1,1,0),(2,1,2,1,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `yg_files_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_files_properties`
--

DROP TABLE IF EXISTS `yg_files_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_files_properties` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTID` int(11) NOT NULL DEFAULT '0',
  `FOLDER` int(11) NOT NULL DEFAULT '0',
  `FILENAME` text NOT NULL,
  `FILESIZE` int(11) NOT NULL DEFAULT '0',
  `FILETYPE` int(11) NOT NULL DEFAULT '0',
  `VERSION` int(11) NOT NULL DEFAULT '0',
  `LOCKED` int(11) NOT NULL DEFAULT '0',
  `LOCKUID` text NOT NULL,
  `TOKEN` text NOT NULL,
  `DELETED` int(11) NOT NULL DEFAULT '0',
  `COMMENTSTATUS` int(11) NOT NULL DEFAULT '1',
  `COMMENTSTATUS_AUTO` int(11) NOT NULL DEFAULT '1',
  `CREATEDTS` int(11) NOT NULL DEFAULT '0',
  `CHANGEDTS` int(11) NOT NULL DEFAULT '0',
  `FILETS` int(11) NOT NULL DEFAULT '0',
  `CREATEDBY` int(11) NOT NULL DEFAULT '0',
  `CHANGEDBY` int(11) NOT NULL DEFAULT '0',
  `HASCHANGED` int(11) NOT NULL,
  `VIEWVERSION` int(11) NOT NULL,
  `APPROVED` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OBJECTID_2` (`OBJECTID`,`VERSION`,`FILETYPE`,`FOLDER`),
  KEY `OBJECTID` (`OBJECTID`),
  KEY `VERSION` (`VERSION`),
  FULLTEXT KEY `FTS` (`FILENAME`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_files_properties`
--

LOCK TABLES `yg_files_properties` WRITE;
/*!40000 ALTER TABLE `yg_files_properties` DISABLE KEYS */;
INSERT INTO `yg_files_properties` VALUES (1,1,1,'',0,8,0,0,'1','',0,0,1,0,0,0,1,1,0,0,1);
/*!40000 ALTER TABLE `yg_files_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_files_props`
--

DROP TABLE IF EXISTS `yg_files_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_files_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `VISIBLE` int(11) NOT NULL DEFAULT '1',
  `READONLY` int(11) NOT NULL DEFAULT '0',
  `TYPE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_files_props`
--

LOCK TABLES `yg_files_props` WRITE;
/*!40000 ALTER TABLE `yg_files_props` DISABLE KEYS */;
INSERT INTO `yg_files_props` VALUES (1,'TXT_NAME','NAME',1,1,'TEXT',1),(2,'TXT_DESCRIPTION','DESCRIPTION',1,1,'TEXTAREA',2);
/*!40000 ALTER TABLE `yg_files_props` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_files_propslv`
--

DROP TABLE IF EXISTS `yg_files_propslv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_files_propslv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `VALUE` varchar(50) NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `LISTORDER` (`LISTORDER`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_files_propslv`
--

LOCK TABLES `yg_files_propslv` WRITE;
/*!40000 ALTER TABLE `yg_files_propslv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_files_propslv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_files_propsv`
--

DROP TABLE IF EXISTS `yg_files_propsv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_files_propsv` (
  `OID` int(11) NOT NULL DEFAULT '0',
  `NAME` text,
  `DESCRIPTION` text,
  PRIMARY KEY (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_files_propsv`
--

LOCK TABLES `yg_files_propsv` WRITE;
/*!40000 ALTER TABLE `yg_files_propsv` DISABLE KEYS */;
INSERT INTO `yg_files_propsv` VALUES (1,'Files','');
/*!40000 ALTER TABLE `yg_files_propsv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_files_tree`
--

DROP TABLE IF EXISTS `yg_files_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_files_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LFT` int(11) NOT NULL DEFAULT '0',
  `RGT` int(11) NOT NULL DEFAULT '0',
  `VERSIONPUBLISHED` int(11) NOT NULL DEFAULT '0',
  `MOVED` int(11) NOT NULL DEFAULT '0',
  `TITLE` text,
  `LEVEL` int(11) NOT NULL DEFAULT '0',
  `PARENT` int(11) NOT NULL DEFAULT '0',
  `PNAME` text,
  PRIMARY KEY (`ID`),
  KEY `LFT` (`LFT`,`RGT`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_files_tree`
--

LOCK TABLES `yg_files_tree` WRITE;
/*!40000 ALTER TABLE `yg_files_tree` DISABLE KEYS */;
INSERT INTO `yg_files_tree` VALUES (1,1,2,0,0,'',1,0,'Files');
/*!40000 ALTER TABLE `yg_files_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_filetypes_permissions`
--

# Dump of table yg_filetypes_permissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `yg_filetypes_permissions`;

CREATE TABLE `yg_filetypes_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OID` (`OID`,`USERGROUPID`)
) ENGINE=MyISAM AUTO_INCREMENT=159 DEFAULT CHARSET=utf8;

LOCK TABLES `yg_filetypes_permissions` WRITE;
/*!40000 ALTER TABLE `yg_filetypes_permissions` DISABLE KEYS */;
INSERT INTO `yg_filetypes_permissions` (`ID`,`OID`,`USERGROUPID`,`RREAD`,`RWRITE`,`RDELETE`,`RSUB`,`RSTAGE`,`RMODERATE`,`RCOMMENT`,`RSEND`)
VALUES
	(1,99,1,1,1,1,1,1,0,0,0),
	(2,99,2,1,0,0,0,0,0,0,0),
	(3,1,1,1,1,1,1,1,0,0,0),
	(4,1,2,1,0,0,0,0,0,0,0),
	(5,2,1,1,1,1,1,1,0,0,0),
	(6,2,2,1,0,0,0,0,0,0,0),
	(7,3,1,1,1,1,1,1,0,0,0),
	(8,3,2,1,0,0,0,0,0,0,0),
	(9,4,1,1,1,1,1,1,0,0,0),
	(10,4,2,1,0,0,0,0,0,0,0),
	(11,5,1,1,1,1,1,1,0,0,0),
	(12,5,2,1,0,0,0,0,0,0,0),
	(13,6,1,1,1,1,1,1,0,0,0),
	(14,6,2,1,0,0,0,0,0,0,0),
	(15,10,1,1,1,1,1,1,0,0,0),
	(16,10,2,1,0,0,0,0,0,0,0),
	(17,8,1,1,1,1,1,1,0,0,0),
	(18,8,2,1,0,0,0,0,0,0,0),
	(19,100,1,1,1,1,1,1,0,0,0),
	(20,100,2,1,0,0,0,0,0,0,0),
	(21,101,1,1,1,1,1,1,0,0,0),
	(22,101,2,1,0,0,0,0,0,0,0),
	(23,102,1,1,1,1,1,1,0,0,0),
	(24,102,2,1,0,0,0,0,0,0,0),
	(25,103,1,1,1,1,1,1,0,0,0),
	(26,103,2,1,0,0,0,0,0,0,0),
	(27,104,1,1,1,1,1,1,0,0,0),
	(28,104,2,1,0,0,0,0,0,0,0),
	(29,105,1,1,1,1,1,1,0,0,0),
	(30,105,2,1,0,0,0,0,0,0,0),
	(31,106,1,1,1,1,1,1,0,0,0),
	(32,106,2,1,0,0,0,0,0,0,0),
	(33,107,1,1,1,1,1,1,0,0,0),
	(34,107,2,1,0,0,0,0,0,0,0),
	(35,108,1,1,1,1,1,1,0,0,0),
	(36,108,2,1,0,0,0,0,0,0,0),
	(37,109,1,1,1,1,1,1,0,0,0),
	(38,109,2,1,0,0,0,0,0,0,0),
	(39,110,1,1,1,1,1,1,0,0,0),
	(40,110,2,1,0,0,0,0,0,0,0),
	(41,111,1,1,1,1,1,1,0,0,0),
	(42,111,2,1,0,0,0,0,0,0,0),
	(43,112,1,1,1,1,1,1,0,0,0),
	(44,112,2,1,0,0,0,0,0,0,0),
	(45,113,1,1,1,1,1,1,0,0,0),
	(46,113,2,1,0,0,0,0,0,0,0),
	(47,114,1,1,1,1,1,1,0,0,0),
	(48,114,2,1,0,0,0,0,0,0,0),
	(49,115,1,1,1,1,1,1,0,0,0),
	(50,115,2,1,0,0,0,0,0,0,0),
	(51,116,1,1,1,1,1,1,0,0,0),
	(52,116,2,1,0,0,0,0,0,0,0),
	(53,117,1,1,1,1,1,1,0,0,0),
	(54,117,2,1,0,0,0,0,0,0,0),
	(55,118,1,1,1,1,1,1,0,0,0),
	(56,118,2,1,0,0,0,0,0,0,0),
	(57,119,1,1,1,1,1,1,0,0,0),
	(58,119,2,1,0,0,0,0,0,0,0),
	(59,120,1,1,1,1,1,1,0,0,0),
	(60,120,2,1,0,0,0,0,0,0,0),
	(61,121,1,1,1,1,1,1,0,0,0),
	(62,121,2,1,0,0,0,0,0,0,0),
	(63,122,1,1,1,1,1,1,0,0,0),
	(64,122,2,1,0,0,0,0,0,0,0),
	(65,123,1,1,1,1,1,1,0,0,0),
	(66,123,2,1,0,0,0,0,0,0,0),
	(67,124,1,1,1,1,1,1,0,0,0),
	(68,124,2,1,0,0,0,0,0,0,0),
	(69,125,1,1,1,1,1,1,0,0,0),
	(70,125,2,1,0,0,0,0,0,0,0),
	(71,126,1,1,1,1,1,1,0,0,0),
	(72,126,2,1,0,0,0,0,0,0,0),
	(73,127,1,1,1,1,1,1,0,0,0),
	(74,127,2,1,0,0,0,0,0,0,0),
	(75,128,1,1,1,1,1,1,0,0,0),
	(76,128,2,1,0,0,0,0,0,0,0),
	(77,129,1,1,1,1,1,1,0,0,0),
	(78,129,2,1,0,0,0,0,0,0,0),
	(79,130,1,1,1,1,1,1,0,0,0),
	(80,130,2,1,0,0,0,0,0,0,0),
	(81,131,1,1,1,1,1,1,0,0,0),
	(82,131,2,1,0,0,0,0,0,0,0),
	(83,132,1,1,1,1,1,1,0,0,0),
	(84,132,2,1,0,0,0,0,0,0,0),
	(85,133,1,1,1,1,1,1,0,0,0),
	(86,133,2,1,0,0,0,0,0,0,0),
	(87,134,1,1,1,1,1,1,0,0,0),
	(88,134,2,1,0,0,0,0,0,0,0),
	(89,135,1,1,1,1,1,1,0,0,0),
	(90,135,2,1,0,0,0,0,0,0,0),
	(91,136,1,1,1,1,1,1,0,0,0),
	(92,136,2,1,0,0,0,0,0,0,0),
	(93,137,1,1,1,1,1,1,0,0,0),
	(94,137,2,1,0,0,0,0,0,0,0),
	(95,138,1,1,1,1,1,1,0,0,0),
	(96,138,2,1,0,0,0,0,0,0,0),
	(97,139,1,1,1,1,1,1,0,0,0),
	(98,139,2,1,0,0,0,0,0,0,0),
	(99,140,1,1,1,1,1,1,0,0,0),
	(100,140,2,1,0,0,0,0,0,0,0),
	(101,141,1,1,1,1,1,1,0,0,0),
	(102,141,2,1,0,0,0,0,0,0,0),
	(103,142,1,1,1,1,1,1,0,0,0),
	(104,142,2,1,0,0,0,0,0,0,0),
	(105,143,1,1,1,1,1,1,0,0,0),
	(106,143,2,1,0,0,0,0,0,0,0),
	(107,144,1,1,1,1,1,1,0,0,0),
	(108,144,2,1,0,0,0,0,0,0,0),
	(109,145,1,1,1,1,1,1,0,0,0),
	(110,145,2,1,0,0,0,0,0,0,0),
	(111,146,1,1,1,1,1,1,0,0,0),
	(112,146,2,1,0,0,0,0,0,0,0),
	(113,147,1,1,1,1,1,1,0,0,0),
	(114,147,2,1,0,0,0,0,0,0,0),
	(115,148,1,1,1,1,1,1,0,0,0),
	(116,148,2,1,0,0,0,0,0,0,0),
	(117,149,1,1,1,1,1,1,0,0,0),
	(118,149,2,1,0,0,0,0,0,0,0),
	(119,150,1,1,1,1,1,1,0,0,0),
	(120,150,2,1,0,0,0,0,0,0,0),
	(121,151,1,1,1,1,1,1,0,0,0),
	(122,151,2,1,0,0,0,0,0,0,0),
	(123,152,1,1,1,1,1,1,0,0,0),
	(124,152,2,1,0,0,0,0,0,0,0),
	(125,153,1,1,1,1,1,1,0,0,0),
	(126,153,2,1,0,0,0,0,0,0,0),
	(127,154,1,1,1,1,1,1,0,0,0),
	(128,154,2,1,0,0,0,0,0,0,0),
	(129,155,1,1,1,1,1,1,0,0,0),
	(130,155,2,1,0,0,0,0,0,0,0),
	(131,156,1,1,1,1,1,1,0,0,0),
	(132,156,2,1,0,0,0,0,0,0,0),
	(133,157,1,1,1,1,1,1,0,0,0),
	(134,157,2,1,0,0,0,0,0,0,0),
	(135,158,1,1,1,1,1,1,0,0,0),
	(136,158,2,1,0,0,0,0,0,0,0),
	(137,159,1,1,1,1,1,1,0,0,0),
	(138,159,2,1,0,0,0,0,0,0,0),
	(139,160,1,1,1,1,1,1,0,0,0),
	(140,160,2,1,0,0,0,0,0,0,0),
	(141,161,1,1,1,1,1,1,0,0,0),
	(142,161,2,1,0,0,0,0,0,0,0),
	(143,162,1,1,1,1,1,1,0,0,0),
	(144,162,2,1,0,0,0,0,0,0,0),
	(145,163,1,1,1,1,1,1,0,0,0),
	(146,163,2,1,0,0,0,0,0,0,0),
	(147,164,1,1,1,1,1,1,0,0,0),
	(148,164,2,1,0,0,0,0,0,0,0),
	(149,165,1,1,1,1,1,1,0,0,0),
	(150,165,2,1,0,0,0,0,0,0,0),
	(151,166,1,1,1,1,1,1,0,0,0),
	(152,166,2,1,0,0,0,0,0,0,0),
	(153,167,1,1,1,1,1,1,0,0,0),
	(154,167,2,1,0,0,0,0,0,0,0),
	(155,168,1,1,1,1,1,1,0,0,0),
	(156,168,2,1,0,0,0,0,0,0,0),
	(157,169,1,1,1,1,1,1,0,0,0),
	(158,169,2,1,0,0,0,0,0,0,0);

/*!40000 ALTER TABLE `yg_filetypes_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_filetypes_properties`
--
DROP TABLE IF EXISTS `yg_filetypes_properties`;

CREATE TABLE `yg_filetypes_properties` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTID` int(11) NOT NULL DEFAULT '0',
  `READONLY` int(11) NOT NULL,
  `FOLDER` int(11) NOT NULL DEFAULT '0',
  `PROCESSOR` varchar(50) NOT NULL DEFAULT 'NONE',
  `IDENTIFIER` varchar(50) NOT NULL DEFAULT 'NONE',
  `COLOR` varchar(50) NOT NULL DEFAULT 'NONE',
  `CODE` varchar(50) NOT NULL DEFAULT 'NONE',
  `NAME` varchar(150) NOT NULL DEFAULT '',
  `EXTENSIONS` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `OBJECTID` (`OBJECTID`),
  KEY `NAME` (`NAME`)
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;

LOCK TABLES `yg_filetypes_properties` WRITE;
/*!40000 ALTER TABLE `yg_filetypes_properties` DISABLE KEYS */;
INSERT INTO `yg_filetypes_properties` (`ID`,`OBJECTID`,`READONLY`,`FOLDER`,`PROCESSOR`,`IDENTIFIER`,`COLOR`,`CODE`,`NAME`,`EXTENSIONS`)
VALUES
	(1,99,0,1,'NONE','NONE','purple','NONE','File Types',''),
	(2,1,0,0,'NONE','FILE','black','FILE','Misc',''),
	(3,2,0,0,'GD','JPG','purple','JPG','JPG','jpg,jpeg'),
	(4,3,0,0,'GD','GIF','purple','GIF','GIF','gif'),
	(5,4,0,0,'GD','BMP','purple','BMP','BMP','bmp'),
	(6,5,0,0,'GD','TIF','purple','TIF','TIF','tif,tiff'),
	(7,6,0,0,'NONE','EPS','purple','EPS','EPS','eps'),
	(9,8,0,0,'NONE','NONE','blue','FLD','Folder',''),
	(10,100,0,0,'GD','PNG','purple','PNG','PNG','png'),
	(11,101,0,0,'GD','PSD','blue','PSD','PSD','psd'),
	(12,102,1,0,'NONE','FILE','black','NONE','DEFAULT',''),
	(13,103,0,0,'NONE','ICO','purple','ICO','ICO','ico'),
	(14,104,0,0,'NONE','ICNS','purple','ICNS','ICNS','icns'),
	(15,105,0,0,'NONE','PIC','purple','PIC','PIC','pic,pict'),
	(16,106,0,0,'NONE','AI','purple','AI','AI','ai'),
	(17,107,0,0,'NONE','AVI','color10','AVI','AVI','avi'),
	(18,108,0,0,'NONE','MP4','color10','MP4','MP4','mp4,m4v,mpeg4,mpg4'),
	(19,109,0,0,'NONE','MPG','color10','MPG','MPG','mpg,mpeg'),
	(20,110,0,0,'NONE','MKV','color10','MKV','MKV','mkv'),
	(21,111,0,0,'NONE','MOV','color10','MOV','MOV','mov'),
	(22,112,0,0,'NONE','OGV','color10','OGV','OGV','ogv'),
	(23,113,0,0,'NONE','WEBM','color10','WEBM','WEBM','webm'),
	(24,114,0,0,'NONE','FLV','color10','FLV','FLV','flv'),
	(25,115,0,0,'NONE','WMV','color10','WMV','WMV','wmv'),
	(26,116,0,0,'NONE','AIF','color2','AIF','AIF','aif,aiff'),
	(27,117,0,0,'NONE','MP3','color1','MP3','MP3','mp3'),
	(28,118,0,0,'NONE','WAV','color1','WAV','WAV','wav,wave'),
	(29,119,0,0,'NONE','MIDI','color1','MIDI','MIDI','midi'),
	(30,120,0,0,'NONE','OGG','color1','OGG','OGG','ogg'),
	(31,121,0,0,'NONE','FLAC','color2','FLAC','FLAC','flac'),
	(32,122,0,0,'NONE','PDF','red','PDF','PDF','pdf'),
	(33,123,0,0,'NONE','DOC','blue','DOC','DOC','doc,docx,dot,dotx'),
	(34,124,0,0,'NONE','XLS','green','XLS','XLS','xls,xlsx,xlt'),
	(35,125,0,0,'NONE','PPT','color4','PPT','PPT','ppt,pptx,pot,potx'),
	(36,126,0,0,'NONE','ODT','blue','ODT','ODT','odt,fodt'),
	(37,127,0,0,'NONE','ODS','green','ODS','ODS','ods,fods'),
	(38,128,0,0,'NONE','ODP','color4','ODP','ODP','odp,fodp'),
	(39,129,0,0,'NONE','CSV','color8','CSV','CSV','csv'),
	(40,130,0,0,'NONE','FLA','color6','FLA','FLA','fla'),
	(41,131,0,0,'NONE','SWF','red','SWF','SWF','swf'),
	(42,132,0,0,'NONE','TXT','color15','TXT','TXT','txt,nfo,ini,diz'),
	(43,133,0,0,'NONE','CAL','color6','CAL','CAL','cal,ical'),
	(44,134,0,0,'NONE','ICS','color6','ICS','ICS','ics'),
	(45,135,0,0,'NONE','VCARD','color11','VCRD','VCARD','vcard'),
	(46,136,0,0,'NONE','EPUB','red','EPUB','EPUB','epub'),
	(47,137,0,0,'NONE','EXE','color12','EXE','EXE','exe'),
	(48,138,0,0,'NONE','SUB','color13','SUB','SUB','sub'),
	(49,139,0,0,'NONE','JS','color16','JS','JS','js'),
	(50,140,0,0,'NONE','AS','color16','AS','AS','as'),
	(51,141,0,0,'NONE','PHP','color16','PHP','PHP','php'),
	(52,142,0,0,'NONE','PY','color16','PY','PY','py'),
	(53,143,0,0,'NONE','PYC','color16','PYC','PYC','pyc'),
	(54,144,0,0,'NONE','C','color16','C','C','c'),
	(55,145,0,0,'NONE','CPP','color16','CPP','CPP','cpp'),
	(56,146,0,0,'NONE','M','color16','M','M','m'),
	(57,147,0,0,'NONE','MPP','color16','MPP','MPP','mpp'),
	(58,148,0,0,'NONE','HTML','color16','HTML','HTML','html,htm'),
	(59,149,0,0,'NONE','XML','color16','XML','XML','xml,xslt'),
	(60,150,0,0,'NONE','YML','color16','YML','YML','yml,yaml'),
	(61,151,0,0,'NONE','APK','color7','APK','APK','apk'),
	(62,152,0,0,'NONE','RPM','color7','RPM','RPM','rpm'),
	(63,153,0,0,'NONE','HQX','color7','HQX','HQX','hqx'),
	(64,154,0,0,'NONE','SQL','color11','SQL','SQL','sql'),
	(65,155,0,0,'NONE','ODB','color11','ODB','ODB','odb'),
	(66,156,0,0,'NONE','MDB','color11','MDB','MDB','mdb,ade,adp,adn,accdb,accdr,mda,mdn,mdt'),
	(67,157,0,0,'NONE','ZIP','color8','ZIP','ZIP','zip,7z,zipx'),
	(68,158,0,0,'NONE','RAR','color8','RAR','RAR','rar'),
	(69,159,0,0,'NONE','BZ','color8','BZIP','BZ','bz,bz2,bzip'),
	(70,160,0,0,'NONE','GZ','color8','GZ','GZ','gz'),
	(71,161,0,0,'NONE','TAR','color8','TAR','TAR','tar'),
	(72,162,0,0,'NONE','ACE','color8','ACE','ACE','ace'),
	(73,163,0,0,'NONE','ICE','color8','ICE','ICE','ice'),
	(74,164,0,0,'NONE','SIT','color8','SIT','SIT','sit,sitx'),
	(75,165,0,0,'NONE','DMG','black','DMG','DMG','dmg'),
	(76,166,0,0,'NONE','IMG','black','IMG','IMG','img'),
	(77,167,0,0,'NONE','ISO','black','ISO','ISO','iso,toast'),
	(78,168,0,0,'NONE','ARC','black','ARC','ARC','arc'),
	(79,169,0,0,'NONE','OPUS','color1','OPUS','OPUS','opus');

/*!40000 ALTER TABLE `yg_filetypes_properties` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `yg_filetypes_props`
--

DROP TABLE IF EXISTS `yg_filetypes_props`;

CREATE TABLE `yg_filetypes_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `VISIBLE` int(11) NOT NULL DEFAULT '1',
  `TYPE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



--
-- Table structure for table `yg_filetypes_propslv`
--

DROP TABLE IF EXISTS `yg_filetypes_propslv`;

CREATE TABLE `yg_filetypes_propslv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `VALUE` varchar(50) NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `LISTORDER` (`LISTORDER`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



--
-- Table structure for table `yg_filetypes_propslv`
--

DROP TABLE IF EXISTS `yg_filetypes_propsv`;

CREATE TABLE `yg_filetypes_propsv` (
  `OID` int(11) NOT NULL DEFAULT '0',
  `NAME` text,
  `DESCRIPTION` text,
  `METAINFO` text,
  `LANGUAGE` text,
  PRIMARY KEY (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Table structure for table `yg_filetypes_tree`
--

DROP TABLE IF EXISTS `yg_filetypes_tree`;

CREATE TABLE `yg_filetypes_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LFT` int(11) NOT NULL DEFAULT '0',
  `RGT` int(11) NOT NULL DEFAULT '0',
  `MOVED` int(11) NOT NULL DEFAULT '0',
  `LEVEL` int(11) NOT NULL DEFAULT '0',
  `PARENT` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `RGT` (`RGT`),
  KEY `LFT` (`LFT`)
) ENGINE=MyISAM AUTO_INCREMENT=170 DEFAULT CHARSET=utf8 PACK_KEYS=0;

LOCK TABLES `yg_filetypes_tree` WRITE;
/*!40000 ALTER TABLE `yg_filetypes_tree` DISABLE KEYS */;
INSERT INTO `yg_filetypes_tree` (`ID`,`LFT`,`RGT`,`MOVED`,`LEVEL`,`PARENT`)
VALUES
	(99,0,157,0,1,0),
	(1,1,4,0,2,99),
	(2,5,6,0,2,99),
	(3,7,8,0,2,99),
	(4,9,10,0,2,99),
	(5,11,12,0,2,99),
	(6,13,14,0,2,99),
	(10,15,16,0,2,99),
	(8,17,18,0,2,99),
	(100,19,20,0,2,99),
	(101,21,22,0,2,99),
	(102,2,3,0,3,1),
	(103,23,24,0,2,99),
	(104,25,26,0,2,99),
	(105,27,28,0,2,99),
	(106,29,30,0,2,99),
	(107,31,32,0,2,99),
	(108,33,34,0,2,99),
	(109,35,36,0,2,99),
	(110,37,38,0,2,99),
	(111,39,40,0,2,99),
	(112,41,42,0,2,99),
	(113,43,44,0,2,99),
	(114,45,46,0,2,99),
	(115,47,48,0,2,99),
	(116,49,50,0,2,99),
	(117,51,52,0,2,99),
	(118,53,54,0,2,99),
	(119,55,56,0,2,99),
	(120,57,58,0,2,99),
	(121,59,60,0,2,99),
	(122,61,62,0,2,99),
	(123,63,64,0,2,99),
	(124,65,66,0,2,99),
	(125,67,68,0,2,99),
	(126,69,70,0,2,99),
	(127,71,72,0,2,99),
	(128,73,74,0,2,99),
	(129,75,76,0,2,99),
	(130,77,78,0,2,99),
	(131,79,80,0,2,99),
	(132,81,82,0,2,99),
	(133,83,84,0,2,99),
	(134,85,86,0,2,99),
	(135,87,88,0,2,99),
	(136,89,90,0,2,99),
	(137,91,92,0,2,99),
	(138,93,94,0,2,99),
	(139,95,96,0,2,99),
	(140,97,98,0,2,99),
	(141,99,100,0,2,99),
	(142,101,102,0,2,99),
	(143,103,104,0,2,99),
	(144,105,106,0,2,99),
	(145,107,108,0,2,99),
	(146,109,110,0,2,99),
	(147,111,112,0,2,99),
	(148,113,114,0,2,99),
	(149,115,116,0,2,99),
	(150,117,118,0,2,99),
	(151,119,120,0,2,99),
	(152,121,122,0,2,99),
	(153,123,124,0,2,99),
	(154,125,126,0,2,99),
	(155,127,128,0,2,99),
	(156,129,130,0,2,99),
	(157,131,132,0,2,99),
	(158,133,134,0,2,99),
	(159,135,136,0,2,99),
	(160,137,138,0,2,99),
	(161,139,140,0,2,99),
	(162,141,142,0,2,99),
	(163,143,144,0,2,99),
	(164,145,146,0,2,99),
	(165,147,148,0,2,99),
	(166,149,150,0,2,99),
	(167,151,152,0,2,99),
	(168,153,154,0,2,99),
	(169,155,156,0,2,99);

/*!40000 ALTER TABLE `yg_filetypes_tree` ENABLE KEYS */;
UNLOCK TABLES;


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

--
-- Table structure for table `yg_views_generated`
--

DROP TABLE IF EXISTS `yg_views_generated`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_views_generated` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FILEID` int(11) NOT NULL,
  `FILEVERSION` int(11) NOT NULL,
  `VIEWID` int(11) NOT NULL,
  `WIDTH` int(11) NOT NULL,
  `HEIGHT` int(11) NOT NULL,
  `TYPE` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_views_generated`
--

LOCK TABLES `yg_views_generated` WRITE;
/*!40000 ALTER TABLE `yg_views_generated` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_views_generated` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_views_lnk_files`
--

DROP TABLE IF EXISTS `yg_views_lnk_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_views_lnk_files` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FILEID` int(11) NOT NULL,
  `FILEVERSION` int(11) NOT NULL,
  `FILEVID` int(11) NOT NULL,
  `VIEWID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_views_lnk_files`
--

LOCK TABLES `yg_views_lnk_files` WRITE;
/*!40000 ALTER TABLE `yg_views_lnk_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_views_lnk_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_views_permissions`
--

DROP TABLE IF EXISTS `yg_views_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_views_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OID` (`OID`,`USERGROUPID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_views_permissions`
--

LOCK TABLES `yg_views_permissions` WRITE;
/*!40000 ALTER TABLE `yg_views_permissions` DISABLE KEYS */;
INSERT INTO `yg_views_permissions` VALUES (1,1,1,1,1,1,1,1,0,0,0),(2,1,2,1,0,0,0,0,0,0,0),(3,2,1,1,1,1,1,1,0,0,0),(4,2,2,1,0,0,0,0,0,0,0),(5,3,1,1,1,1,1,1,0,0,0),(6,3,2,1,0,0,0,0,0,0,0),(7,4,1,1,1,1,1,1,0,0,0),(8,4,2,1,0,0,0,0,0,0,0),(9,5,1,1,1,1,1,1,0,0,0),(10,5,2,1,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `yg_views_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_views_properties`
--

DROP TABLE IF EXISTS `yg_views_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_views_properties` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTID` int(11) NOT NULL DEFAULT '0',
  `FOLDER` int(11) NOT NULL DEFAULT '0',
  `IDENTIFIER` varchar(50) NOT NULL DEFAULT 'NONE',
  `WIDTH` int(11) NOT NULL,
  `HEIGHT` int(11) NOT NULL,
  `HIDDEN` int(11) NOT NULL DEFAULT '0',
  `NAME` varchar(150) NOT NULL DEFAULT '',
  `WIDTHCROP` int(11) NOT NULL,
  `HEIGHTCROP` int(11) NOT NULL,
  `CONSTRAINWIDTH` int(11) NOT NULL,
  `CONSTRAINHEIGHT` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `OBJECTID` (`OBJECTID`),
  KEY `NAME` (`NAME`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_views_properties`
--

LOCK TABLES `yg_views_properties` WRITE;
/*!40000 ALTER TABLE `yg_views_properties` DISABLE KEYS */;
INSERT INTO `yg_views_properties` VALUES (1,1,1,'NONE',0,0,0,'File Views',0,0,0,0),(2,2,0,'yg-thumb',160,107,1,'yg-thumb',0,0,0,0),(3,3,0,'yg-list',100,76,1,'yg-list',0,0,0,0),(4,4,0,'yg-preview',500,400,1,'yg-preview',0,0,0,0),(5,5,0,'yg-bigthumb',223,168,1,'yg-bigthumb',0,0,0,0);
/*!40000 ALTER TABLE `yg_views_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_views_tree`
--

DROP TABLE IF EXISTS `yg_views_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_views_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LFT` int(11) NOT NULL DEFAULT '0',
  `RGT` int(11) NOT NULL DEFAULT '0',
  `MOVED` int(11) NOT NULL DEFAULT '0',
  `LEVEL` int(11) NOT NULL DEFAULT '0',
  `PARENT` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `RGT` (`RGT`),
  KEY `LFT` (`LFT`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_views_tree`
--

LOCK TABLES `yg_views_tree` WRITE;
/*!40000 ALTER TABLE `yg_views_tree` DISABLE KEYS */;
INSERT INTO `yg_views_tree` VALUES (1,0,10,0,1,0),(2,2,3,0,2,1),(3,4,5,0,2,1),(4,6,7,0,2,1),(5,8,9,0,2,1);
/*!40000 ALTER TABLE `yg_views_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_privileges`
--

DROP TABLE IF EXISTS `yg_privileges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `yg_privileges` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PRIVILEGE` text NOT NULL,
  `NAME` text NOT NULL,
  `EXTCODE` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_privileges`
--

LOCK TABLES `yg_privileges` WRITE;
/*!40000 ALTER TABLE `yg_privileges` DISABLE KEYS */;
INSERT INTO `yg_privileges` VALUES (1,'RUSERGROUPS','RUSERGROUPS',''),(2,'RUSERS','RUSERS',''),(3,'RLANGUAGES','RLANGUAGES',''),(4,'RENTRYMASKS','RENTRYMASKS',''),(5,'REXTENSIONS_PAGE','REXTENSIONS_PAGE',''),(6,'REXPORT','REXPORT',''),(7,'RIMPORT','RIMPORT',''),(8,'RTEMPLATES','RTEMPLATES',''),(9,'RPROPERTIES','RPROPERTIES',''),(10,'RFILETYPES','RFILETYPES',''),(11,'RVIEWS','RVIEWS',''),(12,'RPAGES','RPAGES',''),(13,'RCONTENTBLOCKS','RCONTENTBLOCKS',''),(14,'RFILES','RFILES',''),(15,'RTAGS','RTAGS',''),(16,'RSITES','RSITES',''),(17,'RDATA','RDATA',''),(18,'RCOMMENTCONFIG','RCOMMENTCONFIG',''),(19,'REXTENSIONS_CBLISTVIEW','REXTENSIONS_CBLISTVIEW',''),(20,'RMAILINGCONFIG','RMAILINGCONFIG',''),(21,'RMAILINGS','RMAILINGS',''),(22,'RBACKEND','RBACKEND',''),(23,'RCOMMENTS','RCOMMENTS',''),(24,'REXTENSIONS_MAILING','REXTENSIONS_MAILING',''),(25,'REXTENSIONS_FILE','REXTENSIONS_FILE',''),(26,'REXTENSIONS_CBLOCK','REXTENSIONS_CBLOCK',''),(27,'RUPDATER','RUPDATER','');
/*!40000 ALTER TABLE `yg_privileges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_privileges_values`
--

DROP TABLE IF EXISTS `yg_privileges_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `yg_privileges_values` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USERGROUPID` int(11) NOT NULL,
  `PRIVILEGEID` int(11) NOT NULL,
  `VALUE` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_privileges_values`
--

LOCK TABLES `yg_privileges_values` WRITE;
/*!40000 ALTER TABLE `yg_privileges_values` DISABLE KEYS */;
INSERT INTO `yg_privileges_values` VALUES (1,1,1,1),(2,1,2,1),(3,1,3,1),(4,1,4,1),(5,1,5,1),(6,1,6,1),(7,1,7,1),(8,1,8,1),(9,1,9,1),(10,1,10,1),(11,1,11,1),(12,1,12,1),(13,1,13,1),(14,1,14,1),(15,1,15,1),(16,1,16,1),(17,1,17,1),(18,1,18,1),(19,1,19,1),(20,1,20,1),(21,1,21,1),(22,1,22,1),(23,1,23,1),(24,1,24,1),(25,1,25,1),(26,1,26,1),(27,1,27,1),(28,2,1,0),(29,2,2,0),(30,2,3,0),(31,2,4,0),(32,2,5,0),(33,2,6,0),(34,2,7,0),(35,2,8,0),(36,2,9,0),(37,2,10,0),(38,2,11,0),(39,2,12,0),(40,2,13,0),(41,2,14,0),(42,2,15,0),(43,2,16,0),(44,2,17,0),(45,2,18,0),(46,2,19,0),(47,2,20,0),(48,2,21,0),(49,2,22,0),(50,2,23,0),(51,2,24,0),(52,2,25,0),(53,2,26,0),(54,2,27,0),(55,3,1,1),(56,3,2,1),(57,3,3,0),(58,3,4,1),(59,3,5,1),(60,3,6,1),(61,3,7,1),(62,3,8,1),(63,3,9,1),(64,3,10,1),(65,3,11,1),(66,3,12,1),(67,3,13,1),(68,3,14,1),(69,3,15,1),(70,3,16,1),(71,3,17,1),(72,3,18,1),(73,3,19,1),(74,3,20,1),(75,3,21,1),(76,3,22,1),(77,3,23,1),(78,3,24,1),(79,3,25,1),(80,3,26,1),(81,3,27,1),(82,20,1,0),(83,20,2,0),(84,20,3,0),(85,20,4,0),(86,20,5,0),(87,20,6,0),(88,20,7,0),(89,20,8,1),(90,20,9,0),(91,20,10,0),(92,20,11,0),(93,20,12,0),(94,20,13,0),(95,20,14,0),(96,20,15,0),(97,20,16,0),(98,20,17,0),(99,20,18,0),(100,20,19,0),(101,20,20,0),(102,20,21,0),(103,20,22,1),(104,20,23,0),(105,20,24,0),(106,20,25,0),(107,20,26,0),(108,20,27,0);
/*!40000 ALTER TABLE `yg_privileges_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_references`
--

DROP TABLE IF EXISTS `yg_references`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_references` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SRCTYPE` int(11) NOT NULL,
  `SRCOID` int(11) NOT NULL,
  `SRCVER` int(11) NOT NULL,
  `TGTTYPE` int(11) NOT NULL,
  `TGTOID` text COLLATE utf8_unicode_ci NOT NULL,
  `TGTAID` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `SRC` (`SRCTYPE`,`SRCOID`,`SRCVER`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_references`
--

LOCK TABLES `yg_references` WRITE;
/*!40000 ALTER TABLE `yg_references` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_references` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_usergroups`
--

DROP TABLE IF EXISTS `yg_usergroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_usergroups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_usergroups`
--

LOCK TABLES `yg_usergroups` WRITE;
/*!40000 ALTER TABLE `yg_usergroups` DISABLE KEYS */;
INSERT INTO `yg_usergroups` VALUES (1,'Administrator'),(2,'Anonymous');
/*!40000 ALTER TABLE `yg_usergroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_usergroups_permissions`
--

DROP TABLE IF EXISTS `yg_usergroups_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_usergroups_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_usergroups_permissions`
--

LOCK TABLES `yg_usergroups_permissions` WRITE;
/*!40000 ALTER TABLE `yg_usergroups_permissions` DISABLE KEYS */;
INSERT INTO `yg_usergroups_permissions` VALUES (1,1,1,1,1,1,0,0,0),(2,2,1,1,1,1,0,0,0),(3,1,2,0,0,0,0,0,0),(4,2,2,0,0,0,0,0,0);
/*!40000 ALTER TABLE `yg_usergroups_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_site`
--

DROP TABLE IF EXISTS `yg_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_site` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(66) NOT NULL DEFAULT '',
  `DEFAULTTEMPLATE` int(11) NOT NULL,
  `TEMPLATEROOT` int(11) NOT NULL,
  `PNAME` varchar(66) NOT NULL DEFAULT '',
  `FAVICON` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_site`
--

LOCK TABLES `yg_site` WRITE;
/*!40000 ALTER TABLE `yg_site` DISABLE KEYS */;
INSERT INTO `yg_site` VALUES (1,'Example',107,0,'example',0);
/*!40000 ALTER TABLE `yg_site` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_site_1_cron`
--

DROP TABLE IF EXISTS `yg_site_1_cron`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_site_1_cron` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTTYPE` int(11) NOT NULL,
  `OBJECTID` int(11) NOT NULL,
  `ACTIONCODE` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `TIMESTAMP` bigint(20) NOT NULL,
  `EXPIRES` bigint(20) NOT NULL,
  `PARAMETERS` text COLLATE utf8_unicode_ci NOT NULL,
  `USERID` int(11) NOT NULL,
  `STATUS` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_site_1_cron`
--

LOCK TABLES `yg_site_1_cron` WRITE;
/*!40000 ALTER TABLE `yg_site_1_cron` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_site_1_cron` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_site_1_lnk_cb`
--

DROP TABLE IF EXISTS `yg_site_1_lnk_cb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_site_1_lnk_cb` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CBID` int(11) NOT NULL DEFAULT '0',
  `CBVERSION` int(11) NOT NULL DEFAULT '0',
  `CBPID` int(11) NOT NULL DEFAULT '0',
  `PID` int(11) NOT NULL DEFAULT '0',
  `PVERSION` int(11) NOT NULL DEFAULT '0',
  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
  `TEMPLATECONTENTAREA` varchar(85) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `CBID` (`CBID`,`CBVERSION`),
  KEY `CBID_2` (`CBID`,`PID`,`PVERSION`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_site_1_lnk_cb`
--

LOCK TABLES `yg_site_1_lnk_cb` WRITE;
/*!40000 ALTER TABLE `yg_site_1_lnk_cb` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_site_1_lnk_cb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_site_1_permissions`
--

DROP TABLE IF EXISTS `yg_site_1_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_site_1_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OID` (`OID`,`USERGROUPID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_site_1_permissions`
--

LOCK TABLES `yg_site_1_permissions` WRITE;
/*!40000 ALTER TABLE `yg_site_1_permissions` DISABLE KEYS */;
INSERT INTO `yg_site_1_permissions` VALUES (1,1,1,1,1,1,1,1,1,1,1),(2,1,2,1,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `yg_site_1_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_site_1_properties`
--

DROP TABLE IF EXISTS `yg_site_1_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_site_1_properties` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTID` int(11) NOT NULL DEFAULT '0',
  `VERSION` int(11) NOT NULL DEFAULT '0',
  `APPROVED` smallint(6) NOT NULL DEFAULT '0',
  `CREATEDBY` int(11) NOT NULL DEFAULT '0',
  `CHANGEDBY` int(11) NOT NULL DEFAULT '0',
  `HASCHANGED` int(11) NOT NULL DEFAULT '0',
  `TEMPLATEID` int(11) NOT NULL DEFAULT '0',
  `COMMENTSTATUS` int(11) NOT NULL DEFAULT '1',
  `COMMENTSTATUS_AUTO` int(11) NOT NULL DEFAULT '1',
  `NAVIGATION` int(11) NOT NULL DEFAULT '0',
  `ACTIVE` int(11) NOT NULL DEFAULT '0',
  `HIDDEN` int(11) NOT NULL DEFAULT '0',
  `LOCKED` int(11) NOT NULL DEFAULT '0',
  `LOCKUID` text NOT NULL,
  `TOKEN` text NOT NULL,
  `DELETED` int(11) NOT NULL DEFAULT '0',
  `CREATEDTS` int(11) NOT NULL DEFAULT '0',
  `CHANGEDTS` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OBJECTID` (`OBJECTID`,`VERSION`),
  KEY `VERSION` (`VERSION`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_site_1_properties`
--

LOCK TABLES `yg_site_1_properties` WRITE;
/*!40000 ALTER TABLE `yg_site_1_properties` DISABLE KEYS */;
INSERT INTO `yg_site_1_properties` VALUES (1,1,0,1,1,1,0,1,0,1,0,1,0,0,'1','',0,0,0);
/*!40000 ALTER TABLE `yg_site_1_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_site_1_props`
--

DROP TABLE IF EXISTS `yg_site_1_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_site_1_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `VISIBLE` int(11) NOT NULL DEFAULT '1',
  `READONLY` int(11) NOT NULL DEFAULT '0',
  `TYPE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_site_1_props`
--

LOCK TABLES `yg_site_1_props` WRITE;
/*!40000 ALTER TABLE `yg_site_1_props` DISABLE KEYS */;
INSERT INTO `yg_site_1_props` VALUES (1,'TXT_NAME','NAME',1,1,'TEXT',1),(2,'TXT_TITLE','TITLE',1,1,'TEXT',2),(3,'TXT_DESCRIPTION','DESCRIPTION',1,1,'TEXTAREA',3);
/*!40000 ALTER TABLE `yg_site_1_props` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_site_1_propslv`
--

DROP TABLE IF EXISTS `yg_site_1_propslv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_site_1_propslv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `VALUE` varchar(50) NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `LISTORDER` (`LISTORDER`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_site_1_propslv`
--

LOCK TABLES `yg_site_1_propslv` WRITE;
/*!40000 ALTER TABLE `yg_site_1_propslv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_site_1_propslv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_site_1_propsv`
--

DROP TABLE IF EXISTS `yg_site_1_propsv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_site_1_propsv` (
  `OID` int(11) NOT NULL DEFAULT '0',
  `NAME` text,
  `TITLE` text,
  `DESCRIPTION` text,
  PRIMARY KEY (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_site_1_propsv`
--

LOCK TABLES `yg_site_1_propsv` WRITE;
/*!40000 ALTER TABLE `yg_site_1_propsv` DISABLE KEYS */;
INSERT INTO `yg_site_1_propsv` VALUES (1,'Webs',NULL,NULL);
/*!40000 ALTER TABLE `yg_site_1_propsv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_site_1_tree`
--

DROP TABLE IF EXISTS `yg_site_1_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_site_1_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LFT` int(11) NOT NULL DEFAULT '0',
  `RGT` int(11) NOT NULL DEFAULT '0',
  `VERSIONPUBLISHED` int(11) NOT NULL DEFAULT '0',
  `MOVED` int(11) NOT NULL DEFAULT '0',
  `TITLE` text,
  `LEVEL` int(11) NOT NULL DEFAULT '0',
  `PARENT` int(11) NOT NULL DEFAULT '0',
  `PNAME` text,
  PRIMARY KEY (`ID`),
  KEY `LFT` (`LFT`,`RGT`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_site_1_tree`
--

LOCK TABLES `yg_site_1_tree` WRITE;
/*!40000 ALTER TABLE `yg_site_1_tree` DISABLE KEYS */;
INSERT INTO `yg_site_1_tree` VALUES (1,1,2,999999,0,'',1,0,'example');
/*!40000 ALTER TABLE `yg_site_1_tree` ENABLE KEYS */;
UNLOCK TABLES;

-- Table structure for table `yg_tags_permissions`
--

DROP TABLE IF EXISTS `yg_tags_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_tags_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_tags_permissions`
--

LOCK TABLES `yg_tags_permissions` WRITE;
/*!40000 ALTER TABLE `yg_tags_permissions` DISABLE KEYS */;
INSERT INTO `yg_tags_permissions` VALUES (1,1,1,1,1,1,1,1,0,0,0),(2,1,2,1,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `yg_tags_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_templates_navis`
--

DROP TABLE IF EXISTS `yg_templates_navis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_templates_navis` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TEMPLATE` int(11) NOT NULL,
  `DEFAULT` int(11) NOT NULL DEFAULT '0',
  `CODE` varchar(100) NOT NULL,
  `NAME` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_templates_navis`
--

LOCK TABLES `yg_templates_navis` WRITE;
/*!40000 ALTER TABLE `yg_templates_navis` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_templates_navis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_templates_permissions`
--

DROP TABLE IF EXISTS `yg_templates_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_templates_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  `RREAD` smallint(6) NOT NULL DEFAULT '0',
  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
  `RSUB` smallint(6) NOT NULL DEFAULT '0',
  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
  `RSEND` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_templates_permissions`
--

LOCK TABLES `yg_templates_permissions` WRITE;
/*!40000 ALTER TABLE `yg_templates_permissions` DISABLE KEYS */;
INSERT INTO `yg_templates_permissions` VALUES (1,1,1,1,1,1,1,1,0,0,0),(2,1,2,1,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `yg_templates_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_templates_properties`
--

DROP TABLE IF EXISTS `yg_templates_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_templates_properties` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OBJECTID` int(11) NOT NULL DEFAULT '0',
  `FOLDER` int(11) NOT NULL DEFAULT '0',
  `NAME` varchar(150) NOT NULL DEFAULT '',
  `IDENTIFIER` text,
  `DESCRIPTION` text,
  `FILENAME` text,
  `PATH` text,
  `FILE` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `OBJECTID` (`OBJECTID`),
  KEY `NAME` (`NAME`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_templates_properties`
--

LOCK TABLES `yg_templates_properties` WRITE;
/*!40000 ALTER TABLE `yg_templates_properties` DISABLE KEYS */;
INSERT INTO `yg_templates_properties` VALUES (1,1,1,'Templates',NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `yg_templates_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_templates_contentareas`
--

DROP TABLE IF EXISTS `yg_templates_contentareas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_templates_contentareas` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TEMPLATE` int(11) NOT NULL,
  `CODE` varchar(100) NOT NULL,
  `NAME` text,
  `ORDER` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_templates_contentareas`
--

LOCK TABLES `yg_templates_contentareas` WRITE;
/*!40000 ALTER TABLE `yg_templates_contentareas` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_templates_contentareas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_templates_tree`
--

DROP TABLE IF EXISTS `yg_templates_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_templates_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LFT` int(11) NOT NULL DEFAULT '0',
  `RGT` int(11) NOT NULL DEFAULT '0',
  `VERSIONPUBLISHED` int(11) NOT NULL DEFAULT '0',
  `MOVED` int(11) NOT NULL DEFAULT '0',
  `TITLE` text,
  `LEVEL` int(11) NOT NULL DEFAULT '0',
  `PARENT` int(11) NOT NULL DEFAULT '0',
  `PNAME` text,
  PRIMARY KEY (`ID`),
  KEY `LFT` (`LFT`,`RGT`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_templates_tree`
--

LOCK TABLES `yg_templates_tree` WRITE;
/*!40000 ALTER TABLE `yg_templates_tree` DISABLE KEYS */;
INSERT INTO `yg_templates_tree` VALUES (1,1,2,0,0,'',1,0,NULL);
/*!40000 ALTER TABLE `yg_templates_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_user`
--

DROP TABLE IF EXISTS `yg_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_user` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LOGIN` varchar(85) NOT NULL DEFAULT '',
  `PASSWORD` varchar(85) NOT NULL DEFAULT '',
  `LANG` int(11) NOT NULL DEFAULT '1',
  `ACTIVE` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_user`
--

LOCK TABLES `yg_user` WRITE;
/*!40000 ALTER TABLE `yg_user` DISABLE KEYS */;
INSERT INTO `yg_user` VALUES (1,'admin@example.com','$2a$08$PHOeZ/LK5tJuxKb0CZHkiOyfaY90kbsF95ni1a1nv6yBXAzBCAda2',1,1),(2,'anonymous','',1,1);
/*!40000 ALTER TABLE `yg_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_user_lnk_usergroups`
--

DROP TABLE IF EXISTS `yg_user_lnk_usergroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_user_lnk_usergroups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UID` int(11) NOT NULL DEFAULT '0',
  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `UID` (`UID`,`USERGROUPID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_user_lnk_usergroups`
--

LOCK TABLES `yg_user_lnk_usergroups` WRITE;
/*!40000 ALTER TABLE `yg_user_lnk_usergroups` DISABLE KEYS */;
INSERT INTO `yg_user_lnk_usergroups` VALUES (1,1,1),(2,2,2);
/*!40000 ALTER TABLE `yg_user_lnk_usergroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_user_properties`
--

DROP TABLE IF EXISTS `yg_user_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_user_properties` (
  `UID` int(11) NOT NULL DEFAULT '0',
  `TITEL` text NOT NULL,
  `ANMERKUNG` text NOT NULL,
  `NAME` varchar(85) NOT NULL DEFAULT '',
  `VORNAME` varchar(85) NOT NULL DEFAULT '',
  `EMAIL` varchar(85) NOT NULL DEFAULT '',
  `FIRMA` varchar(85) NOT NULL DEFAULT '',
  `ABTEILUNG` varchar(200) NOT NULL DEFAULT '',
  `PHONE` varchar(85) NOT NULL DEFAULT '',
  `FAX` varchar(85) NOT NULL DEFAULT '',
  `ANREDE` varchar(80) NOT NULL DEFAULT '',
  `FLAGS` text NOT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_user_properties`
--

LOCK TABLES `yg_user_properties` WRITE;
/*!40000 ALTER TABLE `yg_user_properties` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_user_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_user_props`
--

DROP TABLE IF EXISTS `yg_user_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_user_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `VISIBLE` int(11) NOT NULL DEFAULT '1',
  `READONLY` int(11) NOT NULL DEFAULT '0',
  `TYPE` text NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=128 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_user_props`
--

LOCK TABLES `yg_user_props` WRITE;
/*!40000 ALTER TABLE `yg_user_props` DISABLE KEYS */;
INSERT INTO `yg_user_props` VALUES (106,'TXT_COMPANY','COMPANY',0,1,'TEXT',9999),(107,'TXT_DEPARTMENT','DEPARTMENT',0,1,'TEXT',9999),(108,'TXT_FIRSTNAME','FIRSTNAME',0,1,'TEXT',9999),(109,'TXT_LASTNAME','LASTNAME',0,1,'TEXT',9999),(110,'TXT_PHONE','PHONE',0,1,'TEXT',9999),(111,'TXT_FAX','FAX',0,1,'TEXT',9999),(112,'TXT_MOBILE','MOBILE',0,1,'TEXT',9999),(113,'TXT_WEBSITE','WEBSITE',0,1,'TEXT',9999),(114,'TXT_PROFILEPICTURE','PROFILEPICTURE',0,1,'PROFILEPICTURE',9999),(115,'TXT_LOGIN_EMAIL','EMAIL',0,1,'TEXT',9999),(116,'TXT_TIMEZONE','TIMEZONE',0,1,'TEXT',9999),(117,'TXT_WEEKSTART','WEEKSTART',0,1,'TEXT',9999),(118,'TXT_DATEFORMAT','DATEFORMAT',0,1,'TEXT',9999),(119,'TXT_TIMEFORMAT','TIMEFORMAT',0,1,'TEXT',9999),(127,'TXT_OBJECT_PROPERTIES','PROPERTIES_HEADLINE_ID',1,1,'HEADLINE',1);
/*!40000 ALTER TABLE `yg_user_props` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_user_propslv`
--

DROP TABLE IF EXISTS `yg_user_propslv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_user_propslv` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `VALUE` varchar(50) NOT NULL,
  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`ID`),
  KEY `LISTORDER` (`LISTORDER`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_user_propslv`
--

LOCK TABLES `yg_user_propslv` WRITE;
/*!40000 ALTER TABLE `yg_user_propslv` DISABLE KEYS */;
/*!40000 ALTER TABLE `yg_user_propslv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_user_propsv`
--

DROP TABLE IF EXISTS `yg_user_propsv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_user_propsv` (
  `OID` int(11) NOT NULL DEFAULT '0',
  `COMPANY` text,
  `DEPARTMENT` text,
  `FIRSTNAME` text,
  `LASTNAME` text,
  `PHONE` text,
  `FAX` text,
  `MOBILE` text,
  `WEBSITE` text,
  `PROFILEPICTURE` text,
  `EMAIL` text,
  `TIMEZONE` text,
  `WEEKSTART` text,
  `DATEFORMAT` text,
  `TIMEFORMAT` text,
  `PROPERTIES_HEADLINE_ID` text,
  PRIMARY KEY (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_user_propsv`
--

LOCK TABLES `yg_user_propsv` WRITE;
/*!40000 ALTER TABLE `yg_user_propsv` DISABLE KEYS */;
INSERT INTO `yg_user_propsv` VALUES (1,'','','Dude','Administrator','','','','',NULL,'admin@example.com','Europe/Berlin','1','dd.mm.YYYY','24',NULL),(2,NULL,NULL,'Anonymous',NULL,NULL,NULL,NULL,NULL,NULL,'anonymous',NULL,NULL,'dd.mm.YYYY','24',NULL);
/*!40000 ALTER TABLE `yg_user_propsv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yg_version`
--


DROP TABLE IF EXISTS `yg_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_version` (
  `VERSION` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yg_version`
--

LOCK TABLES `yg_version` WRITE;
/*!40000 ALTER TABLE `yg_version` DISABLE KEYS */;
INSERT INTO `yg_version` VALUES (11400);
/*!40000 ALTER TABLE `yg_version` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


--
-- Table structure for table `yg_user_tokens`
--

DROP TABLE IF EXISTS `yg_user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yg_user_tokens` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UID` int(11) NOT NULL,
  `TOKEN` text NOT NULL,
  `TS` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
/*!40101 SET character_set_client = @saved_cs_client */;


-- Dump completed on 2012-01-12 16:47:00
