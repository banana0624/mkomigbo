<?php
declare(strict_types=1);

/**
 * project-root/public/platforms/forum/category.php
 *
 * Simple listing of threads under one forum category, e.g.:
 *   /platforms/forum/category.php?slug=history-interpretation
 */

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

// Ensure forum helpers are available (if not already loaded by initialize.php)
if (!function_exists('forum_find_categories')) {
  $forumFns = dirname(__DIR__, 3) . '/private/forum_functions.php';
  if (is_file($forumFns)) {
    require_once $forumFns;
  }
}

/* -------------------------------------------------------------------------
 * Input: category slug
 * ---------------------------------------------------------------------- */

$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$category = $slug !== '' ? forum_find_category_by_slug($slug, true) : null;

if (!$category) {
  // Category not found or not public
  header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
  $page_title = 'Forum category not found';
  $active_nav = 'platforms';
  $breadcrumbs = [
    ['label' => 'Home',           'url' => '/'],
    ['label' => 'Platforms',      'url' => '/platforms/'],
    ['label' => 'Community Forum','url' => '/platforms/forum/'],
    ['label' => 'Category not found'],
  ];

  $extra_head = <<<'HTML'
<style>
  .forum-category-not-found {
    margin: 2rem 0;
    font-size: .95rem;
  }
</style>
HTML;

  require_once dirname(__DIR__, 3) . '/private/shared/header.php';
  ?>
  <section class="forum-category-not-found">
    <h1>Forum category not found</h1>
    <p>We could not find this forum category or it is not available to the public.</p>
    <p>
      <a href="<?= h(url_for('/platforms/forum/')) ?>">Back to Community Forum</a>
    </p>
  </section>
  <?php
  require_once dirname(__DIR__, 3) . '/private/shared/footer.php';
  exit;
}

/* -------------------------------------------------------------------------
 * Normal category view
 * ---------------------------------------------------------------------- */

$page_title = 'Forum: ' . (string)($category['title'] ?? '');
$active_nav = 'platforms';

$breadcrumbs = [
  ['label' => 'Home',           'url' => '/'],
  ['label' => 'Platforms',      'url' => '/platforms/'],
  ['label' => 'Community Forum','url' => '/platforms/forum/'],
  ['label' => (string)($category['title'] ?? '')],
];

// Fetch threads in this category (public only for now)
$threads = forum_find_threads_for_category((int)$category['id'], true, 50, 0);

// Simple styling for the list
$extra_head = <<<'HTML'
<style>
  .forum-category-header {
    margin-bottom: 1.8rem;
  }
  .forum-category-eyebrow {
    font-size: .8rem;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #777;
    margin-bottom: .3rem;
  }
  .forum-category-header h1 {
    font-size: 1.7rem;
    margin: 0 0 .5rem;
  }
  .forum-category-header p {
    max-width: 42rem;
    margin: 0;
    line-height: 1.5;
    font-size: .94rem;
    color: #444;
  }

  .forum-thread-list {
    margin: 0;
    padding: 0;
    list-style: none;
  }

  .forum-thread-card {
    border-radius: .8rem;
    border: 1px solid rgba(0,0,0,.06);
    background: #fff;
    padding: .9rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    display: flex;
    flex-direction: column;
    gap: .25rem;
  }

  .forum-thread-card + .forum-thread-card {
    margin-top: .7rem;
  }

  .forum-thread-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
  }

  .forum-thread-meta {
    font-size: .8rem;
    color: #666;
  }

  .forum-thread-meta span + span::before {
    content: "•";
    padding: 0 .3rem;
    color: #aaa;
  }

  .forum-thread-empty {
    margin-top: 1.2rem;
    font-size: .9rem;
    color: #555;
  }

  .forum-category-actions {
    margin-top: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .forum-category-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .35rem .8rem;
    border-radius: .6rem;
    font-size: .85rem;
    border: 1px solid #111;
    background: #111;
    color: #fff;
    text-decoration: none;
  }
  .forum-category-button.secondary {
    background: transparent;
    color: #111;
    border-color: #ccc;
  }

  @media (max-width: 768px) {
    .forum-category-header h1 {
      font-size: 1.4rem;
    }
  }
</style>
HTML;

require_once dirname(__DIR__, 3) . '/private/shared/header.php';
?>

<section class="forum-category-header">
  <p class="forum-category-eyebrow">Community Forum</p>
  <h1><?= h((string)($category['title'] ?? 'Forum category')) ?></h1>
  <?php if (!empty($category['description'])): ?>
    <p><?= h((string)$category['description']) ?></p>
  <?php endif; ?>
</section>

<?php if (!empty($threads)): ?>
  <ul class="forum-thread-list">
    <?php foreach ($threads as $thread): ?>
      <?php
        $title   = (string)($thread['title'] ?? '');
        $slug    = (string)($thread['slug'] ?? '');
        $status  = (int)($thread['status'] ?? 0);
        $pinned  = !empty($thread['is_pinned']);
        $views   = (int)($thread['views_count'] ?? 0);
        $count   = (int)($thread['posts_count'] ?? 0);
        $last_at = (string)($thread['last_post_at'] ?? '');
        // Future: link to a dedicated thread page, e.g. /platforms/forum/thread.php?slug=...
        $thread_url = url_for('/platforms/forum/thread.php?slug=' . urlencode($slug));
      ?>
      <li class="forum-thread-card">
        <h2 class="forum-thread-title">
          <a href="<?= h($thread_url) ?>" style="text-decoration:none;">
            <?= h($title) ?>
            <?php if ($pinned): ?>
              <span style="font-size:.72rem;font-weight:600;margin-left:.35rem;color:#8a5b00;">[Pinned]</span>
            <?php endif; ?>
          </a>
        </h2>
        <div class="forum-thread-meta">
          <span><?= $count ?> post<?= $count === 1 ? '' : 's' ?></span>
          <span><?= $views ?> view<?= $views === 1 ? '' : 's' ?></span>
          <?php if ($status === 1): ?>
            <span>Locked</span>
          <?php elseif ($status === 2): ?>
            <span>Archived</span>
          <?php endif; ?>
          <?php if ($last_at !== ''): ?>
            <span>Last post: <?= h($last_at) ?></span>
          <?php endif; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p class="forum-thread-empty">
    There are no threads in this category yet. As the forum opens, new
    questions and discussions will appear here.
  </p>
<?php endif; ?>

<div class="forum-category-actions">
  <a class="forum-category-button secondary" href="<?= h(url_for('/platforms/forum/')) ?>">
    Back to Community Forum
  </a>
  <a class="forum-category-button secondary" href="<?= h(url_for('/platforms/')) ?>">
    Back to all platforms
  </a>
</div>

<?php require_once dirname(__DIR__, 3) . '/private/shared/footer.php'; ?>