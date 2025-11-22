<?php
declare(strict_types=1);
/**
 * project-root/public/staff/subjects/history/pages/index.php
 * Staff list of pages for subject “history”
 */

require_once dirname(__DIR__, 5) . '/private/assets/initialize.php';
require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$subject_slug = 'history';
$subject      = subject_row_by_slug($subject_slug);
if (!$subject) {
    http_response_code(404);
    die('Subject not found');
}

$page_title    = 'Pages for ' . h($subject['name']);
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1rem 0">
  <h1>Pages: <?= h($subject['name']) ?></h1>
  <p>
    <a class="btn" href="<?= url_for('/staff/subjects/' . h($subject_slug) . '/pages/new.php') ?>">
      New Page
    </a>
  </p>
  <?php
    $pages = pages_list_by_subject($subject_slug);
    if (empty($pages)) {
        echo '<p>No pages yet for this subject.</p>';
    } else {
  ?>
    <table class="listing">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Slug</th>
          <th>Published</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pages as $page): ?>
          <tr>
            <td><?= (int)$page['id'] ?></td>
            <td><?= h($page['title']) ?></td>
            <td><?= h($page['slug']) ?></td>
            <td><?= (!empty($page['is_published']) ? 'Yes' : 'No') ?></td>
            <td class="nowrap">
              <a href="<?= url_for('/staff/subjects/' . h($subject_slug) . '/pages/show.php?id=' . urlencode($page['id'])) ?>">View</a>
              |
              <a href="<?= url_for('/staff/subjects/' . h($subject_slug) . '/pages/edit.php?id=' . urlencode($page['id'])) ?>">Edit</a>
              |
              <a href="<?= url_for('/staff/subjects/' . h($subject_slug) . '/pages/delete.php?id=' . urlencode($page['id'])) ?>"
                   onclick="return confirm('Delete this page?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php } ?>
</main>
<?php
require PRIVATE_PATH . '/shared/footer.php';
