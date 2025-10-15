<?php
declare(strict_types=1);
/**
 * Staff header wrapper.
 * Usage: set vars, then `require PRIVATE_PATH.'/shared/staff_header.php';`
 * Includes base header, then renders staff-specific nav bar.
 */
require __DIR__ . '/header.php';
?>
  <header class="site-header">
    <div class="container header-bar">
      <div class="brand">
        <a class="logo" href="<?= h(url_for('/staff/')) ?>" aria-label="Staff Console">
          <img src="<?= h(url_for('/lib/images/logo/site.svg')) ?>" alt="" width="32" height="32">
        </a>
        <a class="site-name" href="<?= h(url_for('/staff/')) ?>">Staff Console</a>
      </div>
      <nav class="main-nav">
        <a href="<?= h(url_for('/staff/')) ?>">Home</a>
        <a href="<?= h(url_for('/staff/subjects/')) ?>">Subjects</a>
        <a href="<?= h(url_for('/staff/subjects/pgs/')) ?>">Pages</a>
        <a href="<?= h(url_for('/staff/pages/thumbnails.php')) ?>">Thumbnails</a>
      </nav>
    </div>
    <?php if (!empty($breadcrumbs) && is_array($breadcrumbs)): ?>
      <?php if (function_exists('render_breadcrumbs')): ?>
        <?= render_breadcrumbs($breadcrumbs) ?>
      <?php else: ?>
        <nav class="breadcrumbs"><ol>
          <?php foreach ($breadcrumbs as $bc):
            $label = h((string)($bc['label'] ?? ''));
            $url   = (string)($bc['url'] ?? '');
            if ($url !== ''): ?>
              <li><a href="<?= h(url_for($url)) ?>"><?= $label ?></a></li>
            <?php else: ?>
              <li><?= $label ?></li>
            <?php endif; endforeach; ?>
        </ol></nav>
      <?php endif; ?>
    <?php endif; ?>
  </header>
  <main id="main" class="site-main container">
    <?php if (function_exists('display_session_message')): ?>
      <?= display_session_message() ?>
    <?php endif; ?>
