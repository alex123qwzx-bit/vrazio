-- =====================================================================
-- VRAZIO Haute Joaillerie — Warranty Claim Tables
-- MySQL 8.x / Hostinger Production Migration
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Table: warranty_claims
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `warranty_claims` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_number` VARCHAR(30) NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `product_name` VARCHAR(255) DEFAULT NULL,
  `issue_type` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `status` ENUM('submitted', 'under_review', 'approved', 'rejected', 'resolved') NOT NULL DEFAULT 'submitted',
  `is_checked` TINYINT(1) NOT NULL DEFAULT 0,
  `checked_by` BIGINT UNSIGNED DEFAULT NULL,
  `checked_at` TIMESTAMP NULL DEFAULT NULL,
  `admin_note` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_claim_number` (`claim_number`),
  UNIQUE KEY `uniq_warranty_order_id` (`order_id`),
  KEY `idx_warranty_user_id` (`user_id`),
  KEY `idx_warranty_status` (`status`),
  KEY `idx_warranty_created_at` (`created_at`),
  KEY `idx_warranty_is_checked` (`is_checked`),
  CONSTRAINT `fk_warranty_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_warranty_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_warranty_checked_by` FOREIGN KEY (`checked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: warranty_claim_images
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `warranty_claim_images` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_id` BIGINT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_claim_images_claim_id` (`claim_id`),
  CONSTRAINT `fk_claim_images_claim` FOREIGN KEY (`claim_id`) REFERENCES `warranty_claims` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
