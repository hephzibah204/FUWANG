USE `fuwaagri_fuwang`;

CREATE TABLE IF NOT EXISTS `verification_prices` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nin_by_nin_price` DECIMAL(10,2) NOT NULL DEFAULT 200.00,
    `nin_by_number_price` DECIMAL(10,2) NOT NULL DEFAULT 200.00,
    `nin_by_demography_price` DECIMAL(10,2) NOT NULL DEFAULT 200.00,
    `bvn_by_bvn` DECIMAL(10,2) NOT NULL DEFAULT 100.00,
    `bvn_by_number` DECIMAL(10,2) NOT NULL DEFAULT 100.00,
    `verify_by_tracking_id` DECIMAL(10,2) NOT NULL DEFAULT 300.00,
    `validation_price` DECIMAL(10,2) NOT NULL DEFAULT 700.00,
    `ipe_clearance_price` DECIMAL(10,2) NOT NULL DEFAULT 400.00,
    `personalization_price` DECIMAL(10,2) NOT NULL DEFAULT 100.00,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `verification_prices` (
    `nin_by_nin_price`,
    `nin_by_number_price`,
    `nin_by_demography_price`,
    `bvn_by_bvn`,
    `bvn_by_number`,
    `verify_by_tracking_id`,
    `validation_price`,
    `ipe_clearance_price`,
    `personalization_price`,
    `created_at`,
    `updated_at`
)
SELECT
    200.00,
    200.00,
    200.00,
    100.00,
    100.00,
    300.00,
    700.00,
    400.00,
    100.00,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM `verification_prices`);

CREATE TABLE IF NOT EXISTS `custom_api_verification_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `custom_api_id` BIGINT UNSIGNED NOT NULL,
    `type_key` VARCHAR(80) NOT NULL,
    `label` VARCHAR(120) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `meta` JSON NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `custom_api_type_unique` (`custom_api_id`, `type_key`),
    KEY `custom_api_verification_types_custom_api_id_index` (`custom_api_id`),
    CONSTRAINT `custom_api_verification_types_custom_api_id_foreign`
        FOREIGN KEY (`custom_api_id`)
        REFERENCES `custom_apis` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL,
    `cancelled_at` INT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE `custom_apis`
SET
    `service_type` = 'nin_verification',
    `supported_modes` = JSON_ARRAY(
        'nin',
        'phone',
        'tracking',
        'demographic',
        'share_code',
        'requery'
    ),
    `status` = 1,
    `updated_at` = NOW()
WHERE LOWER(TRIM(`name`)) = 'vuvaa';

SELECT
    `id`,
    `name`,
    `service_type`,
    `supported_modes`,
    `status`
FROM `custom_apis`
WHERE LOWER(TRIM(`name`)) = 'vuvaa';

SELECT * FROM `verification_prices`;

SHOW TABLES LIKE 'custom_api_verification_types';
SHOW TABLES LIKE 'jobs';
