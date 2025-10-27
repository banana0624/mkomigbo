<?php
// project-root/public/staff/contributors/router.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); die('Init not found: ' . $init); }
require_once $init;

// path parsing
$raw = (string)($_GET['path'] ?? '');
$raw = trim($raw, "/ \t\n\r\0\x0B");
$segments = $raw === '' ? [] : explode('/', $raw, 4);

if (!function_exists('h')) {
  function h(string $s=''): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

// Logo (generic contributors)
$page_logo = '/lib/images/icons/users.svg';
if (!is_file(rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR).'/lib/images/icons/users.svg')) {
  $page_logo = '/lib/images/logo/mk-logo.png';
}

/* ===== /contributors ===== */
if (count($segments) === 0) {
  $list = function_exists('contrib_all') ? (array)contrib_all() : [];

  $page_title    = 'Contributors';
  $active_nav    = 'contributors';
  $body_class    = 'route--contributors';
  $stylesheets[] = '/lib/css/ui.css';
  $breadcrumbs   = [['label'=>'Home','url'=>'/'], ['label'=>'Contributors']];

  $meta = seo_build_meta([
    'title'       => 'Contributors',
    'description' => 'Directory of contributors.',
    'canonical'   => '/contributors/',
    'og'          => ['type'=>'website', 'image'=>asset_url($page_logo)],
  ]);

  require PRIVATE_PATH . '/shared/header.php'; ?>
  <main class="container" style="padding:1rem 0">
    <h1>Contributors</h1>
    <?php if (!$list): ?>
      <p class="muted">No contributors listed yet.</p>
    <?php else: ?>
      <ul class="home-links"
          style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;list-style:none;padding:0;margin:0;">
        <?php foreach ($list as $c):
          $slug = (string)($c['slug'] ?? '');
          $name = (string)($c['name'] ?? $slug);
          if ($slug==='') continue; ?>
          <li>
            <a class="card" href="<?= h(url_for('/contributors/'.$slug.'/')) ?>"
               style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;">
              <strong><?= h($name) ?></strong><br>
              <span class="muted"><?= h((string)($c['role'] ?? 'Contributor')) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <div style="margin-top:1rem;display:flex;gap:.5rem;flex-wrap:wrap;">
      <a class="btn" href="<?= h(url_for('/staff/contributors/reviews/')) ?>">Reviews</a>
      <a class="btn" href="<?= h(url_for('/staff/contributors/credits/')) ?>">Credits</a>
    </div>
  </main>
  <?php require PRIVATE_PATH . '/shared/footer.php'; exit;
}

