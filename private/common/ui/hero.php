<?php
// project-root/private/common/ui/hero.php

// Usage: set $hero = ['title'=>'...', 'kicker'=>'...', 'intro'=>'...', 'class'=>'role--public|subject--history|platform--blogs']
if (!isset($hero) || !is_array($hero)) { $hero = []; }
$cls = 'hero ' . ($hero['class'] ?? '');
?>
<section class="<?= h($cls) ?>" style="margin:16px 0 20px;">
  <?php if (!empty($hero['kicker'])): ?>
    <div class="muted" style="letter-spacing:.04em;text-transform:uppercase;font-size:.8rem;">
      <?= h($hero['kicker']) ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($hero['title'])): ?>
    <h1 style="margin:.25rem 0 .5rem;"><?= h($hero['title']) ?></h1>
  <?php endif; ?>
  <?php if (!empty($hero['intro'])): ?>
    <p style="max-width:70ch;"><?= h($hero['intro']) ?></p>
  <?php endif; ?>
</section>
