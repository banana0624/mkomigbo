/* project-root\scripts\bootstrap_more.sql */
SET NAMES utf8mb4;
SET sql_mode='STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ========== PAGES: add missing 'title' column ==========
ALTER TABLE pages
  ADD COLUMN title VARCHAR(255) NULL AFTER menu_name;

-- Back-fill title from menu_name if empty
UPDATE pages SET title = COALESCE(NULLIF(title,''), menu_name);

-- ========== USERS (minimal admin/users support) ==========
CREATE TABLE IF NOT EXISTS users (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name           VARCHAR(150) NOT NULL,
  email          VARCHAR(255) NOT NULL,
  username       VARCHAR(100) NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  role_id        INT UNSIGNED NOT NULL,
  is_active      TINYINT(1)   NOT NULL DEFAULT 1,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME         NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email    (email),
  UNIQUE KEY uq_users_username (username),
  CONSTRAINT fk_users_role
    FOREIGN KEY (role_id) REFERENCES roles(id)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: seed 1 admin user (password hash is a placeholder; replace later via UI)
INSERT INTO users (name,email,username,password_hash,role_id,is_active)
SELECT 'Site Admin','admin@local.test','admin',
       '$2y$10$abcdefghijklmnopqrstuvC9wqVnO1rZ0G1E0p2uQbK1a0R9lO7mXk2',  -- placeholder bcrypt
       r.id, 1
FROM roles r
WHERE r.slug='admin'
AND NOT EXISTS (SELECT 1 FROM users WHERE username='admin');

-- ========== AUDIT LOG (basic) ==========
CREATE TABLE IF NOT EXISTS audit_log (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_time     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actor_user_id  INT UNSIGNED             NULL,
  action         VARCHAR(100)    NOT NULL,
  entity_type    VARCHAR(100)             NULL,
  entity_id      VARCHAR(64)              NULL,
  details_json   LONGTEXT                 NULL, -- JSON text
  ip             VARCHAR(45)              NULL,
  user_agent     VARCHAR(255)             NULL,
  PRIMARY KEY (id),
  KEY idx_audit_time (event_time),
  KEY idx_audit_actor (actor_user_id),
  CONSTRAINT fk_audit_user
    FOREIGN KEY (actor_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
