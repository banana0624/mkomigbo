# Mkomigbo /tools Overview

All scripts here are meant to be run **from the project root**, e.g.:

```powershell
cd F:\xampp\htdocs\mkomigbo\project-root
php tools/audit_project.php
````

They help keep the tree clean and make it obvious what is intentional vs. leftover junk.

---

## audit_project.php

### What it does

Scans the project and prints:

1. **PHP files in project root**

   * Normally should be **empty**.
   * If something appears here, it’s probably a misplaced script.

2. **Suspect PHP files in root (not standard entry points)**

   * Same list as above, minus any filenames you explicitly whitelist inside the script.
   * Anything here is usually a red flag.

3. **Files with backup-ish names (old/bak/copy/tmp)**

   * Looks for names like:

     * `*.bak`, `*.old`, `*.tmp`
     * `*.htaccess.bak*`
     * Anything with `backup` in file or path.
   * It will list:

     * `archive\2025-11-config-bak\...`
     * `archive\2025-11-subject-crud-bak\...`
     * Node toolchain backup-related files under `mkomigbo\` and `public_html\`.

   These are usually **expected** in:

   * `archive\...`
   * `mkomigbo\...`
   * `public_html\...`

   and are safe to ignore.

4. **Files with "test" in the name outside /test or /tests**

   * Only flags filenames where **`test` is a word-ish segment**, e.g.:

     * `test.php`
     * `test_min.php`
     * `my.test.php`
     * `foo-test.js`
   * **Does not** flag things like:

     * `protest.jpeg`
     * `contest.php`
     * `latest.php`
   * It **ignores** anything already under `.../test/...` or `.../tests/...`.

### When to run

* After file cleanups, refactors, or moving things around.
* Before a big commit, to make sure no stray backup/test files leaked into live areas.

### What’s OK to ignore

* Anything under:

  * `archive\...`
  * `mkomigbo\...`
  * `public_html\...`
  * `public\test\legacy\...`
* Node/Vitest-related files (`vitest`, `*.test.ts`, etc.) are expected.

---

## move_public_tests.php

### What it does

* Finds old/legacy public test scripts and moves them into:

  ```text
  public/test/legacy/
  ```

* Targets patterns like:

  * `public/test*.php`
  * `public/test_*.php`
  * `public/testp.php`
  * `public/test.txt`
  * `public/staff/subjects/*/test_ok.php`
  * `public/subjects/test_min.php`
  * `public/hello.php`
  * `public/staff/hello.php`
  * `public/health.php`

* After running, it leaves a clean **public** tree and centralizes the legacy tests.

### When to run

* After experimenting with new test files in public, once you’re done with them.
* Before cleanup/commit, to ensure all those test entrypoints get tucked under `public/test/legacy/`.

### What’s OK to ignore

* The content inside `public/test/legacy/` is **deliberately noisy**.
* Old test files there are historical and don’t affect production routes.

---

## move_misc_backups.php

### What it does

* Finds “misc” backup files that should not sit in live directories and moves them into:

  ```text
  archive/2025-11-config-bak/
  ```

* Examples you’ve already seen moved:

  * `.htaccess.bak.20251105-001400`
  * `public/.htaccess.bak`
  * `public/.htaccess.bak.20251105-001401`
  * `private/common/contributors/contrib_common.php.bak`

### When to run

* After editing Apache config / `.htaccess` / shared PHP configs and creating backups.
* When `audit_project.php` shows stray `.bak` files in live directories.

### What’s OK to ignore

* Once they’re under `archive/2025-11-config-bak/`, all those backups are **fine to leave alone**.
* `audit_project.php` will still list them, but they’re in an “archive” bucket, which is expected.

---

## cleanup_move_backups_and_tests.php

### What it does

* Convenience script to run multiple cleanup operations in one go (e.g. `move_public_tests`, `move_misc_backups`, etc.).
* Exact behavior depends on how it’s wired internally, but the intent is:

  * “Run the usual test + backup movers and leave the tree tidy.”

### When to run

* Occasionally, after a development sprint where you’ve:

  * Created temp public tests.
  * Made config backups (`.htaccess.bak`, `*.php.bak`, etc.).
* Before committing or packaging, to force a consistent cleanup run.

### What’s OK to ignore

* Same as the individual scripts:

  * `archive\...`, `public\test\legacy\...`, `mkomigbo\...`, `public_html\...` are allowed to stay noisy.

---

## find_duplicate_php_names.php

### What it does

* Scans the tree and groups **PHP files with the same filename**.
* Shows **all** duplicates, including:

  * `archive\...`
  * `node_modules\...` (if any PHP sneaks in there)
  * `vendor\...`
  * `mkomigbo\...`
  * `public_html\...`

This is a **raw**, unfiltered view.

### When to run

* When you want a complete picture of every duplicated filename, including archives/vendor.
* Rare; mostly useful when designing naming conventions.

### What’s OK to ignore

* Anything under:

  * `archive\...`
  * `vendor\...`
  * `node_modules\...`
  * `mkomigbo\...`
  * `public_html\...`
* Those are expected to contain their own internal duplicates.

---

## find_duplicate_php_names2.php

### What it does

* Same idea as `find_duplicate_php_names.php`, but **filtered** to focus on your live app code.
* Ignores:

  * `archive\...`
  * `node_modules\...`
  * `vendor\...` (except where needed for completeness)
  * `public\test\legacy\...`
  * `mkomigbo\...`
  * `public_html\...`
* Shows duplicates that matter for your **centralized CRUD** and live routes, e.g.:

  * `create.php`, `edit.php`, `delete.php`, `index.php`, `show.php`, `upload.php`, `toggle_publish.php`, etc.

### When to run

* When you are:

  * Refactoring centralized CRUD in `private/common` and `public/staff/...`.
  * Checking whether any `create.php` / `edit.php` / `delete.php` is redundant or mis-placed.
* Good sanity check before/after large CRUD reorganizations.

### What’s OK to ignore

* Duplicates that reflect **your intentional structure**, for example:

  * `private/common/...` vs `public/staff/...` entrypoints.
  * `_archive` mirrors under `public/_archive/...`.
* The goal is not “zero duplicates”, but “duplicates are **intentional and structured**”.

---

## General Notes

* **archive/**
  Safe place for old backups, snapshots, and historic PHP trees. Expect noise.

* **public/test/legacy/**
  Safe dumping ground for legacy test scripts; doesn’t affect production visitors.

* **mkomigbo/** and **public_html/**
  Contain frontend/Node toolchain code (TS, JS, tests). No need to clean these for PHP routing.

* **vendor/**
  Managed by Composer. Never manually edit or “fix” duplicates here.

When in doubt:

* If a file is under `archive/`, `public/test/legacy/`, `mkomigbo/`, `public_html/`, or `vendor/`, it’s usually **not a problem** unless you know you want to delete it.
* If something odd shows up in the **root** or in `public/` / `private/` outside those buckets, then it’s worth a closer look.