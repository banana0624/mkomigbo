<?php
// project-root/public/index.php

require_once(__DIR__ . '/../private/assets/initialize.php');
$page_title = "Mkomigbo • Igbo Heritage Resource Center";
$meta_description = "Mkomigbo is an online resource center for everything relating to Igbos and their cultural heritage.";
$page_css = ['/lib/css/subjects.css'];
include(__DIR__ . '/../private/shared/header.php');

// Fetch subjects
$subjects = [];
if ($res = $db->query("SELECT id, name, slug, meta_description FROM subjects ORDER BY id ASC")) {
  while ($r = $res->fetch_assoc()) { $subjects[] = $r; }
}
?>
<section class="hero card">
  <h1>Welcome to Mkomigbo</h1>
  <p class="muted">Mkomigbo is an online resource center for everything relating to Igbos and their Cultural Heritage.</p>
</section>

<section class="home-subjects">
  <h2>Explore Subjects</h2>
  <div class="subjects-grid">
    <?php foreach ($subjects as $s): ?>
      <div class="subject-card card">
        <a href="/<?= urlencode($s['slug']); ?>/"><?= htmlspecialchars($s['name']); ?></a>
        <?php if (!empty($s['meta_description'])): ?>
          <div class="desc"><?= htmlspecialchars($s['meta_description']); ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php include(__DIR__ . '/../private/shared/footer.php'); ?>
