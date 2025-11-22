<?php
// project-root/public/staff/platforms/index.php
declare(strict_types=1);

/**
 * Staff Platforms Console
 * - Hero + tiles for quick navigation to each platform
 * - Compact CRUD console for Platforms + Items
 */

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found at: ' . $init);
}
require_once $init;

// ---- Auth guard: prefer unified auth helpers ----
$auth_ok = false;
if (is_file(PRIVATE_PATH . '/functions/auth.php')) {
  require_once PRIVATE_PATH . '/functions/auth.php';
  if (function_exists('require_staff')) {
    require_staff(); // exits/redirects internally if not allowed
    $auth_ok = true;
  }
}
if (!$auth_ok) {
  // Fallback: allow if an admin session exists, else redirect to login
  if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
  }
  $is_staff = !empty($_SESSION['admin']['id']);
  if (!$is_staff) {
    $loginUrl = function_exists('url_for') ? url_for('/staff/login.php') : '/staff/login.php';
    header('Location: ' . $loginUrl);
    exit;
  }
}

require_once PRIVATE_PATH . '/functions/platform_functions.php';

// Local helpers (keep them small + safe)
if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}
if (!function_exists('post')) {
  function post(string $k, ?string $d = null): ?string {
    return isset($_POST[$k]) ? (string)$_POST[$k] : $d;
  }
}
if (!function_exists('as_int')) {
  function as_int($v): int {
    return (int)$v;
  }
}

// ---- Page chrome ----
$page_title   = 'Platforms';
$active_nav   = 'staff';
$body_class   = 'role--staff staff-platforms';
$page_logo    = '/lib/images/icons/grid.svg';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

// Prefer staff header; gracefully fall back to base header
$staffHeader = PRIVATE_PATH . '/shared/staff_header.php';
$baseHeader  = PRIVATE_PATH . '/shared/header.php';
$footer      = PRIVATE_PATH . '/shared/footer.php';

if (is_file($staffHeader)) {
  require $staffHeader;
} else {
  require $baseHeader;
}

/* ====== NAV BLOCK (breadcrumbs + top-right "New Platform") ====== */
$breadcrumbs = [
  ['label' => 'Staff',     'url' => '/staff/'],
  ['label' => 'Platforms', 'url' => '/staff/platforms/'],
];
$show_back  = false; // already on Platforms list
$right_html =
  '<a class="btn" href="' .
  h(function_exists('url_for') ? url_for('/staff/platforms/new.php') : '/staff/platforms/new.php') .
  '" style="display:inline-block;border:1px solid #e2e8f0;background:#0b63bd;color:#fff;padding:.35rem .6rem;border-radius:.5rem;text-decoration:none;">' .
  '+ New Platform</a>';

$nav_partial = PRIVATE_PATH . '/common/ui/staff_platforms_nav.php';
if (is_file($nav_partial)) {
  require $nav_partial;
}
/* ================================================================== */

/** ------------------------------------------------------------------
 *  SECTION A — Optional Hero + Tiles
 *  ------------------------------------------------------------------ */
$heroFile  = PRIVATE_PATH . '/common/ui/hero.php';
$tilesFile = PRIVATE_PATH . '/common/ui/tiles.php';

// Define tiles (slug => staff sub-console)
$platforms_tiles_src = [
  ['slug' => 'audios',        'name' => 'Audios',        'icon' => '/lib/images/icons/audio.svg',      'desc' => 'Podcasts & tracks'],
  ['slug' => 'blogs',         'name' => 'Blogs',         'icon' => '/lib/images/icons/book.svg',       'desc' => 'Articles and posts'],
  ['slug' => 'communities',   'name' => 'Communities',   'icon' => '/lib/images/icons/users.svg',      'desc' => 'Groups & spaces'],
  ['slug' => 'contributions', 'name' => 'Contributions', 'icon' => '/lib/images/icons/hand-heart.svg', 'desc' => 'Submissions & help'],
  ['slug' => 'forums',        'name' => 'Forums',        'icon' => '/lib/images/icons/messages.svg',   'desc' => 'Discussions & threads'],
  ['slug' => 'logs',          'name' => 'Logs',          'icon' => '/lib/images/icons/note.svg',       'desc' => 'Activity & audit logs'],
  ['slug' => 'posts',         'name' => 'Posts',         'icon' => '/lib/images/icons/note.svg',       'desc' => 'Short updates'],
  ['slug' => 'reels',         'name' => 'Reels',         'icon' => '/lib/images/icons/reel.svg',       'desc' => 'Short verticals'],
  ['slug' => 'tags',          'name' => 'Tags',          'icon' => '/lib/images/icons/tag.svg',        'desc' => 'Taxonomy'],
  ['slug' => 'videos',        'name' => 'Videos',        'icon' => '/lib/images/icons/video.svg',      'desc' => 'Clips & streams'],
];

