<?php
// project-root/public/subjects/show.php

require_once(__DIR__ . '/../../private/assets/initialize.php');

$slug = $_GET['slug'] ?? '';
if ($slug === '') { http_response_code(404); die("No subject selected."); }

$stmt = $db->prepare("SELECT * FROM subjects WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$subject = $stmt->get_result()->fetch_assoc();
if (!$subject) { http_response_code(404); die("Subject not found."); }

// (Optional) Fetch related pages; will be empty until you seed pages
$pages = [];
$p = $db->prepare("SELECT id, title, slug FROM pages WHERE subject_id = ? ORDER BY created_at ASC");
$p->bind_param("i", $subject['id']);
$p->execute();
$r = $p->get_result();
while ($row = $r->fetch_assoc()) { $pages[] = $row; }

$page_title = $subject['name'] . " • Mkomigbo";
$meta_description = $subject['meta_description'] ?? '';
$meta_keywords = $subject['meta_keywords'] ?? '';
$page_css = ['/lib/css/subjects.css'];
include(__DIR__ . '/../../private/shared/header.php');
?>
<h1><?= htmlspecialchars($subject['name']); ?></h1>
<?php if (!empty($subject['meta_description'])): ?>
  <p class="muted"><?= htmlspecialchars($subject['meta_description']); ?></p>
<?php endif; ?>

<section>
  <h2>Pages under “<?= htmlspecialchars($subject['name']); ?>”</h2>
  <?php if ($pages): ?>
    <ul>
      <?php foreach ($pages as $pg): ?>
        <li><a href="/subjects/page.php?slug=<?= urlencode($pg['slug']); ?>"><?= htmlspecialchars($pg['title']); ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="muted">No pages available yet for this subject.</p>
  <?php endif; ?>
</section>
<?php include(__DIR__ . '/../../private/shared/footer.php'); ?>
