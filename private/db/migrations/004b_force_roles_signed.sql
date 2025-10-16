-- 004b_force_roles_signed.sql
-- Normalize roles.id to INT(11) signed (to match users.id).
ALTER TABLE roles
  MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

-- Only add the unique index if it's missing.
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'roles'
    AND INDEX_NAME   = 'uq_roles_slug'
);
SET @sql := IF(@idx_exists = 0,
  'ALTER TABLE roles ADD UNIQUE KEY `uq_roles_slug` (`slug`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
