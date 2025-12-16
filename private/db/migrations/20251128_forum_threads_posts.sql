-- project-root/private/db/migrations/20251128_forum_threads_posts.sql

-- 2025-11-28 – Create forum_threads + forum_posts tables

-- 1) Table: forum_threads
CREATE TABLE IF NOT EXISTS `forum_threads` (
  `id`                       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`              INT(10) UNSIGNED NOT NULL,
  `subject_id`               INT(10) UNSIGNED NULL,
  `page_id`                  INT(10) UNSIGNED NULL,
  `starter_contributor_id`   INT(10) UNSIGNED NULL,
  `starter_admin_id`         INT(10) UNSIGNED NULL,
  `starter_display_name`     VARCHAR(190) NOT NULL,
  `title`                    VARCHAR(190) NOT NULL,
  `slug`                     VARCHAR(190) NOT NULL,
  `body`                     TEXT NOT NULL,
  `is_public`                TINYINT(1) NOT NULL DEFAULT 1,
  `status`                   TINYINT(1) NOT NULL DEFAULT 0,  -- 0=open,1=locked,2=archived
  `views_count`              INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `posts_count`              INT(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at`               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,
  `last_post_at`             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_forum_threads_slug` (`slug`),
  KEY `idx_forum_threads_category` (`category_id`),
  KEY `idx_forum_threads_subject_page` (`subject_id`, `page_id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 2) Table: forum_posts  (individual posts inside a thread)
CREATE TABLE IF NOT EXISTS `forum_posts` (
  `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `thread_id`      INT(10) UNSIGNED NOT NULL,
  `contributor_id` INT(10) UNSIGNED NULL,
  `admin_id`       INT(10) UNSIGNED NULL,
  `display_name`   VARCHAR(190) NOT NULL,
  `body`           TEXT NOT NULL,
  `is_public`      TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                      ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_forum_posts_thread` (`thread_id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;