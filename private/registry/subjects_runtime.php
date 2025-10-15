<?php
// project-root/private/registry/subjects_runtime.php

declare(strict_types=1);

/**
 * project-root/private/registry/subjects_runtime.php
 *
 * Runtime helpers around the Subjects registry + optional DB overlay.
 * Prefers the new registry API (subjects_all/subjects_sorted/subject_by_id/subject_by_slug)
 * and uses PDO if available via db() or db_connect().
 *
 * Safe to include multiple times.
 */

if (defined('MK_SUBJECTS_RUNTIME_LOADED')) { return; }
define('MK_SUBJECTS_RUNTIME_LOADED', true);

/** -------- Registry accessors (delegate to new API when available) -------- */

function registry_subjects_raw(): array {
    if (function_exists('subjects_all'))    { return subjects_all(); }
    if (function_exists('subjects_sorted')) { return subjects_sorted(); }
    return [];
}

function registry_subjects(): array {
    if (function_exists('subjects_sorted')) { return subjects_sorted(); }
    // fallback: sort by nav_order / id
    $data = array_values(registry_subjects_raw());
    usort($data, function ($a, $b) {
        $ao = $a['nav_order'] ?? $a['id'] ?? 9999;
        $bo = $b['nav_order'] ?? $b['id'] ?? 9999;
        return $ao <=> $bo;
    });
    return $data;
}

function registry_subject_by_id(int $id): ?array {
    if (function_exists('subject_by_id')) { return subject_by_id($id); }
    foreach (registry_subjects_raw() as $row) {
        if ((int)($row['id'] ?? 0) === $id) return $row;
    }
    return null;
}

function registry_subject_by_slug(string $slug): ?array {
    if (function_exists('subject_by_slug')) { return subject_by_slug($slug); }
    foreach (registry_subjects_raw() as $row) {
        if (strcasecmp((string)($row['slug'] ?? ''), $slug) === 0) return $row;
    }
    return null;
}

/** -------- DB helpers (prefer PDO) -------- */

function __subjects_pdo(): ?PDO {
    if (function_exists('db')) {
        try { $pdo = db(); if ($pdo instanceof PDO) return $pdo; } catch (Throwable $e) {}
    }
    if (function_exists('db_connect')) {
        try { $pdo = db_connect(); if ($pdo instanceof PDO) return $pdo; } catch (Throwable $e) {}
    }
    return null;
}

/**
 * Load subjects primarily from DB (if any rows exist); otherwise fall back to registry order.
 * Shape: id, name, slug, meta_description, meta_keywords
 */
function subjects_load(?PDO $pdo = null): array {
    $pdo ??= __subjects_pdo();
    if ($pdo instanceof PDO) {
        try {
            $rs = $pdo->query("SELECT id, name, slug, meta_description, meta_keywords FROM subjects ORDER BY id ASC");
            $rows = $rs ? $rs->fetchAll(PDO::FETCH_ASSOC) : [];
            if (!empty($rows)) {
                foreach ($rows as &$r) {
                    $r['id'] = (int)$r['id'];
                }
                return $rows;
            }
        } catch (Throwable $e) { /* fall back to registry */ }
    }
    return registry_subjects();
}

/**
 * Merge DB subjects (if present) with registry, keeping registry order in nav.
 * Adds DB-only extra subjects at the end.
 */
function subjects_load_complete(?PDO $pdo = null): array {
    $pdo ??= __subjects_pdo();
    $dbMap = [];

    if ($pdo instanceof PDO) {
        try {
            $rs = $pdo->query("SELECT id, name, slug, meta_description, meta_keywords FROM subjects ORDER BY id ASC");
            $rows = $rs ? $rs->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($rows as $r) {
                $dbMap[(string)$r['slug']] = [
                    'id' => (int)$r['id'],
                    'name' => (string)$r['name'],
                    'slug' => (string)$r['slug'],
                    'meta_description' => (string)($r['meta_description'] ?? ''),
                    'meta_keywords'    => (string)($r['meta_keywords'] ?? ''),
                ];
            }
        } catch (Throwable $e) { /* ignore */ }
    }

    $merged = [];
    $seen   = [];
    foreach (registry_subjects() as $rs) {
        $slug = (string)($rs['slug'] ?? '');
        if ($slug === '') continue;
        if (isset($dbMap[$slug])) {
            $merged[] = $dbMap[$slug];
        } else {
            $merged[] = [
                'id' => (int)($rs['id'] ?? 0),
                'name' => (string)($rs['name'] ?? ''),
                'slug' => $slug,
                'meta_description' => (string)($rs['meta_description'] ?? ''),
                'meta_keywords'    => (string)($rs['meta_keywords'] ?? ''),
            ];
        }
        $seen[$slug] = true;
    }
    foreach ($dbMap as $slug => $row) {
        if (empty($seen[$slug])) $merged[] = $row;
    }
    return $merged;
}

/** Resolve a subject logo web path. Registry icon takes priority; otherwise scan fallback folders. */
function subject_logo_webpath(string $slug): ?string {
    $reg = registry_subject_by_slug($slug);
    if ($reg && !empty($reg['icon'])) {
        // icon in registry is web-relative (e.g. lib/images/logo/subject.svg)
        $web = ltrim((string)$reg['icon'], '/');
        $fs  = rtrim(defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public', DIRECTORY_SEPARATOR)
             . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $web);
        if (is_file($fs)) return $web;
    }

    $public = rtrim(defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public', DIRECTORY_SEPARATOR);
    $folders = [$public . '/lib/images/logo', $public . '/lib/images/subjects'];
    $exts = ['.svg', '.png', '.jpg', '.jpeg', '.jfif', '.webp'];

    foreach ($folders as $dir) {
        foreach ($exts as $ext) {
            $fs = $dir . '/' . $slug . $ext;
            if (is_file($fs)) {
                // return web path relative to /public
                $rel = str_replace($public . DIRECTORY_SEPARATOR, '', str_replace(['\\'], DIRECTORY_SEPARATOR, $fs));
                return str_replace(DIRECTORY_SEPARATOR, '/', $rel);
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
