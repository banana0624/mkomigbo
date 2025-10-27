<?php
// project-root/public/staff/contributors/credits/edit.php
declare(strict_types=1);

// Boot
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

// Permissions (optional)
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['contributors.credits.edit']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

$id  = (string)($_GET['id'] ?? $_POST['id'] ?? '');
$row = ($id !== '') && function_exists('credit_find') ? credit_find($id) : null;
if (!$row) { http_response_code(404); die('Credit not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) csrf_check();

  $subject = trim((string)($_POST['subject'] ?? ''));
  $points  = (int)($_POST['points'] ?? 0);
  $note    = trim((string)($_POST['note'] ?? ''));

  if ($subject !== '') {
    $ok = function_exists('credit_update') ? credit_update($id, compact('subject','points','note')) : false;
    if (function_exists('flash')) {
      flash($ok ? 'success' : 'error', $ok ? 'Credit updated.' : 'Update failed.');
    }
    header('Location: ' . url_for('/staff/contributors/credits/')); exit;
  } else {
    if (function_exists('flash')) flash('error', 'Subject is required.');
  }
}

$page_title    = 'Edit Credit';
$active_nav    = 'contributors';
$body_class    = 'role--staff role--contrib';
$page_logo     = '/lib/images/icons/messages.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Credits','url'=>'/staff/contributors/credits/'],
  ['label'=>'Edit'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Edit Credit</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post" action="">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="id" value="<?= h($id) ?>">

    <div class="field">
      <label>Subject</label>
      <input class="input" type="text" name="subject" value="<?= h($row['subject'] ?? '') ?>" required>
    </div>

    <div class="field">
      <label>Points</label>
      <input class="input" type="number" step="1" name="points" value="<?= (int)($row['points'] ?? 0) ?>">
    </div>

    <div class="field">
      <label>Note</label>
      <textarea class="input" name="note" rows="5"><?= h($row['note'] ?? '') ?></textarea>
    </div>

    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/credits/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
