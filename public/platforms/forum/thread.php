<?php
declare(strict_types=1);

/**
 * project-root/public/platforms/forum/thread.php
 *
 * Display a single forum thread and its posts, e.g.:
 *   /platforms/forum/thread.php?slug=my-first-thread
 */

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: " . htmlspecialchars($init, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>";
  exit;
}
require_once $init;

// Ensure forum helpers are available (if not already loaded by initialize.php)
if (!function_exists('forum_find_thread_by_slug')) {
  $forumFns = dirname(__DIR__, 3) . '/private/forum_functions.php';
  if (is_file($forumFns)) {
    require_once $forumFns;
  }
}

/**
 * Small helper for fetching a category by ID (for breadcrumbs).
 * Only defined here if not already present in forum_functions.php.
 */
if (!function_exists('forum_find_category_by_id')) {
  function forum_find_category_by_id(int $id, bool $only_public = true): ?array {
    if ($id <= 0) {
      return null;
    }
    $db = db();
    $sql = "SELECT * FROM " . forum_categories_table() . " WHERE id = :id";
    if ($only_public) {
      $sql .= " AND is_public = 1";
    }
    $sql .= " LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }
}

/* -------------------------------------------------------------------------
 * Input: thread slug
 * ---------------------------------------------------------------------- */

$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';

if ($slug === '') {
  // Missing slug → treat as not found
  http_response_code(404);
  $page_title  = 'Forum thread not found';
  $active_nav  = 'platforms';
  $body_class  = 'platform-body forum-thread-body';
  $breadcrumbs = [
    ['label' => 'Home',            'url' => '/'],
    ['label' => 'Platforms',       'url' => '/platforms/'],
    ['label' => 'Community Forum', 'url' => '/platforms/forum/'],
    ['label' => 'Thread not found'],
  ];

  $extra_head = <<<'HTML'
<style>
  .forum-thread-not-found {
    margin: 2rem 0;
    font-size: .95rem;
  }
</style>
HTML;

  require_once PRIVATE_PATH . '/shared/header.php';
  ?>
  <main class="mk-container forum-thread-not-found">
    <h1>Forum thread not found</h1>
    <p>We could not find this discussion or it is not available to the public.</p>
    <p>
      <a href="<?= h(url_for('/platforms/forum/')) ?>">Back to Community Forum</a>
    </p>
  </main>
  <?php
  require_once PRIVATE_PATH . '/shared/footer.php';
  exit;
}

$thread = forum_find_thread_by_slug($slug, true);

if (!$thread) {
  // Thread not found or not public
  http_response_code(404);
  $page_title  = 'Forum thread not found';
  $active_nav  = 'platforms';
  $body_class  = 'platform-body forum-thread-body';
  $breadcrumbs = [
    ['label' => 'Home',            'url' => '/'],
    ['label' => 'Platforms',       'url' => '/platforms/'],
    ['label' => 'Community Forum', 'url' => '/platforms/forum/'],
    ['label' => 'Thread not found'],
  ];

  $extra_head = <<<'HTML'
<style>
  .forum-thread-not-found {
    margin: 2rem 0;
    font-size: .95rem;
  }
</style>
HTML;

  require_once PRIVATE_PATH . '/shared/header.php';
  ?>
  <main class="mk-container forum-thread-not-found">
    <h1>Forum thread not found</h1>
    <p>We could not find the forum thread <strong><?= h($slug) ?></strong> or it is not available to the public.</p>
    <p>
      <a href="<?= h(url_for('/platforms/forum/')) ?>">Back to Community Forum</a>
    </p>
  </main>
  <?php
  require_once PRIVATE_PATH . '/shared/footer.php';
  exit;
}

/* -------------------------------------------------------------------------
 * Normal thread view
 * ---------------------------------------------------------------------- */

// Optionally fetch the category for breadcrumbs
$category = null;
if (!empty($thread['category_id'])) {
  $category = forum_find_category_by_id((int)$thread['category_id'], true);
}

// Fetch posts for this thread
$posts = forum_find_posts_for_thread((int)$thread['id'], true);

// Page context for shared header
$page_title = (string)($thread['title'] ?? 'Forum thread');
$active_nav = 'platforms';
$body_class = 'platform-body forum-thread-body';

$breadcrumbs = [
  ['label' => 'Home',            'url' => '/'],
  ['label' => 'Platforms',       'url' => '/platforms/'],
  ['label' => 'Community Forum', 'url' => '/platforms/forum/'],
];

if ($category) {
  $breadcrumbs[] = [
    'label' => (string)$category['title'],
    'url'   => '/platforms/forum/category.php?slug=' . urlencode((string)$category['slug']),
  ];
}

$breadcrumbs[] = ['label' => (string)($thread['title'] ?? '')];

// Styling (kept from your original file, slightly adapted)
$extra_head = <<<'HTML'
<style>
  .forum-thread-header {
    margin-bottom: 1.6rem;
  }
  .forum-thread-eyebrow {
    font-size: .8rem;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #777;
    margin-bottom: .3rem;
  }
  .forum-thread-header h1 {
    font-size: 1.6rem;
    margin: 0 0 .4rem;
  }
  .forum-thread-meta {
    font-size: .82rem;
    color: #666;
  }
  .forum-thread-meta span + span::before {
    content: "•";
    padding: 0 .3rem;
    color: #aaa;
  }

  .forum-post-list {
    list-style: none;
    margin: 1.2rem 0 0;
    padding: 0;
  }

  .forum-post {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(0, 4fr);
    gap: .9rem;
    padding: .8rem .9rem;
    border-radius: .8rem;
    border: 1px solid rgba(0,0,0,.05);
    background: #fff;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
    font-size: .92rem;
  }
  .forum-post + .forum-post {
    margin-top: .7rem;
  }

  .forum-post-author {
    font-size: .8rem;
    color: #555;
  }
  .forum-post-author-name {
    font-weight: 600;
  }
  .forum-post-author-badge {
    display: inline-block;
    margin-left: .25rem;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .1em;
    padding: .08rem .35rem;
    border-radius: 999px;
    border: 1px solid #0a7a3e;
    color: #0a7a3e;
  }
  .forum-post-date {
    font-size: .78rem;
    color: #777;
    margin-top: .15rem;
  }

  .forum-post-body {
    font-size: .92rem;
    color: #333;
    line-height: 1.5;
  }

  .forum-thread-empty {
    margin-top: 1.2rem;
    font-size: .9rem;
    color: #555;
  }

  .forum-thread-actions {
    margin-top: 1.4rem;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }
  .forum-thread-button {
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
  .forum-thread-button.secondary {
    background: transparent;
    color: #111;
    border-color: #ccc;
  }

  @media (max-width: 768px) {
    .forum-post {
      grid-template-columns: minmax(0, 1fr);
    }
  }
</style>
HTML;

require_once PRIVATE_PATH . '/shared/header.php';
?>

<main class="mk-container" style="max-width:820px;padding:1.25rem 0 2rem;">

  <section class="forum-thread-header">
    <p class="forum-thread-eyebrow">
      Community Forum
      <?php if ($category): ?>
        · <?= h((string)$category['title']) ?>
      <?php endif; ?>
    </p>
    <h1><?= h((string)($thread['title'] ?? 'Forum thread')) ?></h1>
    <div class="forum-thread-meta">
      <?php
        // Compute posts_count from loaded posts to avoid depending on a thread column
        $posts_count = count($posts);
        $views_count = (int)($thread['views_count'] ?? 0);
        $status      = (int)($thread['status'] ?? 0);
        $last_at     = (string)($thread['last_post_at'] ?? '');
      ?>
      <span><?= $posts_count ?> post<?= $posts_count === 1 ? '' : 's' ?></span>
      <span><?= $views_count ?> view<?= $views_count === 1 ? '' : 's' ?></span>
      <?php if ($status === 1): ?>
        <span>Locked</span>
      <?php elseif ($status === 2): ?>
        <span>Archived</span>
      <?php endif; ?>
      <?php if ($last_at !== ''): ?>
        <span>Last post: <?= h($last_at) ?></span>
      <?php endif; ?>
    </div>
  </section>

  <?php if (!empty($posts)): ?>
    <ul class="forum-post-list">
      <?php foreach ($posts as $post): ?>
        <?php
          $display_name = (string)($post['display_name'] ?? '');
          $created_at   = (string)($post['created_at'] ?? '');
          $is_admin     = !empty($post['admin_id']);
          $body         = (string)($post['body'] ?? '');
        ?>
        <li class="forum-post">
          <div>
            <div class="forum-post-author">
              <span class="forum-post-author-name">
                <?= h($display_name !== '' ? $display_name : ($is_admin ? 'Staff' : 'Contributor')) ?>
              </span>
              <?php if ($is_admin): ?>
                <span class="forum-post-author-badge">Staff</span>
              <?php endif; ?>
            </div>
            <?php if ($created_at !== ''): ?>
              <div class="forum-post-date">
                Posted: <?= h($created_at) ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="forum-post-body">
            <?= nl2br(h($body)) ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="forum-thread-empty">
      There are no posts in this thread yet. When the forum opens, the
      opening question and responses will appear here.
    </p>
  <?php endif; ?>

  <div class="forum-thread-actions">
    <?php if ($category): ?>
      <a class="forum-thread-button secondary"
         href="<?= h(url_for('/platforms/forum/category.php?slug=' . urlencode((string)$category['slug']))) ?>">
        Back to category
      </a>
    <?php endif; ?>
    <a class="forum-thread-button secondary"
       href="<?= h(url_for('/platforms/forum/')) ?>">
      Back to Community Forum
    </a>
  </div>

</main>

<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>