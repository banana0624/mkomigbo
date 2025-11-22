# Index Plan for Database Schema

## Table: `subjects`

**Key queries**: `WHERE slug = :slug`, `ORDER BY nav_order`, lookups by `name`.

```sql
-- Add index to speed up lookups by slug
ALTER TABLE subjects
  ADD INDEX idx_subjects_slug      (slug);

-- Add index to support sorting or filtering by name
ALTER TABLE subjects
  ADD INDEX idx_subjects_name      (name);

-- Add index to speed up ordering by nav_order
ALTER TABLE subjects
  ADD INDEX idx_subjects_nav_order (nav_order);
```
