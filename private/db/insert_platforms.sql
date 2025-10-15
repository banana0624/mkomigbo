-- project-root/private/db/insert_platforms.sql --

-- Reset and seed demo platforms
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE platforms;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO platforms (id, name, description, created_at) VALUES
(1, 'YouTube', 'Video-sharing platform for multimedia contributors.', NOW()),
(2, 'Medium', 'Writing platform for bloggers and contributors.', NOW()),
(3, 'WordPress', 'CMS platform for publishing articles and posts.', NOW()),
(4, 'Reddit', 'Community forum and discussion platform.', NOW()),
(5, 'Twitter/X', 'Social microblogging platform.', NOW()),
(6, 'Facebook', 'Social media and community interaction platform.', NOW()),
(7, 'Instagram', 'Photo and video sharing platform for creators.', NOW()),
(8, 'TikTok', 'Short-form video content platform.', NOW()),
(9, 'LinkedIn', 'Professional networking and publishing platform.', NOW()),
(10, 'Substack', 'Publishing platform for newsletters and writers.', NOW());
