<?php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;
require_once PRIVATE_PATH . '/common/platforms/platform_common.php';
$platform_slug='tags'; $platform_name='Tags';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $title=trim((string)($_POST['title'] ?? '')); $slug=trim((string)($_POST['slug'] ?? '')); $body=(string)($_POST['body'] ?? '');
  if ($title !== '' && $slug !== '') {
    $items = platform_items_load($platform_slug);
    $items[] = ['title'=>$title,'slug'=>$slug,'body'=>$body,'created_at'=>date('Y-m-d H:i:s')];
    platform_items_save($platform_slug, $items);
    flash('success','Item created.');
    header('Location: ' . url_for("/staff/platforms/{$platform_slug}/items/")); exit;
  } else { flash('error','Title and Slug are required.'); }
}
$page_title="{$platform_name} • Create"; $active_nav='staff'; $body_class="role--staff platform--{$platform_slug}";
$page_logo="/lib/images/platforms/{$platform_slug}.svg"; $stylesheets[]='/lib/css/ui.css';
$breadcrumbs=[['label'=>'Home','url'=>'/'],['label'=>'Staff','url'=>'/staff/'],['label'=>'Platforms','url'=>'/staff/platforms/'],['label'=>$platform_name,'url'=>"/staff/platforms/{$platform_slug}/"],['label'=>'Create']];
require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Create — <?= h($platform_name) ?></h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Title</label><input class="input" type="text" name="title" required></div>
    <div class="field"><label>Slug</label><input class="input" type="text" name="slug" required></div>
    <div class="field"><label>Body</label><textarea class="input" name="body" rows="6"></textarea></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for("/staff/platforms/{$platform_slug}/")) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
