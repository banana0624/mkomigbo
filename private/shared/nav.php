<?php
// project-root/private/shared/nav.php
// Renders the primary navigation using DB → registry fallback.
// Only render when called from header.php, and only once.

// Require a header context
if (!defined('NAV_CONTEXT') || NAV_CONTEXT !== 'header') {
  return; // Someone tried to include nav.php directly — do nothing.
}

// Guard against double-render
if (defined('NAV_RENDERED') && NAV_RENDERED === true) {
  return;
}

$__base = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') . '/' : '/';

$__activeSlug = '';
if (!empty($_GET['slug'])) {
  $__activeSlug = (string)$_GET['slug'];
} else {
  $req = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
  if ($__base !== '/' && strpos($req, rtrim($__base,'/')) === 0) {
    $req = substr($req, strlen(rtrim($__base,'/')));
  }
  $parts = array_values(array_filter(explode('/', $req)));
  $__activeSlug = $parts[0] ?? '';
}

// Load subjects (DB rows override; registry fills any missing)
if (function_exists('subjects_load_complete')) {
  $__subjects = subjects_load_complete($db);
} else {
  $__subjects = subjects_load($db);
}
?>
<nav class="main-nav" role="navigation" aria-label="Primary">
  <ul>
    <li>
      <a href="<?= $__base; ?>index.php"<?= $__activeSlug === '' ? ' class="active"' : '' ?>>Home</a>
    </li>
    <?php foreach ($__subjects as $s):
      $slug = (string)$s['slug'];
      $name = strtoupper((string)$s['name']);
      $href = $__base . rawurlencode($slug) . '/';
      $isActive = ($slug === $__activeSlug);
    ?>
      <li>
        <a href="<?= $href; ?>"<?= $isActive ? ' class="active"' : '' ?>>
          <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
<?php define('NAV_RENDERED', true); ?>
