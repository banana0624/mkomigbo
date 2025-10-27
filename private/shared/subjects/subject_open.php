<?php
// project-root/private/shared/subjects/subject_open.php
// Universal subject wrapper (OPEN).
// Expects $subject_slug to be set by caller (e.g., 'history', 'language2', ...).
// Renders a standard wrapper + article. No CSS/JS here.

require_once __DIR__ . '/subjects_config.php';

if (!isset($subject_slug) || !is_string($subject_slug) || $subject_slug === '') {
  $subject_slug = 'about';
}
$slug  = $subject_slug;
$label = $SUBJECTS[$slug]['label'] ?? ucfirst($slug);

// Begin section
?>
<section id="subject-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>"
         class="subject <?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?> wrapper">
  <article class="subject-article">

    <?php
      // Breadcrumbs / local nav (uses $subject_slug)
      require_once __DIR__ . '/_nav.php';
    ?>

    <!-- ===== PAGE BODY START (<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>) ===== -->
