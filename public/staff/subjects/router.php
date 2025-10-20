<?php
// project-root/public/staff/subjects/router.php
declare(strict_types=1);

/**
 * This file powers PUBLIC /subjects/* URLs via .htaccess rewrites,
 * but will automatically redirect logged-in staff/admins to the STAFF UI
 * so they can CRUD. Public visitors only see published content.
 *
 * .htaccess (already set):
 *   RewriteRule ^subjects/?$ staff/subjects/router.php [L]
 *   RewriteRule ^subjects/(.+)$ staff/subjects/router.php?path=$1 [L]
 */

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); exit('Init not found: '.$init); }
require_once $init;

// Use the canonical subject helpers (do NOT re-declare in this file)
require_once PRIVATE_PATH . '/registry/subjects_runtime.php';

// Parse path: "", "slug", or "slug/page-slug"
$raw = (string)($_GET['path'] ?? '');
$path = trim($raw, "/ \t\r\n");
$parts = $path === '' ? [] : explode('/', $path, 3);
$subjectSlug = $parts[0] ?? '';
$pageSlug    = $parts[1] ?? '';

// --- 1) Staff redirect: if signed in AND allowed to view pages, push to staff UI
if (function_exists('current_user') && current_user()
    && function_exists('auth_has_permission') && auth_has_permission('pages.view')) {

  // A) /subjects -> staff subjects index
  if ($subjectSlug === '') {
    header('Location: ' . url_for('/staff/subjects/')); exit;
  }

  // B) /subjects/<subject>[/...]
  if ($subjectSlug !== '') {
    // If a specific page was requested, send to staff subject hub (where CRUD lives).
    // You can choose to deep-link to show/edit if you want, but hub is safest default.
    header('Location: ' . url_for('/staff/subjects/' . rawurlencode($subjectSlug) . '/')); exit;
  }
}

// --- 2) Public view (visitors): show catalog / subject landing / page — published only

// Helpers: fetch published pages for subject and a single published page by slug.
// (Use your project helpers if available.)
$fnList   = function_exists('public_pages_by_subject') ? 'public_pages_by_subject' : (function_exists('find_published_pages_by_subject_slug') ? 'find_published_pages_by_subject_slug' : null);
$fnSingle = function_exists('public_page_for_subject') ? 'public_page_for_subject' : (function_exists('find_published_page_by_slug') ? 'find_published_page_by_slug' : null);

// Shared header setup
$active_nav    = 'subjects';
$body_class    = 'role--public';
$stylesheets[] = '/lib/css/ui.css'; // keep your site UI styles

// 2A) /subjects  → list all subjects (from registry/DB)
if ($subjectSlug === '') {
  $subjects = subjects_load_complete(); // from subjects_runtime.php (registry + DB overlay)
  $page_title = 'Subjects';
  $breadcrumbs = [
    ['label'=>'Home','url'=>'/'],
    ['label'=>'Subjects'],
  ];
  // SEO (optional)
  if (function_exists('seo_build_meta')) {
    $meta = seo_build_meta([
      'title'       => 'Subjects • ' . ($_ENV['SITE_NAME'] ?? 'Mkomigbo'),
      'description' => 'Browse published subjects and pages.',
      'canonical'   => '/subjects/',
      'og' => ['type' => 'website'],
    ]);
  }

  require PRIVATE_PATH . '/shared/header.php';
  ?>
  <main class="container" style="max-width:1100px;padding:1.25rem 0">
    <h1>Subjects</h1>
    <ul class="home-links"
        style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;list-style:none;padding:0;margin:0;">
      <?php foreach ($subjects as $s): ?>
        <?php
          $slug = (string)$s['slug'];
          $name = (string)$s['name'];
          $href = url_for('/subjects/' . $slug . '/');
        ?>
        <li>
          <a class="card" href="<?= h($href) ?>"
             style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;">
            <strong><?= h($name) ?></strong><br><span class="muted">View pages</span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </main>
  <?php
  require PRIVATE_PATH . '/shared/footer.php';
  exit;
}

// 2B) /subjects/<subject>  → subject landing (published pages list)
if (!subject_exists($subjectSlug)) {
  http_response_code(404);
  require PRIVATE_PATH . '/common/ui/404.php';
  exit;
}

