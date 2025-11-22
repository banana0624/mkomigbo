<?php
// project-root/private/common/ui/staff_platforms_nav.php
// Staff Platforms mini-nav: breadcrumbs + optional "Back to Platforms" and right-side slot.
declare(strict_types=1);

// --- Local shims (avoid redeclare) ---
if (!function_exists('spn_h')) {
  function spn_h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('spn_url_for')) {
  function spn_url_for(string $p): string {
    // Prefer project url_for() if present
    if (function_exists('url_for')) {
      return url_for($p);
    }
    return ($p !== '' && $p[0] !== '/') ? '/'.$p : $p;
  }
}

// $breadcrumbs (optional): array of ['label'=>string, 'url'=>string|'' ]
// Default: Staff → Platforms
$breadcrumbs = (isset($breadcrumbs) && is_array($breadcrumbs) && $breadcrumbs !== [])
  ? $breadcrumbs
  : [
      ['label'=>'Staff','url'=>'/staff/'],
      ['label'=>'Platforms','url'=>'/staff/platforms/'],
    ];

// Back button controls (override per page if you want)
$show_back = array_key_exists('show_back', get_defined_vars()) ? (bool)$show_back : true;
$back_href = isset($back_href) && is_string($back_href) && $back_href !== '' ? $back_href : '/staff/platforms/';
$back_text = isset($back_text) && is_string($back_text) && $back_text !== '' ? $back_text : 'Back to Platforms';

// Optional right-side HTML slot (e.g., “+ New Platform” button)
$right_html = isset($right_html) ? (string)$right_html : '';

?>
<nav class="spn-bar" aria-label="Breadcrumb" style="border-bottom:1px solid #eee;background:#fafafa;">
  <div class="spn-wrap" style="max-width:1100px;margin:0 auto;padding:.6rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;">
    <ol class="spn-bc" role="list" style="display:flex;gap:.5rem;list-style:none;padding:0;margin:0;font-size:.95rem;">
      <?php
      $lastIndex = count($breadcrumbs) - 1;
      foreach ($breadcrumbs as $i => $bc):
        $label = spn_h((string)($bc['label'] ?? ''));
        $url   = (string)($bc['url'] ?? '');
        if ($i > 0): ?>
          <li aria-hidden="true" style="color:#aaa;">/</li>
        <?php endif; ?>
        <li>
          <?php if ($url !== '' && $i !== $lastIndex): ?>
            <a href="<?= spn_h(spn_url_for($url)) ?>" style="text-decoration:none;color:#0b63bd;"><?= $label ?></a>
          <?php else: ?>
            <span <?= $i === $lastIndex ? 'aria-current="page"' : '' ?> style="color:#555;"><?= $label ?></span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>

    <div class="spn-actions" style="display:flex;align-items:center;gap:.5rem;">
      <?php if ($right_html !== ''): ?>
        <div class="spn-right"><?= $right_html ?></div>
      <?php endif; ?>

      <?php if ($show_back): ?>
        <a href="<?= spn_h(spn_url_for($back_href)) ?>"
           class="spn-back"
           style="display:inline-flex;align-items:center;gap:.4rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.5rem;padding:.4rem .7rem;text-decoration:none;color:#0b63bd;">
          ← <?= spn_h($back_text) ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>
