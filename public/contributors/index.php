<?php
declare(strict_types=1);

/**
 * project-root/public/contributors/index.php
 *
 * Public contributors:
 *   /contributors/          → grid of contributors
 *   /contributors/{handle}/ → single contributor profile
 *
 * Staff CRUD lives under:
 *   /staff/contributors/
 */

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found');
}
require_once $init;

/**
 * Helpers
 * - h() and db() and (optionally) current_user() come from initialize.php
 * - Provide a minimal fallback for url_for() only if missing.
 */
if (!function_exists('url_for')) {
  function url_for(string $script_path): string {
    if (!defined('WWW_ROOT')) {
      return $script_path;
    }
    if ($script_path === '') {
      return WWW_ROOT;
    }
    if ($script_path[0] !== '/') {
      $script_path = '/' . $script_path;
    }
    return WWW_ROOT . $script_path;
  }
}

// Handle from rewrite: /contributors/{handle}/ -> ?handle={handle}
$handle = trim((string)($_GET['handle'] ?? ''));

// Page chrome
$page_title  = $handle !== '' ? 'Contributor' : 'Contributors';
$active_nav  = 'contributors';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}
if (!in_array('/lib/css/contributors.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/contributors.css';
}

// Prefer public_header if it exists
$publicHeader = PRIVATE_PATH . '/shared/public_header.php';
$header       = is_file($publicHeader) ? $publicHeader : (PRIVATE_PATH . '/shared/header.php');
require $header;

/**
 * Check if a column exists on contributors table (cached).
 */
if (!function_exists('contributors_column_exists')) {
  function contributors_column_exists(string $column): bool {
    static $cache = [];

    $column = strtolower($column);
    if (array_key_exists($column, $cache)) {
      return $cache[$column];
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
      $stmt = $db->prepare($sql);
      $stmt->execute([':col' => $column]);
      $cache[$column] = (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
      $cache[$column] = false;
    }

    return $cache[$column];
  }
}

$db = db(); /** @var PDO $db */

// Detect optional columns once
$hasSlug     = contributors_column_exists('slug');
$hasBioHtml  = contributors_column_exists('bio_html');
$hasAvatar   = contributors_column_exists('avatar_url');
$hasVisible  = contributors_column_exists('visible');
$hasEmail    = contributors_column_exists('email');

// Build common column list for SELECT
$columns = [
  'id',
  'COALESCE(display_name, username) AS display_name',
  'username',
];

$columns[] = $hasSlug    ? 'slug'       : 'NULL AS slug';
$columns[] = $hasBioHtml ? 'bio_html'   : 'NULL AS bio_html';
$columns[] = $hasAvatar  ? 'avatar_url' : 'NULL AS avatar_url';
$columns[] = $hasEmail   ? 'email'      : 'NULL AS email';

$selectCols = implode(",\n         ", $columns);

/* ==========================================================
 * Single profile: /contributors/{handle}/
 * ======================================================== */
if ($handle !== '') {
  // Which column is used as the public handle
  $handleCol = $hasSlug ? 'slug' : 'username';

  $sql = "
    SELECT {$selectCols}
      FROM contributors
     WHERE {$handleCol} = :handle
  ";
  if ($hasVisible) {
    $sql .= " AND COALESCE(visible,1) = 1";
  }
  $sql .= " LIMIT 1";

  $stmt = $db->prepare($sql);
  $stmt->execute([':handle' => $handle]);
  $c = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$c) {
    http_response_code(404);
    ?>
    <main class="container" style="max-width:900px;padding:1.75rem 0;">
      <div class="page-header-block">
        <h1>Contributor not found</h1>
        <p class="page-intro">
          We couldn’t find a contributor matching
          <strong><?= h($handle) ?></strong>.
        </p>
      </div>
      <p>
        <a class="btn" href="<?= h(url_for('/contributors/')) ?>">
          &larr; Back to all contributors
        </a>
      </p>
    </main>
    <?php
    require PRIVATE_PATH . '/shared/footer.php';
    exit;
  }

  $displayName = $c['display_name'] ?? $c['username'] ?? ('Contributor #' . (string)$c['id']);
  $avatar      = $c['avatar_url']   ?? '/lib/images/avatar-placeholder.png';
  $username    = $c['username']     ?? '';
  $slugValue   = $c['slug']         ?? '';
  $email       = $c['email']        ?? '';

  ?>
  <main class="container" style="max-width:900px;padding:1.75rem 0;">
    <div class="page-header-block" style="margin-bottom:1.5rem;">
      <header style="display:flex;gap:1.5rem;align-items:flex-start;">
        <img src="<?= h($avatar) ?>"
             alt=""
             style="width:80px;height:80px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        <div>
          <h1 style="margin:0 0 .25rem;"><?= h($displayName) ?></h1>
          <?php if ($username !== ''): ?>
            <p class="muted" style="margin:0;">@<?= h($username) ?></p>
          <?php elseif ($slugValue !== ''): ?>
            <p class="muted" style="margin:0;">@<?= h($slugValue) ?></p>
          <?php endif; ?>
          <?php if ($email !== ''): ?>
            <p style="margin:0.25rem 0 0;font-size:0.9rem;">
              <a href="mailto:<?= h($email) ?>"><?= h($email) ?></a>
            </p>
          <?php endif; ?>
        </div>
      </header>
    </div>

    <article class="prose" style="line-height:1.6;">
      <?php
      if ($hasBioHtml && !empty($c['bio_html'])) {
        echo $c['bio_html']; // assumed sanitized on input
      } else {
        echo '<p class="muted">No bio yet.</p>';
      }
      ?>
    </article>

    <p style="margin-top:1.5rem;">
      <a class="btn" href="<?= h(url_for('/contributors/')) ?>">&larr; All contributors</a>
    </p>
  </main>
  <?php
  require PRIVATE_PATH . '/shared/footer.php';
  exit;
}

/* ==========================================================
 * List view: /contributors/ — public card grid
 * ======================================================== */
$sql = "
  SELECT {$selectCols}
    FROM contributors
";
if ($hasVisible) {
  $sql .= " WHERE COALESCE(visible,1) = 1";
}
$sql .= "
   ORDER BY COALESCE(display_name, username, CAST(id AS CHAR))
";

$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Optional: show small note for logged-in staff/admin
$currentUser = function_exists('current_user') ? current_user() : null;
?>
<main class="container" style="padding:1.75rem 0;">
  <div class="page-header-block">
    <h1>Contributors</h1>
    <p class="page-intro">
      Public directory of Mkomigbo contributors.
    </p>
    <?php if ($currentUser): ?>
      <p class="muted" style="margin-top:.25rem;">
        Staff editing tools are available at
        <a href="<?= h(url_for('/staff/contributors/')) ?>">/staff/contributors/</a>.
      </p>
    <?php endif; ?>
  </div>

  <?php if (!$rows): ?>
    <p class="muted">No contributors yet.</p>
  <?php else: ?>
    <div class="contributors-grid"
         style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px;">
      <?php foreach ($rows as $c):
        $id        = (int)($c['id'] ?? 0);
        $name      = $c['display_name'] ?? $c['username'] ?? ('Contributor #' . (string)$id);
        $avatar    = $c['avatar_url']   ?? '/lib/images/avatar-placeholder.png';
        $username  = $c['username']     ?? '';
        $slugValue = $c['slug']         ?? '';
        $email     = $c['email']        ?? '';

        // Choose a public handle for the URL
        $handleKey = $hasSlug && $slugValue !== ''
          ? $slugValue
          : ($username !== '' ? $username : (string)$id);

        $url = url_for('/contributors/' . rawurlencode((string)$handleKey) . '/');

        // Plain-text preview of bio_html, if present
        $bioPreview = '';
        if ($hasBioHtml && !empty($c['bio_html'])) {
          $plain      = trim(strip_tags($c['bio_html']));
          if ($plain !== '') {
            $bioPreview = mb_substr($plain, 0, 160);
            if (mb_strlen($plain) > 160) {
              $bioPreview .= '…';
            }
          }
        }
        ?>
        <article class="card"
                 style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff;
                        box-shadow:0 1px 2px rgba(15,23,42,0.04);">
          <div style="display:flex;gap:10px;align-items:center;margin-bottom:8px;">
            <img src="<?= h($avatar) ?>"
                 alt=""
                 style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
            <div>
              <div style="font-weight:600;"><?= h($name) ?></div>
              <?php if ($username !== ''): ?>
                <div class="muted" style="font-size:12px;">@<?= h($username) ?></div>
              <?php elseif ($slugValue !== ''): ?>
                <div class="muted" style="font-size:12px;">@<?= h($slugValue) ?></div>
              <?php endif; ?>
              <?php if ($email !== ''): ?>
                <div class="muted" style="font-size:12px;margin-top:2px;">
                  <a href="mailto:<?= h($email) ?>"><?= h($email) ?></a>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <?php if ($bioPreview !== ''): ?>
            <p style="margin:0 0 10px;font-size:14px;color:#374151;white-space:pre-wrap;">
              <?= h($bioPreview) ?>
            </p>
          <?php else: ?>
            <p class="muted" style="margin:0 0 10px;font-size:14px;">
              No bio yet.
            </p>
          <?php endif; ?>

          <div style="text-align:right;">
            <a href="<?= h($url) ?>" class="btn btn-primary btn-sm">
              View profile
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
