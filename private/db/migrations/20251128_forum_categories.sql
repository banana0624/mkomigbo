-- project-root/private/db/migrations/20251128_forum_categories.sql
--
-- 2025-11-28 – Create forum_categories table + seed basic categories

-- 1) Table: forum_categories
CREATE TABLE IF NOT EXISTS `forum_categories` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(190) NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `description` TEXT NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `threads_count` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `posts_count` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_forum_categories_slug` (`slug`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 2) Seed: basic categories (INSERT IGNORE so it’s safe to re-run)

INSERT IGNORE INTO `forum_categories`
  (`slug`,                    `title`,                     `description`,                                      `is_public`, `sort_order`)
VALUES
  ('history-interpretation',  'History & interpretation',  'Discuss historical events, sources and perspectives.', 1, 10),
  ('language-expression',     'Language & expression',     'Questions and discussion about language, writing and translation.', 1, 20),
  ('culture-belief-life',     'Culture, belief & life',    'Culture, religion, everyday life and lived experience.', 1, 30),
  ('general-discussion',      'General discussion',        'Announcements, meta-topics and other general questions.', 1, 40);