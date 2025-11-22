<?php
declare(strict_types=1);
/**
 * project-root/private/shared/nav.php
 * Main/site navigation with Platforms submenu + auth-aware login/logout pill.
 *
 * Usage from header.php:
 *   require_once __DIR__ . '/nav.php';
 *   echo render_main_nav($active_nav ?? null);
 */

if (!function_exists('url_for')) {
    function url_for(string $p): string {
        if ($p === '' || $p[0] !== '/') {
            $p = '/'.$p;
        }
        $root = defined('WWW_ROOT') ? (string)WWW_ROOT : '';
        return rtrim($root, '/') . $p;
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('nav_is_staff_context')) {
    function nav_is_staff_context(): bool {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return str_starts_with($uri, '/staff/');
    }
}

if (!function_exists('nav_first_initial')) {
    function nav_first_initial(string $name): string {
        if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
            return mb_strtoupper(mb_substr($name, 0, 1));
        }
        return strtoupper(substr($name, 0, 1));
    }
}

if (!function_exists('platform_links')) {
    /**
     * @param string $base '/platforms/' for public, '/staff/platforms/' for staff
     */
    function platform_links(string $base = '/platforms/'): array {
        $items = [
            ['slug'=>'audios',        'label'=>'Audios'],
            ['slug'=>'blogs',         'label'=>'Blogs'],
            ['slug'=>'communities',   'label'=>'Communities'],
            ['slug'=>'contributions', 'label'=>'Contributions'],
            ['slug'=>'forums',        'label'=>'Forums'],
            ['slug'=>'posts',         'label'=>'Posts'],
            ['slug'=>'reels',         'label'=>'Reels'],
            ['slug'=>'videos',        'label'=>'Videos'],
            ['slug'=>'tags',          'label'=>'Tags'],
            ['slug'=>'logs',          'label'=>'Logs'],
        ];
        foreach ($items as &$it) {
            $it['href'] = url_for(rtrim($base, '/') . '/' . $it['slug'] . '/');
        }
        unset($it);
        return $items;
    }
}

if (!function_exists('nav_links')) {
    function nav_links(): array {
        $isStaffCtx = nav_is_staff_context();

        // Corrected hrefs for public context (point into staff section)
        $subjectsHref     = url_for('/staff/subjects/');
        $contributorsHref = url_for('/staff/contributors/');
        $platformsHref    = url_for('/staff/platforms/');

        // Staff context hrefs
        $staffHomeHref  = url_for('/staff/');
        $staffSubjects  = url_for('/staff/subjects/');
        $staffContribs  = url_for('/staff/contributors/');
        $staffPlatforms = url_for('/staff/platforms/');
        $adminsHref     = url_for('/staff/admins/');

        // Decide which to use based on context
        $subjectsLink     = $isStaffCtx ? $staffSubjects    : $subjectsHref;
        $contributorsLink = $isStaffCtx ? $staffContribs    : $contributorsHref;
        $platformsLink    = $isStaffCtx ? $staffPlatforms   : $platformsHref;
        $platformsBase    = $isStaffCtx ? '/staff/platforms/' : '/staff/platforms/';

        // Auth-aware staff link (login / home)
        $u = function_exists('current_user') ? current_user() : null;
        $staffEntryHref = $u ? $staffHomeHref : url_for('/staff/login.php');

        $links = [
            ['slug'=>'home',         'label'=>'Home',         'href'=>url_for('/')],
            ['slug'=>'subjects',     'label'=>'Subjects',     'href'=>$subjectsLink],
            ['slug'=>'platforms',    'label'=>'Platforms',    'href'=>$platformsLink,    'submenu'=>platform_links($platformsBase)],
            ['slug'=>'contributors', 'label'=>'Contributors',  'href'=>$contributorsLink],
            ['slug'=>'staff',        'label'=>'Staff',        'href'=>$staffEntryHref],
        ];

        $isAdmin = function_exists('auth_has_role') ? auth_has_role('admin') : false;
        if ($isAdmin) {
            $links[] = ['slug'=>'admins', 'label'=>'Admins', 'href'=>$adminsHref];
        }

        return $links;
    }
}

