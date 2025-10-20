-- 008_seed_permissions.sql
-- Give default, sensible permissions to built-in roles
UPDATE roles SET permissions_json='[
  "pages.view","pages.create","pages.edit","pages.delete","pages.publish",
  "media.upload","users.manage","roles.manage"
]' WHERE slug='admin';

UPDATE roles SET permissions_json='[
  "pages.view","pages.create","pages.edit","pages.publish","media.upload"
]' WHERE slug='editor';

UPDATE roles SET permissions_json='[
  "pages.view"
]' WHERE slug='viewer';
