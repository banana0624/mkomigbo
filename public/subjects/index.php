<?php
declare(strict_types=1);

/**
 * project-root/public/subjects/index.php
 * Unified public subjects router:
 *
 *  - /subjects/                       → list all subjects
 *  - /subjects/{subject}/             → subject overview + list of pages
 *  - /subjects/{subject}/{page}/      → show specific page + list of pages
 *
 *  Each subject has an "overview" page which is simply the
 *  first page for that subject (by nav_order / position / id).
 *
 *  This file aims to be:
 *   - robust (works even if some helpers/columns are missing),
 *   - professional (clear layout and structure),
 *   - easy to extend later.
 */

/* 1) Bootstrap initialize.php */
if (!defined('PRIVATE_PATH')) {
  $init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
  if (!is_file($init)) {
    http_response_code(500);
    echo "<h1>FATAL: initialize.php missing</h1>";
    echo "<p>Expected at: " . htmlspecialchars($init, ENT_QUOTES, 'UTF-8') . "</p>";
    exit;
  }
  require_once $init;
}

/* Ensure $db PDO is available */
if (!isset($db) || !($db instanceof PDO)) {
  if (function_exists('db')) {
    $db = db();
  } else {
    http_response_code(500);
    exit('Database connection not available.');
  }
}

/* 2) Helper: safe HTML escape */
if (!function_exists('h')) {
  function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
  }
}

/* 3) Helper: build URL (fallback if url_for not defined) */
if (!function_exists('url_for')) {
  function url_for(string $script_path): string {
    if ($script_path === '' || $script_path[0] !== '/') {
      $script_path = '/' . $script_path;
    }
    // If WWW_ROOT is defined in initialize.php, respect it
    if (defined('WWW_ROOT')) {
      return WWW_ROOT . $script_path;
    }
    return $script_path;
  }
}

/* 4) Data access helpers
 *    These try to use your existing functions if present and
 *    fall back to simple PDO queries if not.
 */

/**
 * Find subject by slug.
 */
