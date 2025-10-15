/* project-root/private/db/insert_admin.sql */

USE mkomigbo;

-- ============================================
-- Reset users/admins (safe to re-run)
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE users;
TRUNCATE TABLE admins;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Insert demo admin into users table
-- (role is set as 'admin')
-- ============================================
INSERT INTO users (id, username, email, password_hash, role, created_at) VALUES
(1, 'admin', 'admin@example.com',
 '$2y$10$YjF4kKQ0EwEjKCF9GxIV7.O0XhvUq4gqJbz7UEVtqWwZ/7a5MQV/O', -- password: admin123
 'admin', NOW());

-- ============================================
-- Optional: insert into admins profile table
-- (linked by user_id)
-- ============================================
INSERT INTO admins (id, user_id, name, created_at) VALUES
(1, 1, 'System Administrator', NOW());
