<?php
declare(strict_types=1);

/**
 * project-root/private/functions/image_functions.php
 *
 * Secure image upload utilities with strict validation and optional resizing (GD).
 * Defaults:
 *  - Max 5 MB (override via IMAGE_MAX_BYTES in .env)
 *  - Allowed mimes: jpg/jpeg (incl. pjpeg/jfif), png, gif, avif (+ optional webp)
 *  - Saves under PUBLIC_PATH . '/lib/uploads/images'
 *
 * Returns consistent ['ok' => bool, 'error' => string|null, ...] payloads.
 */

// ---------- Config ----------

function image_max_bytes(): int {
    $env = (int)($_ENV['IMAGE_MAX_BYTES'] ?? 0);
    return $env > 0 ? $env : 5 * 1024 * 1024; // 5MB default
}

// Add JFIF + PJPEG here
function image_allowed_mimes(): array {
    $extra = [];
    if (!empty($_ENV['IMAGE_ALLOW_WEBP']) && $_ENV['IMAGE_ALLOW_WEBP'] === '1') {
        $extra[] = 'image/webp';
    }
    // include aliases that some servers emit for JPEG
    return array_merge(['image/jpeg', 'image/pjpeg', 'image/jfif', 'image/png', 'image/gif', 'image/avif'], $extra);
}

function image_upload_base_dir(): string {
    $base = defined('PUBLIC_PATH') ? PUBLIC_PATH : (dirname(__DIR__, 2) . '/public');
    return $base . '/lib/uploads/images';
}

// ---------- Helpers ----------

function sanitize_basename(string $name): string {
    $name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $name);
    return trim((string)$name, '._');
}

// JFIF/PJPEG map to JPG
function detect_extension_from_mime(string $mime): ?string {
    $map = [
        'image/jpeg' => 'jpg',
        'image/pjpeg'=> 'jpg', // legacy progressive JPEG
        'image/jfif' => 'jpg', // some environments emit this
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/avif' => 'avif',
        'image/webp' => 'webp',
    ];
    return $map[$mime] ?? null;
}

function ensure_dir(string $dir): bool {
    return is_dir($dir) || @mkdir($dir, 0755, true);
}

function generate_unique_filename(string $prefix, string $ext): string {
    $prefix = sanitize_basename($prefix ?: 'img');
    return $prefix . '-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(8)), 0, 12) . '.' . $ext;
}

function image_public_url_from_path(string $absPath): string {
    // Convert absolute path under PUBLIC_PATH to a web path
    $public = rtrim(defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public', DIRECTORY_SEPARATOR);
    $abs    = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absPath);
    if (strpos($abs, $public) !== 0) {
        return ''; // not under public
    }
    $rel = substr($abs, strlen($public));
    $rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
    return $rel; // begins with '/', suitable for url_for()
}

// ---------- Validation ----------

function validate_image_upload(array $file): array {
    $errors = [];

    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        $errors['upload'] = 'Upload error code: ' . $error;
        return $errors;
    }
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors['tmp'] = 'No valid uploaded file found.';
        return $errors;
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > image_max_bytes()) {
        $errors['size'] = 'Invalid file size. Max ' . number_format(image_max_bytes() / (1024 * 1024), 1) . ' MB.';
        return $errors;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string)($finfo->file($file['tmp_name']) ?: '');
    if (!in_array($mime, image_allowed_mimes(), true)) {
        $errors['type'] = 'Invalid type. Allowed: JPEG, PNG, GIF, AVIF' . (!empty($_ENV['IMAGE_ALLOW_WEBP']) ? ', WEBP' : '') . '.';
        return $errors;
    }

    $ext = detect_extension_from_mime($mime);
    if (!$ext) {
        $errors['ext'] = 'Unable to determine file extension.';
        return $errors;
    }

    return $errors; // empty = OK
}

// ---------- Core uploader ----------

