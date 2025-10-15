<?php
// project-root/public/subjects/index.php

declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title    = 'Subjects';
$active_nav    = null; // public nav, if you have one
$body_class    = 'hub--subjects public';
$page_logo     = '/lib/images/logo/subjects-hub.png'; // optional
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Subjects'],
];

require_once PRIVATE_PATH . '/shared/header.php';

/** Load only public subjects from DB (fallback to all if helper missing) */
if (!function_exists('subjects_public')) {
  function subjects_public(): array {
    global $db;
    if (!isset($db)) return [];
    $sql = "SELECT slug, name, COALESCE(meta_description,'') AS meta_description,
                   COALESCE(nav_order,0) AS nav_order,
                   COALESCE(is_public,1) AS is_public
            FROM subjects
            WHERE COALESCE(is_public,1) = 1
            ORDER BY nav_order ASC, name ASC";
    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}
$subjects = subjects_public();
?>
<main class="container" style="padding:1.25rem 0">
  <header class="hero" style="margin-bottom:1rem;">
    <h1 style="margin:0 0 .25rem 0;">Subjects</h1>
    <p class="muted" style="margin:0">Browse the subject hubs.</p>
  </header>

  <?php if (!$subjects): ?>
    <p class="muted">No public subjects yet.</p>
  <?php else: ?>
    <div class="card-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
      <?php foreach ($subjects as $s):
        $slug = (string)$s['slug'];
        $name = (string)$s['name'];
        $desc = (string)$s['meta_description'];
        $href = url_for("/subjects/{$slug}/");
        $icon = url_for("/lib/images/subjects/{$slug}.svg");
      ?>
      <a class="card subject-card subject--<?= h($slug) ?>" href="<?= h($href) ?>"
         style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;text-decoration:none;background:#fff;">
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <img src="<?= h($icon) ?>" alt="" width="32" height="32" style="flex:0 0 auto;">
          <div style="min-width:0;">
            <div style="font-weight:600;"><?= h($name) ?></div>
            <?php if ($desc !== ''): ?>
              <div class="muted" style="font-size:.85rem;"><?= h($desc) ?></div>
            <?php endif; ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