if (!function_exists('render_auth_block')) {
    function render_auth_block(): string {
        $u = function_exists('current_user') ? current_user() : null;
        $css = '';
        if (!defined('MK_AUTH_BLOCK_CSS_EMITTED')) {
            define('MK_AUTH_BLOCK_CSS_EMITTED', true);
            $css = <<<CSS
<style>
.auth-block{margin-left:auto;display:flex;align-items:center;gap:.5rem}
.auth-user{display:flex;align-items:center;gap:.45rem;padding:.25rem .5rem;border:1px solid #e5e7eb;border-radius:.6rem;background:#fff}
.auth-avatar{width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:600;background:#111;color:#fff}
.auth-name{font-size:.9rem}
.auth-actions a{display:inline-block;padding:.3rem .5rem;border-radius:.45rem;text-decoration:none;border:1px solid #e5e7eb;background:#fff}
</style>
CSS;
        }

        if ($u && is_array($u)) {
            $name    = trim((string)($u['name'] ?? ($u['username'] ?? ($u['email'] ?? 'User'))));
            $initial = nav_first_initial($name);
            $logout  = url_for('/staff/logout.php');
            return $css . '<div class="auth-block">'
                 . '<div class="auth-user"><span class="auth-avatar">'.htmlspecialchars($initial).'</span>'
                 . '<span class="auth-name">'.htmlspecialchars($name).'</span></div>'
                 . '<div class="auth-actions"><a href="'.htmlspecialchars($logout).'">Logout</a></div>'
                 . '</div>';
        }

        $login = url_for('/staff/login.php');
        return $css . '<div class="auth-block"><div class="auth-actions"><a href="'.htmlspecialchars($login).'">Login</a></div></div>';
    }
}

if (!function_exists('render_main_nav')) {
    function render_main_nav(?string $active = null): string {
        if ($active === null && isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
            if (str_starts_with($uri, '/staff/platforms/'))        $active = 'platforms';
            elseif (str_starts_with($uri, '/platforms/'))         $active = 'platforms';
            elseif (str_starts_with($uri, '/staff/subjects/'))     $active = 'subjects';
            elseif (str_starts_with($uri, '/subjects/'))          $active = 'subjects';
            elseif (str_starts_with($uri, '/staff/contributors/')) $active = 'contributors';
            elseif (str_starts_with($uri, '/contributors/'))      $active = 'contributors';
            elseif (str_starts_with($uri, '/staff/admins/'))      $active = 'admins';
            elseif (str_starts_with($uri, '/staff/'))             $active = 'staff';
            else                                                 $active = 'home';
        }

        $items = nav_links();

        $css = '';
        if (!defined('MK_NAV_CSS_EMITTED')) {
            define('MK_NAV_CSS_EMITTED', true);
            $css = <<<CSS
<style>
.main-navbar{display:flex;align-items:center;gap:.75rem}
.main-nav{display:flex;gap:.75rem;align-items:center}
.main-nav a{display:inline-block;padding:.4rem .6rem;border-radius:.5rem;text-decoration:none}
.main-nav a:focus{outline:2px solid #0070f3;outline-offset:2px}
.main-nav .is-active>a{font-weight:600}
.main-nav .has-sub{position:relative}
.main-nav .submenu{position:absolute;left:0;top:100%;min-width:220px;background:#fff;border:1px solid #e5e7eb;border-radius:.6rem;padding:.4rem;margin-top:.25rem;box-shadow:0 6px 24px rgba(0,0,0,.08);display:none;z-index:30}
.main-nav .submenu a{width:100%;padding:.45rem .6rem;white-space:nowrap}
.main-nav .has-sub:focus-within .submenu,
.main-nav .has-sub:hover .submenu{display:block}
</style>
CSS;
        }

        $navHtml  = $css . '<div class="main-navbar">'
                  . '<nav class="main-nav" aria-label="Main">'.PHP_EOL
                  . '  <ul style="display:flex;gap:.25rem;list-style:none;margin:0;padding:0">'.PHP_EOL;

        foreach ($items as $it) {
            $isActive = ($active === ($it['slug'] ?? ''));
            $liClass  = $isActive ? ' class="is-active"' : '';

            if (!empty($it['submenu']) && is_array($it['submenu'])) {
                $navHtml .= '    <li class="has-sub'.($isActive ? ' is-active' : '').'">'
                         . '<a href="'.htmlspecialchars($it['href']).'" aria-haspopup="true" aria-expanded="false">'
                         . htmlspecialchars($it['label']).'</a>'.PHP_EOL
                         . '      <ul class="submenu" role="menu" aria-label="'.htmlspecialchars($it['label']).' Submenu">'.PHP_EOL;
                foreach ($it['submenu'] as $sub) {
                    $navHtml .= '        <li role="none"><a role="menuitem" href="'.htmlspecialchars($sub['href']).'">'.htmlspecialchars($sub['label']).'</a></li>'.PHP_EOL;
                }
                $navHtml .= '      </ul>'.PHP_EOL.'    </li>'.PHP_EOL;
            } else {
                $navHtml .= '    <li'.$liClass.'><a href="'.htmlspecialchars($it['href']).'">'
                         . htmlspecialchars($it['label']).'</a></li>'.PHP_EOL;
            }
        }

        $navHtml .= '  </ul>'.PHP_EOL.'</nav>'.PHP_EOL;

        $authHtml = render_auth_block();

        return $navHtml . $authHtml . '</div>'.PHP_EOL;
    }
}
