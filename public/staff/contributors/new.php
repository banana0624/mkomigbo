<?php
// project-root/public/staff/contributors/new.php
declare(strict_types=1);

// 1) Bootstrap  (contributors → staff → public → project-root)
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  echo "FATAL: initialize.php not found at {$init}";
  exit;
}
require_once $init;

// 2) Auth guard (staff only)
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

// 3) DB + helpers
$db = db(); /** @var PDO $db */

// Small redirect helper that respects url_for()
if (!function_exists('mk_staff_redirect')) {
  function mk_staff_redirect(string $path): never {
    $url = function_exists('url_for') ? url_for($path) : $path;
    header('Location: ' . $url, true, 303);
    exit;
  }
}

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

// Column feature flags
$hasSlug    = contributors_column_exists('slug');
$hasEmail   = contributors_column_exists('email');
$hasVisible = contributors_column_exists('visible');
$hasBioHtml = contributors_column_exists('bio_html');
$hasAvatar  = contributors_column_exists('avatar_url');

// 4) Simple CSRF for this form (isolated)
if (!function_exists('contrib_csrf_token')) {
  function contrib_csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      @session_start();
    }
    if (empty($_SESSION['contrib_csrf'])) {
      $_SESSION['contrib_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['contrib_csrf'];
  }
}

if (!function_exists('contrib_csrf_tag')) {
  function contrib_csrf_tag(): string {
    return '<input type="hidden" name="_token" value="'
      . h(contrib_csrf_token())
      . '">';
  }
}

if (!function_exists('contrib_csrf_verify')) {
  function contrib_csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
      }
      $sent = $_POST['_token'] ?? '';
      $good = isset($_SESSION['contrib_csrf']) &&
              is_string($sent) &&
              hash_equals($_SESSION['contrib_csrf'], $sent);
      if (!$good) {
        http_response_code(403);
        echo "Invalid CSRF token.";
        exit;
      }
    }
  }
}

// 5) Page chrome (header, CSS)
$page_title  = 'New Contributor';
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

// 6) Defaults + validation
$errors = [];
$values = [
  'display_name' => '',
  'slug'         => '',
  'email'        => '',
  'visible'      => '1',
  'bio_html'     => '',
  'avatar_url'   => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  contrib_csrf_verify();

  // Pull values from POST safely
  $values['display_name'] = trim((string)($_POST['display_name'] ?? ''));
  $values['slug']         = trim((string)($_POST['slug'] ?? ''));
  $values['email']        = trim((string)($_POST['email'] ?? ''));
  $values['visible']      = (string)($_POST['visible'] ?? '1');
  $values['bio_html']     = (string)($_POST['bio_html'] ?? '');
  $values['avatar_url']   = trim((string)($_POST['avatar_url'] ?? ''));

  // Display name required
  if (function_exists('v_required')) {
    if ($err = v_required('Display name', $values['display_name'])) {
      $errors[] = $err;
    }
  } else {
    if ($values['display_name'] === '') {
      $errors[] = 'Display name is required.';
    }
  }

  // Slug validation (if column exists and provided)
  if ($hasSlug && $values['slug'] !== '') {
    if (function_exists('v_slug')) {
      if ($err = v_slug('Slug', $values['slug'])) {
        $errors[] = $err;
      }
    } elseif (!preg_match('~^[a-z0-9_-]+$~i', $values['slug'])) {
      $errors[] = 'Slug may contain letters, numbers, underscore or dash only.';
    }
  }

  // Simple email sanity check if email column exists
  if ($hasEmail && $values['email'] !== '') {
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'Email address is not valid.';
    }
  }

  // visible must be 0 or 1
  if ($hasVisible && !in_array($values['visible'], ['0', '1'], true)) {
    $values['visible'] = '1';
  }

  // If no errors so far, attempt insert
  if (!$errors) {
    $fields = [];
    $params = [];

    // display_name is required
    $fields[]                = 'display_name';
    $params[':display_name'] = $values['display_name'];

    if ($hasSlug && $values['slug'] !== '') {
      $fields[]        = 'slug';
      $params[':slug'] = $values['slug'];
    }

    if ($hasEmail && $values['email'] !== '') {
      $fields[]         = 'email';
      $params[':email'] = $values['email'];
    }

    if ($hasVisible) {
      $fields[]           = 'visible';
      $params[':visible'] = (int)$values['visible'];
    }

    if ($hasBioHtml && $values['bio_html'] !== '') {
      $fields[]            = 'bio_html';
      $params[':bio_html'] = $values['bio_html'];
    }

    if ($hasAvatar && $values['avatar_url'] !== '') {
      $fields[]              = 'avatar_url';
      $params[':avatar_url'] = $values['avatar_url'];
    }

    $sql = "INSERT INTO contributors (" . implode(', ', $fields) . ")
            VALUES (" . implode(', ', array_keys($params)) . ")";
    $st = $db->prepare($sql);

    try {
      if ($st->execute($params)) {
        if (function_exists('flash')) {
          flash('success', 'Contributor created successfully.');
        }
        mk_staff_redirect('/staff/contributors/');
      } else {
        $errors[] = 'Insert failed. Please try again.';
      }
    } catch (Throwable $e) {
      $code = (int)$e->getCode();
      $msg  = $e->getMessage();

      // Friendly message for duplicate slug / email
      if ($code === 23000 && stripos($msg, 'uq_contributors_slug') !== false) {
        $errors[] = 'That slug is already in use. Please choose a different slug.';
      } elseif ($code === 23000 && stripos($msg, 'slug') !== false) {
        $errors[] = 'That slug is already in use. Please choose a different slug.';
      } elseif ($code === 23000 && stripos($msg, 'email') !== false) {
        $errors[] = 'That email is already in use. Please choose a different email.';
      } else {
        $errors[] = 'Insert failed. Please try again.';
        if (defined('APP_ENV') && APP_ENV === 'local') {
          $errors[] = $msg;
        }
      }
    }
  }
}

