<?php
// project-root/private/shared/subjects/_nav.php
// Subject breadcrumbs / local nav.
// Expects $subject_slug to be set by the caller or subject_open.php.
// Uses config (subjects_config.php) for safe labels/paths.

require_once __DIR__ . '/subjects_config.php';
$slug = $subject_slug ?? 'about';
$cfg  = $SUBJECTS[$slug] ?? null;

// simple helpers
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

?>
<nav class="subject-nav" style="margin:.5rem 0 1rem;display:flex;gap:.5rem;flex-wrap:wrap;">
  <span class="crumbs" style="opacity:.85;">
    <a href="<?php echo url_for('/'); ?>">Home</a> /
    <a href="<?php echo url_for('/subjects/'); ?>">Subjects</a> /
    <a href="<?php echo url_for('/subjects/' . esc($slug) . '/'); ?>"><?php echo esc($cfg['label'] ?? ucfirst($slug)); ?></a>
  </span>

  <span aria-hidden="true" style="opacity:.4;">|</span>

  <!-- quick local actions; adjust to your routes -->
  <a href="<?php echo url_for('/subjects/' . esc($slug) . '/'); ?>">Overview</a>
  <a href="<?php echo url_for('/staff/subjects/' . esc($slug) . '/pages/'); ?>">Pages</a>
  <a href="<?php echo url_for('/staff/subjects/' . esc($slug) . '/pages/new.php'); ?>">New</a>
</nav>
