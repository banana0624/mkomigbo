-- Bring `pages` up to the schema expected by code.
-- Works safely whether columns/indexes already exist or not.

-- 1) Ensure table exists (no-op if it already exists)
CREATE TABLE IF NOT EXISTS pages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subject_slug VARCHAR(64) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  title VARCHAR(200) NOT NULL,
  body MEDIUMTEXT NULL,
  excerpt TEXT NULL,
  banner_image VARCHAR(255) NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_subject_slug (subject_slug, slug),
  KEY idx_pages_subject (subject_slug),
  KEY idx_pages_published_subject (subject_slug, is_published, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- We'll conditionally add missing columns using information_schema + dynamic SQL
SET @db := DATABASE();

-- subject_slug
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='subject_slug');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN subject_slug VARCHAR(64) NOT NULL AFTER id',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- slug
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='slug');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN slug VARCHAR(160) NOT NULL AFTER subject_slug',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- title
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='title');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN title VARCHAR(200) NOT NULL AFTER slug',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- body
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='body');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN body MEDIUMTEXT NULL AFTER title',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- excerpt
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='excerpt');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN excerpt TEXT NULL AFTER body',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- banner_image
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='banner_image');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN banner_image VARCHAR(255) NULL AFTER excerpt',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- is_published
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='is_published');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 0 AFTER banner_image',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- published_at
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='published_at');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN published_at DATETIME NULL AFTER is_published',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- created_at
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='created_at');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER published_at',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- updated_at
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND COLUMN_NAME='updated_at');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index: uniq_subject_slug (unique on subject_slug, slug)
SET @exists := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND INDEX_NAME='uniq_subject_slug');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD UNIQUE KEY uniq_subject_slug (subject_slug, slug)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index: idx_pages_subject (subject_slug)
SET @exists := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND INDEX_NAME='idx_pages_subject');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD KEY idx_pages_subject (subject_slug)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index: idx_pages_published_subject (subject_slug, is_published, published_at)
SET @exists := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA=@db AND TABLE_NAME='pages' AND INDEX_NAME='idx_pages_published_subject');
SET @sql := IF(@exists=0,
  'ALTER TABLE pages ADD KEY idx_pages_published_subject (subject_slug, is_published, published_at)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
