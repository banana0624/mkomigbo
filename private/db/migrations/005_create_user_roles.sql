-- 005_create_user_roles.sql
-- Drop any previous attempt and create with matching INT(11) signed columns.

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS user_roles;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE user_roles (
  user_id INT(11) NOT NULL,
  role_id INT(11) NOT NULL,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_user_roles_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_user_roles_role
    FOREIGN KEY (role_id) REFERENCES roles(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
