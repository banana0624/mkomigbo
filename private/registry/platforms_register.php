<?php
/**
 * project-root/private/registry/platforms_register.php
 *
 * Canonical registry for site "platform" surfaces.
 * Loaded automatically by initialize.php (registry autoloader).
 *
 * Primary platforms kept:
 *  - blogs, forums, communities, posts, contributions, videos(reels|vlog), tags
 *
 * Removed/merged:
 *  - threads  -> lives under forums
 *  - reels/vlog -> unified under videos (as children)
 *  - logs   -> admin-only (not a public platform)
 *
 * Exposes:
 *  - $PLATFORMS_REGISTER (top-level platforms)
 *  - $PLATFORM_REDIRECTS (legacy slug -> canonical route)
 *  - platforms_all(), platforms_sorted(), platform_by_key(), platform_by_slug()
 *  - platform_exists(), platform_is_enabled()
 *  - platform_children($key), platform_route($key, $child = null)
 *  - platform_redirect_for($legacySlug)
 */

if (defined('MK_REGISTRY_PLATFORMS_LOADED')) { return; }
define('MK_REGISTRY_PLATFORMS_LOADED', true);

/** Helper for URL building */
if (!function_exists('__mk_url')) {
    function __mk_url(string $path): string {
        $path = '/' . ltrim($path, '/');
        if (function_exists('url_for')) {
            return url_for($path);
        }
        $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        return $base . $path;
    }
}

$PLATFORMS_REGISTER = [

    // 1) Long-form editorial content (authored articles)
    'blogs' => [
        'name'           => 'Blogs',
        'slug'           => 'blogs',
        'enabled'        => true,
        'visible_in_nav' => true,
        'nav_order'      => 10,
        'route'          => '/platforms/blogs/',
        'icon'           => 'lib/images/platforms/blogs.svg',
        'permissions'    => [
            'public_read'       => true,
            'contributor_write' => true,
            'staff_manage'      => true,
        ],
        'meta' => [
            'description' => 'Long-form articles and editorials.',
            'keywords'    => 'blogs, articles, editorials, stories',
        ],
    ],

    // 2) Classic message-board with threads
    'forums' => [
        'name'           => 'Forums',
        'slug'           => 'forums',
        'enabled'        => true,
        'visible_in_nav' => true,
        'nav_order'      => 20,
        'route'          => '/platforms/forums/',
        'icon'           => 'lib/images/platforms/forums.svg',
        'permissions'    => [
            'public_read'       => true,
            'contributor_write' => true,  // create threads/replies if allowed
            'staff_manage'      => true,
        ],
        'meta' => [
            'description' => 'Threaded discussions and Q&A.',
            'keywords'    => 'forums, threads, discussions, q&a',
        ],
        // threads live inside forums (not a top-level platform)
        'children' => [
            // child routes would be like /platforms/forums/{thread-slug}/
        ],
    ],

    // 3) Interest-based groups (membership, feeds, events, etc.)
    'communities' => [
        'name'           => 'Communities',
        'slug'           => 'communities',
        'enabled'        => true,
        'visible_in_nav' => true,
        'nav_order'      => 30,
        'route'          => '/platforms/communities/',
        'icon'           => 'lib/images/platforms/communities.svg',
        'permissions'    => [
            'public_read'       => true,   // e.g., allow read-only
            'contributor_write' => true,   // members post within a community
            'staff_manage'      => true,
        ],
        'meta' => [
            'description' => 'Groups organized by interests or regions.',
            'keywords'    => 'communities, groups, members, events',
        ],
    ],

    // 4) Site-wide content feed (aggregates posts from multiple sources)
    'posts' => [
        'name'           => 'Posts',
        'slug'           => 'posts',
        'enabled'        => true,
        'visible_in_nav' => true,
        'nav_order'      => 40,
        'route'          => '/platforms/posts/',
        'icon'           => 'lib/images/platforms/posts.svg',
        'permissions'    => [
            'public_read'       => true,
            'contributor_write' => true,  // quick posts / micro-blogs
            'staff_manage'      => true,
        ],
        'meta' => [
            'description' => 'Unified feed of latest posts across the site.',
            'keywords'    => 'posts, updates, feed, micro',
        ],
    ],

    // 5) Contribution portal (submit content of various types)
    'contributions' => [
        'name'           => 'Contributions',
        'slug'           => 'contributions',
        'enabled'        => true,
        'visible_in_nav' => true,
        'nav_order'      => 50,
        'route'          => '/platforms/contributions/',
        'icon'           => 'lib/images/platforms/contributions.svg',
        'permissions'    => [
            'public_read'       => false,
            'contributor_write' => true,  // submit drafts, uploads, proposals
            'staff_manage'      => true,
        ],
        'meta' => [
            'description' => 'Submit articles, media, and suggestions.',
            'keywords'    => 'contribute, submit, drafts, uploads',
        ],
    ],

    // 6) Unified video surface: short-form (reels) + long-form (vlog)
    'videos' => [
        'name'           => 'Videos',
        'slug'           => 'videos',
        'enabled'        => true,
        'visible_in_nav' => true,
        'nav_order'      => 60,
        'route'          => '/platforms/videos/',
        'icon'           => 'lib/images/platforms/videos.svg',
        'permissions'    => [
            'public_read'       => true,
            'contributor_write' => true,  // upload if allowed
            'staff_manage'      => true,
        ],
        'meta' => [
            'description' => 'All video content: reels and vlogs.',
            'keywords'    => 'videos, reels, vlog, media',
        ],
        'children' => [
            'reels' => [
                'name'  => 'Reels',
                'slug'  => 'reels',
                'route' => '/platforms/videos/reels/',
                'icon'  => 'lib/images/platforms/reels.svg',
                'meta'  => [
                    'description' => 'Short-form vertical clips.',
                    'keywords'    => 'reels, short video, clips',
                ],
            ],
            'vlog' => [
                'name'  => 'Vlog',
                'slug'  => 'vlog',
                'route' => '/platforms/videos/vlog/',
                'icon'  => 'lib/images/platforms/vlog.svg',
                'meta'  => [
                    'description' => 'Long-form video blogs.',
                    'keywords'    => 'vlog, video blog, long-form',
                ],
            ],
        ],
    ],

    // 7) Taxonomy browser (not content-producing itself)
    'tags' => [
        'name'           => 'Tags',
        'slug'           => 'tags',
        'enabled'        => true,
        'visible_in_nav' => true,
        'nav_order'      => 70,
        'route'          => '/platforms/tags/',
        'icon'           => 'lib/images/platforms/tags.svg',
        'permissions'    => [
            'public_read'       => true,
            'contributor_write' => false,
            'staff_manage'      => true,
        ],
        'meta' => [
            'description' => 'Browse content by tags and topics.',
            'keywords'    => 'tags, taxonomy, topics',
        ],
    ],

];

