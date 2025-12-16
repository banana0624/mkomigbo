<?php
declare(strict_types=1);

/**
 * project-root/private/shared/nav.php
 *
 * Site-wide navigation helpers:
 * - render_main_nav(?string $active_nav = null): string
 * - render_breadcrumbs(array $items): string
 *
 * Expects url_for() and h() to be available (from header/initialize).
 * All auth-related pieces are defensive: if certain functions/sessions
 * don't exist, it degrades gracefully.
 */

if (!function_exists('render_main_nav')) {
  /**
   * Primary site navigation: Home • Subjects • Platforms • Contributors • Staff
   *
   * @param string|null $active_nav One of: home, subjects, platforms, contributors, staff
   *                                (set per page before including header.php)
   */
  function render_main_nav(?string $active_nav = null): string {
    $active = $active_nav ? strtolower($active_nav) : '';

    // Core nav items for public + staff + platforms
    $items = [
      'home' => [
        'label' => 'Home',
        'href'  => url_for('/'),
      ],
      'subjects' => [
        'label' => 'Subjects',
        'href'  => url_for('/subjects/'),
      ],
      'platforms' => [
        'label' => 'Platforms',
        'href'  => url_for('/platforms/'),
      ],
      'contributors' => [
        'label' => 'Contributors',
        'href'  => url_for('/contributors/'),
      ],
      'staff' => [
        'label' => 'Staff',
        'href'  => url_for('/staff/'),
      ],
    ];

    // --- Auth / account area (best-effort, defensive) ---------------------
    $is_logged_in = false;
    $display_name = '';
    $logout_url   = null;

    // 1) Try standard helpers if they exist
    if (function_exists('is_logged_in')) {
      $is_logged_in = (bool)is_logged_in();
    } elseif (function_exists('is_logged_in_admin')) {
      $is_logged_in = (bool)is_logged_in_admin();
    } elseif (!empty($_SESSION['admin_id'] ?? null)) {
      // Fallback: basic session check if above functions are absent
      $is_logged_in = true;
    }

    if ($is_logged_in) {
      // Try to identify the user
      if (function_exists('current_user')) {
        $u = current_user();
        if (is_array($u)) {
          $display_name = (string)($u['username'] ?? $u['name'] ?? '');
        } else {
          $display_name = (string)$u;
        }
      } elseif (!empty($_SESSION['admin_username'] ?? '')) {
        $display_name = (string)$_SESSION['admin_username'];
      } elseif (!empty($_SESSION['username'] ?? '')) {
        $display_name = (string)$_SESSION['username'];
      } else {
        $display_name = 'Account';
      }

      // Default logout URL; adjust if your actual route differs
      $logout_url = url_for('/staff/logout.php');
    }

    ob_start();
    ?>
    <nav class="main-nav" aria-label="Primary">
      <div class="main-nav-inner">
        <ul class="main-nav-items">
          <?php foreach ($items as $key => $item):
            $isActive = ($active === $key);
            $liClass  = 'main-nav-item';
            $aClass   = 'main-nav-link';
            if ($isActive) {
              $liClass .= ' is-active';
              $aClass  .= ' is-active';
            }
          ?>
            <li class="<?= h($liClass) ?>">
              <a class="<?= h($aClass) ?>" href="<?= h($item['href']) ?>">
                <?= h($item['label']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="main-nav-account">
          <?php if ($is_logged_in): ?>
            <span class="nav-user">
              <span class="nav-user-label">Signed in as</span>
              <span class="nav-user-name"><?= h($display_name) ?></span>
            </span>
            <?php if ($logout_url): ?>
              <a class="nav-logout" href="<?= h($logout_url) ?>">Logout</a>
            <?php endif; ?>
          <?php else: ?>
            <a class="nav-login" href="<?= h(url_for('/staff/login.php')) ?>">Staff login</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
    <?php
    return (string)ob_get_clean();
  }
}

if (!function_exists('render_breadcrumbs')) {
  /**
   * Standard breadcrumb trail.
   *
   * Each item: ['label' => 'History', 'url' => '/subjects/history/']
   * Last item may omit 'url' and is treated as current page.
   */
  function render_breadcrumbs(array $items): string {
    if (!$items) {
      return '';
    }

    $count = count($items);
    $lastIndex = $count - 1;

    ob_start();
    ?>
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <ol class="breadcrumbs-list">
        <?php foreach ($items as $idx => $bc):
          $label  = h((string)($bc['label'] ?? ''));
          $url    = (string)($bc['url'] ?? '');
          $isLast = ($idx === $lastIndex);
        ?>
          <li class="breadcrumbs-item<?= $isLast ? ' is-current' : '' ?>">
            <?php if ($url !== '' && !$isLast): ?>
              <a href="<?= h(url_for($url)) ?>"><?= $label ?></a>
            <?php else: ?>
              <span<?= $isLast ? ' aria-current="page"' : '' ?>><?= $label ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ol>
    </nav>
    <?php
    return (string)ob_get_clean();
  }
}