$subjectName = subject_human_name($subjectSlug);
$subjectLogo = subject_logo_webpath($subjectSlug);
if (!$subjectLogo) $subjectLogo = '/lib/images/logo/mk-logo.png';

if ($pageSlug === '') {
  // List published pages for this subject
  $published = [];
  if ($fnList) { $published = (array)call_user_func($fnList, $subjectSlug); }

  $page_title = $subjectName;
  $breadcrumbs = [
    ['label'=>'Home','url'=>'/'],
    ['label'=>'Subjects','url'=>'/subjects/'],
    ['label'=>$subjectName],
  ];
  if (function_exists('seo_build_meta')) {
    $meta = seo_build_meta([
      'title'       => "{$subjectName} • " . ($_ENV['SITE_NAME'] ?? 'Mkomigbo'),
      'description' => "Published pages for {$subjectName}.",
      'canonical'   => "/subjects/{$subjectSlug}/",
      'og' => ['type' => 'website', 'image' => auto_og_image_for_subject($subjectSlug, $subjectName) ?? ''],
    ]);
  }

  require PRIVATE_PATH . '/shared/header.php';
  ?>
  <main class="container" style="max-width:900px;padding:1.25rem 0">
    <header style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem">
      <img src="<?= h(asset_url($subjectLogo)) ?>" alt="<?= h($subjectName) ?>" width="48" height="48">
      <h1 style="margin:0"><?= h($subjectName) ?></h1>
    </header>

    <?php if (empty($published)): ?>
      <p class="muted">No published pages yet.</p>
    <?php else: ?>
      <ul style="list-style:none;padding:0;margin:0;display:grid;gap:.5rem">
        <?php foreach ($published as $p): ?>
          <?php
            $ptitle = (string)($p['title'] ?? '');
            $pslug  = (string)($p['slug']  ?? '');
            $href   = url_for("/subjects/{$subjectSlug}/{$pslug}");
          ?>
          <li>
            <a class="card" href="<?= h($href) ?>"
               style="display:block;padding:12px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;text-decoration:none;">
              <strong><?= h($ptitle) ?></strong>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
  <?php
  require PRIVATE_PATH . '/shared/footer.php';
  exit;
}

// 2C) /subjects/<subject>/<page>  → single page (published only)
$record = [];
if ($fnSingle) { $record = (array)call_user_func($fnSingle, $subjectSlug, $pageSlug); }
if (!$record || empty($record['is_published'])) {
  http_response_code(404);
  require PRIVATE_PATH . '/common/ui/404.php';
  exit;
}

$title = (string)($record['title'] ?? '');
$body  = (string)($record['body'] ?? '');

$page_title = $title !== '' ? $title : $subjectName;
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Subjects','url'=>'/subjects/'],
  ['label'=>$subjectName,'url'=>"/subjects/{$subjectSlug}/"],
  ['label'=>$title ?: 'Page'],
];
if (function_exists('seo_build_meta')) {
  $meta = seo_build_meta([
    'title'       => "{$title} • {$subjectName} • " . ($_ENV['SITE_NAME'] ?? 'Mkomigbo'),
    'description' => trim((string)($record['meta_description'] ?? '')),
    'keywords'    => trim((string)($record['meta_keywords'] ?? '')),
    'canonical'   => "/subjects/{$subjectSlug}/{$pageSlug}",
    'og'          => [
      'type'  => 'article',
      'image' => auto_og_image_for_page($subjectSlug, $title) ?? '',
    ],
  ]);
}

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:900px;padding:1.25rem 0">
  <header style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem">
    <img src="<?= h(asset_url($subjectLogo)) ?>" alt="<?= h($subjectName) ?>" width="36" height="36">
    <h1 style="margin:0"><?= h($title ?: $subjectName) ?></h1>
  </header>

  <article class="prose">
    <?= $body /* stored HTML; trusted from staff CMS */ ?>
  </article>

  <p style="margin-top:1rem;">
    <a class="btn" href="<?= h(url_for("/subjects/{$subjectSlug}/")) ?>">&larr; Back to <?= h($subjectName) ?></a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php';
