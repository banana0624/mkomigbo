<?php
// project-root/public/subjects/index.php
require_once(__DIR__ . '/../../private/assets/initialize.php');
$page_title = "Subjects • Mkomigbo";
$page_css = ['/lib/css/subjects.css'];
include(__DIR__ . '/../../private/shared/header.php');

// Fetch subjects
$subjects = [];
if ($res = $db->query("SELECT id, name, slug, meta_description FROM subjects ORDER BY id ASC")) {
  while ($r = $res->fetch_assoc()) { $subjects[] = $r; }
}
?>
<h1>Subjects</h1>
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
<?php include(__DIR__ . '/../../private/shared/footer.php'); ?>
