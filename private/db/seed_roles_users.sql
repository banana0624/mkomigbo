-- private/db/seed_roles_users.sql
-- Seed baseline roles + a stub admin user.
-- Run once (or idempotently) from your SQL client.

START TRANSACTION;

-- 1. Roles
-- Adjust IDs if you already have rows; these INSERT IGNORE will not overwrite.

INSERT IGNORE INTO roles (id, name, permissions_json)
VALUES
  (1, 'admin', JSON_ARRAY('*')),
  (2, 'staff', JSON_ARRAY(
      'subjects.*',
      'pages.*',
      'contributors.read',
      'contributors.write',
      'contributors.credits.*',
      'contributors.reviews.*'
  )),
  (3, 'contributor', JSON_ARRAY(
      'contributors.self'
  ));

-- 2. Admin user (stub)
-- IMPORTANT: password_hash is a placeholder; we will set a real password via PHP.
-- If your users table has extra columns (e.g. is_active, created_at), add them here.

INSERT IGNORE INTO users (id, username, email)
VALUES
  (1, 'admin', 'admin@example.com');

COMMIT;