/**
 * Legacy/removed top-level slugs -> canonical routes.
 * Keep this to avoid 404s from older links or menus.
 */
$PLATFORM_REDIRECTS = [
    'threads'   => '/platforms/forums/',           // Threads live inside forums
    'reels'     => '/platforms/videos/reels/',     // Now a child of videos
    'vlog'      => '/platforms/videos/vlog/',      // Now a child of videos
    'vlogs'     => '/platforms/videos/vlog/',      // plural alias
    'community' => '/platforms/communities/',      // singular alias

    // "logs" is admin-only; not a public platform
    'logs'      => '/admin/logs/',
];

/** Accessors & helpers */
function platforms_all(): array {
    global $PLATFORMS_REGISTER;
    return $PLATFORMS_REGISTER;
}

/** Sorted by nav_order then name */
function platforms_sorted(): array {
    $all = platforms_all();
    uasort($all, function($a, $b) {
        $oa = $a['nav_order'] ?? PHP_INT_MAX;
        $ob = $b['nav_order'] ?? PHP_INT_MAX;
        if ($oa === $ob) {
            return strcmp($a['name'] ?? '', $b['name'] ?? '');
        }
        return $oa <=> $ob;
    });
    return $all;
}

function platform_by_key(string $key): ?array {
    $all = platforms_all();
    return $all[$key] ?? null;
}

function platform_by_slug(string $slug): ?array {
    foreach (platforms_all() as $row) {
        if (($row['slug'] ?? '') === $slug) {
            return $row;
        }
    }
    return null;
}

function platform_exists(string $key): bool {
    return platform_by_key($key) !== null;
}

function platform_is_enabled(string $key): bool {
    $p = platform_by_key($key);
    return (bool)($p['enabled'] ?? false);
}

function platform_children(string $key): array {
    $p = platform_by_key($key);
    if (!$p) { return []; }
    return (array)($p['children'] ?? []);
}

/**
 * Build a platform route.
 * - $key: top-level platform key (e.g., 'videos')
 * - $child: child key under 'children' (e.g., 'reels')
 */
function platform_route(string $key, ?string $child = null): string {
    $p = platform_by_key($key);
    if (!$p) { return __mk_url('/platforms/'); }
    if (!platform_is_enabled($key)) { return __mk_url('/platforms/'); }

    if ($child) {
        $children = platform_children($key);
        if (isset($children[$child]['route'])) {
            return __mk_url($children[$child]['route']);
        }
    }
    return __mk_url($p['route'] ?? '/platforms/');
}

/** Given a legacy top-level slug, return redirect target (or null). */
function platform_redirect_for(string $legacySlug): ?string {
    global $PLATFORM_REDIRECTS;
    return $PLATFORM_REDIRECTS[$legacySlug] ?? null;
}
