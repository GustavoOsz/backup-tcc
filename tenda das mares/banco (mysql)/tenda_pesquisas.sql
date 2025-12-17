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
-- Table structure for table `pesquisas`
--

DROP TABLE IF EXISTS `pesquisas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesquisas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `conteudo` text,
  `imagem` varchar(255) DEFAULT NULL,
  `data` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesquisas`
--

LOCK TABLES `pesquisas` WRITE;
/*!40000 ALTER TABLE `pesquisas` DISABLE KEYS */;
INSERT INTO `pesquisas` VALUES (7,3,'Oque é a Umbanda?','Umbanda é uma religião afro-brasileira que sintetiza o culto aos Orixás e aos demais elementos das religiões africanas, em especial Iorubá e bantu com indígenas e cristãs, porém sem ser definida por eles.\r\n\r\nEstruturada como religião no início do século XX em São Gonçalo, Rio de Janeiro, a partir do sincretismo entre candomblé, o catolicismo e o espiritismo que já se vinha operando ao longo do final do século XIX em quase todo o Brasil. É considerada uma \"religião brasileira por excelência\" caracterizada pela síntese entre a tradição dos orixás africanos, os santos católicos e os espíritos tradicionais de origem indígena. Atualmente, os seus adeptos também são considerados enquanto povos e comunidades tradicionais de matrizes africanas.\r\n\r\nO dia 15 de novembro é considerado a data do surgimento da Umbanda como religião organizada, e foi oficializado no Brasil em 18 de maio de 2012 pela Lei 12.644. Em 8 de novembro de 2016, após estudos do Instituto Rio Patrimônio da Humanidade (IRPH), a umbanda foi incluída na lista de patrimônios imateriais do Rio de Janeiro por meio de decreto.\r\n\r\nEtimologia\r\n\"Umbanda\" ou \"Embanda\" são oriundos da língua quimbunda de Angola, significando \"magia\", \"arte de curar\". Há também a suposição de uma origem em um mantra na língua adâmica cujo significado seria \"conjunto das leis divinas\" ou \"deus ao nosso lado\".\r\n\r\nTambém era conhecida a palavra \"mbanda\" significando “a arte de curar” ou “o culto pelo qual o sacerdote curava”, sendo que \"mbanda\" quer dizer “o Além, onde moram os espíritos”.\r\n\r\nJá as vertentes caracterizadas pela negação de alguns elementos africanos, como a Umbanda Branca, declarou após o I Congresso do Espiritismo de Umbanda de 1941 que \"Umbanda\" vinha das palavras do sânscrito aum e bhanda, termos que foram traduzidos como \"o limite no ilimitado\", \"Princípio divino, luz radiante, a fonte da vida eterna, evolução constante\".',NULL,'2025-08-20');
/*!40000 ALTER TABLE `pesquisas` ENABLE KEYS */;
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
