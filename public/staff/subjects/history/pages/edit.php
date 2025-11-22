<?php
declare(strict_types=1);
/* project-root/public/staff/subjects/edit.php */

// Locate and require initialize.php
$initPath = __DIR__ . '/../../../private/assets/initialize.php';
if (!is_file($initPath)) {
    http_response_code(500);
    die('Init not found at: ' . htmlspecialchars($initPath));
}
require_once $initPath;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$slugRaw = $_GET['slug'] ?? '';
$slug    = trim((string)$slugRaw);
if ($slug === '') {
    http_response_code(400);
    die('Missing slug');
}

$subject = subject_row_by_slug($slug);
if (!$subject) {
    http_response_code(404);
    die('Subject not found for slug: ' . htmlspecialchars($slug));
}

$name    = $subject['name'];
$newSlug = $subject['slug'];
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim((string)($_POST['name'] ?? ''));
    $newSlug = trim((string)($_POST['slug'] ?? ''));

    if ($name === '' || $newSlug === '') {
        $errors[] = 'Name and Slug are required.';
    } else {
        try {
            $ok = subject_update_slug($subject['id'], ['name' => $name, 'slug' => $newSlug]);
            if ($ok) {
                redirect_to(url_for('/staff/subjects/show.php?slug=' . urlencode($newSlug)));
                exit;
            } else {
                $errors[] = 'Update did not apply any changes.';
            }
        } catch (Exception $e) {
            $errors[] = 'Update failed: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$page_title    = 'Edit Subject: ' . htmlspecialchars($subject['name']);
$active_nav    = 'subjects';
$body_class    = 'role--staff';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs   = [
    ['label' => 'Home',    'url' => '/'],
    ['label' => 'Staff',   'url' => '/staff/'],
    ['label' => 'Subjects','url' => '/staff/subjects/'],
    ['label' => 'Edit: ' . htmlspecialchars($subject['name'])],
];

require_once PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1rem 0">
  <h1>Edit Subject</h1>
  <?php if ($errors): ?>
    <div class="error">
      <ul>
      <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
      <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="<?= url_for('/staff/subjects/edit.php?slug=' . urlencode($slug)) ?>" method="post">
    <div class="form-group">
      <label for="name">Name</label>
      <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" required>
    </div>
    <div class="form-group">
      <label for="slug">Slug</label>
      <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($newSlug) ?>" required>
    </div>
    <button class="btn" type="submit">Save</button>
  </form>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
