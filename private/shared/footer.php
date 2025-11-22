<?php
declare(strict_types=1);
/**
 * project-root/private/shared/footer.php
 * Closes <main>, renders a shared footer, emits $scripts_foot, closes HTML.
 */

if (!function_exists('h')) {
  function h(string $s=''): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('url_for')) {
  function url_for(string $p): string {
    if ($p === '' || $p[0] !== '/') $p = '/'.$p;
    return rtrim(defined('WWW_ROOT') ? (string)WWW_ROOT : '', '/') . $p;
  }
}

/** Match header.php’s asset URL behavior (no closures) */
if (!function_exists('asset_public_path')) {
  function asset_public_path(): string {
    static $p = null;
    if ($p === null) {
      $p = defined('PUBLIC_PATH')
        ? rtrim((string)PUBLIC_PATH, DIRECTORY_SEPARATOR)
        : rtrim(dirname(__DIR__, 2) . '/public', DIRECTORY_SEPARATOR);
    }
    return $p;
  }
}
if (!function_exists('asset_url')) {
  function asset_url(string $webPath): string {
    $webPath = '/' . ltrim($webPath, '/');
    $url = url_for($webPath);
    $abs = asset_public_path() . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
    if (is_file($abs)) {
      $ts = @filemtime($abs);
      if ($ts) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'v=' . $ts;
      }
    }
    return $url;
  }
}

$scripts_foot       = isset($scripts_foot) && is_array($scripts_foot) ? $scripts_foot : [];
$footer_note        = isset($footer_note) ? (string)$footer_note : '';
$footer_extra_html  = isset($footer_extra_html) ? (string)$footer_extra_html : '';
$year               = date('Y');
?>
  </main>

  <footer class="site-footer">
    <div class="container" style="display:flex;gap:.75rem;justify-content:space-between;align-items:center;flex-wrap:wrap">
      <p class="muted">&copy; <?= h($year) ?> Mkomigbo</p>
      <?php if ($footer_extra_html !== ''): ?>
        <div class="footer-extra"><?= $footer_extra_html ?></div>
      <?php endif; ?>
      <?php if ($footer_note !== ''): ?>
        <p class="muted"><?= h($footer_note) ?></p>
      <?php endif; ?>
    </div>
  </footer>

  <?php foreach ($scripts_foot as $src): ?>
    <script src="<?= h(asset_url((string)$src)) ?>"></script>
  <?php endforeach; ?>
</body>
</html>
