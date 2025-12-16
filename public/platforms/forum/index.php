<?php
declare(strict_types=1);

/**
 * project-root/public/platforms/forum/index.php
 *
 * Community Forum hub:
 *   /platforms/forum/
 *
 * Shows:
 *   - list of public forum categories
 *   - up to a few recent threads per category
 */

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: " . htmlspecialchars($init, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>";
  exit;
}
require_once $init;

// Ensure forum helpers are loaded
if (!function_exists('forum_find_categories')) {
  $forumFns = dirname(__DIR__, 3) . '/private/forum_functions.php';
  if (is_file($forumFns)) {
    require_once $forumFns;
  }
}

// Fallbacks if forum_functions.php is older
if (!function_exists('forum_find_categories')) {
  function forum_find_categories(bool $only_public = true): array {
    $db = db();
    $sql = "SELECT * FROM forum_categories";
    if ($only_public) {
      $sql .= " WHERE is_public = 1";
    }
    $sql .= " ORDER BY sort_order ASC, title ASC, id ASC";
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

if (!function_exists('forum_find_threads_for_category')) {
  function forum_find_threads_for_category(int $category_id, bool $only_public = true, int $limit = 5): array {
    if ($category_id <= 0) {
      return [];
    }
    $db = db();
    $sql = "SELECT * FROM forum_threads WHERE category_id = :cid";
    if ($only_public) {
      $sql .= " AND is_public = 1";
    }
    $sql .= " ORDER BY last_post_at DESC, created_at DESC";
    if ($limit > 0) {
      $sql .= " LIMIT " . (int)$limit;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute([':cid' => $category_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

// Load categories
$categories = forum_find_categories(true);

// Page context for shared header
$page_title = 'Community Forum';
$active_nav = 'platforms';
$body_class = 'platform-body forum-body';

$breadcrumbs = [
  ['label' => 'Home',      'url' => '/'],
  ['label' => 'Platforms', 'url' => '/platforms/'],
  ['label' => 'Community Forum'],
];

// Extra CSS for this page
$extra_head = <<<'HTML'
<style>
  .forum-hub-header {
    margin-bottom: 1.5rem;
  }
  .forum-hub-eyebrow {
    font-size: .8rem;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: .3rem;
  }
  .forum-hub-header h1 {
    font-size: 1.7rem;
    margin: 0 0 .4rem;
  }
  .forum-hub-lead {
    font-size: .95rem;
    color: #4b5563;
    max-width: 46rem;
    margin: 0;
  }

  .forum-category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1rem;
  }

  .forum-category-card {
    border-radius: .9rem;
    border: 1px solid rgba(0,0,0,.05);
    background: #fff;
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
    padding: .9rem .95rem;
    display: flex;
    flex-direction: column;
    gap: .5rem;
    font-size: .92rem;
  }

  .forum-category-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
  }

  .forum-category-title a {
    color: inherit;
    text-decoration: none;
  }
  .forum-category-title a:hover {
    text-decoration: underline;
  }

  .forum-category-meta {
    font-size: .8rem;
    color: #6b7280;
  }

  .forum-category-description {
    font-size: .9rem;
    color: #4b5563;
    margin: 0;
  }

  .forum-category-threads {
    margin-top: .4rem;
    border-top: 1px dashed #e5e7eb;
    padding-top: .4rem;
  }

  .forum-thread-mini-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: .25rem;
  }

  .forum-thread-mini-item a {
    font-size: .88rem;
    text-decoration: none;
    color: #111827;
  }
  .forum-thread-mini-item a:hover {
    text-decoration: underline;
  }

  .forum-thread-mini-meta {
    font-size: .78rem;
    color: #9ca3af;
  }

  .forum-hub-empty {
    margin-top: 1rem;
    font-size: .92rem;
    color: #4b5563;
  }
</style>
HTML;

require_once PRIVATE_PATH . '/shared/header.php';
?>

<main class="mk-container" style="max-width:960px;padding:1.25rem 0 2rem;">

  <section class="forum-hub-header">
    <p class="forum-hub-eyebrow">Platforms</p>
    <h1><?= h($page_title) ?></h1>
    <p class="forum-hub-lead">
      Join conversations around the subjects and articles on Mkomigbo.
      Topics are organised into categories so that discussions about
      History, Language, Culture and other areas are easy to find.
    </p>
  </section>

  <?php if (empty($categories)): ?>
    <p class="forum-hub-empty">
      Forum categories have not been created yet. As the platform grows,
      discussion spaces for key subjects will appear here.
    </p>
  <?php else: ?>
    <section class="forum-category-grid">
      <?php foreach ($categories as $cat): ?>
        <?php
          $cat_id    = (int)($cat['id'] ?? 0);
          $cat_title = (string)($cat['title'] ?? 'Category');
          $cat_slug  = (string)($cat['slug'] ?? '');
          $cat_desc  = (string)($cat['description'] ?? '');

          $cat_url = url_for('/platforms/forum/category.php?slug=' . urlencode($cat_slug));

          // Thread counts (optional, may not exist in DB yet)
          $threads_count = (int)($cat['threads_count'] ?? 0);
          $posts_count   = (int)($cat['posts_count'] ?? 0);

          // Load a few recent threads for preview
          $recent_threads = $cat_id > 0
            ? forum_find_threads_for_category($cat_id, true, 3)
            : [];
        ?>
        <article class="forum-category-card">
          <header>
            <h2 class="forum-category-title">
              <a href="<?= h($cat_url) ?>">
                <?= h($cat_title) ?>
              </a>
            </h2>
            <p class="forum-category-meta">
              <?php if ($threads_count > 0 || $posts_count > 0): ?>
                <?= $threads_count ?> thread<?= $threads_count === 1 ? '' : 's' ?>,
                <?= $posts_count ?> post<?= $posts_count === 1 ? '' : 's' ?>
              <?php else: ?>
                Discussion space for this topic.
              <?php endif; ?>
            </p>
          </header>

          <?php if ($cat_desc !== ''): ?>
            <p class="forum-category-description">
              <?= h($cat_desc) ?>
            </p>
          <?php endif; ?>

          <?php if (!empty($recent_threads)): ?>
            <div class="forum-category-threads">
              <ul class="forum-thread-mini-list">
                <?php foreach ($recent_threads as $thread): ?>
                  <?php
                    $t_slug  = (string)($thread['slug'] ?? '');
                    $t_title = (string)($thread['title'] ?? 'Thread');
                    $t_url   = url_for('/platforms/forum/thread.php?slug=' . urlencode($t_slug));
                    $t_last  = (string)($thread['last_post_at'] ?? ($thread['created_at'] ?? ''));
                  ?>
                  <li class="forum-thread-mini-item">
                    <a href="<?= h($t_url) ?>">
                      <?= h($t_title) ?>
                    </a>
                    <?php if ($t_last !== ''): ?>
                      <div class="forum-thread-mini-meta">
                        Last activity: <?= h($t_last) ?>
                      </div>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php else: ?>
            <div class="forum-category-threads">
              <p class="forum-thread-mini-meta">
                No threads yet. The first questions and discussions will appear here.
              </p>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

</main>

<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>