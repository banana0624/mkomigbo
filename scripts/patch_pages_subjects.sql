/* project-root\scripts\patch_pages_subjects.sql */
SET NAMES utf8mb4;
SET sql_mode='STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ===== PAGES: publish flag (used in subject_page_functions.php) =====
ALTER TABLE pages
  ADD COLUMN IF NOT EXISTS is_published TINYINT(1) NOT NULL DEFAULT 0;

-- Optional but helpful for UIs that show when a page goes live
ALTER TABLE pages
  ADD COLUMN IF NOT EXISTS published_at DATETIME NULL;

-- ===== SUBJECTS: slug (used in subject_functions.php / settings.php) =====
ALTER TABLE subjects
  ADD COLUMN IF NOT EXISTS slug VARCHAR(150) NULL;

-- Backfill empty slugs from name -> kebab-case
UPDATE subjects
SET slug = LOWER(
            TRIM(
              REGEXP_REPLACE(
                REGEXP_REPLACE(COALESCE(name,''), '[^0-9A-Za-z]+', '-'),
                '(^-+|-+$)', ''
              )
            )
          )
WHERE (slug IS NULL OR slug = '')
  AND name IS NOT NULL AND name <> '';

-- If duplicates slipped in, make them unique with a numeric suffix
-- (MariaDB only executes the UPDATE when duplicates exist)
WITH dups AS (
  SELECT slug, COUNT(*) c
  FROM subjects
  WHERE slug IS NOT NULL AND slug <> ''
  GROUP BY slug
  HAVING COUNT(*) > 1
)
UPDATE subjects s
JOIN (SELECT id,
             slug,
             ROW_NUMBER() OVER (PARTITION BY slug ORDER BY id) AS rn
      FROM subjects
      WHERE slug IS NOT NULL AND slug <> '') x
  ON s.id = x.id
SET s.slug = CONCAT(s.slug, '-', x.rn-1)
WHERE EXISTS (SELECT 1 FROM dups WHERE dups.slug = x.slug)
  AND x.rn > 1;

-- Enforce uniqueness going forward (ignore if it already exists)
ALTER TABLE subjects
  ADD UNIQUE KEY IF NOT EXISTS uq_subjects_slug (slug);
