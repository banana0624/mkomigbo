<?php
declare(strict_types=1);

/**
 * project-root/private/functions/image_functions.php
 *
 * Secure image upload utilities (size/MIME validation, unique filenames,
 * optional EXIF autorotate + resize via GD) with consistent return payloads.
 *
 * Conventions
 * ----------
 * - Base dir:  PUBLIC_PATH . '/lib/uploads/images'
 * - Default max size: 5 MB (override with IMAGE_MAX_BYTES in .env)
 * - Supported image types (by MIME):
 *     - JPEG family: image/jpeg, image/pjpeg, image/jfif
 *     - PNG:         image/png, image/x-png
 *     - GIF:         image/gif
 *     - AVIF:        image/avif
 *     - WEBP:        image/webp (only if IMAGE_ALLOW_WEBP=1)
 *
 * - Returns arrays like:
 *   ['ok'=>true,'filename'=>..., 'path'=>..., 'url'=>..., 'mime'=>..., 'size'=>..., 'width'=>..., 'height'=>...]
 *   or
 *   ['ok'=>false,'error'=>'message','errors'=>['size'=>'...','type'=>'...']]
 *
 * IMPORTANT
 * ---------
 * This file expects common helpers (ensure_dir(), url_for(), etc.) from helper_functions.php.
 * We include it explicitly and DO NOT re-declare any shared helpers here.
 */

require_once __DIR__ . '/helper_functions.php'; // h(), sanitize_text(), slugify(), ensure_dir(), etc.

/* =========================
   Config
   ========================= */

if (!function_exists('image_max_bytes')) {
  function image_max_bytes(): int {
    $env = (int)($_ENV['IMAGE_MAX_BYTES'] ?? 0);
    return $env > 0 ? $env : 5 * 1024 * 1024; // 5MB default
  }
}

/**
 * Allowed MIME types. WEBP can be turned on via IMAGE_ALLOW_WEBP=1
 *
 * NOTE: SVG is not allowed by default due to XSS/security concerns.
 * If you truly need SVG, add 'image/svg+xml' *only* after server-side sanitization.
 */
if (!function_exists('image_allowed_mimes')) {
  function image_allowed_mimes(): array {
    $m = [
      'image/jpeg',   // standard JPEG
      'image/pjpeg',  // progressive JPEG
      'image/jfif',   // some environments report JFIF
      'image/png',    // PNG
      'image/x-png',  // PNG alias (seen on some systems)
      'image/gif',    // GIF
      'image/avif',   // AVIF
    ];
    if (($_ENV['IMAGE_ALLOW_WEBP'] ?? '') === '1') {
      $m[] = 'image/webp'; // WEBP (opt-in)
    }
    // To allow SVG safely, you must sanitize it first, then uncomment the next line:
    // $m[] = 'image/svg+xml';
    return $m;
  }
}

if (!function_exists('image_upload_base_dir')) {
  function image_upload_base_dir(): string {
    $base = defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public';
    return rtrim($base, DIRECTORY_SEPARATOR)
      . DIRECTORY_SEPARATOR . 'lib'
      . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'images';
  }
}

/* =========================
   Local helpers (scoped)
   ========================= */

if (!function_exists('image_sanitize_basename')) {
  /** Keep alnum/underscore/dash/dot; trim leading/trailing dots/underscores */
  function image_sanitize_basename(string $name): string {
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
    return trim((string)$name, '._');
  }
}

if (!function_exists('image_detect_extension_from_mime')) {
  function image_detect_extension_from_mime(string $mime): ?string {
    return match ($mime) {
      'image/jpeg',
      'image/pjpeg',
      'image/jfif'     => 'jpg',

      'image/png',
      'image/x-png'    => 'png',

      'image/gif'      => 'gif',
      'image/webp'     => 'webp',
      'image/avif'     => 'avif',
      // 'image/svg+xml' => 'svg', // dangerous unless sanitized externally
      default          => null,
    };
  }
}

if (!function_exists('image_public_url_from_path')) {
  /** Convert an absolute path under PUBLIC_PATH to a web path beginning with '/' */
  function image_public_url_from_path(string $absPath): string {
    $public = rtrim(
      defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public',
      DIRECTORY_SEPARATOR
    );
    $abs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absPath);

    if (strpos($abs, $public . DIRECTORY_SEPARATOR) !== 0 && $abs !== $public) {
      return '';
    }

    $rel = substr($abs, strlen($public));
    $rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
    return $rel === '' ? '/' : $rel;
  }
}

