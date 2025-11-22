<?php
declare(strict_types=1);

/**
 * project-root/private/registry/subjects_runtime.php
 * Canonical Subjects registry + helpers (DB-aware).
 *
 * - Uses the exact 19 subjects in the order provided:
 *   history, slavery, people, persons, culture, religion, spirituality, tradition,
 *   language1, language2, struggle, biafra, nigeria, ipob, africa, uk, europe, arabs, about
 *
 * - Exposes lookup helpers and optional PDO-backed loaders.
 * - Provides subject_logo_webpath() with sane fallbacks.
 *
 * Safe to include multiple times.
 */

if (defined('MK_SUBJECTS_RUNTIME_LOADED')) { return; }
define('MK_SUBJECTS_RUNTIME_LOADED', true);

/* -------------------------------------------------
 * Static registry (exact order as requested)
 * ------------------------------------------------- */
if (!function_exists('subjects_catalog')) {
  /**
   * @return array<string,array{name:string, nav_order:int}>
   *         Key = slug, values include human name and nav_order to preserve order downstream.
   */
  function subjects_catalog(): array {
    return [
      // nav_order is 1-based and preserves your specified order
      'history'     => ['name' => 'History',      'nav_order' => 1],
      'slavery'     => ['name' => 'Slavery',      'nav_order' => 2],
      'people'      => ['name' => 'People',       'nav_order' => 3],
      'persons'     => ['name' => 'Persons',      'nav_order' => 4],
      'culture'     => ['name' => 'Culture',      'nav_order' => 5],
      'religion'    => ['name' => 'Religion',     'nav_order' => 6],
      'spirituality'=> ['name' => 'Spirituality', 'nav_order' => 7],
      'tradition'   => ['name' => 'Tradition',    'nav_order' => 8],
      'language1'   => ['name' => 'Language1',   'nav_order' => 9],
      'language2'   => ['name' => 'Language2',   'nav_order' => 10],
      'struggles'    => ['name' => 'Struggles',     'nav_order' => 11],
      'biafra'      => ['name' => 'Biafra',       'nav_order' => 12],
      'nigeria'     => ['name' => 'Nigeria',      'nav_order' => 13],
      'ipob'        => ['name' => 'IPOB',         'nav_order' => 14],
      'africa'      => ['name' => 'Africa',       'nav_order' => 15],
      'uk'          => ['name' => 'UK',           'nav_order' => 16],
      'europe'      => ['name' => 'Europe',       'nav_order' => 17],
      'arabs'       => ['name' => 'Arabs',        'nav_order' => 18],
      'about'       => ['name' => 'About',        'nav_order' => 19],
    ];
  }
}

/* -------------------------------------------------
 * Simple lookups (built on the static registry)
 * ------------------------------------------------- */
if (!function_exists('subject_exists')) {
  function subject_exists(string $slug): bool {
    $slug = strtolower(trim($slug));
    return isset(subjects_catalog()[$slug]);
  }
}

if (!function_exists('subject_human_name')) {
  function subject_human_name(string $slug): string {
    $slug = strtolower(trim($slug));
    $all  = subjects_catalog();
    return $all[$slug]['name'] ?? ucfirst(str_replace('-', ' ', $slug));
  }
}

/* -------------------------------------------------
 * Registry accessors (delegate to newer APIs if present)
 * ------------------------------------------------- */
/**
 * Raw subjects from newer registry APIs if available.
 * Expected row shape (best effort): id?, slug?, name?, nav_order?
 * @return array<int,array<string,mixed>>
 */
if (!function_exists('registry_subjects_raw')) {
  function registry_subjects_raw(): array {
    if (function_exists('subjects_all'))    { return subjects_all(); }
    if (function_exists('subjects_sorted')) { return subjects_sorted(); }

    // Fallback to static registry (convert to rows, preserving order)
    $rows = [];
    $i = 0;
    foreach (subjects_catalog() as $slug => $meta) {
      $rows[] = [
        'id'         => 0,
        'slug'       => $slug,
        'name'       => $meta['name'],
        'nav_order'  => $meta['nav_order'] ?? (++$i),
      ];
    }
    return $rows;
  }
}

/**
 * Sorted subjects; prefers subjects_sorted(), else sort by nav_order/id/slug.
 * @return array<int,array<string,mixed>>
 */
if (!function_exists('registry_subjects')) {
  function registry_subjects(): array {
    if (function_exists('subjects_sorted')) { return subjects_sorted(); }
    $data = array_values(registry_subjects_raw());
    usort($data, function ($a, $b) {
      $ao = (int)($a['nav_order'] ?? $a['id'] ?? 9999);
      $bo = (int)($b['nav_order'] ?? $b['id'] ?? 9999);
      if ($ao === $bo) {
        return strcasecmp((string)($a['slug'] ?? ''), (string)($b['slug'] ?? ''));
      }
      return $ao <=> $bo;
    });
    return $data;
  }
}

/** @return array<string,mixed>|null */
if (!function_exists('registry_subject_by_id')) {
  function registry_subject_by_id(int $id): ?array {
    if (function_exists('subject_by_id')) { return subject_by_id($id); }
    foreach (registry_subjects_raw() as $row) {
      if ((int)($row['id'] ?? 0) === $id) return $row;
    }
    return null;
  }
}

