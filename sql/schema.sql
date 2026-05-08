-- ============================================================================
--  Codentra — Database Schema
--  Engine: InnoDB · Charset: utf8mb4 · Collation: utf8mb4_unicode_ci
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── users ───────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(120) NOT NULL,
  `email`           VARCHAR(160) NOT NULL,
  `password_hash`   VARCHAR(255) NOT NULL,
  `role`            ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  `last_login_at`   DATETIME NULL,
  `created_at`      DATETIME NOT NULL,
  `updated_at`      DATETIME NOT NULL,
  `deleted_at`      DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── categories ──────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`          VARCHAR(120) NOT NULL,
  `name`          VARCHAR(120) NOT NULL,
  `description`   VARCHAR(500) NULL,
  `created_at`    DATETIME NOT NULL,
  `updated_at`    DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── posts ───────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`            VARCHAR(180) NOT NULL,
  `title`           VARCHAR(220) NOT NULL,
  `excerpt`         VARCHAR(500) NULL,
  `content`         LONGTEXT NOT NULL,
  `featured_image`  VARCHAR(255) NULL,
  `image_alt`       VARCHAR(255) NULL,
  `category_id`     INT UNSIGNED NULL,
  `author_id`       INT UNSIGNED NULL,
  `status`          ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `views`           INT UNSIGNED NOT NULL DEFAULT 0,
  `published_at`    DATETIME NULL,
  `created_at`      DATETIME NOT NULL,
  `updated_at`      DATETIME NOT NULL,
  `deleted_at`      DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_posts_slug` (`slug`),
  KEY `idx_posts_status` (`status`),
  KEY `idx_posts_published_at` (`published_at`),
  KEY `idx_posts_category_id` (`category_id`),
  KEY `idx_posts_author_id` (`author_id`),
  KEY `idx_posts_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_posts_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_posts_author`
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── leads ───────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS `leads`;
CREATE TABLE `leads` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(120) NOT NULL,
  `email`        VARCHAR(160) NOT NULL,
  `phone`        VARCHAR(40) NULL,
  `company`      VARCHAR(160) NULL,
  `service`      ENUM('web-dev','shopify','ecommerce-mgmt','automation','other') NOT NULL DEFAULT 'other',
  `budget`       VARCHAR(60) NULL,
  `message`      TEXT NOT NULL,
  `source`       VARCHAR(60) NOT NULL DEFAULT 'website',
  `status`       ENUM('new','contacted','qualified','converted','lost') NOT NULL DEFAULT 'new',
  `notes`        TEXT NULL,
  `ip_address`   VARCHAR(45) NULL,
  `user_agent`   VARCHAR(500) NULL,
  `created_at`   DATETIME NOT NULL,
  `updated_at`   DATETIME NOT NULL,
  `deleted_at`   DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_leads_email` (`email`),
  KEY `idx_leads_status` (`status`),
  KEY `idx_leads_created_at` (`created_at`),
  KEY `idx_leads_service` (`service`),
  KEY `idx_leads_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── settings ────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name`   VARCHAR(120) NOT NULL,
  `value`      TEXT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_settings_key_name` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── sessions ────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id`            VARCHAR(128) NOT NULL,
  `user_id`       INT UNSIGNED NULL,
  `payload`       TEXT NOT NULL,
  `last_activity` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sessions_user_id` (`user_id`),
  KEY `idx_sessions_last_activity` (`last_activity`),
  CONSTRAINT `fk_sessions_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── rate_limits ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE `rate_limits` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_hash`   VARCHAR(64) NOT NULL,
  `attempts`   INT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rate_limits_key_hash` (`key_hash`),
  KEY `idx_rate_limits_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
