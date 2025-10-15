/* project-root/private/db/mkomigbo_allinone.sql */

-- =====================================================
-- MKOMIGBO ALL-IN-ONE SCRIPT
-- Rebuild → Repopulate → Test → Insert default admin
-- =====================================================

-- 1. Rebuild database structure
SOURCE private/db/mkomigbo_full.sql;

-- 2. Load sample demo rows
SOURCE private/db/sample_data.sql;

-- 3. Run verification checks
SOURCE private/db/test_mkomigbo.sql;

-- 4. Insert default admin user
USE mkomigbo;

INSERT INTO users (username, email, password_hash, role) VALUES
(
  'admin',
  'admin@example.com',
  '$2y$10$YjF4kKQ0EwEjKCF9GxIV7.O0XhvUq4gqJbz7UEVtqWwZ/7a5MQV/O', -- password_hash('admin123')
  'admin'
)
ON DUPLICATE KEY UPDATE username=username;

-- =====================================================
-- End of All-In-One Script
-- =====================================================
