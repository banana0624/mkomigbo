<?php
// project-root/private/common/staff_subject_media/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('media/index.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$page_title  = "Media • {$subject_name}";
$active_nav  = 'staff';
$body_class  = "role--staff subject--{$subject_slug}";
$page_logo   = "/lib/images/subjects/{$subject_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Media'],
];

require_once PRIVATE_PATH . '/shared/header.php';

/* ---------- Media listing helpers ---------- */
$uploadsDir  = PUBLIC_PATH . "/lib/uploads/{$subject_slug}";
$uploadsUrl  = url_for("/lib/uploads/{$subject_slug}");
if (!is_dir($uploadsDir)) { @mkdir($uploadsDir, 0775, true); }

/** quick helpers */
$IMAGE_EXTS = ['jpg','jpeg','png','gif','webp','svg','avif'];
$DOC_EXTS   = ['pdf','doc','docx','ppt','pptx','xls','xlsx','txt','csv','zip','rar','7z','mp3','wav','mp4','mov','webm'];
$is_image = function(string $f) use ($IMAGE_EXTS): bool {
  $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
  return in_array($ext, $IMAGE_EXTS, true);
};
$human = function(int $bytes): string {
  if ($bytes < 1024) return $bytes . ' B';
  $kb = $bytes/1024; if ($kb < 1024) return number_format($kb,1) . ' KB';
  $mb = $kb/1024;    if ($mb < 1024) return number_format($mb,1) . ' MB';
  $gb = $mb/1024;    return number_format($gb,2) . ' GB';
};

/** scan dir */
$files = [];
if (is_dir($uploadsDir)) {
  $it = new DirectoryIterator($uploadsDir);
  foreach ($it as $fi) {
    if ($fi->isDot() || !$fi->isFile()) continue;
    $name = $fi->getFilename();
    $fs   = $fi->getSize();
    $mtime= $fi->getMTime();
    $files[] = [
      'name'=>$name,
      'size'=>$fs,
      'mtime'=>$mtime,
      'url'=> $uploadsUrl . '/' . rawurlencode($name),
      'is_image'=>$is_image($name),
    ];
  }
}
/** newest first */
usort($files, fn($a,$b) => $b['mtime'] <=> $a['mtime']);
?>
<main id="main" class="container" style="max-width:1100px;margin:1.25rem auto;padding:0 1rem;">

  <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
    <h1 style="margin:0;">Media — <?= h($subject_name) ?></h1>
    <div class="actions" style="display:flex;gap:.5rem;">
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/media/upload.php")) ?>">Upload New</a>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/")) ?>">&larr; Back to Hub</a>
    </div>
  </header>

  <?php if (empty($files)): ?>
    <p class="muted">No media yet for <strong><?= h($subject_name) ?></strong>. Upload something to get started.</p>
    <ul>
      <li>Banner path (optional): <code>/lib/images/banners/<?= h($subject_slug) ?>.jpg</code> (or <code>.webp</code>)</li>
      <li>Icon path: <code>/lib/images/subjects/<?= h($subject_slug) ?>.svg</code></li>
      <li>Uploads folder (auto-created): <code>/lib/uploads/<?= h($subject_slug) ?>/</code></li>
    </ul>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th style="width:96px">Preview</th>
            <th>Name</th>
            <th style="width:140px">Size</th>
            <th style="width:180px">Modified</th>
            <th class="actions" style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($files as $f): ?>
          <tr>
            <td>
              <?php if ($f['is_image']): ?>
                <img src="<?= h($f['url']) ?>" alt="" loading="lazy"
                     style="display:block;width:96px;height:60px;object-fit:cover;border-radius:8px;">
              <?php else: ?>
                <div class="muted" style="width:96px;height:60px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;border-radius:8px;">
                  <span><?= h(strtoupper(pathinfo($f['name'], PATHINFO_EXTENSION))) ?></span>
                </div>
              <?php endif; ?>
            </td>
            <td><a href="<?= h($f['url']) ?>" target="_blank" rel="noopener"><?= h($f['name']) ?></a></td>
            <td><?= h($human((int)$f['size'])) ?></td>
            <td><?= h(date('Y-m-d H:i', (int)$f['mtime'])) ?></td>
            <td class="actions">
              <a class="btn btn-sm" href="<?= h($f['url']) ?>" target="_blank" rel="noopener">Open</a>
              <form method="post" action="<?= h(url_for("/staff/subjects/{$subject_slug}/media/delete.php")) ?><?= csrf_field() ?>"
                    style="display:inline" onsubmit="return confirm('Delete this file?');">
                <input type="hidden" name="name" value="<?= h($f['name']) ?>">
                <button class="btn btn-sm btn-danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
