-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: tenda
-- ------------------------------------------------------
-- Server version	9.1.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` text,
  `cidade` varchar(50) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('comum','admin') DEFAULT 'comum',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'adm@adm.com',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$10$OPY8z/vi/JomyTHzbIDOleoBrwbM4jNZomAqaFXYVBFYxBLkLC6ki','comum','2025-10-24 00:21:25'),(2,'adm2@adm.com',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$10$20FNnl98KUe6NURNRoSpp.obJYUy4mBuFN.4xHMYdCcRTLbhrgyDu','','2025-10-24 00:21:25'),(3,'adm3@adm.com','Gustavo Oliveira Gomes da Silva','41999369485','R. india 325 ','Curitiba','Pr','32443232','$2y$10$1y54w7OfIW2BtJSfrCfOROyHfoMZdsKszZCTtmLQ2hOIVhsr2.49i','admin','2025-10-24 00:21:25'),(4,'teste@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$10$p2HeSdUR0bV8oGis4QAO2.IfzWjLNLriz06bjgy7cE9lBbCfudcXG','','2025-10-24 00:21:25'),(5,'adm5@adm.com',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$10$FqFL6EgMXeDKze3LfmyW5uyTsnFb8BrLflbItcwknFVSdgBIwpLrG','','2025-10-24 02:39:32'),(6,'adm4@adm.com',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$10$5.BoL7uvU3DcqYCNYdQJJeY2qJqQm1KABEkzL/w16myr/Mkb1z7ES','','2025-10-24 02:39:48'),(7,'osz@osz.com','Gustavo Oliveira Gomes da Silva','41999369485','rua india 325','Curitiba','Pr','32443232','$2y$10$X1ASxRwKSFo7H0M/86bOBOBStNGpT50YHDPt0fQBiF9MmU7i9t1dK','','2025-11-05 13:20:41');
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

-- Dump completed on 2025-11-05 13:17:58
