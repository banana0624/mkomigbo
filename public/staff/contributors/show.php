<?php
// project-root/public/staff/contributors/show.php
declare(strict_types=1);

// 1) Bootstrap (contributors → staff → public → project-root)
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  echo "FATAL: initialize.php not found at {$init}";
  exit;
}
require_once $init;

// 2) Auth guard
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

// 3) DB + helpers
$db = db(); /** @var PDO $db */

// Column existence helper (cached)
if (!function_exists('contributors_column_exists')) {
  function contributors_column_exists(string $column): bool {
    static $cache = [];
    $key = strtolower($column);
    if (array_key_exists($key, $cache)) {
      return $cache[$key];
    }
    try {
      $db = db(); /** @var PDO $db */
      $sql = "
        SELECT 1
          FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = 'contributors'
           AND COLUMN_NAME  = :col
         LIMIT 1
      ";
      $st = $db->prepare($sql);
      $st->execute([':col' => $column]);
      $cache[$key] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
      $cache[$key] = false;
    }
    return $cache[$key];
  }
}

$hasSlug    = contributors_column_exists('slug');
$hasEmail   = contributors_column_exists('email');
$hasVisible = contributors_column_exists('visible');
$hasBioHtml = contributors_column_exists('bio_html');
$hasAvatar  = contributors_column_exists('avatar_url');

// 4) ID param
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(404);
  echo "Invalid contributor id.";
  exit;
}

// 5) Load contributor
$cols = [
  'id',
  'COALESCE(display_name, username) AS display_name',
  'username'
];
$cols[] = $hasSlug    ? 'slug'       : 'NULL AS slug';
$cols[] = $hasEmail   ? 'email'      : 'NULL AS email';
$cols[] = $hasVisible ? 'visible'    : 'NULL AS visible';
$cols[] = $hasBioHtml ? 'bio_html'   : 'NULL AS bio_html';
$cols[] = $hasAvatar  ? 'avatar_url' : 'NULL AS avatar_url';

$sql = "SELECT " . implode(', ', $cols) . " FROM contributors WHERE id = :id LIMIT 1";
$st  = $db->prepare($sql);
$st->execute([':id' => $id]);
$c = $st->fetch(PDO::FETCH_ASSOC);

if (!$c) {
  http_response_code(404);
  echo "Contributor not found.";
  exit;
}

$displayName = $c['display_name'] ?: $c['username'];
$username    = $c['username'];
$slug        = $hasSlug ? ($c['slug'] ?? '') : '';
$email       = $hasEmail ? ($c['email'] ?? '') : '';
$visible     = $hasVisible ? (int)($c['visible'] ?? 1) : 1;
$avatar      = $hasAvatar && !empty($c['avatar_url'])
  ? $c['avatar_url']
  : '/lib/images/avatar-placeholder.png';

// Public profile URL (if slug exists, prefer that)
$publicKey = $slug !== '' ? $slug : $username;
$publicUrl = url_for('/contributors/' . rawurlencode((string)$publicKey) . '/');

// 6) Page chrome
$page_title  = 'Contributor: ' . $displayName;
$active_nav  = 'contributors';
$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}
if (!in_array('/lib/css/staff_forms.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/staff_forms.css';
}

if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/staff_header.php')) {
  require SHARED_PATH . '/staff_header.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/header.php')) {
  require PRIVATE_PATH . '/shared/header.php';
}
?>
<main class="mk-main mk-main--staff">
  <section class="mk-section">
    <div class="mk-form">
      <p class="mk-form__crumb" style="margin:0 0 .75rem;">
        <a href="<?= h(url_for('/staff/contributors/')) ?>">&larr; Back to Contributors</a>
      </p>

      <header style="display:flex;gap:1.5rem;align-items:flex-start;margin-bottom:1.5rem;">
        <img src="<?= h($avatar) ?>"
             alt=""
             style="width:80px;height:80px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        <div>
          <h1 style="margin:0 0 .25rem;"><?= h($displayName) ?></h1>
          <p style="margin:0;color:#666;">@<?= h($username) ?></p>
          <?php if ($hasVisible): ?>
            <p style="margin:.4rem 0 0;font-size:.9rem;color:#555;">
              <?= $visible ? 'Visible on public directory' : 'Hidden from public directory' ?>
            </p>
          <?php endif; ?>
        </div>
      </header>

      <dl class="mk-dl">
        <?php if ($hasSlug): ?>
          <div class="mk-dl__row">
            <dt>Slug</dt>
            <dd><?= h($slug) ?></dd>
          </div>
        <?php endif; ?>

        <?php if ($hasEmail): ?>
          <div class="mk-dl__row">
            <dt>Email</dt>
            <dd><?= h($email) ?></dd>
          </div>
        <?php endif; ?>

        <div class="mk-dl__row">
          <dt>Public profile</dt>
          <dd>
            <a href="<?= h($publicUrl) ?>" target="_blank" rel="noopener">
              <?= h($publicUrl) ?>
            </a>
          </dd>
        </div>

        <?php if ($hasBioHtml): ?>
          <div class="mk-dl__row mk-dl__row--block">
            <dt>Bio</dt>
            <dd>
              <div class="mk-bio-box">
                <?= !empty($c['bio_html']) ? $c['bio_html'] : '<p>No bio yet.</p>' ?>
              </div>
            </dd>
          </div>
        <?php endif; ?>
      </dl>

      <div class="mk-form__actions">
        <a href="<?= h(url_for('/staff/contributors/edit.php?id=' . (int)$c['id'])) ?>"
           class="mk-btn-primary">Edit</a>
        <a href="<?= h(url_for('/staff/contributors/delete.php?id=' . (int)$c['id'])) ?>"
           class="mk-btn-secondary">Delete</a>
        <a href="<?= h(url_for('/staff/contributors/')) ?>"
           class="mk-btn-secondary">Back to Contributors</a>
      </div>
    </div>
  </section>
</main>
<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  require SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  require PRIVATE_PATH . '/shared/footer.php';
}
