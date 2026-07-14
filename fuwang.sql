-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: fuwang
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
-- Table structure for table `account_balances`
--

-- DROP TABLE IF EXISTS `account_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `account_balances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `user_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `api_key` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_balances_user_id_unique` (`user_id`),
  KEY `account_balances_email_index` (`email`),
  CONSTRAINT `account_balances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_balances`
--

LOCK TABLES `account_balances` WRITE;
/*!40000 ALTER TABLE `account_balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_log`
--

-- DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_logs`
--

-- DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loggable_type` varchar(255) NOT NULL,
  `loggable_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_loggable_type_loggable_id_index` (`loggable_type`,`loggable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_audit_logs`
--

-- DROP TABLE IF EXISTS `admin_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `admin_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_audit_logs_admin_id_created_at_index` (`admin_id`,`created_at`),
  CONSTRAINT `admin_audit_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_audit_logs`
--

LOCK TABLES `admin_audit_logs` WRITE;
/*!40000 ALTER TABLE `admin_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

-- DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `google2fa_secret` varchar(255) DEFAULT NULL,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_secret` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_username_unique` (`username`),
  UNIQUE KEY `admins_email_unique` (`email`),
  KEY `admins_is_super_admin_index` (`is_super_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_centers`
--

-- DROP TABLE IF EXISTS `api_centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `api_centers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dataverify_api_key` text DEFAULT NULL,
  `dataverify_endpoint_nin` text DEFAULT NULL,
  `dataverify_endpoint_bvn` text DEFAULT NULL,
  `dataverify_endpoint_data` text DEFAULT NULL,
  `dataverify_endpoint_phone` text DEFAULT NULL,
  `dataverify_endpoint_tid` text DEFAULT NULL,
  `dataverify_endpoint_premium_slip` text DEFAULT NULL,
  `dataverify_endpoint_premium_slip_phone` text DEFAULT NULL,
  `dataverify_endpoint_standard_slip` text DEFAULT NULL,
  `dataverify_endpoint_regular_slip` text DEFAULT NULL,
  `dataverify_endpoint_vnin_slip` text DEFAULT NULL,
  `payvessel_api_key` text DEFAULT NULL,
  `payvessel_secret_key` text DEFAULT NULL,
  `paystack_public_key` text DEFAULT NULL,
  `paystack_secret_key` text DEFAULT NULL,
  `flutterwave_public_key` text DEFAULT NULL,
  `flutterwave_secret_key` text DEFAULT NULL,
  `flutterwave_encryption_key` text DEFAULT NULL,
  `paypoint_secret_key` text DEFAULT NULL,
  `paypoint_api_key` text DEFAULT NULL,
  `paypoint_businessid` text DEFAULT NULL,
  `paypoint_endpoint` text DEFAULT NULL,
  `payvessel_endpoint` text DEFAULT NULL,
  `payvessel_businessid` text DEFAULT NULL,
  `monnify_api_key` text DEFAULT NULL,
  `monnify_secret_key` text DEFAULT NULL,
  `monnify_endpoint_auth` text DEFAULT NULL,
  `monnify_endpoint_reserve` text DEFAULT NULL,
  `monnify_contract_code` text DEFAULT NULL,
  `ade_apikey` text DEFAULT NULL,
  `ade_endpoint_exam` text DEFAULT NULL,
  `ade_endpoint_airtime` text DEFAULT NULL,
  `ade_endpoint_bill` text DEFAULT NULL,
  `ade_endpoint_data` text DEFAULT NULL,
  `nexus_notary_key` text DEFAULT NULL,
  `nexus_logistics_key` text DEFAULT NULL,
  `nexus_api_secret` text DEFAULT NULL,
  `robosttech_api_key` text DEFAULT NULL,
  `robosttech_endpoint_nin` text DEFAULT NULL,
  `robosttech_endpoint_validation` text DEFAULT NULL,
  `robosttech_endpoint_clearance` text DEFAULT NULL,
  `robosttech_endpoint_clearance_status` text DEFAULT NULL,
  `robosttech_endpoint_personalization` text DEFAULT NULL,
  `gemini_api_key` text DEFAULT NULL,
  `sms_ai_key` text DEFAULT NULL,
  `sms_ai_endpoint` text DEFAULT NULL,
  `sms_ai_sender` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_centers`
--

LOCK TABLES `api_centers` WRITE;
/*!40000 ALTER TABLE `api_centers` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_centers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_tokens`
--

-- DROP TABLE IF EXISTS `api_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `last_four` varchar(8) DEFAULT NULL,
  `abilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`abilities`)),
  `rate_limit_per_minute` int(10) unsigned NOT NULL DEFAULT 60,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_tokens_token_hash_unique` (`token_hash`),
  KEY `api_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_tokens`
--

LOCK TABLES `api_tokens` WRITE;
/*!40000 ALTER TABLE `api_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auction_admin_audit_logs`
--

-- DROP TABLE IF EXISTS `auction_admin_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `auction_admin_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auction_admin_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auction_admin_audit_logs_auction_admin_id_created_at_index` (`auction_admin_id`,`created_at`),
  CONSTRAINT `auction_admin_audit_logs_auction_admin_id_foreign` FOREIGN KEY (`auction_admin_id`) REFERENCES `auction_admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auction_admin_audit_logs`
--

LOCK TABLES `auction_admin_audit_logs` WRITE;
/*!40000 ALTER TABLE `auction_admin_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `auction_admin_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auction_admins`
--

-- DROP TABLE IF EXISTS `auction_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `auction_admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by_admin_id` bigint(20) unsigned DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auction_admins_email_unique` (`email`),
  KEY `auction_admins_created_by_admin_id_foreign` (`created_by_admin_id`),
  CONSTRAINT `auction_admins_created_by_admin_id_foreign` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auction_admins`
--

LOCK TABLES `auction_admins` WRITE;
/*!40000 ALTER TABLE `auction_admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `auction_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auction_bids`
--

-- DROP TABLE IF EXISTS `auction_bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `auction_bids` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lot_id` varchar(255) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `bid_amount` decimal(12,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'winning',
  `reference` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auction_bids_reference_unique` (`reference`),
  KEY `auction_bids_lot_id_status_index` (`lot_id`,`status`),
  KEY `auction_bids_user_id_index` (`user_id`),
  CONSTRAINT `auction_bids_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auction_bids`
--

LOCK TABLES `auction_bids` WRITE;
/*!40000 ALTER TABLE `auction_bids` DISABLE KEYS */;
/*!40000 ALTER TABLE `auction_bids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auction_lot_images`
--

-- DROP TABLE IF EXISTS `auction_lot_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `auction_lot_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auction_lot_id` bigint(20) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auction_lot_images_auction_lot_id_sort_order_index` (`auction_lot_id`,`sort_order`),
  CONSTRAINT `auction_lot_images_auction_lot_id_foreign` FOREIGN KEY (`auction_lot_id`) REFERENCES `auction_lots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auction_lot_images`
--

LOCK TABLES `auction_lot_images` WRITE;
/*!40000 ALTER TABLE `auction_lot_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `auction_lot_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auction_lots`
--

-- DROP TABLE IF EXISTS `auction_lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `auction_lots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned DEFAULT NULL,
  `lot_code` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `starting_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `current_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `bid_increment` decimal(12,2) NOT NULL DEFAULT 0.00,
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'scheduled',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auction_lots_lot_code_unique` (`lot_code`),
  KEY `auction_lots_seller_id_foreign` (`seller_id`),
  KEY `auction_lots_status_end_at_index` (`status`,`end_at`),
  KEY `auction_lots_category_index` (`category`),
  KEY `auction_lots_location_index` (`location`),
  CONSTRAINT `auction_lots_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `auction_sellers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auction_lots`
--

LOCK TABLES `auction_lots` WRITE;
/*!40000 ALTER TABLE `auction_lots` DISABLE KEYS */;
/*!40000 ALTER TABLE `auction_lots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auction_sellers`
--

-- DROP TABLE IF EXISTS `auction_sellers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `auction_sellers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `reviews_count` int(10) unsigned NOT NULL DEFAULT 0,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `avatar_url` varchar(255) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auction_sellers`
--

LOCK TABLES `auction_sellers` WRITE;
/*!40000 ALTER TABLE `auction_sellers` DISABLE KEYS */;
/*!40000 ALTER TABLE `auction_sellers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auction_watchlists`
--

-- DROP TABLE IF EXISTS `auction_watchlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `auction_watchlists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lot_code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auction_watchlists_user_id_lot_code_unique` (`user_id`,`lot_code`),
  KEY `auction_watchlists_lot_code_index` (`lot_code`),
  CONSTRAINT `auction_watchlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auction_watchlists`
--

LOCK TABLES `auction_watchlists` WRITE;
/*!40000 ALTER TABLE `auction_watchlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `auction_watchlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

-- DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

-- DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `custom_apis`
--

-- DROP TABLE IF EXISTS `custom_apis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `custom_apis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `provider_identifier` varchar(255) DEFAULT NULL,
  `service_type` varchar(255) NOT NULL,
  `supported_modes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`supported_modes`)),
  `endpoint` text DEFAULT NULL,
  `api_key` text DEFAULT NULL,
  `secret_key` text DEFAULT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`headers`)),
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(10) unsigned NOT NULL DEFAULT 0,
  `price` decimal(15,2) DEFAULT NULL,
  `timeout_seconds` int(10) unsigned DEFAULT NULL,
  `retry_count` int(10) unsigned DEFAULT NULL,
  `retry_delay_ms` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_apis_provider_identifier_index` (`provider_identifier`),
  KEY `custom_apis_service_type_index` (`service_type`),
  KEY `custom_apis_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_apis`
--

LOCK TABLES `custom_apis` WRITE;
/*!40000 ALTER TABLE `custom_apis` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_apis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_agents`
--

-- DROP TABLE IF EXISTS `delivery_agents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `delivery_agents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `state` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `means_of_identification` varchar(255) DEFAULT NULL,
  `identification_number` varchar(255) DEFAULT NULL,
  `proof_of_address` varchar(255) DEFAULT NULL,
  `next_of_kin_name` varchar(255) DEFAULT NULL,
  `next_of_kin_phone` varchar(255) DEFAULT NULL,
  `availability_status` enum('available','on_delivery','offline') NOT NULL DEFAULT 'offline',
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_agents_user_id_foreign` (`user_id`),
  KEY `idx_delivery_agents_approval_created_at` (`approval_status`,`created_at`),
  KEY `idx_delivery_agents_availability_created_at` (`availability_status`,`created_at`),
  CONSTRAINT `delivery_agents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_agents`
--

LOCK TABLES `delivery_agents` WRITE;
/*!40000 ALTER TABLE `delivery_agents` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_agents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `developer_api_endpoints`
--

-- DROP TABLE IF EXISTS `developer_api_endpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `developer_api_endpoints` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(120) NOT NULL,
  `group_name` varchar(80) DEFAULT NULL,
  `name` varchar(160) NOT NULL,
  `method` varchar(10) NOT NULL DEFAULT 'GET',
  `path_pattern` varchar(255) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `docs_summary` text DEFAULT NULL,
  `docs_request_example` longtext DEFAULT NULL,
  `docs_response_example` longtext DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `developer_api_endpoints_slug_unique` (`slug`),
  KEY `developer_api_endpoints_group_name_index` (`group_name`),
  KEY `developer_api_endpoints_is_enabled_index` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `developer_api_endpoints`
--

LOCK TABLES `developer_api_endpoints` WRITE;
/*!40000 ALTER TABLE `developer_api_endpoints` DISABLE KEYS */;
/*!40000 ALTER TABLE `developer_api_endpoints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `developer_api_request_logs`
--

-- DROP TABLE IF EXISTS `developer_api_request_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `developer_api_request_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `api_token_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `endpoint_slug` varchar(120) DEFAULT NULL,
  `method` varchar(10) NOT NULL,
  `path` varchar(255) NOT NULL,
  `status_code` smallint(5) unsigned DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `declared_website` varchar(255) DEFAULT NULL,
  `origin_host` varchar(255) DEFAULT NULL,
  `referer_host` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `developer_api_request_logs_api_token_id_index` (`api_token_id`),
  KEY `developer_api_request_logs_user_id_index` (`user_id`),
  KEY `developer_api_request_logs_endpoint_slug_index` (`endpoint_slug`),
  KEY `developer_api_request_logs_status_code_index` (`status_code`),
  KEY `developer_api_request_logs_origin_host_index` (`origin_host`),
  KEY `developer_api_request_logs_referer_host_index` (`referer_host`),
  KEY `developer_api_request_logs_requested_at_index` (`requested_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `developer_api_request_logs`
--

LOCK TABLES `developer_api_request_logs` WRITE;
/*!40000 ALTER TABLE `developer_api_request_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `developer_api_request_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_logs`
--

-- DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `to_email` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'queued',
  `provider_message_id` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `sent_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_logs_user_id_foreign` (`user_id`),
  KEY `email_logs_to_email_index` (`to_email`),
  KEY `email_logs_type_index` (`type`),
  KEY `email_logs_status_index` (`status`),
  CONSTRAINT `email_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_logs`
--

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feature_toggles`
--

-- DROP TABLE IF EXISTS `feature_toggles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `feature_toggles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `feature_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `offline_message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feature_toggles_feature_name_unique` (`feature_name`),
  KEY `feature_toggles_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feature_toggles`
--

LOCK TABLES `feature_toggles` WRITE;
/*!40000 ALTER TABLE `feature_toggles` DISABLE KEYS */;
/*!40000 ALTER TABLE `feature_toggles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

-- DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_attempts_ip_address_unique` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logistics_ai_pricing_models`
--

-- DROP TABLE IF EXISTS `logistics_ai_pricing_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `logistics_ai_pricing_models` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `feature_keys` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`feature_keys`)),
  `weights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`weights`)),
  `multiplier` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metrics`)),
  `trained_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `logistics_ai_pricing_models_version_unique` (`version`),
  KEY `logistics_ai_pricing_models_trained_at_index` (`trained_at`),
  KEY `logistics_ai_pricing_models_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logistics_ai_pricing_models`
--

LOCK TABLES `logistics_ai_pricing_models` WRITE;
/*!40000 ALTER TABLE `logistics_ai_pricing_models` DISABLE KEYS */;
/*!40000 ALTER TABLE `logistics_ai_pricing_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logistics_centers`
--

-- DROP TABLE IF EXISTS `logistics_centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `logistics_centers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `availability_status` varchar(255) NOT NULL DEFAULT 'available',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `capacity_per_day` int(10) unsigned DEFAULT NULL,
  `current_load` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logistics_centers_state_type_is_active_index` (`state`,`type`,`is_active`),
  KEY `logistics_centers_type_index` (`type`),
  KEY `logistics_centers_state_index` (`state`),
  KEY `logistics_centers_city_index` (`city`),
  KEY `logistics_centers_lat_index` (`lat`),
  KEY `logistics_centers_lng_index` (`lng`),
  KEY `logistics_centers_availability_status_index` (`availability_status`),
  KEY `logistics_centers_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logistics_centers`
--

LOCK TABLES `logistics_centers` WRITE;
/*!40000 ALTER TABLE `logistics_centers` DISABLE KEYS */;
INSERT INTO `logistics_centers` VALUES (1,'FuwaPost Pickup Center - Abia','pickup','Abia','Aba',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(2,'FuwaPost Drop-off Center - Abia','dropoff','Abia','Aba',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(3,'FuwaPost Pickup Center - Adamawa','pickup','Adamawa','Alkalawa',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(4,'FuwaPost Drop-off Center - Adamawa','dropoff','Adamawa','Alkalawa',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(5,'FuwaPost Pickup Center - Akwa Ibom','pickup','Akwa Ibom','Abak',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(6,'FuwaPost Drop-off Center - Akwa Ibom','dropoff','Akwa Ibom','Abak',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(7,'FuwaPost Pickup Center - Anambra','pickup','Anambra','Abagana',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(8,'FuwaPost Drop-off Center - Anambra','dropoff','Anambra','Abagana',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(9,'FuwaPost Pickup Center - Bauchi','pickup','Bauchi','Alkaleri',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(10,'FuwaPost Drop-off Center - Bauchi','dropoff','Bauchi','Alkaleri',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(11,'FuwaPost Pickup Center - Bayelsa','pickup','Bayelsa','Brass',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(12,'FuwaPost Drop-off Center - Bayelsa','dropoff','Bayelsa','Brass',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(13,'FuwaPost Pickup Center - Benue','pickup','Benue','Agbadi',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(14,'FuwaPost Drop-off Center - Benue','dropoff','Benue','Agbadi',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(15,'FuwaPost Pickup Center - Borno','pickup','Borno','Askira/Uba',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(16,'FuwaPost Drop-off Center - Borno','dropoff','Borno','Askira/Uba',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(17,'FuwaPost Pickup Center - Cross River','pickup','Cross River','Ababene',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(18,'FuwaPost Drop-off Center - Cross River','dropoff','Cross River','Ababene',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(19,'FuwaPost Pickup Center - Delta','pickup','Delta','Abo',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(20,'FuwaPost Drop-off Center - Delta','dropoff','Delta','Abo',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(21,'FuwaPost Pickup Center - Ebonyi','pickup','Ebonyi','Abakaliki',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(22,'FuwaPost Drop-off Center - Ebonyi','dropoff','Ebonyi','Abakaliki',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(23,'FuwaPost Pickup Center - Edo','pickup','Edo','Aboudou',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(24,'FuwaPost Drop-off Center - Edo','dropoff','Edo','Aboudou',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(25,'FuwaPost Pickup Center - Ekiti','pickup','Ekiti','Ado Ekiti',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(26,'FuwaPost Drop-off Center - Ekiti','dropoff','Ekiti','Ado Ekiti',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(27,'FuwaPost Pickup Center - Enugu','pickup','Enugu','Abor',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(28,'FuwaPost Drop-off Center - Enugu','dropoff','Enugu','Abor',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(29,'FuwaPost Pickup Center - Federal Capital Territory','pickup','Federal Capital Territory','Abuja',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(30,'FuwaPost Drop-off Center - Federal Capital Territory','dropoff','Federal Capital Territory','Abuja',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(31,'FuwaPost Pickup Center - Gombe','pickup','Gombe','Akko',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(32,'FuwaPost Drop-off Center - Gombe','dropoff','Gombe','Akko',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(33,'FuwaPost Pickup Center - Imo','pickup','Imo','Abajah',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(34,'FuwaPost Drop-off Center - Imo','dropoff','Imo','Abajah',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(35,'FuwaPost Pickup Center - Jigawa','pickup','Jigawa','Amaryawa',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(36,'FuwaPost Drop-off Center - Jigawa','dropoff','Jigawa','Amaryawa',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(37,'FuwaPost Pickup Center - Kaduna','pickup','Kaduna','Antan',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(38,'FuwaPost Drop-off Center - Kaduna','dropoff','Kaduna','Antan',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(39,'FuwaPost Pickup Center - Kano','pickup','Kano','Ajingi',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(40,'FuwaPost Drop-off Center - Kano','dropoff','Kano','Ajingi',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(41,'FuwaPost Pickup Center - Katsina','pickup','Katsina','Bakori',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(42,'FuwaPost Drop-off Center - Katsina','dropoff','Katsina','Bakori',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(43,'FuwaPost Pickup Center - Kebbi','pickup','Kebbi','Aleiro',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(44,'FuwaPost Drop-off Center - Kebbi','dropoff','Kebbi','Aleiro',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(45,'FuwaPost Pickup Center - Kogi','pickup','Kogi','Adavi',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(46,'FuwaPost Drop-off Center - Kogi','dropoff','Kogi','Adavi',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(47,'FuwaPost Pickup Center - Kwara','pickup','Kwara','Ajasse Ipo',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(48,'FuwaPost Drop-off Center - Kwara','dropoff','Kwara','Ajasse Ipo',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:09','2026-07-14 16:48:09'),(49,'FuwaPost Pickup Center - Lagos','pickup','Lagos','Adamo',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(50,'FuwaPost Drop-off Center - Lagos','dropoff','Lagos','Adamo',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(51,'FuwaPost Pickup Center - Nasarawa','pickup','Nasarawa','Akwanga',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(52,'FuwaPost Drop-off Center - Nasarawa','dropoff','Nasarawa','Akwanga',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(53,'FuwaPost Pickup Center - Niger','pickup','Niger','Bida',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(54,'FuwaPost Drop-off Center - Niger','dropoff','Niger','Bida',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(55,'FuwaPost Pickup Center - Ogun','pickup','Ogun','Abeokuta',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(56,'FuwaPost Drop-off Center - Ogun','dropoff','Ogun','Abeokuta',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(57,'FuwaPost Pickup Center - Ondo','pickup','Ondo','Akoko North East',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(58,'FuwaPost Drop-off Center - Ondo','dropoff','Ondo','Akoko North East',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(59,'FuwaPost Pickup Center - Osun','pickup','Osun','Aiyedire',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(60,'FuwaPost Drop-off Center - Osun','dropoff','Osun','Aiyedire',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(61,'FuwaPost Pickup Center - Oyo','pickup','Oyo','Ado Awaiye',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(62,'FuwaPost Drop-off Center - Oyo','dropoff','Oyo','Ado Awaiye',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(63,'FuwaPost Pickup Center - Plateau','pickup','Plateau','Barkin Ladi',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(64,'FuwaPost Drop-off Center - Plateau','dropoff','Plateau','Barkin Ladi',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(65,'FuwaPost Pickup Center - Rivers','pickup','Rivers','Abua/Odual',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(66,'FuwaPost Drop-off Center - Rivers','dropoff','Rivers','Abua/Odual',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(67,'FuwaPost Pickup Center - Sokoto','pickup','Sokoto','Balle',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(68,'FuwaPost Drop-off Center - Sokoto','dropoff','Sokoto','Balle',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(69,'FuwaPost Pickup Center - Taraba','pickup','Taraba','Ardo-Kola',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(70,'FuwaPost Drop-off Center - Taraba','dropoff','Taraba','Ardo-Kola',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(71,'FuwaPost Pickup Center - Yobe','pickup','Yobe','Barde',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(72,'FuwaPost Drop-off Center - Yobe','dropoff','Yobe','Barde',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(73,'FuwaPost Pickup Center - Zamfara','pickup','Zamfara','Anka',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10'),(74,'FuwaPost Drop-off Center - Zamfara','dropoff','Zamfara','Anka',NULL,NULL,NULL,'available',1,NULL,0,'2026-07-14 16:48:10','2026-07-14 16:48:10');
/*!40000 ALTER TABLE `logistics_centers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logistics_inventory_items`
--

-- DROP TABLE IF EXISTS `logistics_inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `logistics_inventory_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `logistics_inventory_items_sku_unique` (`sku`),
  KEY `logistics_inventory_items_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logistics_inventory_items`
--

LOCK TABLES `logistics_inventory_items` WRITE;
/*!40000 ALTER TABLE `logistics_inventory_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `logistics_inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logistics_profiles`
--

-- DROP TABLE IF EXISTS `logistics_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `logistics_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `business_type` enum('individual','company','enterprise') NOT NULL DEFAULT 'individual',
  `preferred_delivery` enum('standard','express','overnight') NOT NULL DEFAULT 'standard',
  `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `logistics_profiles_user_id_unique` (`user_id`),
  KEY `logistics_profiles_user_id_is_active_index` (`user_id`,`is_active`),
  CONSTRAINT `logistics_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logistics_profiles`
--

LOCK TABLES `logistics_profiles` WRITE;
/*!40000 ALTER TABLE `logistics_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `logistics_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logistics_requests`
--

-- DROP TABLE IF EXISTS `logistics_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `logistics_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `sender_address` text DEFAULT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `recipient_address` text DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `delivery_type` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `tracking_id` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'processing',
  `waybill_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `assigned_manager_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_officer_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_delivery_agent_id` bigint(20) unsigned DEFAULT NULL,
  `scheduled_pickup_at` timestamp NULL DEFAULT NULL,
  `route_code` varchar(255) DEFAULT NULL,
  `last_status_updated_at` timestamp NULL DEFAULT NULL,
  `agent_assignment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `agent_assignment_responded_at` timestamp NULL DEFAULT NULL,
  `agent_fee_amount` decimal(10,2) DEFAULT NULL,
  `agent_commission_amount` decimal(10,2) DEFAULT NULL,
  `agent_paid_at` timestamp NULL DEFAULT NULL,
  `sender_state` varchar(255) DEFAULT NULL,
  `sender_city` varchar(255) DEFAULT NULL,
  `recipient_state` varchar(255) DEFAULT NULL,
  `recipient_city` varchar(255) DEFAULT NULL,
  `pickup_method` varchar(255) NOT NULL DEFAULT 'center_dropoff',
  `delivery_method` varchar(255) NOT NULL DEFAULT 'home_delivery',
  `pickup_center_id` bigint(20) unsigned DEFAULT NULL,
  `dropoff_center_id` bigint(20) unsigned DEFAULT NULL,
  `sender_lat` decimal(10,7) DEFAULT NULL,
  `sender_lng` decimal(10,7) DEFAULT NULL,
  `recipient_lat` decimal(10,7) DEFAULT NULL,
  `recipient_lng` decimal(10,7) DEFAULT NULL,
  `distance_km` decimal(10,2) DEFAULT NULL,
  `package_length_cm` decimal(10,2) DEFAULT NULL,
  `package_width_cm` decimal(10,2) DEFAULT NULL,
  `package_height_cm` decimal(10,2) DEFAULT NULL,
  `price_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`price_breakdown`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `logistics_requests_tracking_id_unique` (`tracking_id`),
  KEY `logistics_requests_user_id_foreign` (`user_id`),
  KEY `logistics_requests_status_index` (`status`),
  KEY `logistics_requests_assigned_manager_id_index` (`assigned_manager_id`),
  KEY `logistics_requests_assigned_officer_id_index` (`assigned_officer_id`),
  KEY `logistics_requests_assigned_delivery_agent_id_index` (`assigned_delivery_agent_id`),
  KEY `logistics_requests_scheduled_pickup_at_index` (`scheduled_pickup_at`),
  KEY `logistics_requests_route_code_index` (`route_code`),
  KEY `logistics_requests_last_status_updated_at_index` (`last_status_updated_at`),
  KEY `logistics_requests_agent_assignment_status_index` (`agent_assignment_status`),
  KEY `logistics_requests_agent_assignment_responded_at_index` (`agent_assignment_responded_at`),
  KEY `logistics_requests_agent_paid_at_index` (`agent_paid_at`),
  KEY `logistics_requests_sender_state_index` (`sender_state`),
  KEY `logistics_requests_sender_city_index` (`sender_city`),
  KEY `logistics_requests_recipient_state_index` (`recipient_state`),
  KEY `logistics_requests_recipient_city_index` (`recipient_city`),
  KEY `logistics_requests_pickup_method_index` (`pickup_method`),
  KEY `logistics_requests_delivery_method_index` (`delivery_method`),
  KEY `logistics_requests_pickup_center_id_index` (`pickup_center_id`),
  KEY `logistics_requests_dropoff_center_id_index` (`dropoff_center_id`),
  KEY `logistics_requests_distance_km_index` (`distance_km`),
  CONSTRAINT `logistics_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logistics_requests`
--

LOCK TABLES `logistics_requests` WRITE;
/*!40000 ALTER TABLE `logistics_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `logistics_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logistics_staff`
--

-- DROP TABLE IF EXISTS `logistics_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `logistics_staff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by_admin_id` bigint(20) unsigned DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `logistics_staff_email_unique` (`email`),
  KEY `logistics_staff_created_by_admin_id_foreign` (`created_by_admin_id`),
  KEY `logistics_staff_is_active_index` (`is_active`),
  CONSTRAINT `logistics_staff_created_by_admin_id_foreign` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logistics_staff`
--

LOCK TABLES `logistics_staff` WRITE;
/*!40000 ALTER TABLE `logistics_staff` DISABLE KEYS */;
/*!40000 ALTER TABLE `logistics_staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logistics_staff_jwt_sessions`
--

-- DROP TABLE IF EXISTS `logistics_staff_jwt_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `logistics_staff_jwt_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `logistics_staff_id` bigint(20) unsigned NOT NULL,
  `jti` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `revoked_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `logistics_staff_jwt_sessions_jti_unique` (`jti`),
  KEY `logistics_staff_jwt_sessions_logistics_staff_id_expires_at_index` (`logistics_staff_id`,`expires_at`),
  KEY `logistics_staff_jwt_sessions_expires_at_index` (`expires_at`),
  KEY `logistics_staff_jwt_sessions_revoked_at_index` (`revoked_at`),
  CONSTRAINT `logistics_staff_jwt_sessions_logistics_staff_id_foreign` FOREIGN KEY (`logistics_staff_id`) REFERENCES `logistics_staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logistics_staff_jwt_sessions`
--

LOCK TABLES `logistics_staff_jwt_sessions` WRITE;
/*!40000 ALTER TABLE `logistics_staff_jwt_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `logistics_staff_jwt_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

-- DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2023_01_01_000000_create_users_table',1),(2,'2023_01_01_000001_create_password_reset_tokens_table',1),(3,'2023_01_01_000002_create_email_logs_table',1),(4,'2023_01_01_000003_create_login_attempts_table',1),(5,'2023_01_01_000004_create_admins_table',1),(6,'2023_01_01_000006_create_permission_tables',1),(7,'2023_01_01_000007_create_notifications_table',1),(8,'2023_01_01_000008_create_admin_audit_logs_table',1),(9,'2023_01_01_000009_create_feature_toggles_table',1),(10,'2023_01_01_000010_create_transactions_table',1),(11,'2023_01_01_000011_create_account_balances_table',1),(12,'2023_01_01_000012_create_referrals_table',1),(13,'2023_01_01_000013_create_referral_audit_logs_table',1),(14,'2023_10_27_000000_create_activity_logs_table',1),(15,'2023_10_27_110000_add_google2fa_secret_to_admins_table',1),(16,'2024_01_01_000000_add_completed_tours_to_users_table',1),(17,'2024_01_01_000000_create_delivery_agents_table',1),(18,'2024_01_01_000000_create_referral_tiers_table',1),(19,'2024_01_01_000001_create_shipping_providers_table',1),(20,'2024_07_22_100000_create_sessions_table',1),(21,'2026_04_08_224309_create_activity_log_table',1),(22,'2026_04_08_224310_add_event_column_to_activity_log_table',1),(23,'2026_04_08_224311_add_batch_uuid_column_to_activity_log_table',1),(24,'2026_04_08_232704_update_admins_for_2fa',1),(25,'2026_04_10_120000_grandfather_email_verified_at_for_existing_users',1),(26,'2026_04_10_180000_add_details_to_delivery_agents_table',1),(27,'2026_04_11_120000_create_price_list_table',1),(28,'2026_04_13_102754_create_cache_table',1),(29,'2026_04_13_170000_add_google_columns_to_users_table',1),(30,'2026_04_15_205354_add_performance_indexes_to_core_tables',1),(31,'2026_04_15_205911_add_query_tuned_composite_indexes',1),(32,'2026_04_16_000001_create_service_sessions_table',1),(33,'2026_04_16_000002_create_logistics_profiles_table',1),(34,'2026_04_16_145912_add_api_access_status_to_users_table',1),(35,'2026_04_16_160000_create_logistics_staff_table',1),(36,'2026_04_16_160100_create_logistics_staff_jwt_sessions_table',1),(37,'2026_04_16_160200_update_logistics_requests_for_ops_rbac',1),(38,'2026_04_16_160300_create_logistics_inventory_items_table',1),(39,'2026_04_16_170000_add_agent_assignment_fields_to_logistics_requests_table',1),(40,'2026_04_16_180000_create_logistics_centers_table',1),(41,'2026_04_16_180100_add_location_and_pricing_fields_to_logistics_requests_table',1),(42,'2026_04_16_190000_create_logistics_ai_pricing_models_table',1),(43,'2026_04_17_000001_create_api_centers_table',1),(44,'2026_04_17_000002_create_verification_results_table',1),(45,'2026_04_17_000003_create_api_tokens_table',1),(46,'2026_04_17_000004_create_developer_api_endpoints_table',1),(47,'2026_04_17_000005_create_developer_api_request_logs_table',1),(48,'2026_04_17_000006_create_custom_apis_table',1),(49,'2026_04_17_000007_create_system_settings_table',1),(50,'2026_04_17_220000_create_auction_tables',1),(51,'2026_04_17_230000_create_auction_admins_table',1),(52,'2026_04_17_230100_create_auction_admin_audit_logs_table',1),(53,'2026_04_20_140700_add_two_factor_fields_to_admins_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

-- DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

-- DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

-- DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

-- DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
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
-- Table structure for table `permissions`
--

-- DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'manage_logistics_staff','admin','2026-07-14 16:48:08','2026-07-14 16:48:08'),(2,'logistics.orders.view_all','logistics_staff','2026-07-14 16:48:08','2026-07-14 16:48:08'),(3,'logistics.orders.create','logistics_staff','2026-07-14 16:48:08','2026-07-14 16:48:08'),(4,'logistics.orders.edit','logistics_staff','2026-07-14 16:48:08','2026-07-14 16:48:08'),(5,'logistics.orders.assign','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(6,'logistics.orders.update_status','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(7,'logistics.shipments.schedule','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(8,'logistics.shipments.assign_routes','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(9,'logistics.shipments.monitor','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(10,'logistics.agents.onboard','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(11,'logistics.agents.manage_assignments','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(12,'logistics.agents.view_metrics','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(13,'logistics.agents.view','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(14,'logistics.centers.manage','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(15,'logistics.inventory.view','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(16,'logistics.inventory.manage','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(17,'logistics.analytics.view','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `price_list`
--

-- DROP TABLE IF EXISTS `price_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `price_list` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `network` varchar(32) NOT NULL,
  `data_plan` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `validate` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `price_list`
--

LOCK TABLES `price_list` WRITE;
/*!40000 ALTER TABLE `price_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `price_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referral_audit_logs`
--

-- DROP TABLE IF EXISTS `referral_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `referral_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `referral_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ref_audit_referral_id` (`referral_id`),
  KEY `idx_ref_audit_user_created` (`user_id`,`created_at`),
  KEY `referral_audit_logs_action_index` (`action`),
  KEY `referral_audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `idx_ref_audit_referral_id` FOREIGN KEY (`referral_id`) REFERENCES `referrals` (`id`) ON DELETE SET NULL,
  CONSTRAINT `idx_ref_audit_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referral_audit_logs`
--

LOCK TABLES `referral_audit_logs` WRITE;
/*!40000 ALTER TABLE `referral_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `referral_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referral_tiers`
--

-- DROP TABLE IF EXISTS `referral_tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `referral_tiers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `minimum_referrals` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referral_tiers`
--

LOCK TABLES `referral_tiers` WRITE;
/*!40000 ALTER TABLE `referral_tiers` DISABLE KEYS */;
INSERT INTO `referral_tiers` VALUES (1,'Bronze','Bronze Tier',5.00,0,'2026-07-14 16:48:08','2026-07-14 16:48:08'),(2,'Silver','Silver Tier',10.00,10,'2026-07-14 16:48:08','2026-07-14 16:48:08'),(3,'Gold','Gold Tier',15.00,25,'2026-07-14 16:48:08','2026-07-14 16:48:08');
/*!40000 ALTER TABLE `referral_tiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referrals`
--

-- DROP TABLE IF EXISTS `referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `referrer_user_id` bigint(20) unsigned NOT NULL,
  `referred_user_id` bigint(20) unsigned NOT NULL,
  `referral_code` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'registered',
  `registered_at` timestamp NULL DEFAULT NULL,
  `first_funded_at` timestamp NULL DEFAULT NULL,
  `reward_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reward_status` varchar(255) NOT NULL DEFAULT 'none',
  `reward_transaction_id` varchar(255) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referrals_referred_user_id_unique` (`referred_user_id`),
  KEY `referrals_referrer_user_id_status_index` (`referrer_user_id`,`status`),
  KEY `referrals_referral_code_index` (`referral_code`),
  KEY `referrals_status_index` (`status`),
  KEY `referrals_registered_at_index` (`registered_at`),
  KEY `referrals_first_funded_at_index` (`first_funded_at`),
  KEY `referrals_reward_status_index` (`reward_status`),
  KEY `referrals_reward_transaction_id_index` (`reward_transaction_id`),
  CONSTRAINT `referrals_referred_user_id_foreign` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referrals_referrer_user_id_foreign` FOREIGN KEY (`referrer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrals`
--

LOCK TABLES `referrals` WRITE;
/*!40000 ALTER TABLE `referrals` DISABLE KEYS */;
/*!40000 ALTER TABLE `referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

-- DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (2,1),(3,1),(4,1),(5,1),(6,1),(6,2),(7,1),(8,1),(9,1),(9,2),(10,1),(11,1),(12,1),(13,1),(13,2),(14,1),(14,2),(15,1),(15,2),(16,1),(17,1);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

-- DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'logistics_manager','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09'),(2,'logistics_officer','logistics_staff','2026-07-14 16:48:09','2026-07-14 16:48:09');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_sessions`
--

-- DROP TABLE IF EXISTS `service_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `service_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `service` varchar(50) NOT NULL,
  `token` varchar(64) NOT NULL,
  `scopes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scopes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_sessions_token_service_index` (`token`,`service`),
  KEY `service_sessions_user_id_service_index` (`user_id`,`service`),
  KEY `service_sessions_service_expires_at_index` (`service`,`expires_at`),
  CONSTRAINT `service_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_sessions`
--

LOCK TABLES `service_sessions` WRITE;
/*!40000 ALTER TABLE `service_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

-- DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `sessions` (
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
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_providers`
--

-- DROP TABLE IF EXISTS `shipping_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `shipping_providers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `api_base_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipping_providers_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_providers`
--

LOCK TABLES `shipping_providers` WRITE;
/*!40000 ALTER TABLE `shipping_providers` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipping_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

-- DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `group` varchar(80) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`),
  KEY `system_settings_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

-- DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `order_type` varchar(255) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_after` decimal(15,2) NOT NULL DEFAULT 0.00,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_user_email_created_at_index` (`user_email`,`created_at`),
  KEY `transactions_user_email_index` (`user_email`),
  KEY `transactions_order_type_index` (`order_type`),
  KEY `transactions_transaction_id_index` (`transaction_id`),
  KEY `transactions_status_index` (`status`),
  KEY `idx_transactions_status_created_at` (`status`,`created_at`),
  KEY `idx_transactions_transaction_id` (`transaction_id`),
  KEY `idx_transactions_order_type` (`order_type`),
  KEY `idx_transactions_user_email_created_at` (`user_email`,`created_at`),
  KEY `idx_transactions_user_email_transaction_id` (`user_email`,`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

-- DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `username` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_avatar` text DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `transaction_pin` varchar(4) DEFAULT NULL,
  `reseller_id` varchar(255) DEFAULT NULL,
  `referral_id` varchar(255) DEFAULT NULL,
  `referred_user_id` bigint(20) unsigned DEFAULT NULL,
  `online_status` varchar(255) NOT NULL DEFAULT 'offline',
  `user_status` varchar(255) NOT NULL DEFAULT 'active',
  `kyc_tier` varchar(255) DEFAULT NULL,
  `kyc_rejection_reason` text DEFAULT NULL,
  `completed_tours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`completed_tours`)),
  `api_access_status` varchar(255) NOT NULL DEFAULT 'none',
  `api_application_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`api_application_details`)),
  `google2fa_secret` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `referral_tier_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  KEY `users_role_index` (`role`),
  KEY `users_referred_user_id_index` (`referred_user_id`),
  KEY `users_online_status_index` (`online_status`),
  KEY `users_user_status_index` (`user_status`),
  KEY `users_api_access_status_index` (`api_access_status`),
  KEY `users_referral_tier_id_foreign` (`referral_tier_id`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_created_at` (`created_at`),
  KEY `idx_users_role_created_at` (`role`,`created_at`),
  CONSTRAINT `users_referral_tier_id_foreign` FOREIGN KEY (`referral_tier_id`) REFERENCES `referral_tiers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `verification_results`
--

-- DROP TABLE IF EXISTS `verification_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `verification_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `identifier` varchar(191) NOT NULL,
  `provider_name` varchar(191) DEFAULT NULL,
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_data`)),
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `reference_id` varchar(191) DEFAULT NULL,
  `report_path` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `verification_results_user_id_index` (`user_id`),
  KEY `verification_results_service_type_index` (`service_type`),
  KEY `verification_results_identifier_index` (`identifier`),
  KEY `verification_results_provider_name_index` (`provider_name`),
  KEY `verification_results_status_index` (`status`),
  KEY `verification_results_reference_id_index` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `verification_results`
--

LOCK TABLES `verification_results` WRITE;
/*!40000 ALTER TABLE `verification_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `verification_results` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-14 18:48:25
