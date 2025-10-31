-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 29-10-2025 a las 03:40:32
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sync360_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `category_mappings`
--

DROP TABLE IF EXISTS `category_mappings`;
CREATE TABLE IF NOT EXISTS `category_mappings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `icg_category_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icg_category_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'familia',
  `magento_category_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `magento_category_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `product_count` int NOT NULL DEFAULT '0',
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `additional_config` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_mappings_icg_category_key_unique` (`icg_category_key`),
  KEY `category_mappings_icg_category_key_index` (`icg_category_key`),
  KEY `category_mappings_magento_category_id_index` (`magento_category_id`),
  KEY `category_mappings_is_active_icg_category_type_index` (`is_active`,`icg_category_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configurations`
--

DROP TABLE IF EXISTS `configurations`;
CREATE TABLE IF NOT EXISTS `configurations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configurations_key_unique` (`key`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configurations`
--

INSERT INTO `configurations` (`id`, `key`, `value`, `type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'icg_api_url', 'http://190.14.213.6:5004/icg/api/articulo', 'string', 'ICG API Base URL', '2025-10-29 08:11:56', '2025-10-29 08:14:09'),
(2, 'icg_api_username', 'jeremy.castillo@supercarnes.com', 'string', 'ICG API Username', '2025-10-29 08:11:56', '2025-10-29 08:12:59'),
(3, 'icg_api_password', 'supercarnes', 'password', 'ICG API Password', '2025-10-29 08:11:56', '2025-10-29 08:14:09'),
(4, 'icg_api_token', '53KyKWs4vyUhqtJ3bc', 'string', 'ICG API Token (optional)', '2025-10-29 08:11:56', '2025-10-29 08:14:09'),
(5, 'icg_timeout', '30', 'number', 'ICG API Timeout in seconds', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(6, 'icg_enabled', 'true', 'boolean', 'Enable ICG API', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(7, 'magento_api_url', 'https://mcstaging.supercarnes.com', 'string', 'Magento API Base URL', '2025-10-29 08:11:56', '2025-10-29 08:12:59'),
(8, 'magento_api_token', '4vrr3x4vrrzcjgtzpq6anoy5elmm7hh8', 'string', 'Magento API Bearer Token', '2025-10-29 08:11:56', '2025-10-29 08:12:59'),
(9, 'magento_store_code', 'default', 'string', 'Magento Store Code', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(10, 'magento_timeout', '30', 'number', 'Magento API Timeout in seconds', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(11, 'magento_enabled', 'true', 'boolean', 'Enable Magento API', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(12, 'ftp_host', 'ftp.virzi.app', 'string', 'FTP Server Host', '2025-10-29 08:11:56', '2025-10-29 08:14:09'),
(13, 'ftp_port', '21', 'number', 'FTP Server Port', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(14, 'ftp_username', 'updatecron@apps.supercarnes.com', 'string', 'FTP Username', '2025-10-29 08:11:56', '2025-10-29 08:14:09'),
(15, 'ftp_password', 'S0p0rt3*', 'password', 'FTP Password', '2025-10-29 08:11:56', '2025-10-29 08:14:29'),
(16, 'ftp_root', '/', 'string', 'FTP Root Directory', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(17, 'ftp_enabled', 'false', 'boolean', 'Enable FTP', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(18, 'sync_batch_size', '100', 'number', 'Batch size for sync operations', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(19, 'sync_timeout', '300', 'number', 'Sync timeout in seconds', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(20, 'sync_retry_attempts', '3', 'number', 'Number of retry attempts', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(21, 'sync_retry_delay', '60', 'number', 'Delay between retries in seconds', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(22, 'sync_enabled', 'true', 'boolean', 'Enable sync process', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(23, 'logs_retention_days', '30', 'number', 'Log retention period in days', '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(24, 'notifications_email', 'jeremy.castillo@supercarnes.com', 'string', 'Email for notifications', '2025-10-29 08:11:56', '2025-10-29 08:14:09'),
(25, 'notifications_enabled', 'false', 'boolean', 'Enable notifications', '2025-10-29 08:11:56', '2025-10-29 08:11:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `executions`
--

DROP TABLE IF EXISTS `executions`;
CREATE TABLE IF NOT EXISTS `executions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `workflow_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` enum('total','partial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'total',
  `skus` text COLLATE utf8mb4_unicode_ci,
  `date_filter` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('pending','running','completed_success','completed_success_no_ftp','completed_empty','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `duration_seconds` int DEFAULT NULL,
  `total_items` int NOT NULL DEFAULT '0',
  `success_count` int NOT NULL DEFAULT '0',
  `failed_count` int NOT NULL DEFAULT '0',
  `skipped_count` int NOT NULL DEFAULT '0',
  `csv_filename` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `csv_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ftp_uploaded` tinyint(1) NOT NULL DEFAULT '0',
  `result_message` text COLLATE utf8mb4_unicode_ci,
  `error_details` json DEFAULT NULL,
  `configuration_snapshot` json DEFAULT NULL,
  `trigger_type` enum('manual','scheduled','api') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `schedule_rule_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `executions_job_id_unique` (`job_id`),
  KEY `executions_user_id_foreign` (`user_id`),
  KEY `executions_schedule_rule_id_foreign` (`schedule_rule_id`),
  KEY `executions_workflow_id_status_index` (`workflow_id`,`status`),
  KEY `executions_created_at_status_index` (`created_at`,`status`),
  KEY `executions_job_id_index` (`job_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `execution_logs`
--

DROP TABLE IF EXISTS `execution_logs`;
CREATE TABLE IF NOT EXISTS `execution_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `execution_id` bigint UNSIGNED NOT NULL,
  `level` enum('DEBUG','INFO','SUCCESS','WARNING','ERROR','CRITICAL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INFO',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` json DEFAULT NULL,
  `sku` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_page` int DEFAULT NULL,
  `total_pages` int DEFAULT NULL,
  `progress_percentage` int DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `execution_logs_execution_id_level_index` (`execution_id`,`level`),
  KEY `execution_logs_logged_at_index` (`logged_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_26_100005_create_workflows_table', 1),
(5, '2025_10_26_100010_create_executions_table', 1),
(6, '2025_10_26_100015_create_execution_logs_table', 1),
(7, '2025_10_26_100020_create_schedule_rules_table', 1),
(8, '2025_10_26_100025_create_category_mappings_table', 1),
(9, '2025_10_26_100030_create_sync_configurations_table', 1),
(10, '2025_10_27_044058_add_role_and_is_active_to_users_table', 1),
(11, '2025_10_27_044318_create_configurations_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedule_rules`
--

DROP TABLE IF EXISTS `schedule_rules`;
CREATE TABLE IF NOT EXISTS `schedule_rules` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `workflow_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` enum('total','partial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'total',
  `skus` text COLLATE utf8mb4_unicode_ci,
  `frequency` enum('daily','weekly','monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'daily',
  `execution_time` time NOT NULL,
  `day_of_week` int DEFAULT NULL,
  `day_of_month` int DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `run_count` int NOT NULL DEFAULT '0',
  `configuration` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule_rules_user_id_foreign` (`user_id`),
  KEY `schedule_rules_workflow_id_is_enabled_index` (`workflow_id`,`is_enabled`),
  KEY `schedule_rules_next_run_at_index` (`next_run_at`),
  KEY `schedule_rules_execution_time_is_enabled_index` (`execution_time`,`is_enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('zitZSeWYiEuZ8EUHqI19i3yNRPfaPoTrUjpKhDoP', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaXJGS0tBbnBucHY1bXlhaW1yaDY2NmFha0ZGRUFmNWhVYW9pMEVQNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3QvMzYwc3luYy9wdWJsaWMvY29uZmlndXJhdGlvbiI7czo1OiJyb3V0ZSI7czoxOToiY29uZmlndXJhdGlvbi5pbmRleCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1761708044);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sync_configurations`
--

DROP TABLE IF EXISTS `sync_configurations`;
CREATE TABLE IF NOT EXISTS `sync_configurations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `encrypted_value` text COLLATE utf8mb4_unicode_ci,
  `is_encrypted` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('string','integer','boolean','json','url','email','password') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_visible` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `validation_rules` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_value` text COLLATE utf8mb4_unicode_ci,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `last_tested_at` timestamp NULL DEFAULT NULL,
  `test_passed` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sync_configurations_key_unique` (`key`),
  KEY `sync_configurations_updated_by_foreign` (`updated_by`),
  KEY `sync_configurations_category_is_visible_index` (`category`,`is_visible`),
  KEY `sync_configurations_key_index` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `is_active`, `last_login`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Jeremy', 'jeremy.castillo@supercarnes.com', 'admin', '2025-10-29 08:11:56', '$2y$12$JBS/OpxCwCsfBvvgQQGQI.xwB09f8ClRg1GYZm2nGagSF/rby/sA.', 1, NULL, NULL, '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(2, 'Regular User', 'admin@supercarnes.com', 'user', '2025-10-29 08:11:56', '$2y$12$46i6gAnktinKg4UTF7JCkuV1eXFdsHTdp7CLJnL./VqkmR4JuzDz.', 1, NULL, NULL, '2025-10-29 08:11:56', '2025-10-29 08:11:56'),
(3, 'Viewer User', 'viewer@supercarnes.com.com', 'viewer', '2025-10-29 08:11:56', '$2y$12$fTRbLDYY2SlVgf7mIrIwh.bxWwo3eQ8jU3yYii240LV5l0qoAht5K', 1, NULL, NULL, '2025-10-29 08:11:56', '2025-10-29 08:11:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `workflows`
--

DROP TABLE IF EXISTS `workflows`;
CREATE TABLE IF NOT EXISTS `workflows` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'refresh',
  `color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'blue',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `supports_partial` tinyint(1) NOT NULL DEFAULT '1',
  `supports_date_filter` tinyint(1) NOT NULL DEFAULT '1',
  `configuration` json DEFAULT NULL,
  `execution_count` int NOT NULL DEFAULT '0',
  `last_executed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workflows_slug_unique` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
