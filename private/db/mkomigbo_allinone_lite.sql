/* project-root/private/db/mkomigbo_allinone_lite.sql */

USE mkomigbo;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE pages;
TRUNCATE TABLE subjects;
TRUNCATE TABLE users;
TRUNCATE TABLE contributors;
TRUNCATE TABLE platforms;
SET FOREIGN_KEY_CHECKS = 1;

SOURCE private/db/sample_data.sql;
SOURCE private/db/test_mkomigbo.sql;
