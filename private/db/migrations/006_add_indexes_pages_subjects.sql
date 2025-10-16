-- Uniqueness + useful indexes
ALTER TABLE subjects
  ADD UNIQUE KEY uq_subjects_slug (slug);

ALTER TABLE pages
  ADD INDEX idx_pages_subject_id (subject_id),
  ADD INDEX idx_pages_slug (slug);
