-- private/db/seed_roles.sql

INSERT INTO roles (name, slug, description, permissions_json, created_at, updated_at)
VALUES
  (
    'Administrator',
    'admin',
    'Full system access, including staff, subjects, pages, platforms, and roles.',
    JSON_ARRAY(
      '*'
    ),
    NOW(), NOW()
  ),
  (
    'Editor',
    'editor',
    'Can manage content (subjects, pages, contributors) but not system roles or low-level config.',
    JSON_ARRAY(
      'subjects.read',
      'subjects.write',
      'pages.read',
      'pages.write',
      'contributors.read',
      'contributors.write',
      'platforms.read',
      'platforms.write'
    ),
    NOW(), NOW()
  ),
  (
    'Contributor',
    'contributor',
    'Can submit and manage their own contributions; limited access to others.',
    JSON_ARRAY(
      'contributors.self',
      'platforms.submit'
    ),
    NOW(), NOW()
  );

-- Optionally assign role(s) to an existing admin user.
-- Adjust user_id as appropriate.
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.slug = 'admin'
WHERE u.id = 1    -- change this to your real admin user ID
ON DUPLICATE KEY UPDATE user_id = user_id;
