-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: nicosport
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
-- Current Database: `nicosport`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `nicosport` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `nicosport`;

--
-- Table structure for table `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_key` varchar(40) NOT NULL,
  `fecha` varchar(12) NOT NULL,
  `player_id` int(11) NOT NULL,
  `estado` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asis` (`cat_key`,`fecha`,`player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencias`
--

LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_key` varchar(40) DEFAULT NULL,
  `label` varchar(80) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `core` tinyint(4) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  `foto` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat_key` (`cat_key`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (118,'sub6','Sub 6','Iniciación (4 a 6 años) — Ludotécnica',1,1,NULL),(119,'sub12','Sub 12','Desarrollo (10 a 12 años) — Formativa',1,2,NULL),(120,'sub16','Sub 16','Competición (14 a 16 años) — Alto rendimiento',1,3,NULL),(121,'submayor','Sub Mayor',' Tecnificación — Especialización',1,4,NULL);
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comentarios_padres`
--

DROP TABLE IF EXISTS `comentarios_padres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comentarios_padres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `padre_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `es_anonimo` tinyint(1) DEFAULT 0,
  `estado` varchar(20) DEFAULT 'nuevo',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentarios_padres`
--

LOCK TABLES `comentarios_padres` WRITE;
/*!40000 ALTER TABLE `comentarios_padres` DISABLE KEYS */;
/*!40000 ALTER TABLE `comentarios_padres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comprobantes`
--

DROP TABLE IF EXISTS `comprobantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comprobantes` (
  `id` bigint(20) NOT NULL,
  `padre_id` int(11) DEFAULT NULL,
  `padre_nombre` varchar(150) DEFAULT NULL,
  `concepto` varchar(80) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` varchar(12) DEFAULT NULL,
  `referencia` varchar(150) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `comprobante` longtext DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'pendiente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comprobantes`
--