if (!function_exists('image_safe_subdir')) {
  /** Only allow simple folder names (letters, numbers, dash/underscore/slash). No traversal. */
  function image_safe_subdir(string $s): string {
    $s = trim($s, "/\\ \t\n\r\0\x0B");
    // reject traversal
    if ($s === '' || str_contains($s, '..')) {
      return '';
    }
    // keep only segments that match
    $segments = array_filter(
      explode('/', str_replace('\\', '/', $s)),
      function ($seg) {
        return (bool)preg_match('/^[A-Za-z0-9_-]+$/', $seg);
      }
    );
    return implode('/', $segments);
  }
}

/* =========================
   Validation
   ========================= */

if (!function_exists('validate_image_upload')) {
  function validate_image_upload(array $file): array {
    $errors = [];

    $err = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err !== UPLOAD_ERR_OK) {
      $errors['upload'] = 'Upload error code: ' . $err;
      return $errors;
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
      $errors['tmp'] = 'No valid uploaded file found.';
      return $errors;
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > image_max_bytes()) {
      $errors['size'] = 'Invalid file size. Max '
        . number_format(image_max_bytes() / 1048576, 1)
        . ' MB.';
      return $errors;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string)($finfo->file($file['tmp_name']) ?: '');

    if (!in_array($mime, image_allowed_mimes(), true)) {
      $errors['type'] =
        'Invalid type. Allowed: JPEG (jpeg/pjpeg/jfif), PNG, GIF, AVIF'
        . (($_ENV['IMAGE_ALLOW_WEBP'] ?? '') === '1' ? ', WEBP' : '')
        . '.';
      return $errors;
    }

    $ext = image_detect_extension_from_mime($mime);
    if (!$ext) {
      $errors['ext'] = 'Unable to determine file extension from MIME.';
      return $errors;
    }

    return $errors; // empty means OK
  }
}

/* =========================
   Core uploader
   ========================= */

/**
 * Process an uploaded image.
 *
 * @param array $file One entry from $_FILES (e.g. $_FILES['image'])
 * @param array $opts {
 *   @var string 'dest_subdir'      Subfolder under base dir (e.g. 'avatars/2025'), safe names only
 *   @var string 'basename_prefix'  Filename prefix (default 'img')
 *   @var bool   'auto_resize'      Downscale large images if GD available (default false)
 *   @var int    'max_w'            Max width for resize (default 1600)
 *   @var int    'max_h'            Max height for resize (default 1600)
 *   @var bool   'auto_orient'      Autorotate JPEGs using EXIF (default true)
 * }
 * @return array payload (see header doc)
 */
if (!function_exists('process_image_upload')) {
  function process_image_upload(array $file, array $opts = []): array {
    $errors = validate_image_upload($file);
    if ($errors) {
      return [
        'ok'     => false,
        'error'  => reset($errors),
        'errors' => $errors,
      ];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string)$finfo->file($file['tmp_name']);
    $ext   = image_detect_extension_from_mime($mime);

    $prefix  = (string)($opts['basename_prefix'] ?? 'img');
    $baseDir = image_upload_base_dir();
    $sub     = image_safe_subdir((string)($opts['dest_subdir'] ?? ''));
    $destDir = $sub
      ? ($baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sub))
      : $baseDir;

    if (!ensure_dir($destDir)) {
      return ['ok' => false, 'error' => 'Failed to create upload directory.'];
    }

    $filename = image_generate_unique_filename($prefix, (string)$ext);
    $absPath  = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    // Move first
    if (!move_uploaded_file($file['tmp_name'], $absPath)) {
      return ['ok' => false, 'error' => 'Failed to move uploaded file.'];
    }
    @chmod($absPath, 0644);

    // Optional autorotate for JPEGs
    $autoOrient = array_key_exists('auto_orient', $opts)
      ? (bool)$opts['auto_orient']
      : true;

    if (
      $autoOrient &&
      in_array($mime, ['image/jpeg', 'image/pjpeg', 'image/jfif'], true)
    ) {
      __image_try_exif_autorotate($absPath);
    }

    // Optional resize (JPEG/PNG/WEBP only)
    $autoResize = !empty($opts['auto_resize']);
    if (
      $autoResize &&
      extension_loaded('gd') &&
      in_array($mime, ['image/jpeg', 'image/pjpeg', 'image/jfif', 'image/png', 'image/x-png', 'image/webp'], true)
    ) {
      $maxW = (int)($opts['max_w'] ?? 1600);
      $maxH = (int)($opts['max_h'] ?? 1600);
      __image_try_resize($absPath, $mime, $maxW, $maxH);
    }

    $dim = @getimagesize($absPath);
    $url = image_public_url_from_path($absPath);

    return [
      'ok'       => true,
      'filename' => $filename,
      'path'     => $absPath,
      'url'      => function_exists('url_for') && $url !== '' ? url_for($url) : $url,
      'mime'     => $mime,
      'size'     => @filesize($absPath) ?: null,
      'width'    => $dim[0] ?? null,
      'height'   => $dim[1] ?? null,
    ];
  }
}