/* ===== /contributors/reviews ===== */
if ($segments[0] === 'reviews') {
  $reviews = function_exists('review_all') ? (array)review_all() : [];

  $page_title    = 'Contributor Reviews';
  $active_nav    = 'contributors';
  $body_class    = 'route--contributors-reviews';
  $stylesheets[] = '/lib/css/ui.css';
  $breadcrumbs   = [['label'=>'Home','url'=>'/'], ['label'=>'Contributors','url'=>'/contributors/'], ['label'=>'Reviews']];

  $meta = seo_build_meta([
    'title'       => 'Contributor Reviews',
    'description' => 'Reviews by or about contributors.',
    'canonical'   => '/contributors/reviews/',
    'og'          => ['type'=>'article', 'image'=>asset_url($page_logo)],
  ]);

  require PRIVATE_PATH . '/shared/header.php'; ?>
  <main class="container" style="padding:1rem 0;max-width:960px">
    <h1>Reviews</h1>
    <?php if (!$reviews): ?>
      <p class="muted">No reviews yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Title</th><th>Contributor</th></tr></thead>
          <tbody>
          <?php foreach ($reviews as $r): ?>
            <tr>
              <td><?= h((string)($r['title'] ?? 'Untitled')) ?></td>
              <td class="muted"><?= h((string)($r['contributor'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <p style="margin-top:1rem"><a class="btn" href="<?= h(url_for('/contributors/')) ?>">&larr; Back to Contributors</a></p>
  </main>
  <?php require PRIVATE_PATH . '/shared/footer.php'; exit;
}

/* ===== /contributors/credits ===== */
if ($segments[0] === 'credits') {
  $credits = function_exists('credit_all') ? (array)credit_all() : [];

  $page_title    = 'Contributor Credits';
  $active_nav    = 'contributors';
  $body_class    = 'route--contributors-credits';
  $stylesheets[] = '/lib/css/ui.css';
  $breadcrumbs   = [['label'=>'Home','url'=>'/'], ['label'=>'Contributors','url'=>'/contributors/'], ['label'=>'Credits']];

  $meta = seo_build_meta([
    'title'       => 'Contributor Credits',
    'description' => 'Credits and acknowledgements.',
    'canonical'   => '/contributors/credits/',
    'og'          => ['type'=>'article', 'image'=>asset_url($page_logo)],
  ]);

  require PRIVATE_PATH . '/shared/header.php'; ?>
  <main class="container" style="padding:1rem 0;max-width:960px">
    <h1>Credits</h1>
    <?php if (!$credits): ?>
      <p class="muted">No credits yet.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($credits as $c): ?>
          <li><?= h((string)($c['text'] ?? '')) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <p style="margin-top:1rem"><a class="btn" href="<?= h(url_for('/contributors/')) ?>">&larr; Back to Contributors</a></p>
  </main>
  <?php require PRIVATE_PATH . '/shared/footer.php'; exit;
}

/* ===== /contributors/{slug} ===== */
$slug = $segments[0];
if ($slug === '' || $slug === 'reviews' || $slug === 'credits') { http_response_code(404); echo 'Not Found'; exit; }

$all = function_exists('contrib_all') ? (array)contrib_all() : [];
$one = null;
foreach ($all as $c) {
  if ((string)($c['slug'] ?? '') === $slug) { $one = $c; break; }
}

$page_title    = ($one['name'] ?? 'Contributor') . ' • Contributors';
$active_nav    = 'contributors';
$body_class    = 'route--contributors-one';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs   = [['label'=>'Home','url'=>'/'], ['label'=>'Contributors','url'=>'/contributors/'], ['label'=>$one['name'] ?? 'Contributor']];

$meta = seo_build_meta([
  'title'       => $one ? ($one['name'].' • Contributors') : 'Contributor',
  'description' => $one ? (string)($one['bio'] ?? 'Contributor profile') : 'Contributor profile',
  'canonical'   => "/contributors/{$slug}/",
  'og'          => ['type'=>'profile', 'image'=>asset_url($page_logo)],
]);

$meta = seo_with_auto_og($meta, $subjectSlug, $pageSlug /* or null */);

require PRIVATE_PATH . '/shared/header.php'; ?>
<main class="container" style="padding:1rem 0;max-width:880px">
  <?php if (!$one): ?>
    <h1>Not Found</h1>
    <p class="muted">That contributor was not found.</p>
  <?php else: ?>
    <h1><?= h((string)$one['name']) ?></h1>
    <?php if (!empty($one['bio'])): ?>
      <p class="muted"><?= h((string)$one['bio']) ?></p>
    <?php endif; ?>
    <dl>
      <dt>Role</dt><dd><?= h((string)($one['role'] ?? 'Contributor')) ?></dd>
      <?php if (!empty($one['links']) && is_array($one['links'])): ?>
        <dt>Links</dt>
        <dd>
          <?php foreach ($one['links'] as $label => $url): ?>
            <div><a href="<?= h((string)$url) ?>" target="_blank" rel="noopener"><?= h((string)$label) ?></a></div>
          <?php endforeach; ?>
        </dd>
      <?php endif; ?>
    </dl>
    <p style="margin-top:1rem"><a class="btn" href="<?= h(url_for('/contributors/')) ?>">&larr; Back to Contributors</a></p>
  <?php endif; ?>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; exit;


