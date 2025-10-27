-- scripts/grant_admin_perms.sql
USE mkomigbo;

UPDATE roles
SET permissions_json = JSON_ARRAY(
  'pages.view','pages.create','pages.edit','pages.delete','pages.publish',
  'contributors.view','contributors.create','contributors.edit','contributors.delete',
  'contributors.reviews.view','contributors.reviews.create','contributors.reviews.edit','contributors.reviews.delete',
  'contributors.credits.view','contributors.credits.create','contributors.credits.edit','contributors.credits.delete',
  'audit.view'
)
WHERE slug='admin';
