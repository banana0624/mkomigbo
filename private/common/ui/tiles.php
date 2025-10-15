<?php
// project-root/private/common/ui/tiles.php

/**
 * $tiles = [
 *   ['href'=>'/path/','title'=>'Tile','desc'=>'Short desc','class'=>'subject--history|platform--blogs|role--staff','img'=>'/lib/images/subjects/history.svg']
 * ];
 */
if (!isset($tiles) || !is_array($tiles)) { $tiles = []; }
?>
<section class="tiles">
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;">
    <?php foreach ($tiles as $t): ?>
      <a class="card subject-halo <?= h($t['class'] ?? '') ?>" href="<?= h(url_for($t['href'] ?? '#')) ?>">
        <div class="thumb" style="aspect-ratio:16/9;">
          <?php if (!empty($t['img'])): ?>
            <img src="<?= h(url_for($t['img'])) ?>" alt="" loading="lazy">
          <?php endif; ?>
        </div>
        <h3 style="margin:10px 0 4px;"><?= h($t['title'] ?? '') ?></h3>
        <?php if (!empty($t['desc'])): ?>
          <p style="opacity:.8;margin:0;"><?= h($t['desc']) ?></p>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>
