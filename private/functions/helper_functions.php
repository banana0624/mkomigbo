<?php
declare(strict_types=1);

/**
 * project-root/private/assets/helper_functions.php
 * Common helper functions used across the app.
 * Idempotent: each is guarded with function_exists().
 */

/* ===== Basic HTML helpers ===== */
if (!function_exists('h')) {
    function h(string $s = ''): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('html_decode_utf8')) {
    function html_decode_utf8(string $s): string {
        // Double decode to handle things like &amp;#803;
        $s = html_entity_decode($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return html_entity_decode($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/* ===== Text cleaning / slugging ===== */
if (!function_exists('sanitize_text')) {
    /**
     * Strip tags, decode entities, collapse whitespace.
     */
    function sanitize_text(string $s): string {
        $s = html_decode_utf8($s);
        $s = strip_tags($s);
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        $s = preg_replace('~\s+~u', ' ', $s);
        return trim($s);
    }
}

if (!function_exists('slugify')) {
    /**
     * Create a URL-safe slug from arbitrary text.
     * ICU transliterator preferred; falls back to iconv.
     */
    function slugify(string $s): string {
        $s = sanitize_text($s);

        if (class_exists('Transliterator')) {
            $tr = \Transliterator::create('NFKD; [:Nonspacing Mark:] Remove; NFC');
            if ($tr) {
                $s = $tr->transliterate($s);
            }
        } else {
            $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        }

        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('~[^a-z0-9]+~', '-', $s);
        $s = trim($s, '-');

        return $s !== '' ? $s : 'item';
    }
}

/* ===== File-system helpers ===== */
if (!function_exists('safe_path_join')) {
    function safe_path_join(string ...$parts): string {
        $p = join(DIRECTORY_SEPARATOR, array_map(
            fn($x) => trim($x, " \t\n\r\0\x0B\\/"),
            $parts
        ));
        // Normalize double slashes
        return preg_replace('~[\\/]+~', DIRECTORY_SEPARATOR, $p);
    }
}

if (!function_exists('ensure_dir')) {
    function ensure_dir(string $dir, int $mode = 0775): bool {
        if (is_dir($dir)) {
            return true;
        }
        return @mkdir($dir, $mode, true);
    }
}

/* ===== URL helpers ===== */
if (!function_exists('url_for')) {
    /**
     * Build a clean web path (no filesystem paths here).
     *
     * Examples (with WWW_ROOT == '' and vhost pointing at /public):
     *   url_for('staff/login')   → /staff/login
     *   url_for('/staff/login')  → /staff/login
     */
    function url_for(string $script_path): string {
        $script_path = trim($script_path);
        if ($script_path === '' || $script_path[0] !== '/') {
            $script_path = '/' . $script_path;
        }

        if (!defined('WWW_ROOT') || WWW_ROOT === '' || WWW_ROOT === '/') {
            return $script_path;
        }

        return rtrim((string)WWW_ROOT, '/') . $script_path;
    }
}

/**
 * Optional convenience helper for absolute URLs when needed,
 * e.g. in emails or redirects outside the site.
 */
if (!function_exists('abs_url')) {
    function abs_url(string $path = ''): string {
        $path = url_for($path);
        $base = defined('SITE_URL') ? SITE_URL : '';
        return $base . $path;
    }
}

/* ===== Content helpers (Markdown & entity-aware echo) ===== */
if (!function_exists('render_markdown_safe')) {
    /**
     * Very lightweight Markdown: only paragraphs & line breaks.
     * (Swap for a real parser later if needed.)
     */
    function render_markdown_safe(string $md): string {
        $md = html_decode_utf8($md);
        $md = str_replace(["\r\n", "\r"], "\n", $md);
        $paras = array_map('trim', preg_split('~\n{2,}~', $md));
        $html = '';
        foreach ($paras as $p) {
            if ($p === '') {
                continue;
            }
            $p = nl2br(h($p));
            $html .= "<p>{$p}</p>\n";
        }
        return $html;
    }
}

/* ===== Puny helpers for arrays ===== */
if (!function_exists('array_get')) {
    function array_get(array $a, string $key, mixed $default = null): mixed {
        return array_key_exists($key, $a) ? $a[$key] : $default;
    }
}

/* ===== Redirect helper (web URLs only) ===== */
if (!function_exists('redirect_to')) {
     function redirect_to(string $to, int $status = 302): void {
       if (!preg_match('~^https?://~i', $to)) {
         $to = url_for($to);
       }
       header('Location: ' . $to, true, $status);
       exit;
     }
   }


// Pretty CRUD link helpers
// ----------------------------------------------------------------------

if (!function_exists('link_show')) {
  /**
   * Build a "show" URL for an entity.
   *
   * @param string $entity  'subject' | 'page' | other
   * @param int    $id
   * @param string $ctx     'staff' | 'public' | etc.
   */
  function link_show(string $entity, int $id, string $ctx = 'staff'): string {
    $entity = strtolower($entity);
    $ctx    = strtolower($ctx);

    // Modern pretty routes
    if ($ctx === 'staff') {
      if ($entity === 'subject') {
        return url_for("/staff/subjects/{$id}/show/");
      }
      if ($entity === 'page') {
        return url_for("/staff/pages/{$id}/show/");
      }
    }

    // Fallback: old common CRUD engine
    $q = http_build_query(['e' => $entity, 'id' => $id, 'ctx' => $ctx]);
    return url_for('/common/show.php?' . $q);
  }
}

if (!function_exists('link_edit')) {
  function link_edit(string $entity, int $id, string $ctx = 'staff'): string {
    $entity = strtolower($entity);
    $ctx    = strtolower($ctx);

    if ($ctx === 'staff') {
      if ($entity === 'subject') {
        return url_for("/staff/subjects/{$id}/edit/");
      }
      if ($entity === 'page') {
        return url_for("/staff/pages/{$id}/edit/");
      }
    }

    $q = http_build_query(['e' => $entity, 'id' => $id, 'ctx' => $ctx]);
    return url_for('/common/edit.php?' . $q);
  }
}

if (!function_exists('link_delete')) {
  function link_delete(string $entity, int $id, string $ctx = 'staff'): string {
    $entity = strtolower($entity);
    $ctx    = strtolower($ctx);

    if ($ctx === 'staff') {
      if ($entity === 'subject') {
        return url_for("/staff/subjects/{$id}/delete/");
      }
      if ($entity === 'page') {
        return url_for("/staff/pages/{$id}/delete/");
      }
    }

    $q = http_build_query(['e' => $entity, 'id' => $id, 'ctx' => $ctx]);
    return url_for('/common/delete.php?' . $q);
  }
}

if (!function_exists('link_new')) {
  /**
   * Build a "new" URL for an entity.
   *
   * @param string      $entity 'subject' | 'page' | other
   * @param string|null $ctx    'staff' | 'public' | etc.
   */
  function link_new(string $entity, string $ctx = 'staff'): string {
    $entity = strtolower($entity);
    $ctx    = strtolower($ctx);

    if ($ctx === 'staff') {
      if ($entity === 'subject') {
        return url_for("/staff/subjects/new/");
      }
      if ($entity === 'page') {
        return url_for("/staff/pages/new/");
      }
    }

    $q = http_build_query(['e' => $entity, 'ctx' => $ctx]);
    return url_for('/common/new.php?' . $q);
  }
}

if (!function_exists('asset_path')) {
  /**
   * Optional: resolve a public asset web path (e.g. "/lib/css/app.css")
   * to its absolute filesystem path under PUBLIC_PATH.
   */
  function asset_path(string $webPath): string {
    $webPath = '/' . ltrim($webPath, '/');
    $public = defined('PUBLIC_PATH') ? rtrim((string)PUBLIC_PATH, DIRECTORY_SEPARATOR)
                                     : dirname(__DIR__, 2) . '/public';
    return $public . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
  }
}


   

