-- ============================================================================
--  Migration 002 — lead_history
--  Append-only audit log of lifecycle events on a lead row. Drives the
--  Activity timeline on the admin lead-detail page.
-- ============================================================================

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `lead_history`;
CREATE TABLE `lead_history` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id`    INT UNSIGNED NOT NULL,
  `user_id`    INT UNSIGNED NULL,
  `event_type` ENUM('created','status_changed','notes_updated','archived') NOT NULL,
  `event_data` JSON NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lead_history_lead`       (`lead_id`, `created_at` DESC),
  KEY `idx_lead_history_user`       (`user_id`),
  KEY `idx_lead_history_event_type` (`event_type`),
  CONSTRAINT `fk_lead_history_lead`
    FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lead_history_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill a 'created' event for any lead that already exists, so the
-- Activity timeline isn't blank for pre-Phase-8 rows.
INSERT INTO `lead_history` (`lead_id`, `user_id`, `event_type`, `event_data`, `created_at`)
SELECT `id`, NULL, 'created', NULL, `created_at` FROM `leads`;