// 7) Styled form output (styles in /lib/css/staff_forms.css)
?>
<main class="mk-main mk-main--staff">
  <section class="mk-section">
    <div class="mk-form">
      <p class="mk-form__crumb" style="margin:0 0 .75rem;">
        <a href="<?= h(url_for('/staff/contributors/')) ?>">&larr; Back to Contributors</a>
      </p>

      <h1>New Contributor</h1>
      <p class="mk-form__desc">
        Create a new contributor profile for use on the public contributors directory
        and across platforms.
      </p>

      <?php if ($errors): ?>
        <div class="mk-errors">
          <strong>Please fix the following:</strong>
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= h($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <?= contrib_csrf_tag() ?>

        <div class="mk-field">
          <label for="display_name">Display name *</label>
          <input
            type="text"
            id="display_name"
            name="display_name"
            value="<?= h($values['display_name']) ?>"
            required
          >
          <small>The public name shown on profiles and cards.</small>
        </div>

        <?php if ($hasSlug): ?>
          <div class="mk-field">
            <label for="slug">Slug (handle)</label>
            <input
              type="text"
              id="slug"
              name="slug"
              value="<?= h($values['slug']) ?>"
              placeholder="e.g. odie5"
            >
            <small>Optional. Used in URLs: /contributors/&lt;slug&gt;/ (letters, numbers, _ and - only).</small>
          </div>
        <?php endif; ?>

        <?php if ($hasEmail): ?>
          <div class="mk-field">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              value="<?= h($values['email']) ?>"
              placeholder="name@example.com"
            >
            <small>Optional. Used for contact or notifications.</small>
          </div>
        <?php endif; ?>

        <?php if ($hasAvatar): ?>
          <div class="mk-field">
            <label for="avatar_url">Avatar URL</label>
            <input
              type="text"
              id="avatar_url"
              name="avatar_url"
              value="<?= h($values['avatar_url']) ?>"
              placeholder="/lib/images/contributors/odie5.webp"
            >
            <small>Optional. Path or URL to the contributor’s profile image.</small>
          </div>
        <?php endif; ?>

        <?php if ($hasVisible): ?>
          <div class="mk-field">
            <label for="visible">Publicly visible?</label>
            <select id="visible" name="visible">
              <option value="1" <?= $values['visible'] === '1' ? 'selected' : '' ?>>Yes</option>
              <option value="0" <?= $values['visible'] === '0' ? 'selected' : '' ?>>No (hidden)</option>
            </select>
            <small>If set to “No”, the contributor will not appear on the public list.</small>
          </div>
        <?php endif; ?>

        <?php if ($hasBioHtml): ?>
          <div class="mk-field">
            <label for="bio_html">Bio (HTML or formatted text)</label>
            <textarea
              id="bio_html"
              name="bio_html"
            ><?= h($values['bio_html']) ?></textarea>
            <small>
              Optional. A short biography shown on the public profile. If using HTML,
              ensure it’s clean/safe.
            </small>
          </div>
        <?php endif; ?>

        <div class="mk-form__actions">
          <button type="submit" class="mk-btn-primary">Create contributor</button>
          <a href="<?= h(url_for('/staff/contributors/')) ?>" class="mk-btn-secondary">
            Back to Contributors
          </a>
        </div>
      </form>
    </div>
  </section>
</main>
<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  require SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  require PRIVATE_PATH . '/shared/footer.php';
}
