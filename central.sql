-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: central
-- ------------------------------------------------------
-- Server version	8.0.43

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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `caixa_movimentacoes`
--

DROP TABLE IF EXISTS `caixa_movimentacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caixa_movimentacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caixa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `tipo` enum('SANGRIA','SUPRIMENTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caixa_movimentacoes_caixa_id_foreign` (`caixa_id`),
  KEY `caixa_movimentacoes_user_id_foreign` (`user_id`),
  CONSTRAINT `caixa_movimentacoes_caixa_id_foreign` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `caixa_movimentacoes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `caixas`
--

DROP TABLE IF EXISTS `caixas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caixas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `terminal_id` bigint unsigned DEFAULT NULL,
  `status` enum('aberto','fechado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberto',
  `valor_abertura` decimal(10,2) NOT NULL,
  `valor_fechamento` decimal(10,2) DEFAULT NULL,
  `data_abertura` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_fechamento` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caixas_empresa_id_foreign` (`empresa_id`),
  KEY `caixas_user_id_foreign` (`user_id`),
  KEY `caixas_terminal_id_foreign` (`terminal_id`),
  CONSTRAINT `caixas_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `caixas_terminal_id_foreign` FOREIGN KEY (`terminal_id`) REFERENCES `terminais` (`id`) ON DELETE SET NULL,
  CONSTRAINT `caixas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categoria_contas_a_pagar`
--

DROP TABLE IF EXISTS `categoria_contas_a_pagar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria_contas_a_pagar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#cccccc',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_contas_a_pagar_empresa_id_foreign` (`empresa_id`),
  KEY `categoria_contas_a_pagar_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categoria_contas_a_pagar_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `categoria_contas_a_pagar_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categoria_contas_a_pagar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `margem_lucro` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categorias_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `categorias_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias_produto`
--

DROP TABLE IF EXISTS `categorias_produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_produto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cces`
--

DROP TABLE IF EXISTS `cces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nfe_id` bigint unsigned NOT NULL,
  `sequencia_evento` int NOT NULL,
  `caminho_xml` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caminho_pdf` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cces_nfe_id_foreign` (`nfe_id`),
  CONSTRAINT `cces_nfe_id_foreign` FOREIGN KEY (`nfe_id`) REFERENCES `nfes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf_cnpj` varchar(18) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Inscrição Estadual do cliente',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logradouro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_municipio` int DEFAULT NULL COMMENT 'Código IBGE do município do cliente',
  `estado` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `endereco_completo` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_cpf_cnpj_unique` (`cpf_cnpj`),
  KEY `clientes_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `clientes_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `fornecedor_id` bigint unsigned NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_nota` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `serie_nota` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chave_acesso_nfe` varchar(44) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_emissao` date NOT NULL,
  `data_chegada` date DEFAULT NULL,
  `valor_total_produtos` decimal(10,2) DEFAULT NULL,
  `valor_frete` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_total_nota` decimal(10,2) NOT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `compras_chave_acesso_nfe_unique` (`chave_acesso_nfe`),
  KEY `compras_empresa_id_foreign` (`empresa_id`),
  KEY `compras_fornecedor_id_foreign` (`fornecedor_id`),
  CONSTRAINT `compras_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `compras_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `chave` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configuracoes_chave_unique` (`chave`),
  KEY `configuracoes_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `configuracoes_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuracoes_fiscais_padrao`
--

DROP TABLE IF EXISTS `configuracoes_fiscais_padrao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracoes_fiscais_padrao` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome_perfil` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome para identificar este conjunto de regras. Ex: Padrão Vendas Simples',
  `ncm_padrao` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cfop_padrao` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origem_padrao` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `csosn_padrao` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icms_cst_padrao` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pis_cst_padrao` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cofins_cst_padrao` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `configuracoes_fiscais_padrao_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `configuracoes_fiscais_padrao_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conta_pagamentos`
--

DROP TABLE IF EXISTS `conta_pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conta_pagamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conta_a_pagar_id` bigint unsigned NOT NULL,
  `forma_pagamento_id` bigint unsigned NOT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `data_pagamento` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conta_pagamentos_conta_a_pagar_id_foreign` (`conta_a_pagar_id`),
  KEY `conta_pagamentos_forma_pagamento_id_foreign` (`forma_pagamento_id`),
  KEY `conta_pagamentos_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `conta_pagamentos_conta_a_pagar_id_foreign` FOREIGN KEY (`conta_a_pagar_id`) REFERENCES `contas_a_pagar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conta_pagamentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `conta_pagamentos_forma_pagamento_id_foreign` FOREIGN KEY (`forma_pagamento_id`) REFERENCES `forma_pagamentos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contas_a_pagar`
--

DROP TABLE IF EXISTS `contas_a_pagar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contas_a_pagar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `fornecedor_id` bigint unsigned DEFAULT NULL,
  `categoria_conta_a_pagar_id` bigint unsigned DEFAULT NULL,
  `compra_id` bigint unsigned DEFAULT NULL,
  `forma_pagamento_id` bigint unsigned DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_documento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_emissao` date NOT NULL,
  `data_vencimento` date NOT NULL,
  `valor_total` decimal(15,2) NOT NULL,
  `valor_pago` decimal(15,2) NOT NULL DEFAULT '0.00',
  `data_pagamento` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A Pagar',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contas_a_pagar_empresa_id_foreign` (`empresa_id`),
  KEY `contas_a_pagar_fornecedor_id_foreign` (`fornecedor_id`),
  KEY `contas_a_pagar_compra_id_foreign` (`compra_id`),
  KEY `contas_a_pagar_forma_pagamento_id_foreign` (`forma_pagamento_id`),
  KEY `contas_a_pagar_categoria_conta_a_pagar_id_foreign` (`categoria_conta_a_pagar_id`),
  CONSTRAINT `contas_a_pagar_categoria_conta_a_pagar_id_foreign` FOREIGN KEY (`categoria_conta_a_pagar_id`) REFERENCES `categoria_contas_a_pagar` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contas_a_pagar_compra_id_foreign` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contas_a_pagar_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contas_a_pagar_forma_pagamento_id_foreign` FOREIGN KEY (`forma_pagamento_id`) REFERENCES `forma_pagamentos` (`id`),
  CONSTRAINT `contas_a_pagar_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contas_a_receber`
--

DROP TABLE IF EXISTS `contas_a_receber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contas_a_receber` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parcela_numero` int NOT NULL DEFAULT '1',
  `parcela_total` int NOT NULL DEFAULT '1',
  `valor` decimal(10,2) NOT NULL,
  `valor_recebido` decimal(15,2) NOT NULL DEFAULT '0.00',
  `data_vencimento` date NOT NULL,
  `status` enum('A Receber','Recebido Parcialmente','Recebido','Cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A Receber',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `venda_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contas_a_receber_empresa_id_foreign` (`empresa_id`),
  KEY `contas_a_receber_venda_id_foreign` (`venda_id`),
  KEY `contas_a_receber_cliente_id_foreign` (`cliente_id`),
  CONSTRAINT `contas_a_receber_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contas_a_receber_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contas_a_receber_venda_id_foreign` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cotacao_fornecedor`
--

DROP TABLE IF EXISTS `cotacao_fornecedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotacao_fornecedor` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cotacao_id` bigint unsigned NOT NULL,
  `fornecedor_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cotacao_fornecedor_cotacao_id_foreign` (`cotacao_id`),
  KEY `cotacao_fornecedor_fornecedor_id_foreign` (`fornecedor_id`),
  CONSTRAINT `cotacao_fornecedor_cotacao_id_foreign` FOREIGN KEY (`cotacao_id`) REFERENCES `cotacoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cotacao_fornecedor_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cotacao_produto`
--

DROP TABLE IF EXISTS `cotacao_produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotacao_produto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cotacao_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `quantidade` decimal(15,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cotacao_produto_cotacao_id_foreign` (`cotacao_id`),
  KEY `cotacao_produto_produto_id_foreign` (`produto_id`),
  CONSTRAINT `cotacao_produto_cotacao_id_foreign` FOREIGN KEY (`cotacao_id`) REFERENCES `cotacoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cotacao_produto_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cotacao_respostas`
--

DROP TABLE IF EXISTS `cotacao_respostas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotacao_respostas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cotacao_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `fornecedor_id` bigint unsigned NOT NULL,
  `preco_ofertado` decimal(15,2) DEFAULT NULL,
  `prazo_entrega_dias` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cotacao_respostas_cotacao_id_foreign` (`cotacao_id`),
  KEY `cotacao_respostas_produto_id_foreign` (`produto_id`),
  KEY `cotacao_respostas_fornecedor_id_foreign` (`fornecedor_id`),
  CONSTRAINT `cotacao_respostas_cotacao_id_foreign` FOREIGN KEY (`cotacao_id`) REFERENCES `cotacoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cotacao_respostas_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cotacao_respostas_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cotacoes`
--

DROP TABLE IF EXISTS `cotacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL COMMENT 'Usuário que criou a cotação',
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_cotacao` date NOT NULL,
  `status` enum('aberta','finalizada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberta',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cotacoes_empresa_id_foreign` (`empresa_id`),
  KEY `cotacoes_user_id_foreign` (`user_id`),
  CONSTRAINT `cotacoes_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cotacoes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dados_fiscais_produto`
--

DROP TABLE IF EXISTS `dados_fiscais_produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dados_fiscais_produto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `ncm` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomenclatura Comum do Mercosul (Obrigatório para NF-e/NFC-e)',
  `cest` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código Especificador da Substituição Tributária',
  `origem` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Origem da mercadoria (0: Nacional, 1: Estrangeira, etc.)',
  `cfop` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código Fiscal de Operações e Prestações',
  `icms_cst` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icms_mod_bc` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Modalidade de determinação da BC do ICMS',
  `icms_aliquota` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Alíquota do ICMS em %',
  `icms_reducao_bc` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Percentual de redução da BC do ICMS',
  `pis_cst` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pis_aliquota` decimal(5,2) NOT NULL DEFAULT '0.00',
  `cofins_cst` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cofins_aliquota` decimal(5,2) NOT NULL DEFAULT '0.00',
  `csosn` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dados_fiscais_produto_produto_id_unique` (`produto_id`),
  KEY `dados_fiscais_produto_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `dados_fiscais_produto_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dados_fiscais_produto_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detalhes_item_mercado`
--

DROP TABLE IF EXISTS `detalhes_item_mercado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalhes_item_mercado` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `marca` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ex: Nestlé, Coca-Cola',
  `codigo_barras` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código EAN/UPC, essencial para o PDV com leitor',
  `categoria_id` bigint unsigned DEFAULT NULL,
  `fornecedor_id` bigint unsigned DEFAULT NULL,
  `preco_custo` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Quanto você pagou pelo produto',
  `preco_promocional` decimal(10,2) DEFAULT NULL COMMENT 'Preço para promoções com data definida',
  `data_inicio_promocao` date DEFAULT NULL,
  `data_fim_promocao` date DEFAULT NULL,
  `estoque_atual` decimal(10,3) NOT NULL DEFAULT '0.000',
  `estoque_minimo` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT 'Para relatórios de ponto de pedido',
  `unidade_medida_id` bigint unsigned NOT NULL,
  `controla_validade` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se TRUE, o sistema deve controlar lotes e datas de validade',
  `vendido_por_peso` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se TRUE, o PDV solicitará o peso da balança',
  PRIMARY KEY (`id`),
  UNIQUE KEY `detalhes_item_mercado_codigo_barras_unique` (`codigo_barras`),
  KEY `detalhes_item_mercado_categoria_id_foreign` (`categoria_id`),
  KEY `detalhes_item_mercado_fornecedor_id_foreign` (`fornecedor_id`),
  KEY `detalhes_item_mercado_unidade_medida_id_foreign` (`unidade_medida_id`),
  KEY `detalhes_item_mercado_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `detalhes_item_mercado_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_produto` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalhes_item_mercado_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalhes_item_mercado_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `detalhes_item_mercado_unidade_medida_id_foreign` FOREIGN KEY (`unidade_medida_id`) REFERENCES `unidades_medida` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `razao_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_fantasia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnpj` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `im` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crt` int NOT NULL,
  `ambiente_nfe` tinyint NOT NULL DEFAULT '2',
  `csc_nfe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código de Segurança do Contribuinte para emissão de NFC-e/NF-e',
  `csc_id_nfe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do CSC (geralmente 000001 ou 000002)',
  `nfe_proximo_numero` int DEFAULT NULL,
  `nfce_proximo_numero` int DEFAULT NULL,
  `logradouro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `municipio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_uf` int DEFAULT NULL COMMENT 'Código IBGE da Unidade da Federação',
  `codigo_municipio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificado_a1_path` text COLLATE utf8mb4_unicode_ci,
  `certificado_a1_password` text COLLATE utf8mb4_unicode_ci,
  `nicho_negocio` enum('mercado','oficina','restaurante','loja_roupas') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empresas_cnpj_unique` (`cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `estoque_movimentos`
--

DROP TABLE IF EXISTS `estoque_movimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_movimentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `tipo_movimento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `saldo_anterior` decimal(10,3) NOT NULL,
  `saldo_novo` decimal(10,3) NOT NULL,
  `origem_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origem_id` bigint unsigned NOT NULL,
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estoque_movimentos_empresa_id_foreign` (`empresa_id`),
  KEY `estoque_movimentos_produto_id_foreign` (`produto_id`),
  KEY `estoque_movimentos_user_id_foreign` (`user_id`),
  KEY `estoque_movimentos_origem_type_origem_id_index` (`origem_type`,`origem_id`),
  CONSTRAINT `estoque_movimentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `estoque_movimentos_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `estoque_movimentos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ficha_tecnica_producao`
--

DROP TABLE IF EXISTS `ficha_tecnica_producao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ficha_tecnica_producao` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `produto_acabado_id` bigint unsigned NOT NULL COMMENT 'FK para produtos (tipo = produto_acabado)',
  `materia_prima_id` bigint unsigned NOT NULL COMMENT 'FK para produtos (tipo = materia_prima)',
  `quantidade` decimal(10,4) NOT NULL COMMENT 'Qtd da matéria-prima para 1 unidade do produto acabado',
  `observacoes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ficha_tecnica_producao_empresa_id_foreign` (`empresa_id`),
  KEY `ficha_tecnica_producao_produto_acabado_id_foreign` (`produto_acabado_id`),
  KEY `ficha_tecnica_producao_materia_prima_id_foreign` (`materia_prima_id`),
  CONSTRAINT `ficha_tecnica_producao_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ficha_tecnica_producao_materia_prima_id_foreign` FOREIGN KEY (`materia_prima_id`) REFERENCES `produtos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ficha_tecnica_producao_produto_acabado_id_foreign` FOREIGN KEY (`produto_acabado_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forma_pagamentos`
--

DROP TABLE IF EXISTS `forma_pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forma_pagamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: Cartão de Crédito 30 dias, PIX, Boleto 15 DDL',
  `codigo_sefaz` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código da forma de pagamento para a SEFAZ (tPag)',
  `tipo` enum('a_vista','a_prazo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'a_vista',
  `numero_parcelas` int NOT NULL DEFAULT '1',
  `dias_intervalo` int NOT NULL DEFAULT '30' COMMENT 'Dias de intervalo entre as parcelas',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `forma_pagamentos_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `forma_pagamentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fornecedores`
--

DROP TABLE IF EXISTS `fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fornecedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `razao_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_fantasia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pessoa` enum('fisica','juridica') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'juridica',
  `cpf_cnpj` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` text COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fornecedores_cnpj_unique` (`cpf_cnpj`),
  KEY `fornecedores_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `fornecedores_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `historico_pedidos`
--

DROP TABLE IF EXISTS `historico_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historico_pedidos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `descricao_acao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_acao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pedido_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `historico_pedidos_pedido_id_foreign` (`pedido_id`),
  KEY `historico_pedidos_usuario_id_foreign` (`usuario_id`),
  KEY `historico_pedidos_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `historico_pedidos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `historico_pedidos_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `historico_pedidos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventario_items`
--

DROP TABLE IF EXISTS `inventario_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventario_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `estoque_esperado` decimal(10,3) NOT NULL,
  `quantidade_contada` decimal(10,3) DEFAULT NULL,
  `diferenca` decimal(10,3) NOT NULL DEFAULT '0.000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventario_items_inventario_id_foreign` (`inventario_id`),
  KEY `inventario_items_produto_id_foreign` (`produto_id`),
  CONSTRAINT `inventario_items_inventario_id_foreign` FOREIGN KEY (`inventario_id`) REFERENCES `inventarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventario_items_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventarios`
--

DROP TABLE IF EXISTS `inventarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `data_inicio` timestamp NOT NULL,
  `data_conclusao` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planejado',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventarios_empresa_id_foreign` (`empresa_id`),
  KEY `inventarios_user_id_foreign` (`user_id`),
  CONSTRAINT `inventarios_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventarios_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itens_compra`
--

DROP TABLE IF EXISTS `itens_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itens_compra` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `compra_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned DEFAULT NULL,
  `descricao_item_nota` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ncm` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cfop` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ean` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_custo_nota` decimal(10,4) NOT NULL,
  `preco_entrada` decimal(10,4) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `valor_frete` decimal(10,2) DEFAULT NULL,
  `valor_ipi` decimal(10,2) DEFAULT NULL,
  `valor_icms` decimal(10,2) DEFAULT NULL,
  `valor_pis` decimal(10,2) DEFAULT NULL,
  `valor_cofins` decimal(10,2) DEFAULT NULL,
  `total_item` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `itens_compra_compra_id_foreign` (`compra_id`),
  KEY `itens_compra_produto_id_foreign` (`produto_id`),
  KEY `itens_compra_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `itens_compra_compra_id_foreign` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itens_compra_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itens_compra_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itens_pedido`
--

DROP TABLE IF EXISTS `itens_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itens_pedido` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_unitario_venda` decimal(10,2) NOT NULL COMMENT 'Preço no momento da venda',
  `subtotal` decimal(10,2) NOT NULL,
  `pedido_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `unidade_medida_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `itens_pedido_pedido_id_foreign` (`pedido_id`),
  KEY `itens_pedido_produto_id_foreign` (`produto_id`),
  KEY `itens_pedido_unidade_medida_id_foreign` (`unidade_medida_id`),
  CONSTRAINT `itens_pedido_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itens_pedido_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `itens_pedido_unidade_medida_id_foreign` FOREIGN KEY (`unidade_medida_id`) REFERENCES `unidades_medida` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `natureza_operacoes`
--

DROP TABLE IF EXISTS `natureza_operacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `natureza_operacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cfop` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nfe_items`
--

DROP TABLE IF EXISTS `nfe_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nfe_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nfe_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `numero_item` int NOT NULL,
  `quantidade` decimal(15,4) NOT NULL,
  `valor_unitario` decimal(15,4) NOT NULL,
  `valor_total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nfe_items_nfe_id_foreign` (`nfe_id`),
  KEY `nfe_items_produto_id_foreign` (`produto_id`),
  CONSTRAINT `nfe_items_nfe_id_foreign` FOREIGN KEY (`nfe_id`) REFERENCES `nfes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nfe_items_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nfes`
--

DROP TABLE IF EXISTS `nfes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nfes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `venda_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chave_acesso` varchar(44) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `protocolo_autorizacao` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cce_sequencia_evento` tinyint NOT NULL DEFAULT '1',
  `numero_nfe` int NOT NULL,
  `serie` int NOT NULL,
  `modelo` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '55',
  `ambiente` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caminho_xml` text COLLATE utf8mb4_unicode_ci,
  `caminho_danfe` text COLLATE utf8mb4_unicode_ci,
  `justificativa_cancelamento` text COLLATE utf8mb4_unicode_ci,
  `justificativa_contingencia` text COLLATE utf8mb4_unicode_ci,
  `mensagem_erro` text COLLATE utf8mb4_unicode_ci,
  `motivo_rejeicao` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nfes_chave_acesso_unique` (`chave_acesso`),
  KEY `nfes_empresa_id_foreign` (`empresa_id`),
  KEY `nfes_venda_id_foreign` (`venda_id`),
  CONSTRAINT `nfes_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `nfes_venda_id_foreign` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orcamento_itens`
--

DROP TABLE IF EXISTS `orcamento_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orcamento_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `orcamento_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `descricao_produto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `valor_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orcamento_itens_orcamento_id_foreign` (`orcamento_id`),
  KEY `orcamento_itens_produto_id_foreign` (`produto_id`),
  KEY `orcamento_itens_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `orcamento_itens_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orcamento_itens_orcamento_id_foreign` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orcamento_itens_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orcamentos`
--

DROP TABLE IF EXISTS `orcamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orcamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `vendedor_id` bigint unsigned NOT NULL,
  `status` enum('Pendente','Aprovado','Recusado','Cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendente',
  `data_emissao` date NOT NULL,
  `data_validade` date DEFAULT NULL,
  `valor_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orcamentos_empresa_id_foreign` (`empresa_id`),
  KEY `orcamentos_cliente_id_foreign` (`cliente_id`),
  KEY `orcamentos_vendedor_id_foreign` (`vendedor_id`),
  CONSTRAINT `orcamentos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `orcamentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `orcamentos_vendedor_id_foreign` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordem_producao_itens`
--

DROP TABLE IF EXISTS `ordem_producao_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordem_producao_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ordem_producao_id` bigint unsigned NOT NULL,
  `materia_prima_id` bigint unsigned NOT NULL,
  `quantidade_necessaria` decimal(10,4) NOT NULL COMMENT 'Qtd total calculada para a OP',
  `quantidade_baixada` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `custo_unitario_momento` decimal(10,2) NOT NULL COMMENT 'Custo da MP no momento da criação da OP',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordem_producao_itens_ordem_producao_id_foreign` (`ordem_producao_id`),
  KEY `ordem_producao_itens_materia_prima_id_foreign` (`materia_prima_id`),
  CONSTRAINT `ordem_producao_itens_materia_prima_id_foreign` FOREIGN KEY (`materia_prima_id`) REFERENCES `produtos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ordem_producao_itens_ordem_producao_id_foreign` FOREIGN KEY (`ordem_producao_id`) REFERENCES `ordens_producao` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordens_producao`
--

DROP TABLE IF EXISTS `ordens_producao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordens_producao` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `produto_acabado_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Planejada' COMMENT 'Ex: Planejada, Em Produção, Concluída, Cancelada',
  `quantidade_planejada` decimal(10,3) NOT NULL,
  `quantidade_produzida` decimal(10,3) DEFAULT NULL,
  `data_inicio_prevista` date DEFAULT NULL,
  `data_fim_prevista` date DEFAULT NULL,
  `data_inicio_real` timestamp NULL DEFAULT NULL,
  `data_fim_real` timestamp NULL DEFAULT NULL,
  `custo_total_estimado` decimal(15,2) DEFAULT NULL,
  `custo_total_real` decimal(15,2) DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordens_producao_empresa_id_foreign` (`empresa_id`),
  KEY `ordens_producao_produto_acabado_id_foreign` (`produto_acabado_id`),
  KEY `ordens_producao_user_id_foreign` (`user_id`),
  CONSTRAINT `ordens_producao_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ordens_producao_produto_acabado_id_foreign` FOREIGN KEY (`produto_acabado_id`) REFERENCES `produtos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ordens_producao_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagamentos`
--

DROP TABLE IF EXISTS `pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `venda_id` bigint unsigned NOT NULL,
  `forma_pagamento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `parcelas` int NOT NULL DEFAULT '1',
  `detalhes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pagamentos_empresa_id_foreign` (`empresa_id`),
  KEY `pagamentos_venda_id_foreign` (`venda_id`),
  CONSTRAINT `pagamentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pagamentos_venda_id_foreign` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `status` enum('Pendente','Em Separação','Separado','Em Transporte','Entregue','Cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendente',
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `data_pedido` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cliente_id` bigint unsigned NOT NULL,
  `vendedor_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedidos_cliente_id_foreign` (`cliente_id`),
  KEY `pedidos_vendedor_id_foreign` (`vendedor_id`),
  KEY `pedidos_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `pedidos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pedidos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedidos_vendedor_id_foreign` FOREIGN KEY (`vendedor_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `produto_fornecedores`
--

DROP TABLE IF EXISTS `produto_fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto_fornecedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `produto_id` bigint unsigned NOT NULL,
  `fornecedor_id` bigint unsigned NOT NULL,
  `codigo_produto_fornecedor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preco_custo_ultima_compra` decimal(10,4) DEFAULT NULL,
  `data_ultima_compra` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prod_forn_codigo_unique` (`fornecedor_id`,`codigo_produto_fornecedor`),
  KEY `produto_fornecedores_produto_id_foreign` (`produto_id`),
  CONSTRAINT `produto_fornecedores_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `produto_fornecedores_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `produtos`
--

DROP TABLE IF EXISTS `produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produtos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidade` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Unidade de medida do produto (UN, KG, PC, etc.)',
  `codigo_barras` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preco_venda` decimal(10,2) NOT NULL,
  `estoque_atual` decimal(10,3) NOT NULL DEFAULT '0.000',
  `preco_custo` decimal(10,2) DEFAULT NULL,
  `margem_lucro` decimal(8,2) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `tipo` enum('venda','materia_prima','produto_acabado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'venda' COMMENT 'Classifica o item para diferentes lógicas (venda, insumo, resultado de produção).',
  `detalhe_id` bigint unsigned DEFAULT NULL,
  `detalhe_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `categoria_id` bigint unsigned DEFAULT NULL,
  `setor_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `produtos_codigo_barras_unique` (`codigo_barras`),
  KEY `produtos_detalhe_id_detalhe_type_index` (`detalhe_id`,`detalhe_type`),
  KEY `produtos_categoria_id_foreign` (`categoria_id`),
  KEY `produtos_empresa_id_foreign` (`empresa_id`),
  KEY `produtos_setor_id_foreign` (`setor_id`),
  CONSTRAINT `produtos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  CONSTRAINT `produtos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `produtos_setor_id_foreign` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recebimentos`
--

DROP TABLE IF EXISTS `recebimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recebimentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conta_a_receber_id` bigint unsigned NOT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `forma_pagamento_id` bigint unsigned DEFAULT NULL,
  `valor_recebido` decimal(15,2) NOT NULL COMMENT 'Valor efetivamente recebido nesta transação',
  `juros` decimal(15,2) DEFAULT '0.00',
  `multa` decimal(15,2) DEFAULT '0.00',
  `desconto` decimal(15,2) DEFAULT '0.00',
  `data_recebimento` date NOT NULL,
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recebimentos_conta_a_receber_id_foreign` (`conta_a_receber_id`),
  KEY `recebimentos_empresa_id_foreign` (`empresa_id`),
  KEY `recebimentos_forma_pagamento_id_foreign` (`forma_pagamento_id`),
  CONSTRAINT `recebimentos_conta_a_receber_id_foreign` FOREIGN KEY (`conta_a_receber_id`) REFERENCES `contas_a_receber` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recebimentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recebimentos_forma_pagamento_id_foreign` FOREIGN KEY (`forma_pagamento_id`) REFERENCES `forma_pagamentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `regras_tributarias`
--

DROP TABLE IF EXISTS `regras_tributarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regras_tributarias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `cfop` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uf_origem` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nulo para Todas',
  `uf_destino` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nulo para Todas',
  `crt_emitente` tinyint DEFAULT NULL COMMENT 'Nulo para Todos. 1=SN, 3=Normal',
  `icms_origem` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `icms_cst` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Para Regime Normal',
  `csosn` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Para Simples Nacional',
  `icms_mod_bc` int DEFAULT NULL,
  `icms_aliquota` decimal(10,2) NOT NULL DEFAULT '0.00',
  `icms_reducao_bc` decimal(10,2) NOT NULL DEFAULT '0.00',
  `icms_mod_bc_st` int DEFAULT NULL,
  `mva_st` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Margem de Valor Agregado %',
  `icms_aliquota_st` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ipi_cst` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ipi_codigo_enquadramento` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '999',
  `ipi_aliquota` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pis_cst` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pis_aliquota` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cofins_cst` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cofins_aliquota` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `setores`
--

DROP TABLE IF EXISTS `setores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `setores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `setores_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `setores_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `terminais`
--

DROP TABLE IF EXISTS `terminais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `terminais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `numero` int unsigned NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `terminais_empresa_id_numero_unique` (`empresa_id`,`numero`),
  CONSTRAINT `terminais_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transportadoras`
--

DROP TABLE IF EXISTS `transportadoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transportadoras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `razao_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_fantasia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnpj` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inscricao_estadual` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logradouro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rntc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Registro Nacional de Transportadores Rodoviários de Carga',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transportadoras_cnpj_unique` (`cnpj`),
  KEY `transportadoras_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `transportadoras_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `unidades_medida`
--

DROP TABLE IF EXISTS `unidades_medida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidades_medida` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sigla` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unidades_medida_nome_unique` (`nome`),
  KEY `unidades_medida_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `unidades_medida_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_pin_unique` (`pin`),
  KEY `users_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `users_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perfil` enum('admin','vendedor','estoquista') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vendedor',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `venda_items`
--

DROP TABLE IF EXISTS `venda_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `venda_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venda_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `descricao_produto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal_item` decimal(10,2) NOT NULL,
  `cfop` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `venda_items_venda_id_foreign` (`venda_id`),
  KEY `venda_items_produto_id_foreign` (`produto_id`),
  CONSTRAINT `venda_items_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  CONSTRAINT `venda_items_venda_id_foreign` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `venda_pagamentos`
--

DROP TABLE IF EXISTS `venda_pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `venda_pagamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `venda_id` bigint unsigned NOT NULL,
  `forma_pagamento_id` bigint unsigned NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `venda_pagamentos_empresa_id_foreign` (`empresa_id`),
  KEY `venda_pagamentos_venda_id_foreign` (`venda_id`),
  KEY `venda_pagamentos_forma_pagamento_id_foreign` (`forma_pagamento_id`),
  CONSTRAINT `venda_pagamentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `venda_pagamentos_forma_pagamento_id_foreign` FOREIGN KEY (`forma_pagamento_id`) REFERENCES `forma_pagamentos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `venda_pagamentos_venda_id_foreign` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vendas`
--

DROP TABLE IF EXISTS `vendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caixa_id` bigint unsigned DEFAULT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `transportadora_id` bigint unsigned DEFAULT NULL,
  `frete_modalidade` int NOT NULL DEFAULT '9',
  `frete_valor` decimal(10,2) NOT NULL DEFAULT '0.00',
  `peso_bruto` decimal(10,3) DEFAULT NULL,
  `peso_liquido` decimal(10,3) DEFAULT NULL,
  `orcamento_id` bigint unsigned DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `desconto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'concluida',
  `nfe_chave_acesso` varchar(44) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfe_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendas_empresa_id_foreign` (`empresa_id`),
  KEY `vendas_user_id_foreign` (`user_id`),
  KEY `vendas_cliente_id_foreign` (`cliente_id`),
  KEY `vendas_orcamento_id_foreign` (`orcamento_id`),
  KEY `vendas_transportadora_id_foreign` (`transportadora_id`),
  KEY `vendas_caixa_id_foreign` (`caixa_id`),
  CONSTRAINT `vendas_caixa_id_foreign` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `vendas_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `vendas_orcamento_id_foreign` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos` (`id`),
  CONSTRAINT `vendas_transportadora_id_foreign` FOREIGN KEY (`transportadora_id`) REFERENCES `transportadoras` (`id`),
  CONSTRAINT `vendas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-14 14:08:17
