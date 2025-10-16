-- 004a_fix_roles_signature.sql
-- Make sure roles table matches users: INT(11) signed, InnoDB, utf8mb4, slug unique.

CREATE TABLE IF NOT EXISTS roles (
  id   INT(11) NOT NULL AUTO_INCREMENT,
  slug VARCHAR(50)  NOT NULL,
  name VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If roles already exists, normalize id type and ensure unique slug.
ALTER TABLE roles
  MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE roles
  ADD UNIQUE KEY uq_roles_slug (slug);
