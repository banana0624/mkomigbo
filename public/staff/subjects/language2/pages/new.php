<?php
// project-root/public/staff/subjects/language2/pages/new.php
declare(strict_types=1);

$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) {
    die('Init not found at: ' . $init);
}
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$subject_slug = basename(dirname(__DIR__));
$subject_name = function_exists('subject_human_name')
    ? subject_human_name($subject_slug)
    : ucfirst(str_replace('-', ' ', $subject_slug));

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['pages.create']);
require PRIVATE_PATH . '/middleware/guard.php';


// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug  = trim($_POST['slug']  ?? '');

    if ($title === '') {
        $errors[] = "Title is required.";
    }
    if ($slug === '') {
        $errors[] = "Slug is required.";
    }

    if (empty($errors)) {
        // Proceed with create logic (e.g. call a subject_page_create_by_subject_slug())
        try {
            subject_page_create_by_subject_slug($subject_slug, [
                'title' => $title,
                'slug'  => $slug,
                // ... other data ...
            ]);
            // Redirect or success message
            redirect_to(url_for("/staff/subjects/{$subject_slug}/pages/index.php"));
            exit;
        } catch (Exception $e) {
            $errors[] = "Create failed: " . $e->getMessage();
        }
    }
}

require PRIVATE_PATH . '/common/staff_subject_pages/new.php';
?>

<!-- Within the HTML form, ensure these fields exist: -->
<?php if (!empty($errors)): ?>
  <div class="errors">
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?php echo h($e); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form action="<?php echo h(url_for("/staff/subjects/{$subject_slug}/pages/new.php")); ?>" method="post">
  <label for="title">Title</label>
  <input type="text" name="title" id="title" value="<?php echo h($title ?? ''); ?>" required>

  <label for="slug">Slug</label>
  <input type="text" name="slug" id="slug" value="<?php echo h($slug ?? ''); ?>" required>

  <!-- Add any other fields you require -->

  <button type="submit">Create Page</button>
</form>
