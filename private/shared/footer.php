<?php
declare(strict_types=1);
/**
 * project-root/private/shared/footer.php
 *
 * Base footer that:
 *  - Closes <main> (opened by the base header)
 *  - Renders a shared site footer (can be customized via $footer_note / $footer_extra_html)
 *  - Emits any $scripts_foot[] JS files
 *  - Closes </body></html>
 *
 * Optional vars you can set before including:
 *  - $scripts_foot       string[]  JS paths to load at the end of body (relative to WWW_ROOT)
 *  - $footer_note        string    Small text shown inside footer (right side)
 *  - $footer_extra_html  string    Raw HTML injected inside the footer container (above the note)
 */

if (!function_exists('h')) {
  function h(string $s=''): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('url_for')) {
  function url_for(string $p): string {
    if ($p === '' || $p[0] !== '/') $p = '/'.$p;
    return rtrim(defined('WWW_ROOT') ? WWW_ROOT : '', '/') . $p;
  }
}

$scripts_foot      = isset($scripts_foot) && is_array($scripts_foot) ? $scripts_foot : [];
$footer_note       = isset($footer_note) ? (string)$footer_note : '';
$footer_extra_html = isset($footer_extra_html) ? (string)$footer_extra_html : '';
?>
  </main>

  <footer class="site-footer">
    <div class="container" style="display:flex;gap:.75rem;justify-content:space-between;align-items:center;flex-wrap:wrap">
      <p class="muted">&copy; <?= date('Y') ?> Mkomigbo</p>
      <?php if ($footer_extra_html !== ''): ?>
        <div class="footer-extra"><?= $footer_extra_html ?></div>
      <?php endif; ?>
      <?php if ($footer_note !== ''): ?>
        <p class="muted"><?= h($footer_note) ?></p>
      <?php endif; ?>
    </div>
  </footer>

  <?php foreach ($scripts_foot as $src): ?>
    <script src="<?= h(url_for($src)) ?>"></script>
  <?php endforeach; ?>
</body>
</html>