function subjects_load_subject_by_slug(string $slug): ?array {
  if (function_exists('find_subject_by_slug')) {
    $row = find_subject_by_slug($slug);
  } else {
    global $db;
    $sql = "SELECT * FROM subjects WHERE slug = :slug LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }
  return is_array($row) ? $row : null;
}

/**
 * Get all public subjects (for /subjects/ listing).
 */
function subjects_load_all_public_subjects(): array {
  if (function_exists('find_all_subjects')) {
    // If your helper already filters public, great.
    $rows = find_all_subjects();
    return is_array($rows) ? $rows : [];
  }

  global $db;

  // Try visible column first; fallback if missing.
  try {
    $sql = "SELECT * FROM subjects WHERE visible = 1 ORDER BY position ASC, id ASC";
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  } catch (Throwable $e) {
    $sql = "SELECT * FROM subjects ORDER BY id ASC";
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

/**
 * Get all pages for a subject, roughly ordered with "overview" first.
 */
function subjects_load_pages_for_subject(int $subject_id, bool $public_only = true): array {
  // Prefer your own helper if present.
  if (function_exists('find_pages_by_subject_id')) {
    $rows = find_pages_by_subject_id($subject_id, $public_only);
    return is_array($rows) ? $rows : [];
  }

  global $db;

  $params = [':sid' => $subject_id];

  // Attempt with is_public + nav_order
  try {
    $sql = "SELECT * FROM pages WHERE subject_id = :sid";
    if ($public_only) {
      $sql .= " AND is_public = 1";
    }
    $sql .= " ORDER BY nav_order ASC, id ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
      return $rows;
    }
  } catch (Throwable $e) {
    // ignore and fall through
  }

  // Fallback: visible + position (older schema)
  try {
    $sql = "SELECT * FROM pages WHERE subject_id = :sid";
    if ($public_only) {
      $sql .= " AND visible = 1";
    }
    $sql .= " ORDER BY position ASC, id ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
      return $rows;
    }
  } catch (Throwable $e) {
    // ignore and fall through
  }

  // Final fallback: just get something.
  $sql = "SELECT * FROM pages WHERE subject_id = :sid";
  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Find a single page by subject + slug.
 */
function subjects_load_page_by_slug(int $subject_id, string $page_slug, bool $public_only = true): ?array {
  global $db;

  // If you have a helper, use it.
  if (function_exists('find_page_by_slug_and_subject_id')) {
    $row = find_page_by_slug_and_subject_id($page_slug, $subject_id, $public_only);
    return is_array($row) ? $row : null;
  }

  // Try modern schema (is_public, slug)
  try {
    $sql = "SELECT * FROM pages
            WHERE subject_id = :sid
              AND slug = :slug";
    if ($public_only) {
      $sql .= " AND is_public = 1";
    }
    $sql .= " LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([
      ':sid'  => $subject_id,
      ':slug' => $page_slug,
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      return $row;
    }
  } catch (Throwable $e) {
    // ignore and fall through
  }

  // Fallback: older schema might not have is_public but should still have slug
  $sql = "SELECT * FROM pages
          WHERE subject_id = :sid
            AND slug = :slug
          LIMIT 1";
  $stmt = $db->prepare($sql);
  $stmt->execute([
    ':sid'  => $subject_id,
    ':slug' => $page_slug,
  ]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

/* 5) Presentation helpers */

function subjects_subject_name(array $subject): string {
  return (string)($subject['name']
    ?? $subject['menu_name']
    ?? $subject['title']
    ?? $subject['slug']
    ?? 'Subject');
}

function subjects_subject_tagline(array $subject): ?string {
  return $subject['tagline'] ?? $subject['description'] ?? null;
}

function subjects_page_title(array $page): string {
  return (string)($page['title']
    ?? $page['menu_name']
    ?? $page['name']
    ?? $page['slug']
    ?? 'Page');
}

function subjects_page_body(array $page): string {
  return (string)($page['body'] ?? $page['content'] ?? '');
}

/**
 * Render the list of subjects for /subjects/.
 */
function subjects_render_subjects_index(array $subjects): void {
  // These variables will be seen inside header.php
  $page_title  = 'Subjects';
  $body_class  = 'subjects-body';
  $active_nav  = 'subjects';

  include PRIVATE_PATH . '/shared/header.php';
  ?>

  <main class="subjects-index-layout mk-container">
    <header class="subjects-index-header">
      <h1 class="subjects-index-title"><?= h($page_title) ?></h1>
      <p class="subjects-index-lead">
        Explore all <?= count($subjects) ?> subjects. Each subject has its own overview page,
        introducing the theme and listing all related articles.
      </p>
    </header>

    <section class="subjects-grid">
      <?php if (empty($subjects)): ?>
        <p>No subjects are currently available.</p>
      <?php else: ?>
        <ul class="subjects-grid-list">
          <?php foreach ($subjects as $subject): ?>
            <?php
              $slug_raw = $subject['slug'] ?? null;
              if (!$slug_raw) { continue; }

              $name = subjects_subject_name($subject);
              $slug = rawurlencode((string)$slug_raw);

              // Build clean web URL: /subjects/{subject}/
              if (function_exists('url_for')) {
                $url = url_for('/subjects/' . $slug . '/');
              } else {
                $url = '/subjects/' . $slug . '/';
              }
            ?>
            <li class="subjects-grid-item">
              <article class="subject-card">
                <h2 class="subject-card-title">
                  <a href="<?= h($url) ?>"><?= h($name) ?></a>
                </h2>
                <?php if ($tagline = subjects_subject_tagline($subject)): ?>
                  <p class="subject-card-tagline">
                    <?= h($tagline) ?>
                  </p>
                <?php else: ?>
                  <p class="subject-card-tagline">
                    Introduction and articles about <?= h($name) ?>.
                  </p>
                <?php endif; ?>
                <p class="subject-card-more">
                  <a href="<?= h($url) ?>" class="subject-card-link">
                    View overview &amp; pages →
                  </a>
                </p>
              </article>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </main>

  <?php
  include PRIVATE_PATH . '/shared/footer.php';
}

/**
 * Render view for a single subject (overview + page + list of pages).
 */
function subjects_render_subject_view(
  array $subject,
  ?array $current_page,
  array $all_pages,
  ?string $requested_page_slug
): void {
  $subject_slug_raw = $subject['slug'] ?? '';
  $subject_slug     = (string)$subject_slug_raw;
  $subject_name     = subjects_subject_name($subject);

  // Determine overview page as "first page" (if any)
  $overview_page = !empty($all_pages) ? $all_pages[0] : null;

  // Page title: current page if present, else overview, else subject
  if ($current_page) {
    $page_title = subjects_page_title($current_page);
  } elseif ($overview_page) {
    $page_title = subjects_page_title($overview_page);
  } else {
    $page_title = $subject_name;
  }

  // For the article body, show the selected page if there is one,
  // otherwise fall back to the overview page.
  $page_for_body = $current_page ?? $overview_page;

  // Optional: build a clean overview URL
  $overview_slug = '';
  if ($overview_page && !empty($overview_page['slug'])) {
    $overview_slug = (string)$overview_page['slug'];
  }

  if ($overview_slug !== '') {
    $overview_url = function_exists('url_for')
      ? url_for('/subjects/' . rawurlencode($subject_slug) . '/' . rawurlencode($overview_slug) . '/')
      : '/subjects/' . rawurlencode($subject_slug) . '/' . rawurlencode($overview_slug) . '/';
  } else {
    // If we don't have any pages, just point to the subject root
    $overview_url = function_exists('url_for')
      ? url_for('/subjects/' . rawurlencode($subject_slug) . '/')
      : '/subjects/' . rawurlencode($subject_slug) . '/';
  }

  // let header.php know which nav / body style to use
  $body_class = 'subjects-body';
  $active_nav = 'subjects';

  include PRIVATE_PATH . '/shared/header.php';
  ?>

  <main class="subject-article-layout mk-container">

    <nav class="breadcrumbs">
      <a href="<?= h(url_for('/')) ?>">Home</a>
      <span class="breadcrumb-separator">»</span>
      <a href="<?= h(url_for('/subjects/')) ?>">Subjects</a>
      <span class="breadcrumb-separator">»</span>
      <a href="<?= h($overview_url) ?>"><?= h($subject_name) ?></a>
      <?php
        $is_overview_view =
          ($requested_page_slug === null || $requested_page_slug === '' || $requested_page_slug === $overview_slug);

        if ($page_for_body && !$is_overview_view):
      ?>
        <span class="breadcrumb-separator">»</span>
        <span><?= h(subjects_page_title($page_for_body)) ?></span>
      <?php endif; ?>
    </nav>

    <div class="subject-layout-columns">
      <!-- LEFT: main article -->
      <section class="subject-layout-main">

        <article class="subject-article">
          <header class="subject-article-header">
            <p class="subject-article-subject">
              <?= h($subject_name) ?>
            </p>

            <h1 class="subject-article-title">
              <?= h($page_title) ?>
            </h1>

            <?php if ($is_overview_view && $overview_page): ?>
              <p class="subject-article-lead">
                This overview introduces the main ideas in the <?= h($subject_name) ?> subject
                and lists the articles available for further reading.
              </p>
            <?php elseif (!$is_overview_view && $page_for_body): ?>
              <p class="subject-article-lead">
                This article is part of the <?= h($subject_name) ?> subject. Scroll down to see
                more pages and topics within this area.
              </p>
            <?php endif; ?>
          </header>

          <div class="subject-article-body">
            <?php if ($page_for_body): ?>
              <?php
                // IMPORTANT: body/content is already HTML; we do NOT escape it again.
                $body_html = subjects_page_body($page_for_body);
                echo $body_html;
              ?>
            <?php else: ?>
              <p>No content is available for this subject yet.</p>
            <?php endif; ?>
          </div>

          <footer class="subject-article-footer">
            <p>
              <a href="<?= h(url_for('/subjects/')) ?>">← Back to all subjects</a>
              <?php if (!$is_overview_view && $overview_page): ?>
                &nbsp;|&nbsp;
                <a href="<?= h($overview_url) ?>">Back to overview</a>
              <?php endif; ?>
            </p>
          </footer>

          <!-- Discuss this article: forum links -->
          <section class="article-forum-links" style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #e5e7eb;">
            <h2 style="font-size:1rem;margin:0 0 .5rem;">Discuss this article</h2>

            <?php
              // Map subject slugs to forum category slugs.
              $forum_category_slug = null;
              switch ($subject['slug'] ?? '') {
                case 'history':
                  $forum_category_slug = 'history-interpretation';
                  break;
                case 'language1':
                case 'language2':
                  $forum_category_slug = 'language-expression';
                  break;
                case 'culture':
                  $forum_category_slug = 'culture-belief-life';
                  break;
                // Add more mappings as you wish...
                default:
                  $forum_category_slug = null;
                  break;
              }
            ?>

            <?php if ($forum_category_slug !== null): ?>
              <p style="margin:.1rem 0 .6rem;font-size:.9rem;color:#555;">
                You can explore questions and discussions related to this topic in the Community Forum.
              </p>
              <p style="margin:0 0 .7rem;">
                <a href="<?= h(url_for('/platforms/forum/category.php?slug=' . urlencode($forum_category_slug))) ?>">
                  View discussions in the forum
                </a>
              </p>
            <?php else: ?>
              <p style="margin:.1rem 0 .7rem;font-size:.9rem;color:#555;">
                Forum discussions for this subject will be organised as the platform grows.
              </p>
            <?php endif; ?>

            <?php
              // Staff detection
              $is_staff = false;
              if (function_exists('is_logged_in_admin')) {
                $is_staff = (bool)is_logged_in_admin();
              } elseif (!empty($_SESSION['admin_id'] ?? null)) {
                $is_staff = true;
              }
            ?>

            <?php if ($is_staff): ?>
              <?php
                $subject_id = (int)($subject['id'] ?? 0);
                $page_id    = (int)($page_for_body['id'] ?? 0);

                // staff route for creating a thread
                $staff_new_thread_url = url_for(
                  '/staff/platforms/forums/create.php?subject_id=' . $subject_id . '&page_id=' . $page_id
                );
              ?>
              <p style="margin:.6rem 0 0;font-size:.82rem;color:#444;">
                <strong>Staff:</strong>
                <a href="<?= h($staff_new_thread_url) ?>">
                  Start a new forum thread linked to this article
                </a>
              </p>
            <?php endif; ?>
          </section>

          <!-- Contributors for this article (your snippet, wired to $page_for_body) -->
          <?php
            $page = $page_for_body; // make $page available for the snippet

            $contributors = [];
            if (isset($page['id']) && function_exists('contributors_find_for_page')) {
              $contributors = contributors_find_for_page((int)$page['id']);
            }
          ?>

          <?php if (!empty($contributors)): ?>
            <section class="subject-article-contributors">
              <h2>Contributors for this article</h2>
              <ul class="contributors-list">
                <?php foreach ($contributors as $c): ?>
                  <li class="contributor-pill">
                    <span class="contributor-name">
                      <?= h(contributor_display_name($c)) ?>
                    </span>
                    <?php if (!empty($c['role_label'] ?? '')): ?>
                      <span class="contributor-role">
                        (<?= h($c['role_label']) ?>)
                      </span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endif; ?>

        </article>

      </section>

      <!-- RIGHT: table of contents for this subject -->
      <aside class="subject-layout-sidebar">
        <section class="subject-toc">
          <h2 class="subject-toc-title">
            Pages under <?= h($subject_name) ?>
          </h2>

          <?php if (empty($all_pages)): ?>
            <p>No pages have been published for this subject yet.</p>
          <?php else: ?>
            <ul class="subject-page-list">
              <?php foreach ($all_pages as $page): ?>
                <?php
                  $subject_slug_local = (string)($subject['slug'] ?? '');
                  $page_slug          = (string)($page['slug'] ?? '');

                  if ($page_slug === '') { continue; }

                  // Build the clean web URL: /subjects/{subject}/{page}/
                  if (function_exists('url_for')) {
                    $page_url = url_for(
                      '/subjects/' . rawurlencode($subject_slug_local) . '/' . rawurlencode($page_slug) . '/'
                    );
                  } else {
                    $page_url = '/subjects/' . rawurlencode($subject_slug_local) . '/' . rawurlencode($page_slug) . '/';
                  }

                  // Determine if this list item is the current page
                  if ($requested_page_slug !== null && $requested_page_slug !== '') {
                    $is_current = ($requested_page_slug === $page_slug);
                  } else {
                    // No page slug in URL → we treat the overview (first item) as current
                    $is_current = isset($all_pages[0]['id'], $page['id'])
                                  ? ((int)$all_pages[0]['id'] === (int)$page['id'])
                                  : false;
                  }
                ?>
                <li class="subject-page-item<?= $is_current ? ' is-current' : '' ?>">
                  <a href="<?= h($page_url) ?>" class="subject-page-link">
                    <?= h(subjects_page_title($page)) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>
      </aside>
    </div>

  </main>

  <?php
  include PRIVATE_PATH . '/shared/footer.php';
}

/* 6) ROUTING LOGIC */

// Parse the path: e.g. /subjects/, /subjects/history/, /subjects/history/history-overview/
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// Expecting:
//   [0] => 'subjects'
//   [1] => '{subject-slug}' (optional)
//   [2] => '{page-slug}'    (optional)
$subject_slug = $segments[1] ?? null;
$page_slug    = $segments[2] ?? null;

// Fallback to query parameters if needed (e.g. ?subject=history&page=overview)
if (!$subject_slug && isset($_GET['subject'])) {
  $subject_slug = (string)$_GET['subject'];
}
if (!$page_slug && isset($_GET['page'])) {
  $page_slug = (string)$_GET['page'];
}

/* No subject slug → show list of all subjects */
if (empty($subject_slug)) {
  $subjects = subjects_load_all_public_subjects();
  subjects_render_subjects_index($subjects);
  exit;
}

/* Subject slug present → load subject */
$subject = subjects_load_subject_by_slug($subject_slug);

if (!$subject) {
  http_response_code(404);

  $page_title  = 'Subject not found';
  $body_class  = 'subjects-body';
  $active_nav  = 'subjects';

  include PRIVATE_PATH . '/shared/header.php';
  ?>
  <main class="subject-article-layout mk-container">
    <article class="subject-article">
      <header class="subject-article-header">
        <h1 class="subject-article-title">Subject not found</h1>
      </header>
      <p>We couldn’t find the subject <strong><?= h($subject_slug) ?></strong>.</p>
      <p><a href="<?= h(url_for('/subjects/')) ?>">← Back to all subjects</a></p>
    </article>
  </main>
  <?php
  include PRIVATE_PATH . '/shared/footer.php';
  exit;
}

/* Load all pages for this subject (public only) */
$pages = subjects_load_pages_for_subject((int)$subject['id'], true);

/* Determine current page */
$current_page = null;

if (!empty($page_slug)) {
  $current_page = subjects_load_page_by_slug((int)$subject['id'], $page_slug, true);
} else {
  if (!empty($pages)) {
    $current_page = $pages[0]; // overview/landing page
  }
}

/* Render subject view */
subjects_render_subject_view(
  $subject,
  $current_page,
  $pages,
  $page_slug
);