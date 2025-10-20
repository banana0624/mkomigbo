-- 009_seed_page_perms.sql
UPDATE roles
SET permissions_json = JSON_ARRAY('pages.view','pages.create','pages.edit','pages.delete','pages.publish')
WHERE slug='admin';

UPDATE roles
SET permissions_json = JSON_ARRAY('pages.view','pages.create','pages.edit')
WHERE slug='editor';
