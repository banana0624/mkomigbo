-- 004_create_roles.sql
CREATE TABLE IF NOT EXISTS roles (
  id   INT(11) NOT NULL AUTO_INCREMENT,
  slug VARCHAR(50)  NOT NULL,
  name VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed canonical roles
INSERT IGNORE INTO roles (id, slug, name) VALUES
  (1,'admin','Admin'),
  (2,'editor','Editor'),
  (3,'viewer','Viewer');
