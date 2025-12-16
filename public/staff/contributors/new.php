<?php
declare(strict_types=1);

/**
 * project-root/public/staff/contributors/new.php
 * Staff: create a new contributor.
 */

/* 1) Bootstrap */
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: " . htmlspecialchars($init, ENT_QUOTES, 'UTF-8') . "</p>";
  exit;
}
require_once $init;

/* 2) Auth guard */
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

/* 3) Ensure contributor functions are loaded */
$contribFns = PRIVATE_PATH . '/functions/contributor_functions.php';
if (is_file($contribFns)) {
  require_once $contribFns;
}

$errors = [];
$values = [
  'display_name'     => '',
  'email'            => '',
  'status'           => 1,
  'slug'             => '',
  'bio_html'         => '',
  'avatar_path'      => '',
  'roles'            => '',
  'primary_subjects' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $values['display_name']     = $_POST['display_name']     ?? '';
  $values['email']            = $_POST['email']            ?? '';
  $values['status']           = $_POST['status']           ?? 1;
  $values['slug']             = $_POST['slug']             ?? '';
  $values['bio_html']         = $_POST['bio_html']         ?? '';
  $values['avatar_path']      = $_POST['avatar_path']      ?? '';
  $values['roles']            = $_POST['roles']            ?? '';
  $values['primary_subjects'] = $_POST['primary_subjects'] ?? '';

  if (function_exists('contributors_insert')) {
    [$success, $errors, $new_id] = contributors_insert($values);
    if ($success && empty($errors)) {
      header('Location: ' . url_for('/staff/contributors/index.php'));
      exit;
    }
  } else {
    $errors[] = 'contributors_insert() not available.';
  }
}

$page_title = 'New contributor';
$body_class = 'staff-body';
$active_nav = 'contributors';

include PRIVATE_PATH . '/shared/staff_header.php';
?>

<main class="staff-main mk-container">
  <header class="staff-page-header">
    <h1><?= h($page_title) ?></h1>
    <p>Create a new contributor profile for articles and content.</p>
  </header>

  <?php if (!empty($errors)): ?>
    <div class="form-errors">
      <p>Please fix the following:</p>
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= h($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="<?= h(url_for('/staff/contributors/new.php')) ?>" method="post" class="staff-form">
    <div class="form-row">
      <label for="display_name">Display name</label>
      <input type="text" name="display_name" id="display_name"
             value="<?= h($values['display_name']) ?>" required>
    </div>

    <div class="form-row">
      <label for="email">Email</label>
      <input type="email" name="email" id="email"
             value="<?= h($values['email']) ?>" required>
    </div>

    <div class="form-row">
      <label for="status">Status</label>
      <select name="status" id="status">
        <option value="1" <?= (int)$values['status'] === 1 ? 'selected' : '' ?>>Active</option>
        <option value="0" <?= (int)$values['status'] === 0 ? 'selected' : '' ?>>Inactive</option>
      </select>
    </div>

    <div class="form-row">
      <label for="slug">Slug (optional)</label>
      <input type="text" name="slug" id="slug"
             placeholder="e.g. chinua-achebe"
             value="<?= h($values['slug']) ?>">
      <p class="hint">Used for URLs like /contributors/chinua-achebe/ if slug column exists.</p>
    </div>

    <div class="form-row">
      <label for="roles">Roles (optional)</label>
      <input type="text" name="roles" id="roles"
             placeholder="e.g. author, editor, translator"
             value="<?= h($values['roles']) ?>">
    </div>

    <div class="form-row">
      <label for="primary_subjects">Primary subjects (optional)</label>
      <input type="text" name="primary_subjects" id="primary_subjects"
             placeholder="e.g. History, Biafra, Language"
             value="<?= h($values['primary_subjects']) ?>">
    </div>

    <div class="form-row">
      <label for="avatar_path">Avatar path (optional)</label>
      <input type="text" name="avatar_path" id="avatar_path"
             placeholder="/lib/images/contributors/example.jpg"
             value="<?= h($values['avatar_path']) ?>">
    </div>

    <div class="form-row">
      <label for="bio_html">Short bio (HTML allowed, optional)</label>
      <textarea name="bio_html" id="bio_html" rows="5"><?= h($values['bio_html']) ?></textarea>
    </div>

    <div class="form-actions">
      <button type="submit" class="button button-primary">Create contributor</button>
      <a href="<?= h(url_for('/staff/contributors/index.php')) ?>" class="button button-secondary">
        Cancel
      </a>
    </div>
  </form>
</main>

<?php include PRIVATE_PATH . '/shared/footer.php'; ?>