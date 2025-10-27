-- scripts/contributors_schema.sql
-- Creates minimal tables used by staff/contributors (Directory, Reviews, Credits)

CREATE DATABASE IF NOT EXISTS `mkomigbo`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `mkomigbo`;

CREATE TABLE IF NOT EXISTS contributors_directory (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  email VARCHAR(190) NULL,
  bio TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_contrib_slug (slug),
  KEY idx_contrib_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contributors_reviews (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  subject VARCHAR(190) NOT NULL,
  rating TINYINT UNSIGNED NOT NULL DEFAULT 0, -- 0..5
  comment TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_reviews_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contributors_credits (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(190) NOT NULL,
  url VARCHAR(255) NULL,
  contributor VARCHAR(190) NULL,  -- free text; you can later FK to directory.id
  role VARCHAR(190) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_credits_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