$tiles = array_map(function (array $p) {
  return [
    'href'  => "/staff/platforms/{$p['slug']}/",
    'title' => $p['name'],
    'desc'  => $p['desc'],
    'class' => "platform--{$p['slug']}",
    'img'   => $p['icon'],
  ];
}, $platforms_tiles_src);

if (is_file($heroFile)) {
  $hero = [
    'title' => 'Platforms',
    'intro' => 'Manage platform types and their items.',
    'class' => 'role--staff',
  ];
  require $heroFile;
}

if (is_file($tilesFile)) {
  require $tilesFile;
} else {
  // Minimal fallback if tiles partial isn’t present
  echo '<section class="container" style="margin:1rem 0;"><h2>Quick Links</h2><ul class="list">';
  foreach ($tiles as $t) {
    echo '<li><a href="' . h($t['href']) . '">' . h($t['title']) . '</a> — <span class="muted">' . h($t['desc']) . '</span></li>';
  }
  echo '</ul></section>';
}

/** ------------------------------------------------------------------
 *  SECTION B — Compact CRUD Console (Platforms + Items)
 *  ------------------------------------------------------------------ */

// Handle actions
$act = $_POST['act'] ?? '';
if ($act !== '' && function_exists('csrf_check')) {
  csrf_check();
}

$msgs = [];

/* Platforms CRUD */
if ($act === 'pl_create') {
  $res = create_platform([
    'name'             => trim((string)post('pl_name', '')),
    'slug'             => (string)post('pl_slug', ''),
    'description_html' => (string)post('pl_desc', ''),
    'visible'          => as_int(post('pl_visible', '1')),
    'position'         => as_int(post('pl_position', '1')),
  ]);
  $msgs[] = !empty($res['ok'])
    ? 'Platform created.'
    : 'Create failed: ' . json_encode($res['errors'] ?? []);
}

if ($act === 'pl_update') {
  $res = update_platform(as_int(post('pl_id', '0')), [
    'name'             => trim((string)post('pl_name', '')),
    'slug'             => (string)post('pl_slug', ''),
    'description_html' => (string)post('pl_desc', ''),
    'visible'          => as_int(post('pl_visible', '1')),
    'position'         => as_int(post('pl_position', '1')),
  ]);
  $msgs[] = !empty($res['ok'])
    ? 'Platform updated.'
    : 'Update failed: ' . json_encode($res['errors'] ?? []);
}

if ($act === 'pl_delete') {
  $ok = delete_platform(as_int(post('pl_id', '0')));
  $msgs[] = $ok ? 'Platform deleted.' : 'Delete failed.';
}

/* Items CRUD */
if ($act === 'it_create') {
  $res = create_item(as_int(post('it_platform_id', '0')), [
    'menu_name' => trim((string)post('it_name', '')),
    'slug'      => (string)post('it_slug', ''),
    'body_html' => (string)post('it_body', ''),
    'visible'   => as_int(post('it_visible', '1')),
    'position'  => as_int(post('it_position', '1')),
  ]);
  $msgs[] = !empty($res['ok'])
    ? 'Item created.'
    : 'Create failed: ' . json_encode($res['errors'] ?? []);
}

if ($act === 'it_update') {
  $res = update_item(as_int(post('it_id', '0')), [
    'menu_name' => trim((string)post('it_name', '')),
    'slug'      => (string)post('it_slug', ''),
    'body_html' => (string)post('it_body', ''),
    'visible'   => as_int(post('it_visible', '1')),
    'position'  => as_int(post('it_position', '1')),
  ]);
  $msgs[] = !empty($res['ok'])
    ? 'Item updated.'
    : 'Update failed: ' . json_encode($res['errors'] ?? []);
}

