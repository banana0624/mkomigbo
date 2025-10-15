<?php
declare(strict_types=1);

/**
 * Helpers around the Platforms Registry:
 * - building nav arrays
 * - resolving routes by slug or key
 * - legacy redirect helpers
 */

require_once __DIR__ . '/registry_functions.php'; // generic registry utilities

function platforms_nav_items(bool $onlyVisible = true): array {
    if (!function_exists('platforms_all')) { return []; }
    $all = platforms_all();

    // filter by enabled/visible (optional)
    $items = array_filter($all, function($p) use ($onlyVisible) {
        if (!($p['enabled'] ?? false)) return false;
        if ($onlyVisible && !($p['visible_in_nav'] ?? false)) return false;
        return true;
    });

    // sort by nav_order then name
    uasort($items, function($a, $b) {
        $oa = $a['nav_order'] ?? PHP_INT_MAX;
        $ob = $b['nav_order'] ?? PHP_INT_MAX;
        if ($oa === $ob) return strcmp($a['name'] ?? '', $b['name'] ?? '');
        return $oa <=> $ob;
    });

    // normalize to minimal menu shape
    return array_map(function($p) {
        return [
            'name'  => $p['name'] ?? '',
            'slug'  => $p['slug'] ?? '',
            'url'   => platform_route($p['slug'] ?? ''), // route() accepts key; key==slug in our registry
            'icon'  => $p['icon'] ?? null,
            'meta'  => $p['meta'] ?? [],
        ];
    }, $items);
}

function platform_url_by_slug(string $slug, ?string $child = null): string {
    // our registry keys == slugs; safe to call directly
    return platform_route($slug, $child);
}

/** Return 301 redirect target for a legacy slug (or null). */
function platform_redirect_target(string $legacySlug): ?string {
    return function_exists('platform_redirect_for')
        ? platform_redirect_for($legacySlug)
        : null;
}

/** Quick helper for active-state checks in nav UIs. */
function platform_is_active(string $slug, ?string $currentPath = null): bool {
    $currentPath ??= ($_SERVER['REQUEST_URI'] ?? '');
    $route = platform_url_by_slug($slug);
    return $route !== '' && str_starts_with($currentPath, rtrim($route, '/').'/');
}
