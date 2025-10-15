<?php
// project-root/private/common/staff_subject_settings.php
declare(strict_types=1);

$init = dirname(__DIR__) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('settings.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$page_title   = "Settings • {$subject_name}";
$active_nav   = 'staff';
$body_class   = "role--staff subject--{$subject_slug}";
$page_logo    = "/lib/images/subjects/{$subject_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Settings'],
];

/* === Handle POST (Save settings or Toggle visibility) === */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['_action'] ?? 'save';

  if ($action === 'toggle') {
    $new = subject_toggle_visibility($subject_slug);
    if ($new === null) {
      flash('error', 'Subject not found.');
    } else {
      flash('success', $new ? 'Subject is now Public.' : 'Subject is now Hidden.');
    }
    header('Location: ' . url_for("/staff/subjects/{$subject_slug}/settings.php"));
    exit;
  }

  // Default action: save fields
  $ok = subject_update_settings($subject_slug, $_POST);
  if ($ok) {
    flash('success', 'Settings saved.');
  } else {
    flash('error', 'Save failed.');
  }
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/settings.php"));
  exit;
}

/* Load current values for display */
$subj = subject_find($subject_slug);
$is_public       = (int)($subj['is_public'] ?? 1);
$nav_order       = (int)($subj['nav_order'] ?? 0);
$meta_description= (string)($subj['meta_description'] ?? '');

require_once PRIVATE_PATH . '/shared/header.php';
?>
<main id="main" class="container" style="max-width:800px;margin:1.25rem auto;padding:0 1rem;">
  <header style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;">
    <h1 style="margin:0;">Settings — <?= h($subject_name) ?></h1>
    <form method="post" action="<?= h(url_for("/staff/subjects/{$subject_slug}/settings.php")) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="_action" value="toggle">
      <button class="btn" type="submit">
        <?= $is_public ? 'Make Hidden' : 'Make Public' ?>
      </button>
    </form>
  </header>

  <form method="post" action="<?= h(url_for("/staff/subjects/{$subject_slug}/settings.php")) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="_action" value="save">

    <div class="field">
      <label>Public</label>
      <label style="display:inline-flex;align-items:center;gap:.5rem;">
        <input type="checkbox" name="is_public" value="1" <?= $is_public ? 'checked' : '' ?>> Visible
      </label>
    </div>

    <div class="field">
      <label>Navigation Order</label>
      <input class="input" type="number" name="nav_order" min="0" step="1" value="<?= (int)$nav_order ?>">
      <div class="muted" style="font-size:.85rem;">Lower numbers appear first on the staff subjects hub.</div>
    </div>

    <div class="field">
      <label>Meta Description</label>
      <textarea class="input" name="meta_description" rows="3"><?= h($meta_description) ?></textarea>
    </div>

    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/")) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
