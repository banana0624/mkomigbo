<?php
// project-root/private/functions/uploads_functions.php
declare(strict_types=1);

/**
 * Robust upload + embed helpers for images, audio, video, CSS, JS/TS, etc.
 *
 * Usage:
 *   // 1) Handle an uploaded file from a staff form:
 *   $asset = mk_store_upload($_FILES['file'], 'assets'); // returns array with url, mime, ext, filename, path
 *
 *   // 2) Render an embed tag for a stored asset:
 *   echo mk_tag_for_asset($asset['url'], $asset['mime'], ['class'=>'w-full']);
 *
 *   // 3) Normalize pasted content (heredoc, etc.) before save:
 *   $body = mk_normalize_pasted_content($_POST['body'] ?? '');
 *
 *   // 4) Render arbitrary page body (staff/public):
 *   echo mk_render_body_staff($body);   // staff preview (allows <script>)
 *   echo mk_render_body_public($body);  // public (sanitized; scripts stripped)
 */


/* =========================
   0) Paths / URLs
   ========================= */

/** /public absolute file path */
function mk_public_path(): string {
  if (defined('PUBLIC_PATH')) return PUBLIC_PATH;
  return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
}

/** Base URL like "" or "http://host" + WWW_ROOT */
function mk_base_url(): string {
  $root = defined('WWW_ROOT') ? WWW_ROOT : '';
  return rtrim($root, '/');
}

/** Ensure /public/lib/uploads/{bucket}/YYYY/MM exists; return absolute dir path */
function mk_uploads_dir(string $bucket = 'assets'): string {
  $root = mk_public_path() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $bucket;
  $sub  = date('Y') . DIRECTORY_SEPARATOR . date('m');
  $dir  = $root . DIRECTORY_SEPARATOR . $sub;
  if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
  }
  return $dir;
}

/** URL for uploads dir matching mk_uploads_dir() */
function mk_uploads_url(string $bucket = 'assets'): string {
  $base = mk_base_url();
  $sub  = date('Y') . '/' . date('m');
  return $base . '/lib/uploads/' . rawurlencode($bucket) . '/' . $sub;
}


/* =========================
   1) Allowed types map
   ========================= */

/** Returns an array of allowed extensions → [mime, category] */
function mk_allowed_mime_map(): array {
  return [
    // Images
    'png'  => ['image/png',  'image'],
    'jpg'  => ['image/jpeg', 'image'],
    'jpeg' => ['image/jpeg', 'image'],
    'gif'  => ['image/gif',  'image'],
    'webp' => ['image/webp', 'image'],
    'svg'  => ['image/svg+xml', 'image'],

    // Audio
    'mp3'  => ['audio/mpeg',   'audio'],
    'm4a'  => ['audio/mp4',    'audio'],
    'aac'  => ['audio/aac',    'audio'],
    'wav'  => ['audio/wav',    'audio'],
    'flac' => ['audio/flac',   'audio'],
    'ogg'  => ['audio/ogg',    'audio'],
    'opus' => ['audio/opus',   'audio'],

    // Video
    'mp4'  => ['video/mp4',    'video'],
    'webm' => ['video/webm',   'video'],
    'ogv'  => ['video/ogg',    'video'],
    'mov'  => ['video/quicktime', 'video'], // plays inconsistently on web

    // Styles
    'css'  => ['text/css',     'style'],

    // Scripts (serve JS; accept TS as source but browser won’t run TS natively)
    'js'   => ['text/javascript', 'script'],
    'mjs'  => ['text/javascript', 'script-module'],
    'ts'   => ['text/plain',   'typescript'], // stored; you can transpile offline

    // Generic text
    'txt'  => ['text/plain',   'text'],
    'md'   => ['text/markdown','text'],
    'json' => ['application/json', 'text'],
  ];
}

function mk_ext(string $filename): string {
  $p = pathinfo($filename, PATHINFO_EXTENSION);
  return strtolower($p ?? '');
}

