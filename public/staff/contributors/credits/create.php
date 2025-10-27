<?php
// project-root/public/staff/contributors/credits/create.php
declare(strict_types=1);

// Boot
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

// Permissions (optional)
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['contributors.credits.create']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) csrf_check();

  $subject = trim((string)($_POST['subject'] ?? ''));
  $points  = (int)($_POST['points'] ?? 0);
  $note    = trim((string)($_POST['note'] ?? ''));

  if ($subject !== '') {
    if (function_exists('credit_add')) {
      credit_add(compact('subject','points','note'));
      if (function_exists('flash')) flash('success', 'Credit added.');
      header('Location: ' . url_for('/staff/contributors/credits/')); exit;
    }
  }
  if (function_exists('flash')) flash('error', 'Subject is required.');
}

$page_title    = 'New Credit';
$active_nav    = 'contributors';
$body_class    = 'role--staff role--contrib';
$page_logo     = '/lib/images/icons/messages.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Credits','url'=>'/staff/contributors/credits/'],
  ['label'=>'Create'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>New Credit</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post" action="">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>

    <div class="field">
      <label>Subject (name or ID)</label>
      <input class="input" type="text" name="subject" value="<?= h((string)($_POST['subject'] ?? '')) ?>" required>
    </div>

    <div class="field">
      <label>Points</label>
      <input class="input" type="number" step="1" name="points" value="<?= (int)($_POST['points'] ?? 0) ?>">
    </div>

    <div class="field">
      <label>Note</label>
      <textarea class="input" name="note" rows="5"><?= h((string)($_POST['note'] ?? '')) ?></textarea>
    </div>

    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/credits/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
