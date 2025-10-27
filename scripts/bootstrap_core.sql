-- Charset / SQL mode (safe defaults for local dev)
SET NAMES utf8mb4;
SET sql_mode='STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ===== roles ===============================================================
CREATE TABLE IF NOT EXISTS roles (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name             VARCHAR(100) NOT NULL,
  slug             VARCHAR(100) NOT NULL,
  permissions_json LONGTEXT     NULL,   -- store JSON text
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME     NULL     ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- admin role (idempotent upsert)
INSERT INTO roles (name, slug, permissions_json)
VALUES
('Administrator','admin', JSON_ARRAY(
  'pages.view','pages.create','pages.edit','pages.delete','pages.publish',
  'contributors.view','contributors.create','contributors.edit',
  'contributors.reviews.view','contributors.reviews.create','contributors.reviews.edit',
  'contributors.credits.view','contributors.credits.create','contributors.credits.edit',
  'audit.view'
))
ON DUPLICATE KEY UPDATE
  permissions_json = VALUES(permissions_json);

-- ===== subjects (minimal stub so pages can relate) =========================
CREATE TABLE IF NOT EXISTS subjects (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  menu_name   VARCHAR(255) NOT NULL,
  position    INT          NOT NULL DEFAULT 1,
  visible     TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NULL     ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed one subject if empty
INSERT INTO subjects (menu_name, position, visible)
SELECT 'People', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM subjects);

-- ===== pages ===============================================================
CREATE TABLE IF NOT EXISTS pages (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  subject_id  INT UNSIGNED NOT NULL,
  menu_name   VARCHAR(255) NOT NULL,      -- page title/menu label
  position    INT          NOT NULL DEFAULT 1,
  visible     TINYINT(1)   NOT NULL DEFAULT 1,
  content     MEDIUMTEXT   NULL,
  slug        VARCHAR(255) NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NULL     ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pages_subject (subject_id),
  CONSTRAINT fk_pages_subject
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed one page (idempotent-ish)
INSERT INTO pages (subject_id, menu_name, position, visible, content, slug)
SELECT s.id, 'Welcome', 1, 1, 'Hello world from the People subject.', 'welcome'
FROM subjects s
WHERE s.menu_name='People'
  AND NOT EXISTS (SELECT 1 FROM pages WHERE slug='welcome');

-- (Optional) you can add admins/users tables later if those screens need them.