function mk_is_allowed_upload(string $filename, ?string $clientMime): bool {
  $map = mk_allowed_mime_map();
  $ext = mk_ext($filename);
  if (!isset($map[$ext])) return false;
  // We trust our map; client mime is advisory only.
  return true;
}


/* =========================
   2) Store upload
   ========================= */

/**
 * Store uploaded file into /public/lib/uploads/{bucket}/YYYY/MM
 * Returns: ['path','url','mime','ext','filename','category','size']
 */
function mk_store_upload(array $file, string $bucket = 'assets'): array {
  if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Upload error code: ' . (int)($file['error'] ?? -1));
  }
  $orig = $file['name'] ?? 'file';
  $ext  = mk_ext($orig);
  if (!mk_is_allowed_upload($orig, $file['type'] ?? null)) {
    throw new RuntimeException('File type not allowed: .' . $ext);
  }

  $map = mk_allowed_mime_map();
  [$mime, $category] = $map[$ext];

  $safeBase = preg_replace('~[^a-zA-Z0-9._-]+~', '-', pathinfo($orig, PATHINFO_FILENAME));
  $stamp    = date('Ymd-His');
  $rand     = substr(sha1($safeBase . $stamp . random_bytes(6)), 0, 8);
  $fname    = $safeBase . '-' . $stamp . '-' . $rand . '.' . $ext;

  $dir = mk_uploads_dir($bucket);
  $url = mk_uploads_url($bucket) . '/' . rawurlencode($fname);
  $dest = $dir . DIRECTORY_SEPARATOR . $fname;

  if (!@move_uploaded_file($file['tmp_name'], $dest)) {
    throw new RuntimeException('Failed to move uploaded file.');
  }

  // Set conservative perms
  @chmod($dest, 0644);

  return [
    'path'     => $dest,
    'url'      => $url,
    'mime'     => $mime,
    'category' => $category,
    'ext'      => $ext,
    'filename' => $fname,
    'size'     => filesize($dest) ?: 0,
  ];
}


/* =========================
   3) Embed helpers
   ========================= */

