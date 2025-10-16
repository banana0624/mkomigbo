-- 006_backfill_user_roles.sql
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.slug = u.role
ON DUPLICATE KEY UPDATE user_id = user_id;
