/* project-root/private/db/test_mkomigbo.sql */

USE mkomigbo;

SHOW TABLES;

DESCRIBE subjects;
DESCRIBE pages;
DESCRIBE users;
DESCRIBE contributors;
DESCRIBE platforms;

SELECT 'subjects' AS table_name, COUNT(*) AS row_count FROM subjects;
SELECT 'pages' AS table_name, COUNT(*) AS row_count FROM pages;
SELECT 'users' AS table_name, COUNT(*) AS row_count FROM users;
SELECT 'contributors' AS table_name, COUNT(*) AS row_count FROM contributors;
SELECT 'platforms' AS table_name, COUNT(*) AS row_count FROM platforms;

SELECT * FROM subjects LIMIT 10;
SELECT * FROM pages LIMIT 10;
SELECT * FROM users LIMIT 10;
SELECT * FROM contributors LIMIT 10;
SELECT * FROM platforms LIMIT 10;
