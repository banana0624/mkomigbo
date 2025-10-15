<?php
// project-root/private/common/platforms/platform_common.php
// Lightweight storage helpers for platform “items” + settings.

declare(strict_types=1);

// Ensure base data dir
function platform_data_dir(): string {
  $dir = PRIVATE_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'platforms';
  if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
  return $dir;
}

function platform_items_path(string $slug): string {
  return platform_data_dir() . DIRECTORY_SEPARATOR . $slug . '.json';
}
function platform_settings_path(string $slug): string {
  return platform_data_dir() . DIRECTORY_SEPARATOR . $slug . '-settings.json';
}

function platform_items_load(string $slug): array {
  $file = platform_items_path($slug);
  if (!is_file($file)) return [];
  $json = @file_get_contents($file);
  $data = json_decode($json ?: '[]', true);
  return is_array($data) ? $data : [];
}

function platform_items_save(string $slug, array $items): bool {
  $file = platform_items_path($slug);
  $json = json_encode(array_values($items), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  return (bool)@file_put_contents($file, $json);
}

function platform_settings_load(string $slug): array {
  $file = platform_settings_path($slug);
  if (!is_file($file)) return [];
  $json = @file_get_contents($file);
  $data = json_decode($json ?: '[]', true);
  return is_array($data) ? $data : [];
}

function platform_settings_save(string $slug, array $settings): bool {
  $file = platform_settings_path($slug);
  $json = json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  return (bool)@file_put_contents($file, $json);
}

// Uploads dir (public) e.g. /public/lib/uploads/platforms/{slug}
function platform_uploads_dir(string $slug): string {
  $dir = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'platforms' . DIRECTORY_SEPARATOR . $slug;
  if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
  return $dir;
}
function platform_uploads_url(string $slug): string {
  return url_for("/lib/uploads/platforms/{$slug}");
}
