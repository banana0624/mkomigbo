<?php
// project-root/private/common/staff_subject_hub.php

// Renders a staff subject hub. Caller must set: $subject_slug, $subject_name
declare(strict_types=1);

// Resolve init: .../private/common → up 1 to /private → /assets/initialize.php
$init = dirname(__DIR__) . '/assets/initialize.php';
if (!is_file($init)) {
  $alt = dirname(__DIR__, 2) . '/private/assets/initialize.php';
  if (is_file($alt)) { $init = $alt; }
}
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

// Required inputs
if (empty($subject_slug)) { die('staff_subject_hub: $subject_slug is required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', (string)$subject_slug)); }

// Helper: banner/url with graceful fallback
if (!function_exists('banner_url_for_subject')) {
  function banner_url_for_subject(string $slug): string {
    $base = "/lib/images/banners/{$slug}";
    if (is_file(PUBLIC_PATH . "{$base}.webp")) return url_for("{$base}.webp");
    if (is_file(PUBLIC_PATH . "{$base}.jpg"))  return url_for("{$base}.jpg");
    return url_for("/lib/images/subjects/{$slug}.svg"); // fallback
  }
}

// Page context
$page_title = "Staff • {$subject_name}";
$active_nav = 'staff';
$body_class = "role--staff subject--{$subject_slug}";
$subject_logo = $subject_logo ?? "/lib/images/subjects/{$subject_slug}.svg";
$page_logo = $subject_logo;

$breadcrumbs = [
  ['label'=>'Home',     'url'=>'/'],
  ['label'=>'Staff',    'url'=>'/staff/'],
  ['label'=>'Subjects', 'url'=>'/staff/subjects/'],
  ['label'=>$subject_name]
];

if (!isset($stylesheets) || !is_array($stylesheets)) { $stylesheets = []; }
$stylesheets[] = '/lib/css/ui.css';

require_once PRIVATE_PATH . '/shared/header.php';

// Optional banner
$banner = banner_url_for_subject($subject_slug);
?>
<main id="main" class="container" style="max-width:1000px;margin:2rem auto;padding:0 1rem;">
  <div class="banner-wrap">
    <img class="banner" src="<?= h($banner) ?>" alt="" loading="lazy" style="width:100%;height:auto;border-radius:12px;margin:0 0 16px;">
  </div>

  <header style="display:flex;align-items:center;gap:12px;margin-bottom:1rem;">
    <img src="<?= h(url_for($subject_logo)) ?>" alt="<?= h($subject_name) ?>" width="48" height="48">
    <h1 style="margin:0;"><?= h($subject_name) ?> (Staff)</h1>
  </header>

  <div class="grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
    <a class="card subject-halo" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">
      <h3 style="margin:0 0 .25rem;">View Pages</h3>
      <p class="muted" style="margin:0;">List & manage pages</p>
    </a>
    <a class="card subject-halo" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/new.php")) ?>">
      <h3 style="margin:0 0 .25rem;">Add New Page</h3>
      <p class="muted" style="margin:0;">Create a new page</p>
    </a>
    <a class="card subject-halo" href="<?= h(url_for("/staff/subjects/{$subject_slug}/media/")) ?>">
      <h3 style="margin:0 0 .25rem;">Manage Media</h3>
      <p class="muted" style="margin:0;">Upload images & files</p>
    </a>
    <a class="card subject-halo" href="<?= h(url_for("/staff/subjects/{$subject_slug}/settings.php")) ?>">
      <h3 style="margin:0 0 .25rem;">Subject Settings</h3>
      <p class="muted" style="margin:0;">Meta, visibility, order</p>
    </a>
  </div>

  <p style="margin-top:1rem;">
    <a class="btn" href="<?= h(url_for('/staff/subjects/')) ?>">&larr; Back to Subjects</a>
  </p>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
