/* project-root/private/db/insert_contributors.sql */

USE mkomigbo;

-- ============================================
-- Reset tables (safe to re-run)
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE users;
TRUNCATE TABLE contributors;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Insert demo contributors into users table
-- (role is set as 'contributor')
-- ============================================
INSERT INTO users (id, username, email, password_hash, role, created_at) VALUES
(1, 'contrib1', 'contrib1@example.com', 
 '$2y$10$A0m1bp8AxttD.2zREpIzleuAXP8pmpJZBkNAgvKRTROaSru6j9bLq', -- password: contrib123
 'contributor', NOW()),
(2, 'contrib2', 'contrib2@example.com', 
 '$2y$10$YQ4rsm.gh75i/3rmlf2P6ukYYWQzZZGfZ2Sl0K9hwDg9qIsafCe9S', -- password: contrib123
 'contributor', NOW());

-- ============================================
-- Insert contributor profiles
-- (linked by user_id → assumes FK relationship)
-- ============================================
INSERT INTO contributors (id, user_id, name, bio, created_at) VALUES
(1, 1, 'John Doe', 'Historian and contributor to Mkomigbo.', NOW()),
(2, 2, 'Jane Smith', 'Anthropologist contributing to Mkomigbo.', NOW()),
(3, 3, 'Obioma Nkem', 'Contributor focused on African struggles.', NOW());