if (!function_exists('image_generate_unique_filename')) {
  function image_generate_unique_filename(string $prefix, string $ext): string {
    $prefix = image_sanitize_basename($prefix !== '' ? $prefix : 'img');
    $rand   = substr(bin2hex(random_bytes(8)), 0, 12);
    return $prefix . '-' . date('Ymd-His') . '-' . $rand . '.' . $ext;
  }
}

/* =========================
   Image transforms (best-effort)
   ========================= */

if (!function_exists('__image_try_exif_autorotate')) {
  /**
   * Rotate JPEG in place if EXIF Orientation present. No-op if exif not loaded.
   */
  function __image_try_exif_autorotate(string $absPath): void {
    if (!extension_loaded('exif') || !extension_loaded('gd')) {
      return;
    }

    $exif = @exif_read_data($absPath);
    if (!$exif || empty($exif['Orientation'])) {
      return;
    }

    $orientation = (int)$exif['Orientation'];
    if ($orientation === 1) {
      return;
    }

    $img = @imagecreatefromjpeg($absPath);
    if (!$img) {
      return;
    }

    $rot = null;
    switch ($orientation) {
      case 3: $rot = imagerotate($img, 180, 0); break;
      case 6: $rot = imagerotate($img, -90, 0); break;
      case 8: $rot = imagerotate($img, 90, 0);  break;
      default: /* unknown */ break;
    }

    if ($rot) {
      @imagejpeg($rot, $absPath, 90);
      imagedestroy($rot);
      imagedestroy($img);
      return;
    }

    imagedestroy($img);
  }
}

if (!function_exists('__image_try_resize')) {
  /**
   * Downscale in place (keeps aspect ratio); supports JPEG/PNG/WEBP
   */
  function __image_try_resize(string $absPath, string $mime, int $maxW, int $maxH): void {
    [$w, $h] = @getimagesize($absPath) ?: [0, 0];
    if ($w <= 0 || $h <= 0) {
      return;
    }

    $scale = min($maxW / $w, $maxH / $h);
    if ($scale >= 1.0) {
      return;
    }

    $newW = max(1, (int)floor($w * $scale));
    $newH = max(1, (int)floor($h * $scale));

    switch ($mime) {
      case 'image/jpeg':
      case 'image/pjpeg':
      case 'image/jfif':
        $src = @imagecreatefromjpeg($absPath);
        break;

      case 'image/png':
      case 'image/x-png':
        $src = @imagecreatefrompng($absPath);
        break;

      case 'image/webp':
        if (!function_exists('imagecreatefromwebp')) {
          return;
        }
        $src = @imagecreatefromwebp($absPath);
        break;

      default:
        return;
    }

    if (!$src) {
      return;
    }

    $dst = imagecreatetruecolor($newW, $newH);

    // preserve alpha for PNG/WEBP
    if (in_array($mime, ['image/png', 'image/x-png', 'image/webp'], true)) {
      imagealphablending($dst, false);
      imagesavealpha($dst, true);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

    switch ($mime) {
      case 'image/jpeg':
      case 'image/pjpeg':
      case 'image/jfif':
        @imagejpeg($dst, $absPath, 88);
        break;

      case 'image/png':
      case 'image/x-png':
        @imagepng($dst, $absPath, 6);
        break;

      case 'image/webp':
        if (function_exists('imagewebp')) {
          @imagewebp($dst, $absPath, 88);
        }
        break;
    }

    imagedestroy($src);
    imagedestroy($dst);
  }
}

/* =========================
   Deletion
   ========================= */

/**
 * Delete by absolute path, or by (baseDir, filename). Returns true if the file
 * is gone (deleted or never existed).
 */
if (!function_exists('delete_image')) {
  function delete_image(string $absPathOrBaseDir, ?string $filename = null): bool {
    $abs = $filename
      ? rtrim($absPathOrBaseDir, DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . image_sanitize_basename($filename)
      : $absPathOrBaseDir;

    if (!file_exists($abs)) {
      return true;
    }
    return @unlink($abs);
  }
}
