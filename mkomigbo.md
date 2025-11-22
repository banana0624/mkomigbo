# Refactoring Checklist – Project “mkomigbo”

## 1. File: private/functions/subject_page_functions.php

- [ ] Wrap all function definitions with `if (!function_exists(...))` to avoid redeclaration.
- [ ] Remove duplicate delete functions (`subject_delete_by_slug()` vs `subject_delete()`).
- [ ] Ensure slug normalization (`trim`, `strtolower`) is applied consistently.
- [ ] Add PHPDoc blocks for each function (parameters, return types, exceptions).
- [ ] Remove dead/commented code or debug logging not behind a flag.
- [ ] Ensure function names follow a consistent naming strategy (verb_noun, e.g., `subject_row_by_slug`).

## 2. File: public/staff/subjects/show.php

- [ ] Validate slug parameter from `$_GET` correctly.
- [ ] Use guard clauses: if slug missing → error response.
- [ ] Use normalized slug when calling `subject_row_by_slug()`.
- [ ] Breadcrumbs reflect correct URL structure ( `/staff/subjects/`).
- [ ] Actions (Edit / Delete) have correct hrefs and URL encoding.

## 3. File: public/staff/subjects/edit.php

- [ ] Capture slug parameter properly (`$_GET['slug'] ?? ''`).
- [ ] If POST: validate `name` and `slug` are present.
- [ ] Use `subject_update_slug()` (or equivalent) to update name/slug.
- [ ] After update: redirect to correct show page with new slug.
- [ ] Ensure UI form uses values populated from current subject.

## 4. File: public/staff/subjects/delete.php

- [ ] Capture slug parameter properly.
- [ ] Confirm subject exists (`subject_row_by_slug`).
- [ ] Call `subject_delete()` or correct function to remove row.
- [ ] Redirect/feedback properly after success or failure.
- [ ] Protect against accidental deletes (confirmation prompt in UI).

## 5. Registry & Seed Files

### File: private/registry/subjects_register.php

- [ ] Ensure slug/name/nav_order values match actual DB rows.
- [ ] Remove duplication if both registry and DB carry same data; pick one source of truth.
- [ ] Use consistent naming for keys (slug in lowercase, no spaces).

### File: seed.sql

- [ ] Ensure script uses correct columns (`slug`, `name`, `menu_name`, `position`, `visible`, `nav_order` if present).
- [ ] Use `ON DUPLICATE KEY UPDATE` or safe delete/insert strategy.
- [ ] After run: verify `SELECT id, slug, name FROM subjects WHERE slug='history';` returns expected row.

## 6. Modules: Platforms & Contributors

- [ ] For each module (Platforms / Contributors): review views (Show/Edit/Delete) equivalently to Subjects.
- [ ] Ensure URL structure updates to `/staff/platforms/`, `/staff/contributors/` as intended.
- [ ] Ensure helper functions exist for CRUD operations in those modules.
- [ ] Apply slug or id usage consistency (whichever approach you use).
- [ ] Ensure routing/breadcrumbs reflect module context (Staff vs Public).

## 7. Code Style & Structure

- [ ] Adopt a coding standard (PSR-12 recommended).
- [ ] Use consistent indentation, braces, spacing.
- [ ] Use expressive variable/function names (no unclear abbreviations).
- [ ] Extract repeated logic into helpers to avoid duplication.
- [ ] Document non-obvious logic using inline comments or PHPDoc.
- [ ] Remove any commented-out legacy logic unless flagged for pending evaluation.

## 8. Testing & Verification

- [ ] Create simple manual tests for core CRUD operations (Subjects, Platforms, Contributors).
- [ ] After each refactor step, verify web UI links still work (Show, Edit, Delete) for key slugs.
- [ ] Use CLI tests for helper functions (e.g., `subject_row_by_slug('history')`).
- [ ] If available, integrate static analysis or linter to catch code style / unused code issues.

## 9. Deployment & Backup

- [ ] Backup database before running seed or delete operations.
- [ ] Use version control (Git) and create feature branch for refactoring tasks.
- [ ] Tag or note major refactoring steps in changelog.
- [ ] After refactor, confirm everything on staging/ local before production.