LOCK TABLES `comprobantes` WRITE;
/*!40000 ALTER TABLE `comprobantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `comprobantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuraciones`
--

DROP TABLE IF EXISTS `configuraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuraciones` (
  `clave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuraciones`
--

LOCK TABLES `configuraciones` WRITE;
/*!40000 ALTER TABLE `configuraciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `configuraciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos`
--

DROP TABLE IF EXISTS `documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documentos` (
  `doc_key` varchar(40) NOT NULL,
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`doc_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos`
--

LOCK TABLES `documentos` WRITE;
/*!40000 ALTER TABLE `documentos` DISABLE KEYS */;
INSERT INTO `documentos` VALUES ('eval_global','[{\"id\":1,\"catKey\":\"sub6\",\"name\":\"Categor\\u00eda Sub 6 \",\"desc\":\"Eficiencia Ludot\\u00e9cnica\",\"value\":96},{\"id\":2,\"catKey\":\"sub12\",\"name\":\"Categor\\u00eda Sub 12 \",\"desc\":\"Rendimiento de Torneo\",\"value\":94},{\"id\":3,\"catKey\":\"sub16\",\"name\":\"Categor\\u00eda Sub 16 \",\"desc\":\"Despliegue T\\u00e1ctico\",\"value\":89},{\"id\":4,\"catKey\":\"submayor\",\"name\":\"Sub Mayor \",\"desc\":\"Din\\u00e1mica y Presi\\u00f3n\",\"value\":91},{\"id\":1004,\"catKey\":\"catmuue1wnx\",\"name\":\"Categor\\u00eda lhg\\u00f1l\\u00f1g\",\"desc\":\"Rendimiento General\",\"value\":100}]'),('torneo','[{\"faseActual\":\"ninguna\",\"tabla\":[{\"nombre\":\"Nico Sport ANS\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0}],\"ultimoResultado\":{\"golesNico\":0,\"golesRival\":0,\"rival\":\"Pr\\u00f3ximo\",\"goleadores\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"},\"proximoPartido\":{\"fecha\":\"Pr\\u00f3ximo\",\"condicion\":\"Pr\\u00f3ximo\",\"rival\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"},\"key\":\"sub6\",\"label\":\"Sub 6\",\"descripcion\":\"Iniciaci\\u00f3n (4 a 6 a\\u00f1os) \\u2014 Ludot\\u00e9cnica\"},{\"faseActual\":\"campeon\",\"tabla\":[{\"nombre\":\"Nico Sport ANS\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0}],\"ultimoResultado\":{\"golesNico\":0,\"golesRival\":0,\"rival\":\"Pr\\u00f3ximo\",\"goleadores\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"},\"proximoPartido\":{\"fecha\":\"Pr\\u00f3ximo\",\"condicion\":\"Pr\\u00f3ximo\",\"rival\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"},\"key\":\"sub12\",\"label\":\"Sub 12\",\"descripcion\":\"Desarrollo (10 a 12 a\\u00f1os) \\u2014 Formativa\"},{\"faseActual\":\"ninguna\",\"tabla\":[{\"nombre\":\"Nico Sport ANS\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0}],\"ultimoResultado\":{\"golesNico\":0,\"golesRival\":0,\"rival\":\"Pr\\u00f3ximo\",\"goleadores\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"},\"proximoPartido\":{\"fecha\":\"Pr\\u00f3ximo\",\"condicion\":\"Pr\\u00f3ximo\",\"rival\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"},\"key\":\"sub16\",\"label\":\"Sub 16\",\"descripcion\":\"Competici\\u00f3n (14 a 16 a\\u00f1os) \\u2014 Alto rendimiento\"},{\"faseActual\":\"ninguna\",\"tabla\":[{\"nombre\":\"Nico Sport ANS\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0}],\"ultimoResultado\":{\"golesNico\":0,\"golesRival\":0,\"rival\":\"Pr\\u00f3ximo\",\"goleadores\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"},\"proximoPartido\":{\"fecha\":\"Pr\\u00f3ximo\",\"condicion\":\"Pr\\u00f3ximo\",\"rival\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"},\"key\":\"submayor\",\"label\":\"Sub Mayor\",\"descripcion\":\" Tecnificaci\\u00f3n \\u2014 Especializaci\\u00f3n\"},{\"key\":\"catmuue1wnx\",\"label\":\"lhg\\u00f1l\\u00f1g\",\"descripcion\":\"\",\"faseActual\":\"ninguna\",\"tabla\":[{\"nombre\":\"Nico Sport ANS\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0},{\"nombre\":\"Pr\\u00f3ximo\",\"pj\":0,\"pg\":0,\"pe\":0,\"pp\":0,\"gf\":0,\"gc\":0,\"pts\":0}],\"ultimoResultado\":{\"golesNico\":0,\"golesRival\":0,\"rival\":\"Pr\\u00f3ximo\",\"goleadores\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"},\"proximoPartido\":{\"fecha\":\"Pr\\u00f3ximo\",\"condicion\":\"Pr\\u00f3ximo\",\"rival\":\"Pr\\u00f3ximo\",\"sede\":\"Pr\\u00f3ximo\"}}]');
/*!40000 ALTER TABLE `documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `encuestas`
--

DROP TABLE IF EXISTS `encuestas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encuestas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `padre` varchar(150) DEFAULT NULL,
  `fecha` varchar(20) DEFAULT NULL,
  `aspectos` text DEFAULT NULL,
  `gusta_mas` text DEFAULT NULL,
  `mejorar` text DEFAULT NULL,
  `crecimiento` text DEFAULT NULL,
  `crecimiento_why` text DEFAULT NULL,
  `recomienda` text DEFAULT NULL,
  `recomienda_why` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `encuestas`
--

LOCK TABLES `encuestas` WRITE;
/*!40000 ALTER TABLE `encuestas` DISABLE KEYS */;
INSERT INTO `encuestas` VALUES (1,'Abdiel','08/30/2026','[4,4,4,4,4,4,4,4,4,4,4]','','','','','Sí','','');
/*!40000 ALTER TABLE `encuestas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluaciones`
--

DROP TABLE IF EXISTS `evaluaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_key` varchar(40) NOT NULL,
  `player_id` int(11) NOT NULL,
  `fecha` varchar(20) DEFAULT '',
  `tecnicos` text DEFAULT NULL,
  `tacticos` text DEFAULT NULL,
  `fisicos` text DEFAULT NULL,
  `actitudinales` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_eval` (`cat_key`,`player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluaciones`
--

LOCK TABLES `evaluaciones` WRITE;
/*!40000 ALTER TABLE `evaluaciones` DISABLE KEYS */;
INSERT INTO `evaluaciones` VALUES (1,'sub16',764583,'08/30/2026','[5,5,4,4,5,5,3,2,3]','[4,4,4,3,4,3]','[5,3,4,4,5]','[5,5,5,5,5,5]'),(3,'sub12',2,'09/01/2026','[1,0,0,0,0,0,0,0,0]','[0,0,0,0,0,0]','[0,0,0,0,0]','[0,0,0,0,0,0]'),(5,'sub6',20,'09/01/2026','[3,0,0,0,0,0,0,0,0]','[0,0,0,0,0,0]','[0,0,0,0,0]','[0,0,0,0,0,0]');
/*!40000 ALTER TABLE `evaluaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jugadores`
--

DROP TABLE IF EXISTS `jugadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jugadores` (
  `id` int(11) NOT NULL,
  `cat_key` varchar(40) NOT NULL,
  `name` varchar(150) NOT NULL,
  `dorsal` int(11) NOT NULL,
  `pos` varchar(10) DEFAULT '',
  `foto` longtext DEFAULT NULL,
  PRIMARY KEY (`cat_key`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jugadores`
--

LOCK TABLES `jugadores` WRITE;
/*!40000 ALTER TABLE `jugadores` DISABLE KEYS */;
INSERT INTO `jugadores` VALUES (1,'sub12','E. Morales',1,'POR',NULL),(2,'sub12','C. Herrera',2,'DEF',NULL),(3,'sub12','J. Rivera',3,'MED',NULL),(4,'sub12','M. Aguirre',4,'MED',NULL),(5,'sub12','A. Díaz',5,'DEL',NULL),(6,'sub12','F. Silva',6,'DEL',NULL),(7,'sub12','L. Campos',7,'DEF',NULL),(8,'sub12','S. Ramos',8,'MED',NULL),(9,'sub12','D. Paredes',9,'DEL',NULL),(10,'sub12','K. Nuñez',10,'POR',NULL),(11,'sub12','R. Castillo',11,'DEF',NULL),(12,'sub12','G. Mendoza',12,'MED',NULL),(13,'sub12','H. Rojas',13,'DEL',NULL),(14,'sub12','B. Montero',14,'DEF',NULL),(15,'sub12','V. Quedo',15,'MED',NULL),(16,'sub12','J. León',16,'DEL',NULL),(17,'sub12','P. Sosa',17,'DEF',NULL),(18,'sub12','N. Garza',18,'MED',NULL),(19,'sub12','T. Ponce',19,'DEL',NULL),(20,'sub12','W. Cordero',20,'POR',NULL),(63389,'sub12','Juan',90,'MED',NULL),(206047,'sub12','jakdjad',24,'MED',NULL),(377510,'sub12','kdkkds',22,'MED',NULL),(610893,'sub12','fddf',21,'POR',NULL),(664031,'sub12','hsjdhs',23,'MED',NULL),(1,'sub16','G. Vargas',1,'POR',''),(2,'sub16','K. Ramos',2,'DEF',NULL),(3,'sub16','R. Delgado',3,'DEF',NULL),(4,'sub16','B. Martínez',4,'MED',NULL),(5,'sub16','H. Salazar',5,'DEL',NULL),(6,'sub16','N. Vega',6,'DEL',NULL),(7,'sub16','J. Alveo',7,'MED',NULL),(8,'sub16','S. Bultrón',8,'DEF',NULL),(9,'sub16','L. Samaniego',9,'DEL',NULL),(10,'sub16','D. Quintero',10,'MED',NULL),(11,'sub16','M. Araúz',11,'POR',NULL),(12,'sub16','C. Espinoza',12,'DEF',NULL),(13,'sub16','A. Batista',13,'MED',NULL),(14,'sub16','E. Becker',14,'DEL',NULL),(15,'sub16','O. Jiménez',15,'DEF',NULL),(16,'sub16','F. Wong',16,'MED',NULL),(17,'sub16','I. Pimentel',17,'DEL',NULL),(18,'sub16','Y. Mosquera',18,'DEF',NULL),(19,'sub16','X. Valdés',19,'MED',NULL),(20,'sub16','Z. Guerra',20,'DEL',NULL),(1,'sub6','M. Aguirre',1,'POR','data:image/jpeg;base64,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'),(2,'sub6','D. Castro',2,'DEF','data:image/jpeg;base64,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'),(3,'sub6','S. Pérez',3,'DEF','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT4lR6G4XYRzv3EmSMKGnQZnklRxJe2OWZIPu5ov-0Ejw&s=10'),(4,'sub6','A. Ruiz',4,'MED',NULL),(5,'sub6','L. Torres',5,'DEL',NULL),(6,'sub6','J. Gómez',6,'DEL',NULL),(7,'sub6','K. Morales',7,'MED',NULL),(8,'sub6','E. Ríos',8,'DEF',NULL),(9,'sub6','C. Vega',9,'DEL',NULL),(10,'sub6','B. Soto',10,'MED',NULL),(11,'sub6','R. Pinto',11,'POR',NULL),(12,'sub6','G. Navas',12,'DEF',NULL),(13,'sub6','H. Molina',13,'MED',NULL),(14,'sub6','J. Cedeño',14,'DEL',NULL),(15,'sub6','F. Benítez',15,'DEF',NULL),(16,'sub6','T. Ortega',16,'MED',NULL),(17,'sub6','N. Duarte',17,'DEL',NULL),(18,'sub6','P. Acuña',18,'MED',NULL),(19,'sub6','I. Vargas',19,'DEF',NULL),(20,'sub6','O. Espino',20,'DEL',NULL),(840716,'sub6','kkdk',26,'POR',NULL),(1,'submayor','V. Córdoba',1,'POR',''),(2,'submayor','O. Paredes',2,'DEF',NULL),(3,'submayor','I. Navarro',3,'DEF',NULL),(4,'submayor','E. Castillo',4,'MED',NULL),(5,'submayor','T. Meza',5,'MED',NULL),(6,'submayor','P. Fuentes',6,'DEL',NULL),(7,'submayor','R. Jaramillo',7,'DEL',NULL),(8,'submayor','K. Grimaldo',8,'MED',NULL),(9,'submayor','L. Melgar',9,'DEF',NULL),(10,'submayor','J. Caballero',10,'POR',NULL),(11,'submayor','S. Bernal',11,'DEF',NULL),(12,'submayor','A. Cárdenas',12,'MED',NULL),(13,'submayor','D. Hurtado',13,'DEL',NULL),(14,'submayor','F. Cedeño',14,'DEF',NULL),(15,'submayor','M. Patiño',15,'MED',NULL),(16,'submayor','G. Solís',16,'DEL',NULL),(17,'submayor','H. Macías',17,'DEF',NULL),(18,'submayor','N. Broce',18,'MED',NULL),(19,'submayor','B. Ureña',19,'DEL',NULL),(20,'submayor','J. Vergara',20,'MED',NULL);
/*!40000 ALTER TABLE `jugadores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jugadores_reciclados`
--

DROP TABLE IF EXISTS `jugadores_reciclados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jugadores_reciclados` (
  `cat_key` varchar(40) NOT NULL,
  `dorsal` int(11) NOT NULL,
  `data` longtext DEFAULT NULL,
  `guardado_en` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`cat_key`,`dorsal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jugadores_reciclados`
--

LOCK TABLES `jugadores_reciclados` WRITE;
/*!40000 ALTER TABLE `jugadores_reciclados` DISABLE KEYS */;
INSERT INTO `jugadores_reciclados` VALUES ('sub12',90,'{\"id\":63389,\"catKey\":\"sub12\",\"name\":\"juan1\",\"dorsal\":90,\"pos\":\"MED\",\"foto\":null,\"guardadoEn\":\"2026-08-30T20:44:01.709Z\"}','2026-08-30T20:44:01.709Z'),('sub6',21,'{\"id\":105914,\"catKey\":\"sub6\",\"name\":\"niko\",\"dorsal\":21,\"pos\":\"MED\",\"foto\":null,\"guardadoEn\":\"2026-10-04T18:18:29.353Z\"}','2026-10-04T18:18:29.353Z'),('sub6',22,'{\"id\":524306,\"catKey\":\"sub6\",\"name\":\"dsdsdssd\",\"dorsal\":22,\"pos\":\"MED\",\"foto\":null,\"guardadoEn\":\"2026-10-04T18:18:26.408Z\"}','2026-10-04T18:18:26.408Z'),('sub6',23,'{\"id\":918930,\"catKey\":\"sub6\",\"name\":\"Nito\",\"dorsal\":23,\"pos\":\"MED\",\"foto\":null,\"guardadoEn\":\"2026-10-04T18:18:23.800Z\"}','2026-10-04T18:18:23.800Z'),('sub6',24,'{\"id\":765064,\"catKey\":\"sub6\",\"name\":\"dsdsdsd\",\"dorsal\":24,\"pos\":\"MED\",\"foto\":null,\"guardadoEn\":\"2026-10-04T18:18:21.769Z\"}','2026-10-04T18:18:21.769Z'),('sub6',25,'{\"id\":938459,\"catKey\":\"sub6\",\"name\":\"Juan\",\"dorsal\":25,\"pos\":\"POR\",\"foto\":null,\"guardadoEn\":\"2026-10-04T18:18:18.737Z\"}','2026-10-04T18:18:18.737Z');
/*!40000 ALTER TABLE `jugadores_reciclados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_club`
--

DROP TABLE IF EXISTS `pagos_club`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos_club` (
  `id` bigint(20) NOT NULL,
  `padre_id` int(11) DEFAULT NULL,
  `padre_nombre` varchar(150) DEFAULT NULL,
  `concepto` varchar(80) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` varchar(12) DEFAULT NULL,
  `referencia` varchar(150) DEFAULT NULL,
  `metodo` varchar(20) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_club`
--

LOCK TABLES `pagos_club` WRITE;
/*!40000 ALTER TABLE `pagos_club` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos_club` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_padre`
--

DROP TABLE IF EXISTS `pagos_padre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos_padre` (
  `id` bigint(20) NOT NULL,
  `padre_id` int(11) DEFAULT NULL,
  `concepto` varchar(80) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` varchar(12) DEFAULT NULL,
  `referencia` varchar(150) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `comprobante` longtext DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'pendiente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_padre`
--

LOCK TABLES `pagos_padre` WRITE;
/*!40000 ALTER TABLE `pagos_padre` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos_padre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_plataforma`
--

DROP TABLE IF EXISTS `pagos_plataforma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos_plataforma` (
  `id` bigint(20) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `referencia` varchar(150) DEFAULT NULL,
  `fecha` varchar(12) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `comprobante` longtext DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'pendiente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_plataforma`
--

LOCK TABLES `pagos_plataforma` WRITE;
/*!40000 ALTER TABLE `pagos_plataforma` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos_plataforma` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player_stats`
--

DROP TABLE IF EXISTS `player_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_key` varchar(40) NOT NULL,
  `player_id` int(11) NOT NULL,
  `goles` int(11) DEFAULT 0,
  `asistencias` int(11) DEFAULT 0,
  `mvp` int(11) DEFAULT 0,
  `updated_at` varchar(40) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stats` (`cat_key`,`player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_stats`
--

LOCK TABLES `player_stats` WRITE;
/*!40000 ALTER TABLE `player_stats` DISABLE KEYS */;
INSERT INTO `player_stats` VALUES (2,'sub6',1,0,0,0,'2026-08-30T19:57:32.000Z'),(6,'sub16',764583,2,1,1,'2026-08-30T22:44:58.522Z'),(14,'sub12',1,2,2,2,'2026-09-01T14:47:42.049Z'),(18,'sub6',20,1,1,1,'2026-09-01T20:18:22.329Z'),(29,'sub6',2,2,2,2,'2026-10-04T18:18:14.585Z');
/*!40000 ALTER TABLE `player_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `cargo` varchar(150) DEFAULT NULL,
  `licencia` varchar(150) DEFAULT NULL,
  `exp` varchar(150) DEFAULT NULL,
  `especialidad` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `foto_icon` text DEFAULT NULL,
  `color` varchar(120) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,'Director Técnico Principal & Fundador','Director Técnico Principal & Fundador','Licencia A FEPAFUT','18 Años de Experiencia','Financiera','Fundador de la Academia Nico Sport en 2008. Ha formado a más de 500 jóvenes deportistas y obtenido múltiples títulos en torneos locales.','fa-user-tie','from-nico-orange via-red-600 to-amber-600',1),(2,'Prof. Luis Muñoz','Preparador Físico & psicológico','Grado en Ciencias del Deporte','10 Años de Experiencia','Resistencia Aeróbica, Potencia y Prevención de Lesiones','Especialista en acondicionamiento físico adaptado a fútbol base y juvenil.','fa-stopwatch-20','from-nico-green to-emerald-700',2),(3,'Abdiel Aguirre','Entrenador de Táctico','Próximamente...','9 Años de Experiencia en el deporte','Reflejos, Juego Aéreo y Salida con los Pies','Jugador amateur enfocado en perfeccionar la técnica de juego, posicionamiento en la cancha y liderazgo.','fa-chalkboard-user','from-nico-blue to-cyan-700',3),(4,'Fernado','Psicóloga Deportiva & Formación Integral','Próximamente...','5 Años de Experiencia','Gestión de la Frustración, Trabajo en Equipo y Concentración','Encargada del desarrollo mental y emocional de los alumnos.','fa-brain','from-purple-600 to-indigo-800',4),(884226,'Nikooo','Entrenador Sub-12','Por definir...','Nueva incorporación','Táctica base y Formación','Nuevo entrenador asignado a la categoría Sub-12. (Edita este texto con el lápiz naranja)','fa-user-tie','from-nico-blue to-cyan-700',5);
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tarifas`
--

DROP TABLE IF EXISTS `tarifas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mensualidad` decimal(10,2) DEFAULT 15.00,
  `inscripcion` decimal(10,2) DEFAULT 15.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifas`
--

LOCK TABLES `tarifas` WRITE;
/*!40000 ALTER TABLE `tarifas` DISABLE KEYS */;
/*!40000 ALTER TABLE `tarifas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `textos`
--

DROP TABLE IF EXISTS `textos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `textos` (
  `clave` varchar(80) NOT NULL,
  `html` longtext DEFAULT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `textos`
--

LOCK TABLES `textos` WRITE;
/*!40000 ALTER TABLE `textos` DISABLE KEYS */;
INSERT INTO `textos` VALUES ('catmtia360v-desc','Categoría personalizada de la Academia Nico Sport.&amp;nbsp;'),('catmut6dw4j-badge',''),('catmut6dw4j-desc',''),('cuotaInscripcion','15'),('cuotaMensual','15'),('gira-overlay1','Evento Especial'),('hero-lema','DISCIPLINA • RESPETO • eSFUERZO • PASIÓN'),('insignia-franjas','El naranja y rojo representan la energía y pasión en la cancha; el negro la seriedad, fuerza y disciplina.'),('media-title','video oficial'),('staff-intro',' dedicados a la enseñanza técnica y formación en valores.'),('stat-copas','15'),('sub6-cancha','<strong class=\"text-white\">Cancha:</strong> <span x-text=\"cat.cancha\"></span>'),('sub6-desc','Coordinación motriz primaria, psicomotricidad básica, lateralidad y primera adaptación divertida al balón mediante dinámicas ludotécnicas.'),('sub6-horario','<strong class=\"text-nico-green\">Horarios de Entreno:</strong> <span x-text=\"cat.horario\"></span>'),('sub6-torneos','<strong class=\"text-nico-orange\">Torneos:</strong> <span x-text=\"cat.torneos\"></span>'),('uniforme-overlay1','G&amp;amp;M Deportes S.A.');
/*!40000 ALTER TABLE `textos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trofeos_custom`
--

DROP TABLE IF EXISTS `trofeos_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trofeos_custom` (
  `id` varchar(60) NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `cat_key` varchar(40) DEFAULT NULL,
  `categoria` varchar(120) DEFAULT NULL,
  `anio` varchar(10) DEFAULT NULL,
  `icono` varchar(60) DEFAULT NULL,
  `color` varchar(60) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `evaluacion` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trofeos_custom`
--

LOCK TABLES `trofeos_custom` WRITE;
/*!40000 ALTER TABLE `trofeos_custom` DISABLE KEYS */;
INSERT INTO `trofeos_custom` VALUES ('custom-1791144259579','z<sza','sub6','Sub-6','','fa-trophy','text-amber-400','Campeonato obtenido por el equipo.','{\"rendimientoGlobal\":100,\"posesion\":\"-\",\"efectividadPases\":\"-\",\"golesAFavor\":0,\"golesEnContra\":0,\"disciplina\":\"Excelente\",\"resumenTactico\":\"Informe tcnico pendiente.\"}');
/*!40000 ALTER TABLE `trofeos_custom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trofeos_editados`
--

DROP TABLE IF EXISTS `trofeos_editados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trofeos_editados` (
  `id` varchar(60) NOT NULL,
  `data` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trofeos_editados`
--

LOCK TABLES `trofeos_editados` WRITE;
/*!40000 ALTER TABLE `trofeos_editados` DISABLE KEYS */;
/*!40000 ALTER TABLE `trofeos_editados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trofeos_ocultos`
--

DROP TABLE IF EXISTS `trofeos_ocultos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trofeos_ocultos` (
  `id` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trofeos_ocultos`
--

LOCK TABLES `trofeos_ocultos` WRITE;
/*!40000 ALTER TABLE `trofeos_ocultos` DISABLE KEYS */;
/*!40000 ALTER TABLE `trofeos_ocultos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `hijo_cat` varchar(40) DEFAULT NULL,
  `hijo_id` bigint(20) DEFAULT NULL,
  `cat_key` varchar(40) DEFAULT NULL,
  `cat_label` varchar(80) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `metodo_pago` varchar(20) DEFAULT 'linea',
  `fecha_vencimiento` varchar(12) DEFAULT NULL,
  `bloqueado` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin','$2y$10$kU1kr1dlKWK08KLsTwwvfuCKYG.T4wr.L6WowLhyd1tilZGLg0pFO','admin','ADMIN ',NULL,NULL,NULL,NULL,NULL,'linea',NULL,0),(19,'juan','$2y$10$bUDEP3IQR2RJGpppEzXnJe.8j6F.C9bYhvX3Kxxc1wRiTIYZF6eES','padre','juan','sub6',840716,NULL,NULL,'69210425','linea','2026-10-20',0),(20,'nito','$2y$10$INry4FKY0UR8PMPoObZfaOWcENbmXbr6/POHJZqL7mi9RcIhU197K','padre','sddsd','sub12',206047,NULL,NULL,'69210425','linea',NULL,0);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-08  8:58:33
