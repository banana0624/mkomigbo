<?php
// project-root/private/functions/registry.php

declare(strict_types=1);

if (!defined('REGISTRY_DIR')) {
  define('REGISTRY_DIR', BASE_PATH . '/private/registry');
}
if (!defined('SUBJECTS_FILE')) {
  define('SUBJECTS_FILE', REGISTRY_DIR . '/subjects.php');
}
if (!defined('SUBJECTS_DEFAULT_FILE')) {
  define('SUBJECTS_DEFAULT_FILE', REGISTRY_DIR . '/subjects.default.php');
}

/** Ensure registry/subjects.php exists by copying default or generating from default array */
function registry_bootstrap(): void {
  if (is_file(SUBJECTS_FILE)) return;
  if (!is_dir(REGISTRY_DIR)) @mkdir(REGISTRY_DIR, 0775, true);

  // Prefer copying the provided default file
  if (is_file(SUBJECTS_DEFAULT_FILE)) {
    @copy(SUBJECTS_DEFAULT_FILE, SUBJECTS_FILE);
    return;
  }

  // Fallback: write a minimal file with 19 subjects
  $arr = registry_default_subjects();
  $code = "<?php\nreturn " . var_export($arr, true) . ";\n";
  @file_put_contents(SUBJECTS_FILE, $code);
}

/** Hard-coded default (used only if default file is missing) */
function registry_default_subjects(): array {
  return is_file(SUBJECTS_DEFAULT_FILE)
    ? (include SUBJECTS_DEFAULT_FILE)
    : []; // (we rely on the provided default file above)
}

/** Raw subjects array from registry file (auto-bootstrap if missing) */
function registry_subjects_raw(): array {
  if (!is_file(SUBJECTS_FILE)) registry_bootstrap();
  $data = is_file(SUBJECTS_FILE) ? include SUBJECTS_FILE : registry_default_subjects();
  return is_array($data) ? $data : [];
}

/** Sorted by nav_order (or id) */
function registry_subjects(): array {
  $data = array_values(registry_subjects_raw());
  usort($data, function ($a, $b) {
    $ao = $a['nav_order'] ?? $a['id'] ?? 9999;
    $bo = $b['nav_order'] ?? $b['id'] ?? 9999;
    return $ao <=> $bo;
  });
  return $data;
}

function registry_subject_by_id(int $id): ?array {
  $raw = registry_subjects_raw();
  return $raw[$id] ?? null;
}

function registry_subject_by_slug(string $slug): ?array {
  foreach (registry_subjects_raw() as $s) {
    if (strcasecmp($s['slug'] ?? '', $slug) === 0) return $s;
  }
  return null;
}

/** Main loader for UI: prefer DB if populated, else registry */
function subjects_load(?mysqli $db): array {
  try {
    if ($db instanceof mysqli) {
      $rs = $db->query("SELECT id, name, slug, meta_description, meta_keywords FROM subjects ORDER BY id ASC");
      if ($rs && $rs->num_rows > 0) {
        $rows = [];
        while ($r = $rs->fetch_assoc()) {
          $rows[] = [
            'id' => (int)$r['id'],
            'name' => (string)$r['name'],
            'slug' => (string)$r['slug'],
            'meta_description' => (string)($r['meta_description'] ?? ''),
            'meta_keywords' => (string)($r['meta_keywords'] ?? ''),
          ];
        }
        return $rows;
      }
    }
  } catch (Throwable $e) { /* ignore and fall back */ }
  return registry_subjects();
}

/** Resolve a subject logo path (web path) – registry icon takes priority */
function subject_logo_webpath(string $slug): ?string {
  $reg = registry_subject_by_slug($slug);
  if ($reg && !empty($reg['icon'])) {
    $iconFs = BASE_PATH . '/' . ltrim($reg['icon'], '/');
    if (is_file($iconFs)) return ltrim($reg['icon'], '/');
  }
  $folders = [BASE_PATH . '/lib/images/logo', BASE_PATH . '/lib/images/subjects'];
  $exts = ['.svg', '.png', '.jpg', '.jpeg', '.jfif', '.webp'];

  foreach ($folders as $dir) {
    foreach ($exts as $ext) {
      $fs = $dir . '/' . $slug . $ext;
      if (is_file($fs)) {
        return (substr($dir, -5) === '/logo')
          ? 'lib/images/logo/' . $slug . $ext
          : 'lib/images/subjects/' . $slug . $ext;
      }
    }
  }
  return null;
}

/** First letter initial (UTF-8 safe) */
function first_initial(string $name): string {
  return (function_exists('mb_substr') && function_exists('mb_strtoupper'))
    ? mb_strtoupper(mb_substr($name, 0, 1))
    : strtoupper(substr($name, 0, 1));
}


function subjects_load_complete(?mysqli $db): array {
  // Build DB map keyed by slug (use DB if any rows exist)
  $dbMap = [];
  try {
    if ($db instanceof mysqli) {
      $rs = $db->query("SELECT id, name, slug, meta_description, meta_keywords FROM subjects ORDER BY id ASC");
      if ($rs && $rs->num_rows > 0) {
        while ($r = $rs->fetch_assoc()) {
          $slug = (string)$r['slug'];
          $dbMap[$slug] = [
            'id' => (int)$r['id'],
            'name' => (string)$r['name'],
            'slug' => $slug,
            'meta_description' => (string)($r['meta_description'] ?? ''),
            'meta_keywords' => (string)($r['meta_keywords'] ?? ''),
          ];
        }
      }
    }
  } catch (Throwable $e) {
    // ignore and fall back to registry-only
  }

  // Merge with registry (registry order dictates nav)
  $merged = [];
  $reg = registry_subjects(); // ordered by nav_order/id
  $seen = [];

  foreach ($reg as $rs) {
    $slug = (string)$rs['slug'];
    if (isset($dbMap[$slug])) {
      $merged[] = $dbMap[$slug];
    } else {
      $merged[] = [
        'id' => (int)$rs['id'],
        'name' => (string)$rs['name'],
        'slug' => $slug,
        'meta_description' => (string)($rs['meta_description'] ?? ''),
        'meta_keywords' => (string)($rs['meta_keywords'] ?? ''),
      ];
    }
    $seen[$slug] = true;
  }

  // Append any extra DB subjects not in registry (future growth)
  foreach ($dbMap as $slug => $row) {
    if (empty($seen[$slug])) $merged[] = $row;
  }

  return $merged;
}

