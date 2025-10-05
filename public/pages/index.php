<?php
// project-root/public/pages/index.php
require_once('../../private/assets/initialize.php');

$slug = $_GET['slug'] ?? '';
if($slug === '') { redirect_to(url_for('/index.php')); }

$page = find_page_by_slug($slug);
if(!$page) {
  $page_title = 'Page Not Found';
  include_once(dirname(__DIR__, 2) . '/private/shared/public_header.php');
  echo "<h2>Page not found.</h2>";
  include_once(dirname(__DIR__, 2) . '/private/shared/public_footer.php');
  exit;
}

$subject = find_subject_by_id($page['subject_id']);

$page_title       = h($page['title']);
$page_description = h($page['meta_description'] ?? '');
$page_keywords    = h($page['meta_keywords'] ?? '');

include_once(dirname(__DIR__, 2) . '/private/shared/public_header.php');
?>

<?php if($subject) { ?>
<nav class="breadcrumbs">
  <a href="<?php echo url_for('/'); ?>">Home</a>
  <span class="crumb-sep">›</span>
  <a href="<?php echo url_for('/subjects/' . h($subject['slug']) . '/'); ?>">
    <?php echo h($subject['name']); ?>
  </a>
  <span class="crumb-sep">›</span>
  <span class="crumb-current"><?php echo h($page['title']); ?></span>
</nav>
<?php } ?>

<div id="page-content">
  <h2><?php echo h($page['title']); ?></h2>
  <div class="page-body">
    <?php echo $page['content']; ?>
  </div>

  <?php if($subject) { ?>
    <p class="back-link">
      <a href="<?php echo url_for('/subjects/' . h($subject['slug']) . '/'); ?>">
        ← Back to <?php echo h($subject['name']); ?>
      </a>
    </p>
  <?php } ?>
</div>

<?php include_once(dirname(__DIR__, 2) . '/private/shared/public_footer.php'); ?>
