/* project-root/scripts/010_finish_line.sql */
SET NAMES utf8mb4;
SET @db := DATABASE();

/* =========================
   USERS
   ========================= */
CREATE TABLE IF NOT EXISTS users (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username       VARCHAR(64)  NOT NULL,
  email          VARCHAR(191) NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  role           VARCHAR(32)  NOT NULL DEFAULT 'editor',
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email    (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure role column exists (safe if it already does)
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS role VARCHAR(32) NOT NULL DEFAULT 'editor';

-- Collision-safe admin seeding (no ON DUPLICATE KEY; won’t throw 1062):
-- 1) If 'admin' user already exists, just ensure its role is 'admin'
UPDATE users
SET role = 'admin'
WHERE username = 'admin';

-- 2) Only insert a new 'admin' if NEITHER username nor email exists
--    (keeps any existing password/email intact on re-runs)
INSERT INTO users (username, email, password_hash, role)
SELECT 'admin', 'admin@example.com', 'x', 'admin'
WHERE NOT EXISTS (
  SELECT 1 FROM users
  WHERE username = 'admin' OR email = 'admin@example.com'
);

/* =========================
   ROLES
   ========================= */
CREATE TABLE IF NOT EXISTS roles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NOT NULL,
  slug VARCHAR(64) NOT NULL,
  permissions_json JSON NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (name, slug, permissions_json)
SELECT 'Admin','admin', JSON_ARRAY(
  'pages.view','pages.create','pages.edit','pages.delete','pages.publish',
  'contributors.view','contributors.create','contributors.edit','contributors.delete',
  'contributors.reviews.view','contributors.reviews.create','contributors.reviews.edit','contributors.reviews.delete',
  'contributors.credits.view','contributors.credits.create','contributors.credits.edit','contributors.credits.delete',
  'audit.view'
)
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE slug='admin');

/* =========================
   USER_ROLES (mapping)
   ========================= */
CREATE TABLE IF NOT EXISTS user_roles (
  user_id INT UNSIGNED NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure admin user is mapped to admin role
INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.slug='admin'
WHERE u.username='admin';

/* =========================
   AUDIT LOG
   Supports both shapes:
   A) user_id + meta_json
   B) actor_id + details
   Our PHP reads with COALESCE(...) to tolerate both.
   ========================= */
CREATE TABLE IF NOT EXISTS audit_log (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      INT NULL,
  actor_id     INT NULL,
  action       VARCHAR(100) NOT NULL,
  entity_type  VARCHAR(100) NULL,
  entity_id    VARCHAR(64)  NULL,
  meta_json    JSON NULL,
  details      TEXT NULL,
  ip_addr      VARCHAR(45) NULL,
  user_agent   VARCHAR(255) NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns if missing (MariaDB 10.4 supports IF NOT EXISTS on columns)
ALTER TABLE audit_log
  ADD COLUMN IF NOT EXISTS user_id INT NULL,
  ADD COLUMN IF NOT EXISTS actor_id INT NULL,
  ADD COLUMN IF NOT EXISTS action VARCHAR(100) NOT NULL,
  ADD COLUMN IF NOT EXISTS entity_type VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS entity_id VARCHAR(64) NULL,
  ADD COLUMN IF NOT EXISTS meta_json JSON NULL,
  ADD COLUMN IF NOT EXISTS details TEXT NULL,
  ADD COLUMN IF NOT EXISTS ip_addr VARCHAR(45) NULL,
  ADD COLUMN IF NOT EXISTS user_agent VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Create indexes IF ABSENT (MariaDB 10.4-safe via INFORMATION_SCHEMA + dynamic SQL)
-- idx_audit_created_at
SET @exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='audit_log' AND INDEX_NAME='idx_audit_created_at'
);
SET @sql := IF(@exists=0, 'CREATE INDEX idx_audit_created_at ON audit_log (created_at)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_audit_actor on (user_id, created_at)
SET @exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='audit_log' AND INDEX_NAME='idx_audit_actor'
);
SET @sql := IF(@exists=0, 'CREATE INDEX idx_audit_actor ON audit_log (user_id, created_at)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_audit_actor2 on (actor_id, created_at)
SET @exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='audit_log' AND INDEX_NAME='idx_audit_actor2'
);
SET @sql := IF(@exists=0, 'CREATE INDEX idx_audit_actor2 ON audit_log (actor_id, created_at)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
