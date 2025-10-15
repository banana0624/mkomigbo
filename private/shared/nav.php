<?php
declare(strict_types=1);

/**
 * project-root/private/shared/nav.php
 * Main/site navigation with a Platforms submenu listing all 10 platforms.
 *
 * Usage from header.php:
 *   require_once __DIR__ . '/nav.php';
 *   echo render_main_nav($active_nav ?? null);
 */

if (!function_exists('url_for')) {
  function url_for(string $p): string {
    if ($p === '' || $p[0] !== '/') $p = '/'.$p;
    return rtrim(defined('WWW_ROOT') ? WWW_ROOT : '', '/') . $p;
  }
}

/* Tiny polyfill for environments missing str_starts_with (PHP < 8), safe on 8+. */
if (!function_exists('str_starts_with')) {
  function str_starts_with(string $haystack, string $needle): bool {
    return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
  }
}

if (!function_exists('platform_links')) {
  /** 10 platforms (single source of truth). */
  function platform_links(): array {
    // Keep this in sync with /staff/platforms/ tiles
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
      ['slug'=>'logs',          'label'=>'Logs'], // keep Logs last
    ];
    foreach ($items as &$it) {
      $it['href'] = url_for('/staff/platforms/' . $it['slug'] . '/');
    }
    unset($it);
    return $items;
  }
}

if (!function_exists('nav_links')) {
  /** Top-level nav structure (left to right). */
  function nav_links(): array {
    return [
      ['slug' => 'home',        'label' => 'Home',         'href' => url_for('/')],
      ['slug' => 'staff',       'label' => 'Staff',        'href' => url_for('/staff/')],
      ['slug' => 'subjects',    'label' => 'Subjects',     'href' => url_for('/staff/subjects/')],
      // Platforms has a submenu:
      ['slug' => 'platforms',   'label' => 'Platforms',    'href' => url_for('/staff/platforms/'), 'submenu' => platform_links()],
      ['slug' => 'contributors','label' => 'Contributors', 'href' => url_for('/staff/contributors/')],
      ['slug' => 'admins',      'label' => 'Admins',       'href' => url_for('/staff/admins/')],
    ];
  }
}

if (!function_exists('render_main_nav')) {
  /** Render main nav with accessible submenu for Platforms. */
  function render_main_nav(?string $active = null): string {
    // Auto-detect active if not passed (helps when some pages forget $active_nav)
    if ($active === null && isset($_SERVER['REQUEST_URI'])) {
      $uri = $_SERVER['REQUEST_URI'];
      if (str_starts_with($uri, '/staff/platforms/'))       $active = 'platforms';
      elseif (str_starts_with($uri, '/staff/subjects/'))    $active = 'subjects';
      elseif (str_starts_with($uri, '/staff/contributors')) $active = 'contributors';
      elseif (str_starts_with($uri, '/staff/admins'))       $active = 'admins';
      elseif (str_starts_with($uri, '/staff'))              $active = 'staff';
      else                                                  $active = 'home';
    }

    $items = nav_links();

    // Scoped CSS (emit once)
    $css = '';
    if (!defined('MK_NAV_CSS_EMITTED')) {
      define('MK_NAV_CSS_EMITTED', true);
      $css = <<<CSS
<style>
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

    $html = $css . '<nav class="main-nav" aria-label="Main">' . PHP_EOL .
            '  <ul style="display:flex;gap:.25rem;list-style:none;margin:0;padding:0">' . PHP_EOL;

    foreach ($items as $it) {
      $isActive = $active === ($it['slug'] ?? '');
      $liClass  = $isActive ? ' class="is-active"' : '';

      // Platforms submenu
      if (!empty($it['submenu']) && is_array($it['submenu'])) {
        $html .= '    <li class="has-sub' . ($isActive ? ' is-active' : '') . '">' .
                 '<a href="'.htmlspecialchars($it['href']).'" aria-haspopup="true" aria-expanded="false">' .
                 htmlspecialchars($it['label']) . '</a>' . PHP_EOL .
                 '      <ul class="submenu" role="menu" aria-label="Platforms">' . PHP_EOL;

        foreach ($it['submenu'] as $sub) {
          $html .= '        <li role="none"><a role="menuitem" href="' .
                   htmlspecialchars($sub['href']) . '">' .
                   htmlspecialchars($sub['label']) . '</a></li>' . PHP_EOL;
        }
        $html .= '      </ul>' . PHP_EOL . '    </li>' . PHP_EOL;
        continue;
      }

      // Regular item
      $html .= '    <li' . $liClass . '><a href="' . htmlspecialchars($it['href']) . '">' .
               htmlspecialchars($it['label']) . '</a></li>' . PHP_EOL;
    }

    $html .= '  </ul>' . PHP_EOL . '</nav>' . PHP_EOL;
    return $html;
  }
}