if ($act === 'it_delete') {
  $ok = delete_item(as_int(post('it_id', '0')));
  $msgs[] = $ok ? 'Item deleted.' : 'Delete failed.';
}

// Load data for console
$platforms = list_platforms_all();
$selected_pl_id = (int)($_GET['pid'] ?? 0);
if ($selected_pl_id === 0 && !empty($platforms)) {
  $selected_pl_id = (int)$platforms[0]['id'];
}
$items = $selected_pl_id ? list_items_all($selected_pl_id) : [];

// Render CRUD console
?>
<section class="container" style="margin:1.25rem 0;">
  <h2>Manage Platforms &amp; Items</h2>

  <?php if (!empty($msgs)): ?>
    <div class="alert">
      <ul>
        <?php foreach ($msgs as $m): ?>
          <li><?= h($m) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns: 1fr 1fr;gap:1rem;">
    <!-- Left: Platforms -->
    <div>
      <h3>All Platforms</h3>
      <table class="table">
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Slug</th><th>Vis</th><th>Pos</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($platforms as $pl): $id = (int)$pl['id']; ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= h($pl['name']) ?></td>
            <td><?= h($pl['slug']) ?></td>
            <td><?= (int)$pl['visible'] ?></td>
            <td><?= (int)$pl['position'] ?></td>
            <td style="white-space:nowrap;display:flex;gap:.4rem;flex-wrap:wrap;">
              <a class="btn" href="<?= h(url_for('/staff/platforms/?pid=' . $id)) ?>">Items</a>
              <a class="btn" href="<?= h(url_for('/staff/platforms/edit.php?id=' . $id)) ?>">Edit</a>
              <a class="btn btn-danger" href="<?= h(url_for('/staff/platforms/delete.php?id=' . $id)) ?>">Delete</a>
              <a class="btn" href="<?= h(url_for('/staff/platforms/items/new.php?platform_id=' . $id)) ?>">+ Item</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Fallback button styles in case .btn not defined in CSS -->
      <style>
        .btn {
          display:inline-block;
          border:1px solid #e2e8f0;
          background:#f8fafc;
          padding:.35rem .6rem;
          border-radius:.5rem;
          text-decoration:none;
          color:#0b63bd;
        }
        .btn-danger {
          border-color:#fecaca;
          background:#fee2e2;
          color:#991b1b;
        }
        .muted { color:#6b7280; }
      </style>

      <h4>Create Platform</h4>
      <form method="post">
        <?= function_exists('csrf_field') ? csrf_field() : '' ?>
        <input type="hidden" name="act" value="pl_create">

        <label>Name
          <input name="pl_name" required>
        </label>

        <label>Slug
          <input name="pl_slug" placeholder="auto-from-name if blank">
        </label>

        <label>Visible
          <select name="pl_visible">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </label>

        <label>Position
          <input name="pl_position" type="number" value="1">
        </label>

        <label>Description (HTML)
          <textarea name="pl_desc" rows="4"></textarea>
        </label>

        <button class="btn btn-primary">Create</button>
      </form>

      <?php if ($selected_pl_id):
        $curr = find_platform_by_id($selected_pl_id);
        if ($curr): ?>
        <h4>Edit Platform #<?= (int)$curr['id'] ?></h4>
        <form method="post">
          <?= function_exists('csrf_field') ? csrf_field() : '' ?>
          <input type="hidden" name="act" value="pl_update">
          <input type="hidden" name="pl_id" value="<?= (int)$curr['id'] ?>">

          <label>Name
            <input name="pl_name" value="<?= h($curr['name']) ?>" required>
          </label>

          <label>Slug
            <input name="pl_slug" value="<?= h($curr['slug']) ?>">
          </label>

          <label>Visible
            <select name="pl_visible">
              <option value="1"<?= (int)$curr['visible'] === 1 ? ' selected' : ''; ?>>Yes</option>
              <option value="0"<?= (int)$curr['visible'] === 0 ? ' selected' : ''; ?>>No</option>
            </select>
          </label>

          <label>Position
            <input name="pl_position" type="number" value="<?= (int)$curr['position'] ?>">
          </label>

          <label>Description (HTML)
            <textarea name="pl_desc" rows="4"><?= h($curr['description_html'] ?? '') ?></textarea>
          </label>

          <button class="btn btn-primary">Save</button>
        </form>

        <form method="post"
              onsubmit="return confirm('Delete platform and all its items?');"
              style="margin-top:.5rem;">
          <?= function_exists('csrf_field') ? csrf_field() : '' ?>
          <input type="hidden" name="act" value="pl_delete">
          <input type="hidden" name="pl_id" value="<?= (int)$curr['id'] ?>">
          <button class="btn btn-danger">Delete</button>
        </form>
      <?php endif; endif; ?>
    </div>

    <!-- Right: Items for selected platform -->
    <div>
      <h3>
        Items
        <?php
        if ($selected_pl_id ?? false) {
          $curr = $curr ?? ($selected_pl_id ? find_platform_by_id($selected_pl_id) : null);
          if (!empty($curr['name'])) {
            echo ' for “' . h($curr['name']) . '”';
          }
        }
        ?>
      </h3>

      <?php if (!$selected_pl_id): ?>
        <p class="muted">Select a platform to manage its items.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr><th>ID</th><th>Menu Name</th><th>Slug</th><th>Vis</th><th>Pos</th></tr>
          </thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= (int)$it['id'] ?></td>
              <td><?= h($it['menu_name']) ?></td>
              <td><?= h($it['slug']) ?></td>
              <td><?= (int)$it['visible'] ?></td>
              <td><?= (int)$it['position'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <h4>Create Item</h4>
        <form method="post">
          <?= function_exists('csrf_field') ? csrf_field() : '' ?>
          <input type="hidden" name="act" value="it_create">
          <input type="hidden" name="it_platform_id" value="<?= (int)$selected_pl_id ?>">

          <label>Menu Name
            <input name="it_name" required>
          </label>

          <label>Slug
            <input name="it_slug" placeholder="auto-from-name if blank">
          </label>

          <label>Visible
            <select name="it_visible">
              <option value="1">Yes</option>
              <option value="0">No</option>
            </select>
          </label>

          <label>Position
            <input name="it_position" type="number" value="1">
          </label>

          <label>Body (HTML)
            <textarea name="it_body" rows="5"></textarea>
          </label>

          <button class="btn btn-primary">Create</button>
        </form>

        <?php if (!empty($items)): ?>
          <h4>Edit Item (quick pick)</h4>
          <form method="post" style="display:grid;gap:.5rem;">
            <?= function_exists('csrf_field') ? csrf_field() : '' ?>
            <input type="hidden" name="act" value="it_update">

            <label>Item
              <select name="it_id" required>
                <?php foreach ($items as $it): ?>
                  <option value="<?= (int)$it['id'] ?>">
                    #<?= (int)$it['id'] ?> — <?= h($it['menu_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>

            <label>Menu Name
              <input name="it_name" placeholder="(leave blank to keep)">
            </label>

            <label>Slug
              <input name="it_slug" placeholder="(leave blank to keep)">
            </label>

            <label>Visible
              <select name="it_visible">
                <option value="">(no change)</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </label>

            <label>Position
              <input name="it_position" type="number" placeholder="(keep)">
            </label>

            <label>Body (HTML)
              <textarea name="it_body" rows="5" placeholder="(keep)"></textarea>
            </label>

            <button class="btn btn-primary">Save</button>
          </form>

          <h4>Delete Item</h4>
          <form method="post" onsubmit="return confirm('Delete this item?');">
            <?= function_exists('csrf_field') ? csrf_field() : '' ?>
            <input type="hidden" name="act" value="it_delete">
            <label>Item
              <select name="it_id" required>
                <?php foreach ($items as $it): ?>
                  <option value="<?= (int)$it['id'] ?>">
                    #<?= (int)$it['id'] ?> — <?= h($it['menu_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <button class="btn btn-danger">Delete</button>
          </form>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
if (is_file($footer)) {
  require $footer;
}
