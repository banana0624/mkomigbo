-- project-root/private/db/migrations/2025_11_27_forum_scheme.sql
-- =====================================================================
-- Forum schema (core)
-- Tables:
--   forum_categories
--   forum_threads
--   forum_posts
-- =====================================================================

-- You can adjust ENGINE / CHARSET to match your existing schema
-- (likely InnoDB + utf8mb4_unicode_ci).

-- ---------------------------------------------------------------------
-- 1) Categories
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS forum_categories (
  id           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  slug         VARCHAR(190)     NOT NULL,
  title        VARCHAR(190)     NOT NULL,
  description  TEXT             NULL,
  position     INT(10) UNSIGNED NOT NULL DEFAULT 0,
  is_public    TINYINT(1)       NOT NULL DEFAULT 1,
  created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_forum_categories_slug (slug),
  KEY idx_forum_categories_position (position),
  KEY idx_forum_categories_is_public (is_public)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2) Threads
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS forum_threads (
  id                       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id              INT(10) UNSIGNED NOT NULL,
  subject_id               INT(10) UNSIGNED NULL,
  page_id                  INT(10) UNSIGNED NULL,

  title                    VARCHAR(190)     NOT NULL,
  slug                     VARCHAR(190)     NOT NULL,

  starter_contributor_id   INT(10) UNSIGNED NULL,
  starter_admin_id         INT(10) UNSIGNED NULL,
  starter_display_name     VARCHAR(190)     NULL,

  -- status: 0=open, 1=locked (no new posts), 2=archived (read-only / hidden)
  status                   TINYINT(1)       NOT NULL DEFAULT 0,
  is_pinned                TINYINT(1)       NOT NULL DEFAULT 0,
  is_public                TINYINT(1)       NOT NULL DEFAULT 1,

  views_count              INT(10) UNSIGNED NOT NULL DEFAULT 0,
  posts_count              INT(10) UNSIGNED NOT NULL DEFAULT 0,
  last_post_at             DATETIME         NULL,

  created_at               DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at               DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_forum_threads_slug (slug),
  KEY idx_forum_threads_category (category_id),
  KEY idx_forum_threads_subject (subject_id),
  KEY idx_forum_threads_page (page_id),
  KEY idx_forum_threads_status (status),
  KEY idx_forum_threads_pinned (is_pinned),
  KEY idx_forum_threads_public (is_public),
  KEY idx_forum_threads_last_post (last_post_at),

  CONSTRAINT fk_forum_threads_category
    FOREIGN KEY (category_id) REFERENCES forum_categories (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,

  -- Optional: these assume you have these tables.
  CONSTRAINT fk_forum_threads_subject
    FOREIGN KEY (subject_id) REFERENCES subjects (id)
    ON UPDATE CASCADE ON DELETE SET NULL,

  CONSTRAINT fk_forum_threads_page
    FOREIGN KEY (page_id) REFERENCES pages (id)
    ON UPDATE CASCADE ON DELETE SET NULL,

  CONSTRAINT fk_forum_threads_starter_contributor
    FOREIGN KEY (starter_contributor_id) REFERENCES contributors (id)
    ON UPDATE CASCADE ON DELETE SET NULL,

  CONSTRAINT fk_forum_threads_starter_admin
    FOREIGN KEY (starter_admin_id) REFERENCES admins (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3) Posts
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS forum_posts (
  id                   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  thread_id            INT(10) UNSIGNED NOT NULL,
  parent_post_id       INT(10) UNSIGNED NULL,

  contributor_id       INT(10) UNSIGNED NULL,
  admin_id             INT(10) UNSIGNED NULL,
  display_name         VARCHAR(190)     NULL,

  body                 TEXT             NOT NULL,

  is_visible           TINYINT(1)       NOT NULL DEFAULT 1,
  is_edited            TINYINT(1)       NOT NULL DEFAULT 0,
  edited_at            DATETIME         NULL,
  edited_by_admin_id   INT(10) UNSIGNED NULL,

  created_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_forum_posts_thread (thread_id),
  KEY idx_forum_posts_parent (parent_post_id),
  KEY idx_forum_posts_visible (is_visible),
  KEY idx_forum_posts_created (created_at),

  CONSTRAINT fk_forum_posts_thread
    FOREIGN KEY (thread_id) REFERENCES forum_threads (id)
    ON UPDATE CASCADE ON DELETE CASCADE,

  CONSTRAINT fk_forum_posts_parent
    FOREIGN KEY (parent_post_id) REFERENCES forum_posts (id)
    ON UPDATE CASCADE ON DELETE SET NULL,

  CONSTRAINT fk_forum_posts_contributor
    FOREIGN KEY (contributor_id) REFERENCES contributors (id)
    ON UPDATE CASCADE ON DELETE SET NULL,

  CONSTRAINT fk_forum_posts_admin
    FOREIGN KEY (admin_id) REFERENCES admins (id)
    ON UPDATE CASCADE ON DELETE SET NULL,

  CONSTRAINT fk_forum_posts_edited_by
    FOREIGN KEY (edited_by_admin_id) REFERENCES admins (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;