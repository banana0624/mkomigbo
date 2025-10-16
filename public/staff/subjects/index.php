<?php
// project-root/public/staff/subjects/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();     // for staff-only areas
// require_admin();  // use this on admins-only pages

$page_title    = 'Subjects';
$active_nav    = 'staff';
$body_class    = 'role--staff hub--subjects';
$page_logo     = '/lib/images/logo/subjects-hub.png'; // optional hub logo
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects'],
];

require_once PRIVATE_PATH . '/shared/staff_header.php';

/** Load subjects from DB (helper). Fallback to empty array if helper missing. */
$subjects = function_exists('subjects_all') ? subjects_all() : [];
?>
<main class="container" style="padding:1.25rem 0">
  <header class="hero" style="margin-bottom:1rem;">
    <h1 style="margin:0 0 .25rem 0;">Subjects</h1>
    <p class="muted" style="margin:0">Choose a subject hub to manage pages, media, and settings.</p>
  </header>

  <?php if (!$subjects): ?>
    <p class="muted">No subjects found. Did you run the seed migration?</p>
    <p><code>php bin/migrate.php</code></p>
  <?php else: ?>
    <div class="card-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
      <?php foreach ($subjects as $s):
        $slug = (string)$s['slug'];
        $name = (string)$s['name'];
        $is_public = (int)$s['is_public'];
        $nav_order = (int)$s['nav_order'];
        $href = url_for("/staff/subjects/{$slug}/");
        $icon = url_for("/lib/images/subjects/{$slug}.svg");
      ?>
      <a class="card subject-card subject--<?= h($slug) ?>" href="<?= h($href) ?>"
         style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;text-decoration:none;background:#fff;">
        <div style="display:flex;gap:10px;align-items:center;">
          <img src="<?= h($icon) ?>" alt="" width="32" height="32" style="flex:0 0 auto;">
          <div style="min-width:0;">
            <div style="font-weight:600;"><?= h($name) ?></div>
            <div class="muted" style="font-size:.85rem;">
              <?= $is_public ? 'Public' : 'Hidden' ?> · Order: <?= (int)$nav_order ?>
            </div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