/**
 * @param array $file  One entry from $_FILES (e.g., $_FILES['image'])
 * @param array $opts  Options:
 *   - 'dest_subdir' (string): appended under base dir, e.g. 'avatars'
 *   - 'basename_prefix' (string): default 'img'
 *   - 'auto_resize' (bool): if true and GD available, downscale to max box
 *   - 'max_w' (int), 'max_h' (int): resize box (defaults 1600x1600)
 */
function process_image_upload(array $file, array $opts = []): array {
    $errors = validate_image_upload($file);
    if ($errors) {
        return ['ok' => false, 'error' => reset($errors), 'errors' => $errors];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string)$finfo->file($file['tmp_name']);
    $ext   = detect_extension_from_mime($mime);
    $prefix = (string)($opts['basename_prefix'] ?? 'img');

    $baseDir  = image_upload_base_dir();
    $subdir   = trim((string)($opts['dest_subdir'] ?? ''), '/\\');
    $destDir  = $subdir ? ($baseDir . DIRECTORY_SEPARATOR . $subdir) : $baseDir;

    if (!ensure_dir($destDir)) {
        return ['ok' => false, 'error' => 'Failed to create upload directory.'];
    }

    $filename = generate_unique_filename($prefix, $ext);
    $absPath  = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    // Move first
    if (!move_uploaded_file($file['tmp_name'], $absPath)) {
        return ['ok' => false, 'error' => 'Failed to move uploaded file.'];
    }
    @chmod($absPath, 0644);

    // Optional resize (requires GD, only for jpg/png/webp)
    $autoResize = !empty($opts['auto_resize']);
    if ($autoResize && extension_loaded('gd') && in_array($mime, ['image/jpeg','image/pjpeg','image/jfif','image/png','image/webp'], true)) {
        $maxW = (int)($opts['max_w'] ?? 1600);
        $maxH = (int)($opts['max_h'] ?? 1600);
        __image_try_resize($absPath, $mime, $maxW, $maxH);
    }

    // Info
    $dim = @getimagesize($absPath);
    $url = image_public_url_from_path($absPath);

    return [
        'ok'       => true,
        'filename' => $filename,
        'path'     => $absPath,
        'url'      => function_exists('url_for') ? url_for($url) : $url,
        'mime'     => $mime,
        'size'     => filesize($absPath),
        'width'    => $dim[0] ?? null,
        'height'   => $dim[1] ?? null,
    ];
}

/**
 * Best-effort resize in place (keeps aspect ratio).
 */
function __image_try_resize(string $absPath, string $mime, int $maxW, int $maxH): void {
    [$w, $h] = @getimagesize($absPath) ?: [0, 0];
    if ($w <= 0 || $h <= 0) { return; }

    $scale = min($maxW / $w, $maxH / $h);
    if ($scale >= 1.0) { return; } // no need

    $newW = max(1, (int)floor($w * $scale));
    $newH = max(1, (int)floor($h * $scale));

    switch ($mime) {
        case 'image/jpeg':
        case 'image/pjpeg':
        case 'image/jfif':
            $src = @imagecreatefromjpeg($absPath); break;
        case 'image/png':
            $src = @imagecreatefrompng($absPath);  break;
        case 'image/webp':
            $src = @imagecreatefromwebp($absPath); break;
        default: return;
    }
    if (!$src) { return; }

    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

    switch ($mime) {
        case 'image/jpeg':
        case 'image/pjpeg':
        case 'image/jfif':
            @imagejpeg($dst, $absPath, 88); break;
        case 'image/png':
            @imagepng($dst,  $absPath, 6);  break;
        case 'image/webp':
            @imagewebp($dst, $absPath, 88); break;
    }

    imagedestroy($src);
    imagedestroy($dst);
}

// ---------- Deletion ----------

/**
 * Delete an uploaded image by absolute path, or by base_dir + filename.
 * Returns true if file disappeared (deleted or never existed).
 */
function delete_image(string $absPathOrBaseDir, ?string $filename = null): bool {
    $abs = $filename
        ? rtrim($absPathOrBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . sanitize_basename($filename)
        : $absPathOrBaseDir;

    if (!file_exists($abs)) { return true; }
    return @unlink($abs);
}
