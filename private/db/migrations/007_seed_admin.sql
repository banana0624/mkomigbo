-- 007_seed_admin.sql

-- Ensure core roles exist
INSERT INTO roles (slug, name) VALUES
  ('admin','Admin'),
  ('editor','Editor'),
  ('viewer','Viewer')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Create an admin user placeholder (change later)
INSERT INTO users (username, email, password_hash, role)
VALUES ('admin', 'admin@example.com',
        '$2y$10$KqfC8G7Z8i0WcT6lXq1Qfe7YH9C3oJ1w8s4i8wE2P3Qbq3o6JrWlS',
        'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- Link admin user to admin role via join table
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.slug = 'admin'
WHERE u.username = 'admin'
ON DUPLICATE KEY UPDATE user_id = user_id;
