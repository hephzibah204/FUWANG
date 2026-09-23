-- ==============================================================================
-- FUWA.NG DATABASE SCHEMA MIGRATION SCRIPT FOR PRODUCTION / LIVE SERVER
-- Generated Date: 2026-09-23 09:50:57 UTC
-- Target: MySQL 5.7+ / MariaDB 10.3+ / MySQL 8.0+
-- Description: Full DDL migrations batch (DEF-15) for Fuwa.NG
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';


-- ==============================================================================
-- IDEMPOTENT HELPER PROCEDURES
-- Allows ADD COLUMN, ADD INDEX, and ADD FOREIGN KEY to run safely even if they already exist
-- ==============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `fuwa_add_column_if_not_exists`$$
CREATE PROCEDURE `fuwa_add_column_if_not_exists`(
    IN in_table VARCHAR(64),
    IN in_column VARCHAR(64),
    IN in_def TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS 
        WHERE table_schema = DATABASE() 
          AND table_name = in_table 
          AND column_name = in_column
    ) THEN
        SET @query = CONCAT('ALTER TABLE `', in_table, '` ADD `', in_column, '` ', in_def);
        PREPARE stmt FROM @query;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `fuwa_add_index_if_not_exists`$$
CREATE PROCEDURE `fuwa_add_index_if_not_exists`(
    IN in_table VARCHAR(64),
    IN in_index VARCHAR(64),
    IN in_columns TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS 
        WHERE table_schema = DATABASE() 
          AND table_name = in_table 
          AND index_name = in_index
    ) THEN
        SET @query = CONCAT('ALTER TABLE `', in_table, '` ADD INDEX `', in_index, '` (', in_columns, ')');
        PREPARE stmt FROM @query;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `fuwa_add_unique_if_not_exists`$$
CREATE PROCEDURE `fuwa_add_unique_if_not_exists`(
    IN in_table VARCHAR(64),
    IN in_index VARCHAR(64),
    IN in_columns TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS 
        WHERE table_schema = DATABASE() 
          AND table_name = in_table 
          AND index_name = in_index
    ) THEN
        SET @query = CONCAT('ALTER TABLE `', in_table, '` ADD UNIQUE `', in_index, '` (', in_columns, ')');
        PREPARE stmt FROM @query;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `fuwa_add_fk_if_not_exists`$$
CREATE PROCEDURE `fuwa_add_fk_if_not_exists`(
    IN in_table VARCHAR(64),
    IN in_fk VARCHAR(64),
    IN in_def TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS 
        WHERE table_schema = DATABASE() 
          AND table_name = in_table 
          AND constraint_name = in_fk 
          AND constraint_type = 'FOREIGN KEY'
    ) THEN
        SET @query = CONCAT('ALTER TABLE `', in_table, '` ADD CONSTRAINT `', in_fk, '` ', in_def);
        PREPARE stmt FROM @query;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;



