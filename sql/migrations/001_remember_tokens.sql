-- ============================================================================
--  Migration 001 — remember_tokens
--  Run on production after applying schema.sql + seed.sql.
--  Stores selector/validator tuples for "Remember me" persistent login.
-- ============================================================================

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE `remember_tokens` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`           INT UNSIGNED NOT NULL,
  `selector`          VARCHAR(32)  NOT NULL,   -- 16-byte random, hex-encoded
  `hashed_validator`  VARCHAR(255) NOT NULL,   -- sha256(validator), hex
  `expires_at`        DATETIME NOT NULL,
  `created_at`        DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_remember_tokens_selector`   (`selector`),
  KEY        `idx_remember_tokens_user_id`     (`user_id`),
  KEY        `idx_remember_tokens_expires_at`  (`expires_at`),
  CONSTRAINT `fk_remember_tokens_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