/** @return array<string,mixed>|null */
if (!function_exists('registry_subject_by_slug')) {
  function registry_subject_by_slug(string $slug): ?array {
    if (function_exists('subject_by_slug')) { return subject_by_slug($slug); }
    $cmp = strtolower(trim($slug));
    foreach (registry_subjects_raw() as $row) {
      if (strcasecmp((string)($row['slug'] ?? ''), $cmp) === 0) return $row;
    }
    // Fallback to static catalog if newer API returns nothing
    $cat = subjects_catalog();
    if (isset($cat[$cmp])) {
      return [
        'id'        => 0,
        'slug'      => $cmp,
        'name'      => $cat[$cmp]['name'],
        'nav_order' => $cat[$cmp]['nav_order'] ?? 9999,
      ];
    }
    return null;
  }
}

/* -------------------------------------------------
 * Optional DB helpers (prefer PDO if present)
 * ------------------------------------------------- */
if (!function_exists('__subjects_pdo')) {
  function __subjects_pdo(): ?PDO {
    if (function_exists('db')) {
      try { $pdo = db(); if ($pdo instanceof PDO) return $pdo; } catch (Throwable $e) {}
    }
    if (function_exists('db_connect')) {
      try { $pdo = db_connect(); if ($pdo instanceof PDO) return $pdo; } catch (Throwable $e) {}
    }
    return null;
  }
}

/**
 * Load subjects from DB if the table has rows; else fallback to registry order.
 * Expected columns: id, name, slug, meta_description, meta_keywords.
 * @return array<int,array<string,mixed>>
 */
if (!function_exists('subjects_load')) {
  function subjects_load(?PDO $pdo = null): array {
    $pdo ??= __subjects_pdo();
    if ($pdo instanceof PDO) {
      try {
        $rs = $pdo->query("SELECT id, name, slug, meta_description, meta_keywords FROM subjects ORDER BY id ASC");
        $rows = $rs ? $rs->fetchAll(PDO::FETCH_ASSOC) : [];
        if ($rows) {
          foreach ($rows as &$r) { $r['id'] = (int)$r['id']; }
          return $rows;
        }
      } catch (Throwable $e) {
        // fall back to registry
      }
    }
    return registry_subjects();
  }
}

/**
 * Merge DB subjects (if any) with registry order; append DB-only extras at end.
 * @return array<int,array<string,mixed>>
 */
if (!function_exists('subjects_load_complete')) {
  function subjects_load_complete(?PDO $pdo = null): array {
    $pdo ??= __subjects_pdo();
    $dbMap = [];

    if ($pdo instanceof PDO) {
      try {
        $rs = $pdo->query("SELECT id, name, slug, meta_description, meta_keywords FROM subjects ORDER BY id ASC");
        $rows = $rs ? $rs->fetchAll(PDO::FETCH_ASSOC) : [];
        foreach ($rows as $r) {
          $dbMap[(string)$r['slug']] = [
            'id'                => (int)$r['id'],
            'name'              => (string)$r['name'],
            'slug'              => (string)$r['slug'],
            'meta_description'  => (string)($r['meta_description'] ?? ''),
            'meta_keywords'     => (string)($r['meta_keywords'] ?? ''),
          ];
        }
      } catch (Throwable $e) { /* ignore */ }
    }

    $merged = [];
    $seen   = [];
    foreach (subjects_catalog() as $slug => $meta) {
      if (isset($dbMap[$slug])) {
        $merged[] = $dbMap[$slug];
      } else {
        $merged[] = [
          'id'               => 0,
          'name'             => $meta['name'],
          'slug'             => $slug,
          'meta_description' => '',
          'meta_keywords'    => '',
        ];
      }
      $seen[$slug] = true;
    }
    // Append any DB-only extras
    foreach ($dbMap as $slug => $row) {
      if (empty($seen[$slug])) $merged[] = $row;
    }
    return $merged;
  }
}

/* -------------------------------------------------
 * Media/logo helpers
 * ------------------------------------------------- */
if (!function_exists('subject_logo_webpath')) {
  /**
   * Returns a root-absolute web path to a subject logo (with fallback).
   * Search order:
   *   1) Registry icon (if present and file exists)
   *   2) /lib/images/subjects/{slug}.{svg|png|jpg|jpeg|webp}
   *   3) /lib/images/logo/{slug}.{svg|png|jpg|jpeg|webp}
   *   4) /lib/images/logo/mk-logo.png (project default)
   */
  function subject_logo_webpath(string $slug): string {
    $slug = strtolower(trim($slug));

    // 1) If registry supplies an icon path, prefer it
    $reg = registry_subject_by_slug($slug);
    if ($reg && !empty($reg['icon'])) {
      $web = '/' . ltrim((string)$reg['icon'], '/');
      if (function_exists('asset_exists') && asset_exists($web)) return $web;

      // Manual FS check as a fallback to asset_exists()
      $public = defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public';
      $fs = rtrim($public, DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, $web);
      if (is_file($fs)) return $web;
    }

    // 2) & 3) Conventional locations
    $public  = defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public';
    $folders = ['/lib/images/subjects', '/lib/images/logo'];
    $exts    = ['.svg', '.png', '.jpg', '.jpeg', '.webp'];

    foreach ($folders as $dirWeb) {
      foreach ($exts as $ext) {
        $web = $dirWeb . '/' . $slug . $ext;
        if (function_exists('asset_exists')) {
          if (asset_exists($web)) return $web;
        } else {
          $fs = rtrim($public, DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, $web);
          if (is_file($fs)) return $web;
        }
      }
    }

    // 4) Default project logo
    return '/lib/images/logo/mk-logo.png';
  }
}

/* -------------------------------------------------
 * Misc
 * ------------------------------------------------- */
if (!function_exists('first_initial')) {
  /** First letter initial (UTF-8 safe) */
  function first_initial(string $name): string {
    return (function_exists('mb_substr') && function_exists('mb_strtoupper'))
      ? mb_strtoupper(mb_substr($name, 0, 1))
      : strtoupper(substr($name, 0, 1));
  }
}