-- ------------------------------------------------------------------------------
-- Migration [1]: 2024_01_01_000000_create_delivery_agents_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_agents` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `state` varchar(255) not null, `city` varchar(255) not null, `availability_status` enum('available', 'on_delivery', 'offline') not null default 'offline', `rating` decimal(2, 1) not null default '0', `approval_status` enum('pending', 'approved', 'rejected') not null default 'pending', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('delivery_agents', 'delivery_agents_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');

-- ------------------------------------------------------------------------------
-- Migration [2]: 2024_01_01_000000_create_notary_settings_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notary_settings` (`id` bigint unsigned not null auto_increment primary key, `document_type` varchar(255) not null, `category` varchar(255) null, `price` decimal(18, 2) not null default '0', `stamp_path` varchar(255) null, `signature_path` varchar(255) null, `description` text null, `requires_court_stamp` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('notary_settings', 'notary_settings_document_type_unique', '`document_type`');

-- ------------------------------------------------------------------------------
-- Migration [3]: 2024_01_01_000000_create_pages_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (`id` bigint unsigned not null auto_increment primary key, `title` varchar(255) not null, `slug` varchar(255) not null, `content` longtext null, `status` varchar(255) not null default 'draft', `seo_title` varchar(255) null, `seo_description` text null, `seo_keywords` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('pages', 'pages_slug_unique', '`slug`');

-- ------------------------------------------------------------------------------
-- Migration [4]: 2024_01_01_000000_create_payment_gateways_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payment_gateways` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `display_name` varchar(255) null, `is_active` tinyint(1) not null default '1', `config` json null, `priority` int not null default '0', `logo_url` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('payment_gateways', 'payment_gateways_name_unique', '`name`');

-- ------------------------------------------------------------------------------
-- Migration [5]: 2024_01_01_000000_create_posts_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (`id` bigint unsigned not null auto_increment primary key, `title` varchar(255) not null, `slug` varchar(255) not null, `content` longtext null, `excerpt` text null, `featured_image` varchar(255) null, `status` varchar(255) not null default 'draft', `seo_title` varchar(255) null, `seo_description` text null, `seo_keywords` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('posts', 'posts_slug_unique', '`slug`');

-- ------------------------------------------------------------------------------
-- Migration [6]: 2024_01_01_000000_create_referral_tiers_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `referral_tiers` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `description` text null, `commission_rate` decimal(5, 2) not null, `minimum_referrals` int not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_column_if_not_exists('users', 'referral_tier_id', 'bigint unsigned null');
CALL fuwa_add_fk_if_not_exists('users', 'users_referral_tier_id_foreign', 'foreign key (`referral_tier_id`) references `referral_tiers` (`id`)');

-- ------------------------------------------------------------------------------
-- Migration [7]: 2024_01_01_000000_create_verification_prices_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `verification_prices` (`id` bigint unsigned not null auto_increment primary key, `nin_by_nin_price` decimal(18, 2) not null default '100', `nin_by_number_price` decimal(18, 2) not null default '100', `nin_by_demography_price` decimal(18, 2) not null default '100', `bvn_by_bvn` decimal(18, 2) not null default '100', `bvn_by_number` decimal(18, 2) not null default '100', `verify_by_tracking_id` decimal(18, 2) not null default '100', `validation_price` decimal(18, 2) not null default '100', `ipe_clearance_price` decimal(18, 2) not null default '100', `personalization_price` decimal(18, 2) not null default '100', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- ------------------------------------------------------------------------------
-- Migration [8]: 2024_01_01_000000_create_virtual_account_audit_logs_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `virtual_account_audit_logs` (`id` bigint unsigned not null auto_increment primary key, `virtual_account_id` bigint unsigned null, `user_id` bigint unsigned not null, `gateway` varchar(255) null, `action` varchar(255) not null, `status` varchar(255) not null, `message` text null, `context` json null, `created_at` timestamp not null default CURRENT_TIMESTAMP) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('virtual_account_audit_logs', 'virtual_account_audit_logs_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('virtual_account_audit_logs', 'virtual_account_audit_logs_virtual_account_id_index', '`virtual_account_id`');
CALL fuwa_add_index_if_not_exists('virtual_account_audit_logs', 'virtual_account_audit_logs_action_index', '`action`');
CALL fuwa_add_index_if_not_exists('virtual_account_audit_logs', 'virtual_account_audit_logs_status_index', '`status`');

-- ------------------------------------------------------------------------------
-- Migration [9]: 2024_01_01_000000_create_virtual_accounts_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `virtual_accounts` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `gateway` varchar(255) null, `account_number` varchar(255) null, `bank_name` varchar(255) null, `account_name` varchar(255) null, `currency` varchar(255) not null default 'NGN', `status` varchar(255) not null default 'active', `reference` varchar(255) null, `provider_customer_reference` varchar(255) null, `provider_account_reference` varchar(255) null, `meta` json null, `error_message` text null, `activated_at` timestamp null, `last_synced_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('virtual_accounts', 'virtual_accounts_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('virtual_accounts', 'virtual_accounts_account_number_index', '`account_number`');
CALL fuwa_add_index_if_not_exists('virtual_accounts', 'virtual_accounts_status_index', '`status`');

-- ------------------------------------------------------------------------------
-- Migration [10]: 2024_01_01_000000_create_virtual_cards_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `virtual_cards` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `card_name` varchar(255) null, `card_number` varchar(255) null, `expiry_date` varchar(255) null, `cvv` varchar(255) null, `currency` varchar(255) not null default 'USD', `balance` decimal(18, 2) not null default '0', `status` varchar(255) not null default 'active', `reference` varchar(255) null, `provider_card_id` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('virtual_cards', 'virtual_cards_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('virtual_cards', 'virtual_cards_status_index', '`status`');

-- ------------------------------------------------------------------------------
-- Migration [11]: 2024_01_01_000000_create_vtu_transactions_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vtu_transactions` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned null, `custom_api_id` bigint unsigned null, `service_type` varchar(255) not null, `direction` varchar(255) not null default 'debit', `amount` decimal(18, 2) not null default '0', `fee` decimal(18, 2) not null default '0', `total` decimal(18, 2) not null default '0', `transaction_id` varchar(255) not null, `status` varchar(255) not null default 'pending', `request_payload` json null, `response_payload` json null, `provider_reference` varchar(255) null, `error_message` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_index_if_not_exists('vtu_transactions', 'vtu_transactions_user_id_index', '`user_id`');
CALL fuwa_add_index_if_not_exists('vtu_transactions', 'vtu_transactions_custom_api_id_index', '`custom_api_id`');
CALL fuwa_add_index_if_not_exists('vtu_transactions', 'vtu_transactions_service_type_index', '`service_type`');
CALL fuwa_add_unique_if_not_exists('vtu_transactions', 'vtu_transactions_transaction_id_unique', '`transaction_id`');
CALL fuwa_add_index_if_not_exists('vtu_transactions', 'vtu_transactions_status_index', '`status`');

-- ------------------------------------------------------------------------------
-- Migration [12]: 2024_01_01_000000_create_whatsapp_click_logs_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `whatsapp_click_logs` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned null, `page_url` varchar(255) null, `ip_address` varchar(45) null, `user_agent` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_index_if_not_exists('whatsapp_click_logs', 'whatsapp_click_logs_user_id_index', '`user_id`');

-- ------------------------------------------------------------------------------
-- Migration [13]: 2024_01_01_000001_create_ab_events_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ab_events` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned null, `session_id` varchar(255) null, `experiment` varchar(255) not null, `variant` varchar(255) not null, `event_name` varchar(255) not null, `page` varchar(255) null, `meta` json null, `ip` varchar(255) null, `user_agent` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- ------------------------------------------------------------------------------
-- Migration [14]: 2024_01_01_000001_create_shipping_providers_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shipping_providers` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `slug` varchar(255) not null, `api_key` varchar(255) null, `api_secret` varchar(255) null, `api_base_url` varchar(255) null, `is_active` tinyint(1) not null default '0', `config` json null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('shipping_providers', 'shipping_providers_slug_unique', '`slug`');

-- ------------------------------------------------------------------------------
-- Migration [15]: 2024_01_01_000002_create_notary_tables
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notary_settings` (`id` bigint unsigned not null auto_increment primary key, `document_type` varchar(255) not null, `category` varchar(255) null, `price` decimal(12, 2) not null default '0', `description` text null, `requires_court_stamp` tinyint(1) not null default '0', `status` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('notary_settings', 'notary_settings_document_type_unique', '`document_type`');
CREATE TABLE IF NOT EXISTS `notary_requests` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `document_type` varchar(255) not null, `form_data` json null, `generated_content` longtext null, `status` varchar(255) not null default 'pending', `draft_pdf_path` varchar(255) null, `final_pdf_path` varchar(255) null, `amount_paid` decimal(12, 2) not null default '0', `reference` varchar(255) null, `stamped_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CREATE TABLE IF NOT EXISTS `legal_documents` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `document_type` varchar(255) null, `title` varchar(255) null, `content` longtext null, `status` varchar(255) not null default 'draft', `file_path` varchar(255) null, `amount_paid` decimal(12, 2) not null default '0', `reference` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- ------------------------------------------------------------------------------
-- Migration [16]: 2024_01_01_000003_create_admin_support_tables
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifying_centers` (`id` bigint unsigned not null auto_increment primary key, `email` varchar(255) null, `phone` varchar(255) null, `whatsapp` varchar(255) null, `telegram` varchar(255) null, `email_enabled` tinyint(1) not null default '0', `sms_enabled` tinyint(1) not null default '0', `whatsapp_enabled` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CREATE TABLE IF NOT EXISTS `verification_prices` (`id` bigint unsigned not null auto_increment primary key, `service_type` varchar(255) not null, `price` decimal(12, 2) not null default '0', `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('verification_prices', 'verification_prices_service_type_unique', '`service_type`');
CREATE TABLE IF NOT EXISTS `manual_funding` (`id` bigint unsigned not null auto_increment primary key, `bank_name` varchar(255) null, `account_number` varchar(255) null, `account_name` varchar(255) null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CREATE TABLE IF NOT EXISTS `payment_gateways` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `display_name` varchar(255) null, `is_active` tinyint(1) not null default '1', `priority` int not null default '1', `logo_url` varchar(255) null, `config` json null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('payment_gateways', 'payment_gateways_name_unique', '`name`');

-- ------------------------------------------------------------------------------
-- Migration [17]: 2026_04_08_232704_update_admins_for_2fa
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [18]: 2026_04_10_120000_grandfather_email_verified_at_for_existing_users
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [19]: 2026_04_10_180000_add_details_to_delivery_agents_table
-- ------------------------------------------------------------------------------
CALL fuwa_add_column_if_not_exists('delivery_agents', 'address', 'varchar(255) null after `city`');
CALL fuwa_add_column_if_not_exists('delivery_agents', 'phone_number', 'varchar(255) null after `address`');
CALL fuwa_add_column_if_not_exists('delivery_agents', 'means_of_identification', 'varchar(255) null after `phone_number`');
CALL fuwa_add_column_if_not_exists('delivery_agents', 'identification_number', 'varchar(255) null after `means_of_identification`');
CALL fuwa_add_column_if_not_exists('delivery_agents', 'proof_of_address', 'varchar(255) null after `identification_number`');
CALL fuwa_add_column_if_not_exists('delivery_agents', 'next_of_kin_name', 'varchar(255) null after `proof_of_address`');
CALL fuwa_add_column_if_not_exists('delivery_agents', 'next_of_kin_phone', 'varchar(255) null after `next_of_kin_name`');

-- ------------------------------------------------------------------------------
-- Migration [20]: 2026_04_11_120000_create_price_list_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `price_list` (`id` bigint unsigned not null auto_increment primary key, `network` varchar(32) not null, `data_plan` varchar(255) not null, `amount` decimal(12, 2) not null, `validate` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- ------------------------------------------------------------------------------
-- Migration [21]: 2026_04_13_102754_create_cache_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cache` (`key` varchar(255) not null, `value` mediumtext not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CREATE TABLE IF NOT EXISTS `cache_locks` (`key` varchar(255) not null, `owner` varchar(255) not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- ------------------------------------------------------------------------------
-- Migration [22]: 2026_04_13_170000_add_google_columns_to_users_table
-- ------------------------------------------------------------------------------
CALL fuwa_add_column_if_not_exists('users', 'google_id', 'varchar(255) null after `email`');
CALL fuwa_add_unique_if_not_exists('users', 'users_google_id_unique', '`google_id`');
CALL fuwa_add_column_if_not_exists('users', 'google_avatar', 'text null after `google_id`');

-- ------------------------------------------------------------------------------
-- Migration [23]: 2026_04_15_205354_add_performance_indexes_to_core_tables
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [24]: 2026_04_15_205911_add_query_tuned_composite_indexes
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [25]: 2026_04_16_000001_create_service_sessions_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_sessions` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `service` varchar(50) not null, `token` varchar(64) not null, `scopes` json null, `ip_address` varchar(45) null, `user_agent` varchar(255) null, `expires_at` timestamp not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('service_sessions', 'service_sessions_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('service_sessions', 'service_sessions_token_service_index', '`token`, `service`');
CALL fuwa_add_index_if_not_exists('service_sessions', 'service_sessions_user_id_service_index', '`user_id`, `service`');
CALL fuwa_add_index_if_not_exists('service_sessions', 'service_sessions_service_expires_at_index', '`service`, `expires_at`');

-- ------------------------------------------------------------------------------
-- Migration [26]: 2026_04_16_000002_create_logistics_profiles_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logistics_profiles` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `company_name` varchar(255) null, `contact_person` varchar(255) null, `phone` varchar(20) null, `alternate_phone` varchar(20) null, `email` varchar(255) null, `address` text null, `city` varchar(100) null, `state` varchar(100) null, `business_type` enum('individual', 'company', 'enterprise') not null default 'individual', `preferred_delivery` enum('standard', 'express', 'overnight') not null default 'standard', `notification_preferences` json null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('logistics_profiles', 'logistics_profiles_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('logistics_profiles', 'logistics_profiles_user_id_is_active_index', '`user_id`, `is_active`');
CALL fuwa_add_unique_if_not_exists('logistics_profiles', 'logistics_profiles_user_id_unique', '`user_id`');

-- ------------------------------------------------------------------------------
-- Migration [27]: 2026_04_16_145912_add_api_access_status_to_users_table
-- ------------------------------------------------------------------------------
CALL fuwa_add_column_if_not_exists('users', 'api_access_status', 'varchar(255) not null default \'none\'');
CALL fuwa_add_column_if_not_exists('users', 'api_application_details', 'json null');
CALL fuwa_add_index_if_not_exists('users', 'users_api_access_status_index', '`api_access_status`');

-- ------------------------------------------------------------------------------
-- Migration [28]: 2026_04_16_160000_create_logistics_staff_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logistics_staff` (`id` bigint unsigned not null auto_increment primary key, `fullname` varchar(255) null, `email` varchar(255) not null, `password` varchar(255) not null, `is_active` tinyint(1) not null default '1', `created_by_admin_id` bigint unsigned null, `last_login_at` timestamp null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('logistics_staff', 'logistics_staff_created_by_admin_id_foreign', 'foreign key (`created_by_admin_id`) references `admins` (`id`) on delete set null');
CALL fuwa_add_unique_if_not_exists('logistics_staff', 'logistics_staff_email_unique', '`email`');
CALL fuwa_add_index_if_not_exists('logistics_staff', 'logistics_staff_is_active_index', '`is_active`');

-- ------------------------------------------------------------------------------
-- Migration [29]: 2026_04_16_160100_create_logistics_staff_jwt_sessions_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logistics_staff_jwt_sessions` (`id` bigint unsigned not null auto_increment primary key, `logistics_staff_id` bigint unsigned not null, `jti` varchar(64) not null, `expires_at` timestamp not null, `revoked_at` timestamp null, `ip_address` varchar(45) null, `user_agent` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('logistics_staff_jwt_sessions', 'logistics_staff_jwt_sessions_logistics_staff_id_foreign', 'foreign key (`logistics_staff_id`) references `logistics_staff` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('logistics_staff_jwt_sessions', 'logistics_staff_jwt_sessions_logistics_staff_id_expires_at_index', '`logistics_staff_id`, `expires_at`');
CALL fuwa_add_unique_if_not_exists('logistics_staff_jwt_sessions', 'logistics_staff_jwt_sessions_jti_unique', '`jti`');
CALL fuwa_add_index_if_not_exists('logistics_staff_jwt_sessions', 'logistics_staff_jwt_sessions_expires_at_index', '`expires_at`');
CALL fuwa_add_index_if_not_exists('logistics_staff_jwt_sessions', 'logistics_staff_jwt_sessions_revoked_at_index', '`revoked_at`');

-- ------------------------------------------------------------------------------
-- Migration [30]: 2026_04_16_160200_update_logistics_requests_for_ops_rbac
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logistics_requests` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `sender_name` varchar(255) null, `sender_address` text null, `recipient_name` varchar(255) null, `recipient_address` text null, `weight` decimal(10, 2) null, `description` text null, `delivery_type` varchar(255) null, `amount` decimal(10, 2) null, `tracking_id` varchar(255) not null, `status` varchar(255) not null default 'processing', `waybill_path` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('logistics_requests', 'logistics_requests_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_unique_if_not_exists('logistics_requests', 'logistics_requests_tracking_id_unique', '`tracking_id`');
CALL fuwa_add_index_if_not_exists('logistics_requests', 'logistics_requests_status_index', '`status`');
CALL fuwa_add_column_if_not_exists('logistics_requests', 'assigned_manager_id', 'bigint unsigned null');
CALL fuwa_add_column_if_not_exists('logistics_requests', 'assigned_officer_id', 'bigint unsigned null');
CALL fuwa_add_column_if_not_exists('logistics_requests', 'assigned_delivery_agent_id', 'bigint unsigned null');
CALL fuwa_add_column_if_not_exists('logistics_requests', 'scheduled_pickup_at', 'timestamp null');
CALL fuwa_add_column_if_not_exists('logistics_requests', 'route_code', 'varchar(255) null');
CALL fuwa_add_column_if_not_exists('logistics_requests', 'last_status_updated_at', 'timestamp null');
CALL fuwa_add_index_if_not_exists('logistics_requests', 'logistics_requests_assigned_manager_id_index', '`assigned_manager_id`');
CALL fuwa_add_index_if_not_exists('logistics_requests', 'logistics_requests_assigned_officer_id_index', '`assigned_officer_id`');
CALL fuwa_add_index_if_not_exists('logistics_requests', 'logistics_requests_assigned_delivery_agent_id_index', '`assigned_delivery_agent_id`');
CALL fuwa_add_index_if_not_exists('logistics_requests', 'logistics_requests_scheduled_pickup_at_index', '`scheduled_pickup_at`');
CALL fuwa_add_index_if_not_exists('logistics_requests', 'logistics_requests_route_code_index', '`route_code`');
CALL fuwa_add_index_if_not_exists('logistics_requests', 'logistics_requests_last_status_updated_at_index', '`last_status_updated_at`');

-- ------------------------------------------------------------------------------
-- Migration [31]: 2026_04_16_160300_create_logistics_inventory_items_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logistics_inventory_items` (`id` bigint unsigned not null auto_increment primary key, `sku` varchar(255) not null, `name` varchar(255) not null, `description` text null, `quantity` int unsigned not null default '0', `location` varchar(255) null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('logistics_inventory_items', 'logistics_inventory_items_sku_unique', '`sku`');
CALL fuwa_add_index_if_not_exists('logistics_inventory_items', 'logistics_inventory_items_is_active_index', '`is_active`');

-- ------------------------------------------------------------------------------
-- Migration [32]: 2026_04_16_170000_add_agent_assignment_fields_to_logistics_requests_table
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [33]: 2026_04_16_180000_create_logistics_centers_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logistics_centers` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `type` varchar(255) not null, `state` varchar(255) not null, `city` varchar(255) null, `address` varchar(255) null, `lat` decimal(10, 7) null, `lng` decimal(10, 7) null, `availability_status` varchar(255) not null default 'available', `is_active` tinyint(1) not null default '1', `capacity_per_day` int unsigned null, `current_load` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_index_if_not_exists('logistics_centers', 'logistics_centers_state_type_is_active_index', '`state`, `type`, `is_active`');
CALL fuwa_add_index_if_not_exists('logistics_centers', 'logistics_centers_type_index', '`type`');
CALL fuwa_add_index_if_not_exists('logistics_centers', 'logistics_centers_state_index', '`state`');
CALL fuwa_add_index_if_not_exists('logistics_centers', 'logistics_centers_city_index', '`city`');
CALL fuwa_add_index_if_not_exists('logistics_centers', 'logistics_centers_lat_index', '`lat`');
CALL fuwa_add_index_if_not_exists('logistics_centers', 'logistics_centers_lng_index', '`lng`');
CALL fuwa_add_index_if_not_exists('logistics_centers', 'logistics_centers_availability_status_index', '`availability_status`');
CALL fuwa_add_index_if_not_exists('logistics_centers', 'logistics_centers_is_active_index', '`is_active`');

-- ------------------------------------------------------------------------------
-- Migration [34]: 2026_04_16_180100_add_location_and_pricing_fields_to_logistics_requests_table
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [35]: 2026_04_16_190000_create_logistics_ai_pricing_models_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logistics_ai_pricing_models` (`id` bigint unsigned not null auto_increment primary key, `version` varchar(255) not null, `feature_keys` json not null, `weights` json not null, `multiplier` decimal(18, 6) not null default '0', `metrics` json null, `trained_at` timestamp null, `is_active` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('logistics_ai_pricing_models', 'logistics_ai_pricing_models_version_unique', '`version`');
CALL fuwa_add_index_if_not_exists('logistics_ai_pricing_models', 'logistics_ai_pricing_models_trained_at_index', '`trained_at`');
CALL fuwa_add_index_if_not_exists('logistics_ai_pricing_models', 'logistics_ai_pricing_models_is_active_index', '`is_active`');

-- ------------------------------------------------------------------------------
-- Migration [36]: 2026_04_17_000001_create_api_centers_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_centers` (`id` bigint unsigned not null auto_increment primary key, `dataverify_api_key` text null, `dataverify_endpoint_nin` text null, `dataverify_endpoint_bvn` text null, `dataverify_endpoint_data` text null, `dataverify_endpoint_phone` text null, `dataverify_endpoint_tid` text null, `dataverify_endpoint_premium_slip` text null, `dataverify_endpoint_premium_slip_phone` text null, `dataverify_endpoint_standard_slip` text null, `dataverify_endpoint_regular_slip` text null, `dataverify_endpoint_vnin_slip` text null, `payvessel_api_key` text null, `payvessel_secret_key` text null, `paystack_public_key` text null, `paystack_secret_key` text null, `flutterwave_public_key` text null, `flutterwave_secret_key` text null, `flutterwave_encryption_key` text null, `paypoint_secret_key` text null, `paypoint_api_key` text null, `paypoint_businessid` text null, `paypoint_endpoint` text null, `payvessel_endpoint` text null, `payvessel_businessid` text null, `monnify_api_key` text null, `monnify_secret_key` text null, `monnify_endpoint_auth` text null, `monnify_endpoint_reserve` text null, `monnify_contract_code` text null, `ade_apikey` text null, `ade_endpoint_exam` text null, `ade_endpoint_airtime` text null, `ade_endpoint_bill` text null, `ade_endpoint_data` text null, `nexus_notary_key` text null, `nexus_logistics_key` text null, `nexus_api_secret` text null, `robosttech_api_key` text null, `robosttech_endpoint_nin` text null, `robosttech_endpoint_validation` text null, `robosttech_endpoint_clearance` text null, `robosttech_endpoint_clearance_status` text null, `robosttech_endpoint_personalization` text null, `gemini_api_key` text null, `sms_ai_key` text null, `sms_ai_endpoint` text null, `sms_ai_sender` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- ------------------------------------------------------------------------------
-- Migration [37]: 2026_04_17_000002_create_verification_results_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `verification_results` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `service_type` varchar(100) not null, `identifier` varchar(191) not null, `provider_name` varchar(191) null, `response_data` json null, `status` varchar(50) not null default 'pending', `reference_id` varchar(191) null, `report_path` text null, `admin_note` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_index_if_not_exists('verification_results', 'verification_results_user_id_index', '`user_id`');
CALL fuwa_add_index_if_not_exists('verification_results', 'verification_results_service_type_index', '`service_type`');
CALL fuwa_add_index_if_not_exists('verification_results', 'verification_results_identifier_index', '`identifier`');
CALL fuwa_add_index_if_not_exists('verification_results', 'verification_results_provider_name_index', '`provider_name`');
CALL fuwa_add_index_if_not_exists('verification_results', 'verification_results_status_index', '`status`');
CALL fuwa_add_index_if_not_exists('verification_results', 'verification_results_reference_id_index', '`reference_id`');

-- ------------------------------------------------------------------------------
-- Migration [38]: 2026_04_17_000003_create_api_tokens_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_tokens` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `name` varchar(120) not null, `token_hash` varchar(64) not null, `last_four` varchar(8) null, `abilities` json null, `rate_limit_per_minute` int unsigned not null default '60', `last_used_at` timestamp null, `expires_at` timestamp null, `revoked_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_index_if_not_exists('api_tokens', 'api_tokens_user_id_index', '`user_id`');
CALL fuwa_add_unique_if_not_exists('api_tokens', 'api_tokens_token_hash_unique', '`token_hash`');

-- ------------------------------------------------------------------------------
-- Migration [39]: 2026_04_17_000004_create_developer_api_endpoints_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `developer_api_endpoints` (`id` bigint unsigned not null auto_increment primary key, `slug` varchar(120) not null, `group_name` varchar(80) null, `name` varchar(160) not null, `method` varchar(10) not null default 'GET', `path_pattern` varchar(255) not null, `is_enabled` tinyint(1) not null default '1', `docs_summary` text null, `docs_request_example` longtext null, `docs_response_example` longtext null, `sort_order` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('developer_api_endpoints', 'developer_api_endpoints_slug_unique', '`slug`');
CALL fuwa_add_index_if_not_exists('developer_api_endpoints', 'developer_api_endpoints_group_name_index', '`group_name`');
CALL fuwa_add_index_if_not_exists('developer_api_endpoints', 'developer_api_endpoints_is_enabled_index', '`is_enabled`');

-- ------------------------------------------------------------------------------
-- Migration [40]: 2026_04_17_000005_create_developer_api_request_logs_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `developer_api_request_logs` (`id` bigint unsigned not null auto_increment primary key, `api_token_id` bigint unsigned null, `user_id` bigint unsigned null, `endpoint_slug` varchar(120) null, `method` varchar(10) not null, `path` varchar(255) not null, `status_code` smallint unsigned null, `ip_address` varchar(64) null, `declared_website` varchar(255) null, `origin_host` varchar(255) null, `referer_host` varchar(255) null, `user_agent` text null, `requested_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_index_if_not_exists('developer_api_request_logs', 'developer_api_request_logs_api_token_id_index', '`api_token_id`');
CALL fuwa_add_index_if_not_exists('developer_api_request_logs', 'developer_api_request_logs_user_id_index', '`user_id`');
CALL fuwa_add_index_if_not_exists('developer_api_request_logs', 'developer_api_request_logs_endpoint_slug_index', '`endpoint_slug`');
CALL fuwa_add_index_if_not_exists('developer_api_request_logs', 'developer_api_request_logs_status_code_index', '`status_code`');
CALL fuwa_add_index_if_not_exists('developer_api_request_logs', 'developer_api_request_logs_origin_host_index', '`origin_host`');
CALL fuwa_add_index_if_not_exists('developer_api_request_logs', 'developer_api_request_logs_referer_host_index', '`referer_host`');
CALL fuwa_add_index_if_not_exists('developer_api_request_logs', 'developer_api_request_logs_requested_at_index', '`requested_at`');

-- ------------------------------------------------------------------------------
-- Migration [41]: 2026_04_17_000006_create_custom_apis_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `custom_apis` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `provider_identifier` varchar(255) null, `service_type` varchar(255) not null, `supported_modes` json null, `endpoint` text null, `api_key` text null, `secret_key` text null, `headers` json null, `config` json null, `status` tinyint(1) not null default '1', `priority` int unsigned not null default '0', `price` decimal(15, 2) null, `timeout_seconds` int unsigned null, `retry_count` int unsigned null, `retry_delay_ms` int unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_index_if_not_exists('custom_apis', 'custom_apis_provider_identifier_index', '`provider_identifier`');
CALL fuwa_add_index_if_not_exists('custom_apis', 'custom_apis_service_type_index', '`service_type`');
CALL fuwa_add_index_if_not_exists('custom_apis', 'custom_apis_status_index', '`status`');

-- ------------------------------------------------------------------------------
-- Migration [42]: 2026_04_17_000007_create_system_settings_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_settings` (`id` bigint unsigned not null auto_increment primary key, `key` varchar(255) not null, `value` longtext null, `type` varchar(50) null, `group` varchar(80) null, `label` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('system_settings', 'system_settings_key_unique', '`key`');
CALL fuwa_add_index_if_not_exists('system_settings', 'system_settings_group_index', '`group`');

-- ------------------------------------------------------------------------------
-- Migration [43]: 2026_04_17_000008_create_custom_api_verification_types_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `custom_api_verification_types` (`id` bigint unsigned not null auto_increment primary key, `custom_api_id` bigint unsigned not null, `type_key` varchar(255) not null, `label` varchar(255) null, `price` decimal(18, 2) not null default '0', `status` tinyint(1) not null default '1', `sort_order` int not null default '0', `meta` json null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('custom_api_verification_types', 'custom_api_verification_types_custom_api_id_foreign', 'foreign key (`custom_api_id`) references `custom_apis` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('custom_api_verification_types', 'custom_api_verification_types_type_key_index', '`type_key`');

-- ------------------------------------------------------------------------------
-- Migration [44]: 2026_04_17_220000_create_auction_tables
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auction_sellers` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `location` varchar(255) null, `rating` decimal(3, 1) null, `reviews_count` int unsigned not null default '0', `verified` tinyint(1) not null default '0', `avatar_url` varchar(255) null, `about` text null, `created_at` timestamp null, `updated_at` timestamp null, `deleted_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CREATE TABLE IF NOT EXISTS `auction_lots` (`id` bigint unsigned not null auto_increment primary key, `seller_id` bigint unsigned null, `lot_code` varchar(255) not null, `title` varchar(255) not null, `category` varchar(255) null, `location` varchar(255) null, `description` text null, `starting_price` decimal(12, 2) not null default '0', `current_price` decimal(12, 2) not null default '0', `bid_increment` decimal(12, 2) not null default '0', `start_at` timestamp null, `end_at` timestamp null, `status` varchar(255) not null default 'scheduled', `featured` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null, `deleted_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('auction_lots', 'auction_lots_seller_id_foreign', 'foreign key (`seller_id`) references `auction_sellers` (`id`) on delete set null');
CALL fuwa_add_index_if_not_exists('auction_lots', 'auction_lots_status_end_at_index', '`status`, `end_at`');
CALL fuwa_add_index_if_not_exists('auction_lots', 'auction_lots_category_index', '`category`');
CALL fuwa_add_index_if_not_exists('auction_lots', 'auction_lots_location_index', '`location`');
CALL fuwa_add_unique_if_not_exists('auction_lots', 'auction_lots_lot_code_unique', '`lot_code`');
CREATE TABLE IF NOT EXISTS `auction_lot_images` (`id` bigint unsigned not null auto_increment primary key, `auction_lot_id` bigint unsigned not null, `url` varchar(255) not null, `sort_order` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null, `deleted_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('auction_lot_images', 'auction_lot_images_auction_lot_id_foreign', 'foreign key (`auction_lot_id`) references `auction_lots` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('auction_lot_images', 'auction_lot_images_auction_lot_id_sort_order_index', '`auction_lot_id`, `sort_order`');
CREATE TABLE IF NOT EXISTS `auction_bids` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `lot_id` varchar(255) not null, `item_name` varchar(255) null, `bid_amount` decimal(12, 2) not null, `status` varchar(255) not null default 'winning', `reference` varchar(255) not null, `created_at` timestamp null, `updated_at` timestamp null, `deleted_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('auction_bids', 'auction_bids_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('auction_bids', 'auction_bids_lot_id_status_index', '`lot_id`, `status`');
CALL fuwa_add_index_if_not_exists('auction_bids', 'auction_bids_user_id_index', '`user_id`');
CALL fuwa_add_unique_if_not_exists('auction_bids', 'auction_bids_reference_unique', '`reference`');
CREATE TABLE IF NOT EXISTS `auction_watchlists` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `lot_code` varchar(255) not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('auction_watchlists', 'auction_watchlists_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_unique_if_not_exists('auction_watchlists', 'auction_watchlists_user_id_lot_code_unique', '`user_id`, `lot_code`');
CALL fuwa_add_index_if_not_exists('auction_watchlists', 'auction_watchlists_lot_code_index', '`lot_code`');

-- ------------------------------------------------------------------------------
-- Migration [45]: 2026_04_17_230000_create_auction_admins_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auction_admins` (`id` bigint unsigned not null auto_increment primary key, `fullname` varchar(255) null, `email` varchar(255) not null, `password` varchar(255) not null, `is_active` tinyint(1) not null default '1', `created_by_admin_id` bigint unsigned null, `last_login_at` timestamp null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('auction_admins', 'auction_admins_created_by_admin_id_foreign', 'foreign key (`created_by_admin_id`) references `admins` (`id`) on delete set null');
CALL fuwa_add_unique_if_not_exists('auction_admins', 'auction_admins_email_unique', '`email`');

-- ------------------------------------------------------------------------------
-- Migration [46]: 2026_04_17_230100_create_auction_admin_audit_logs_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auction_admin_audit_logs` (`id` bigint unsigned not null auto_increment primary key, `auction_admin_id` bigint unsigned null, `action` varchar(255) not null, `meta` json null, `ip` varchar(45) null, `user_agent` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('auction_admin_audit_logs', 'auction_admin_audit_logs_auction_admin_id_foreign', 'foreign key (`auction_admin_id`) references `auction_admins` (`id`) on delete set null');
CALL fuwa_add_index_if_not_exists('auction_admin_audit_logs', 'auction_admin_audit_logs_auction_admin_id_created_at_index', '`auction_admin_id`, `created_at`');

-- ------------------------------------------------------------------------------
-- Migration [47]: 2026_04_20_140700_add_two_factor_fields_to_admins_table
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [48]: 2026_07_23_220000_make_admin_id_nullable_in_admin_audit_logs_table
-- ------------------------------------------------------------------------------
alter table `admin_audit_logs` modify `admin_id` bigint unsigned null;

-- ------------------------------------------------------------------------------
-- Migration [49]: 2026_07_23_221000_fix_fundings_table_auto_increment
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [50]: 2026_07_24_000001_repair_custom_apis_auto_increment
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [51]: 2026_07_24_000002_repair_dataverify_nin_endpoint
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Migration [52]: 2026_07_24_145920_create_jobs_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jobs` (`id` bigint unsigned not null auto_increment primary key, `queue` varchar(255) not null, `payload` longtext not null, `attempts` tinyint unsigned not null, `reserved_at` int unsigned null, `available_at` int unsigned not null, `created_at` int unsigned not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_index_if_not_exists('jobs', 'jobs_queue_index', '`queue`');

-- ------------------------------------------------------------------------------
-- Migration [53]: 2026_07_24_145922_create_failed_jobs_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `failed_jobs` (`id` bigint unsigned not null auto_increment primary key, `uuid` varchar(255) not null, `connection` text not null, `queue` text not null, `payload` longtext not null, `exception` longtext not null, `failed_at` timestamp not null default CURRENT_TIMESTAMP) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_unique_if_not_exists('failed_jobs', 'failed_jobs_uuid_unique', '`uuid`');

-- ------------------------------------------------------------------------------
-- Migration [54]: 2026_09_19_082313_create_parcel_couriers_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parcel_couriers` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `adapter_class` varchar(255) not null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
insert into `parcel_couriers` (`name`, `adapter_class`, `is_active`, `created_at`, `updated_at`) values ('FuwaPost', 'App\\Services\\Parcels\\Adapters\\FuwaPostAdapter', 1, '2026-09-23 09:49:49', '2026-09-23 09:49:49');

-- ------------------------------------------------------------------------------
-- Migration [55]: 2026_09_19_082314_create_parcel_shops_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parcel_shops` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `address` varchar(255) null, `state` varchar(255) null, `city` varchar(255) null, `lat` decimal(10, 7) null, `lng` decimal(10, 7) null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null, `deleted_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- ------------------------------------------------------------------------------
-- Migration [56]: 2026_09_19_082316_create_parcel_agents_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parcel_agents` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `shop_id` bigint unsigned not null, `nin_number` varchar(255) null, `status` varchar(255) not null default 'pending', `verified_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null, `deleted_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('parcel_agents', 'parcel_agents_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_fk_if_not_exists('parcel_agents', 'parcel_agents_shop_id_foreign', 'foreign key (`shop_id`) references `parcel_shops` (`id`) on delete cascade');

-- ------------------------------------------------------------------------------
-- Migration [57]: 2026_09_19_082317_create_parcels_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parcels` (`id` bigint unsigned not null auto_increment primary key, `tracking_number` varchar(255) not null, `courier_id` bigint unsigned not null, `shop_id` bigint unsigned not null, `status` varchar(255) not null, `condition` varchar(255) not null default 'good', `sender_data` json null, `receiver_data` json null, `weight` decimal(8, 2) null, `price` decimal(10, 2) null, `created_at` timestamp null, `updated_at` timestamp null, `deleted_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('parcels', 'parcels_courier_id_foreign', 'foreign key (`courier_id`) references `parcel_couriers` (`id`) on delete cascade');
CALL fuwa_add_fk_if_not_exists('parcels', 'parcels_shop_id_foreign', 'foreign key (`shop_id`) references `parcel_shops` (`id`) on delete cascade');
CALL fuwa_add_unique_if_not_exists('parcels', 'parcels_tracking_number_unique', '`tracking_number`');
CALL fuwa_add_index_if_not_exists('parcels', 'parcels_status_index', '`status`');

-- ------------------------------------------------------------------------------
-- Migration [58]: 2026_09_19_082318_create_parcel_custody_events_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parcel_custody_events` (`id` bigint unsigned not null auto_increment primary key, `parcel_id` bigint unsigned not null, `agent_id` bigint unsigned not null, `event_type` varchar(255) not null, `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('parcel_custody_events', 'parcel_custody_events_parcel_id_foreign', 'foreign key (`parcel_id`) references `parcels` (`id`) on delete cascade');
CALL fuwa_add_fk_if_not_exists('parcel_custody_events', 'parcel_custody_events_agent_id_foreign', 'foreign key (`agent_id`) references `parcel_agents` (`id`) on delete cascade');
CALL fuwa_add_index_if_not_exists('parcel_custody_events', 'parcel_custody_events_event_type_index', '`event_type`');

-- ------------------------------------------------------------------------------
-- Migration [59]: 2026_09_22_000000_create_enrollment_agents_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `enrollment_agents` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `full_name` varchar(255) not null, `phone_number` varchar(255) not null, `residential_address` text not null, `office_address` text not null, `bvn` varchar(11) not null, `nin` varchar(11) not null, `nin_verified` tinyint(1) not null default '0', `nin_verification_meta` json null, `machine_imei` varchar(255) null, `status` enum('pending', 'approved', 'rejected', 'suspended') not null default 'pending', `rejection_reason` text null, `total_enrollments` int unsigned not null default '0', `monthly_enrollments` int unsigned not null default '0', `is_mva_of_month` tinyint(1) not null default '0', `meta` json null, `approved_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('enrollment_agents', 'enrollment_agents_user_id_foreign', 'foreign key (`user_id`) references `users` (`id`) on delete cascade');
CALL fuwa_add_unique_if_not_exists('enrollment_agents', 'enrollment_agents_user_id_unique', '`user_id`');

-- ------------------------------------------------------------------------------
-- Migration [60]: 2026_09_22_010000_add_agent_fields_to_tickets_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tickets` (`id` bigint unsigned not null auto_increment primary key, `user_email` varchar(255) not null, `agent_id` bigint unsigned null, `subject` varchar(255) not null, `category` varchar(255) not null default 'other', `machine_imei` varchar(255) null, `priority` varchar(255) not null default 'medium', `status` varchar(255) not null default 'open', `attachment_path` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('tickets', 'tickets_agent_id_foreign', 'foreign key (`agent_id`) references `enrollment_agents` (`id`) on delete set null');
CREATE TABLE IF NOT EXISTS `ticket_replies` (`id` bigint unsigned not null auto_increment primary key, `ticket_id` bigint unsigned not null, `sender_type` enum('user', 'admin') not null default 'user', `message` text not null, `attachment_path` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('ticket_replies', 'ticket_replies_ticket_id_foreign', 'foreign key (`ticket_id`) references `tickets` (`id`) on delete cascade');

-- ------------------------------------------------------------------------------
-- Migration [61]: 2026_09_22_030000_add_onboarding_fields_to_enrollment_agents
-- ------------------------------------------------------------------------------
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'agent_type', 'enum(\'existing\', \'new\') not null default \'new\' after `user_id`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'company_agent_code', 'varchar(255) null after `agent_type`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'state', 'varchar(255) null after `phone_number`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'has_machine', 'tinyint(1) not null default \'0\' after `state`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'utility_bill_path', 'varchar(255) null after `machine_imei`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'picture_path', 'varchar(255) null after `utility_bill_path`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'business_registration_number', 'varchar(255) null after `picture_path`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'business_registration_doc_path', 'varchar(255) null after `business_registration_number`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'nin_server_status', 'enum(\'verified\', \'pending_fallback\', \'failed\') not null default \'pending_fallback\' after `nin_verified`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'accepted_terms', 'tinyint(1) not null default \'0\' after `status`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'terms_accepted_at', 'timestamp null after `accepted_terms`');
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'onboarding_step', 'enum(\'basic_info\', \'kyc_docs\', \'compliance_terms\', \'submitted\') not null default \'basic_info\' after `terms_accepted_at`');

-- ------------------------------------------------------------------------------
-- Migration [62]: 2026_09_22_040000_add_is_fast_tracked_to_enrollment_agents
-- ------------------------------------------------------------------------------
CALL fuwa_add_column_if_not_exists('enrollment_agents', 'is_fast_tracked', 'tinyint(1) not null default \'0\' after `company_agent_code`');

-- ------------------------------------------------------------------------------
-- Migration [63]: 2026_09_22_050000_create_pre_approved_agents_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pre_approved_agents` (`id` bigint unsigned not null auto_increment primary key, `agent_code` varchar(255) not null, `first_name` varchar(255) null, `last_name` varchar(255) null, `full_name` varchar(255) null, `email` varchar(255) null, `phone_number` varchar(255) null, `is_claimed` tinyint(1) not null default '0', `claimed_at` timestamp null, `claimed_by_user_id` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
CALL fuwa_add_fk_if_not_exists('pre_approved_agents', 'pre_approved_agents_claimed_by_user_id_foreign', 'foreign key (`claimed_by_user_id`) references `users` (`id`) on delete set null');
CALL fuwa_add_unique_if_not_exists('pre_approved_agents', 'pre_approved_agents_agent_code_unique', '`agent_code`');
CALL fuwa_add_index_if_not_exists('pre_approved_agents', 'pre_approved_agents_email_index', '`email`');
CALL fuwa_add_index_if_not_exists('pre_approved_agents', 'pre_approved_agents_phone_number_index', '`phone_number`');

-- ------------------------------------------------------------------------------
-- Migration [64]: 2026_09_22_050000_drop_delivery_agents_table
-- ------------------------------------------------------------------------------
drop table if exists `delivery_agents`;

-- ==============================================================================
-- Laravel Migrations Tracker Registration
-- Inserts migration entries into `migrations` table with batch = 2
-- ==============================================================================
INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
  ('2024_01_01_000000_create_delivery_agents_table', 2),
  ('2024_01_01_000000_create_notary_settings_table', 2),
  ('2024_01_01_000000_create_pages_table', 2),
  ('2024_01_01_000000_create_payment_gateways_table', 2),
  ('2024_01_01_000000_create_posts_table', 2),
  ('2024_01_01_000000_create_referral_tiers_table', 2),
  ('2024_01_01_000000_create_verification_prices_table', 2),
  ('2024_01_01_000000_create_virtual_account_audit_logs_table', 2),
  ('2024_01_01_000000_create_virtual_accounts_table', 2),
  ('2024_01_01_000000_create_virtual_cards_table', 2),
  ('2024_01_01_000000_create_vtu_transactions_table', 2),
  ('2024_01_01_000000_create_whatsapp_click_logs_table', 2),
  ('2024_01_01_000001_create_ab_events_table', 2),
  ('2024_01_01_000001_create_shipping_providers_table', 2),
  ('2024_01_01_000002_create_notary_tables', 2),
  ('2024_01_01_000003_create_admin_support_tables', 2),
  ('2026_04_08_232704_update_admins_for_2fa', 2),
  ('2026_04_10_120000_grandfather_email_verified_at_for_existing_users', 2),
  ('2026_04_10_180000_add_details_to_delivery_agents_table', 2),
  ('2026_04_11_120000_create_price_list_table', 2),
  ('2026_04_13_102754_create_cache_table', 2),
  ('2026_04_13_170000_add_google_columns_to_users_table', 2),
  ('2026_04_15_205354_add_performance_indexes_to_core_tables', 2),
  ('2026_04_15_205911_add_query_tuned_composite_indexes', 2),
  ('2026_04_16_000001_create_service_sessions_table', 2),
  ('2026_04_16_000002_create_logistics_profiles_table', 2),
  ('2026_04_16_145912_add_api_access_status_to_users_table', 2),
  ('2026_04_16_160000_create_logistics_staff_table', 2),
  ('2026_04_16_160100_create_logistics_staff_jwt_sessions_table', 2),
  ('2026_04_16_160200_update_logistics_requests_for_ops_rbac', 2),
  ('2026_04_16_160300_create_logistics_inventory_items_table', 2),
  ('2026_04_16_170000_add_agent_assignment_fields_to_logistics_requests_table', 2),
  ('2026_04_16_180000_create_logistics_centers_table', 2),
  ('2026_04_16_180100_add_location_and_pricing_fields_to_logistics_requests_table', 2),
  ('2026_04_16_190000_create_logistics_ai_pricing_models_table', 2),
  ('2026_04_17_000001_create_api_centers_table', 2),
  ('2026_04_17_000002_create_verification_results_table', 2),
  ('2026_04_17_000003_create_api_tokens_table', 2),
  ('2026_04_17_000004_create_developer_api_endpoints_table', 2),
  ('2026_04_17_000005_create_developer_api_request_logs_table', 2),
  ('2026_04_17_000006_create_custom_apis_table', 2),
  ('2026_04_17_000007_create_system_settings_table', 2),
  ('2026_04_17_000008_create_custom_api_verification_types_table', 2),
  ('2026_04_17_220000_create_auction_tables', 2),
  ('2026_04_17_230000_create_auction_admins_table', 2),
  ('2026_04_17_230100_create_auction_admin_audit_logs_table', 2),
  ('2026_04_20_140700_add_two_factor_fields_to_admins_table', 2),
  ('2026_07_23_220000_make_admin_id_nullable_in_admin_audit_logs_table', 2),
  ('2026_07_23_221000_fix_fundings_table_auto_increment', 2),
  ('2026_07_24_000001_repair_custom_apis_auto_increment', 2),
  ('2026_07_24_000002_repair_dataverify_nin_endpoint', 2),
  ('2026_07_24_145920_create_jobs_table', 2),
  ('2026_07_24_145922_create_failed_jobs_table', 2),
  ('2026_09_19_082313_create_parcel_couriers_table', 2),
  ('2026_09_19_082314_create_parcel_shops_table', 2),
  ('2026_09_19_082316_create_parcel_agents_table', 2),
  ('2026_09_19_082317_create_parcels_table', 2),
  ('2026_09_19_082318_create_parcel_custody_events_table', 2),
  ('2026_09_22_000000_create_enrollment_agents_table', 2),
  ('2026_09_22_010000_add_agent_fields_to_tickets_table', 2),
  ('2026_09_22_030000_add_onboarding_fields_to_enrollment_agents', 2),
  ('2026_09_22_040000_add_is_fast_tracked_to_enrollment_agents', 2),
  ('2026_09_22_050000_create_pre_approved_agents_table', 2),
  ('2026_09_22_050000_drop_delivery_agents_table', 2),
  ('2026_09_23_140000_expand_users_username_column', 2);

-- ------------------------------------------------------------------------------
-- Migration [65]: 2026_09_23_140000_expand_users_username_column
-- ------------------------------------------------------------------------------
ALTER TABLE `users` MODIFY `username` VARCHAR(60) NULL;


-- ==============================================================================
-- CLEANUP HELPER PROCEDURES
-- ==============================================================================
DROP PROCEDURE IF EXISTS `fuwa_add_column_if_not_exists`;
DROP PROCEDURE IF EXISTS `fuwa_add_index_if_not_exists`;
DROP PROCEDURE IF EXISTS `fuwa_add_unique_if_not_exists`;
DROP PROCEDURE IF EXISTS `fuwa_add_fk_if_not_exists`;

SET FOREIGN_KEY_CHECKS = 1;