/** Build HTML attributes safely */
function mk_attr(array $attrs): string {
  $out = '';
  foreach ($attrs as $k => $v) {
    if ($v === true)        { $out .= ' ' . htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8'); continue; }
    if ($v === false || $v === null) continue;
    $out .= ' ' . htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '"';
  }
  return $out;
}

/**
 * Return an appropriate HTML tag for a stored asset by MIME/category.
 * You may pass extra attributes in $attrs (e.g., ['controls'=>true,'class'=>'w-full'])
 */
function mk_tag_for_asset(string $url, string $mime, array $attrs = []): string {
  $attrs = $attrs + ['loading' => 'lazy', 'decoding' => 'async'];

  // Images
  if (str_starts_with($mime, 'image/')) {
    if ($mime === 'image/svg+xml') {
      // For SVG, keep it as <img> unless you need inline SVG parsing
      return '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . mk_attr($attrs) . ' />';
    }
    $attrs['alt'] = $attrs['alt'] ?? '';
    return '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . mk_attr($attrs) . ' />';
  }

  // Audio
  if (str_starts_with($mime, 'audio/')) {
    $attrs = ['controls' => true] + $attrs;
    return '<audio' . mk_attr($attrs) . '><source src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" type="' . htmlspecialchars($mime, ENT_QUOTES, 'UTF-8') . '"></audio>';
  }

  // Video
  if (str_starts_with($mime, 'video/')) {
    $attrs = ['controls' => true] + $attrs;
    return '<video' . mk_attr($attrs) . '><source src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" type="' . htmlspecialchars($mime, ENT_QUOTES, 'UTF-8') . '"></video>';
  }

  // CSS
  if ($mime === 'text/css') {
    return '<link rel="stylesheet" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . mk_attr($attrs) . ' />';
  }

  // JS (module if .mjs was used)
  if ($mime === 'text/javascript') {
    $type = (str_ends_with(parse_url($url, PHP_URL_PATH) ?? '', '.mjs')) ? 'module' : 'text/javascript';
    return '<script src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '"' . mk_attr($attrs) . '></script>';
  }

  // Typescript (not natively runnable; offer download/link)
  if ($mime === 'text/plain' && str_ends_with(strtolower($url), '.ts')) {
    return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . mk_attr($attrs) . '>Download TypeScript</a>';
  }

  // Fallback link
  return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . mk_attr($attrs) . '>Download</a>';
}


/* =========================
   4) Normalize + Render body
   ========================= */

/** Strip PHP heredoc wrappers if the user pasted <<<ID ... ID; */
function mk_normalize_pasted_content(string $s): string {
  $t = trim($s);
  // Match: <<<ID\n ... \nID;   (ID = [A-Z0-9_]+)
  if (preg_match('/^<<<[ \t]*([A-Z_][A-Z0-9_]*)\R(.*)\R\1;?\s*$/is', $t, $m)) {
    return rtrim($m[2]) . "\n";
  }
  return $s;
}

/** Staff-side rendering: allow HTML including <script> (trusted editors) */
function mk_render_body_staff(string $body): string {
  $trim = ltrim($body);
  if ($trim !== '' && $trim[0] === '<') {
    return $body;
  }
  // Heuristics for CSS or JS-ish text blobs
  if (preg_match('~^[\s/*]*@?(charset|import|media|keyframes|font-face)|[{].*[}]~ms', $body)) {
    return "<style>\n{$body}\n</style>";
  }
  if (preg_match('~^\s*(//|/\*|const |let |var |function |import |export )~', $body)) {
    return "<script type=\"module\">\n{$body}\n</script>";
  }
  return '<pre style="white-space:pre-wrap;word-wrap:break-word;">' . htmlspecialchars($body, ENT_QUOTES, 'UTF-8') . '</pre>';
}

/**
 * Public rendering: sanitize aggressively.
 * Allows a minimal safe subset: headings, p, lists, a[href], img[src], pre/code,
 * audio/video/source/track, figure/figcaption, blockquote, hr, br, strong/em,
 * and link[rel=stylesheet]. Strips <script>, inline event handlers, and style/script URLs.
 */
function mk_render_body_public(string $html): string {
  // Quick deny-list: remove <script>…</script>
  $html = preg_replace('~<script\b[^>]*>.*?</script>~is', '', $html ?? '');

  // Strip on* attributes (onclick, onload, …)
  $html = preg_replace_callback(
    '~<([a-z0-9:-]+)\b[^>]*>~i',
    function ($m) {
      $tag = $m[1];
      $orig = $m[0];
      // Remove on* and javascript: and data: in href/src
      $clean = preg_replace([
        '/\s+on[a-z]+="[^"]*"/i',
        "/\s+on[a-z]+='[^']*'/i",
        '/\s+on[a-z]+=([^\s>]+)/i',
        '/\s+(href|src)\s*=\s*"javascript:[^"]*"/i',
        "/\s+(href|src)\s*=\s*'javascript:[^']*'/i",
        '/\s+(href|src)\s*=\s*"data:[^"]*"/i',
        "/\s+(href|src)\s*=\s*'data:[^']*'/i",
      ], '', $orig);

      // Allow only a safe set of tags; otherwise escape
      static $allow = [
        'h1','h2','h3','h4','h5','h6','p','ul','ol','li','a','img','pre','code','blockquote','hr','br','strong','em','figure','figcaption',
        'audio','video','source','track',
        'link', // only rel=stylesheet kept below
      ];
      if (!in_array(strtolower($tag), $allow, true)) {
        return htmlspecialchars($orig, ENT_QUOTES, 'UTF-8');
      }

      // For <link>: keep only rel=stylesheet + href (no inline events)
      if (strtolower($tag) === 'link') {
        if (!preg_match('~rel\s*=\s*["\']stylesheet["\']~i', $clean)) {
          return ''; // drop non-stylesheet links
        }
      }

      return $clean;
    },
    $html
  );

  return $html;
}
