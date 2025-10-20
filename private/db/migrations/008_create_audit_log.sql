-- 008_create_audit_log.sql
CREATE TABLE IF NOT EXISTS audit_log (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       INT            NULL,
  action        VARCHAR(64)    NOT NULL,          -- e.g. page.publish, page.unpublish, page.delete
  entity_type   VARCHAR(64)    NOT NULL,          -- e.g. page
  entity_id     BIGINT         NOT NULL,
  meta_json     JSON           NULL,
  ip_addr       VARCHAR(64)    NULL,
  user_agent    VARCHAR(255)   NULL,
  created_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_entity (entity_type, entity_id),
  KEY idx_user (user_id),
  KEY idx_action (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
