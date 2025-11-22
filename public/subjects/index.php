<?php
declare(strict_types=1);

/**
 * project-root/public/subjects/index.php
 * Unified public subjects router:
 * - /subjects/                  → list all subjects
 * - /subjects/{subject}/        → list pages for that subject
 * - /subjects/{subject}/{page}/ → show one page
 *
 * Flexible for schema (menu_name/name; body/content; visible flags)
 */

/* 1) initialize.php (tolerant path walker) */
if (!defined('PRIVATE_PATH')) {
  $base = __DIR__;
  $init = '';
  for ($i = 0; $i < 6; $i++) {
    $try = $base . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'initialize.php';
    if (is_file($try)) { $init = $try; break; }
    $base = dirname($base);
  }
  if ($init === '') {
    http_response_code(500);
    exit('Init not found');
  }
  require_once $init;
}

/* Ensure $db PDO is available */
if (!isset($db) && function_exists('db')) {
  $db = db();
}

/* 2) local helpers (fallbacks only if missing) */
if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

if (!function_exists('url_for')) {
  function url_for(string $p): string {
    return ($p !== '' && $p[0] !== '/') ? '/' . $p : $p;
  }
}

if (!function_exists('subject_css_class_from_slug')) {
  function subject_css_class_from_slug(string $slug): string {
    $slug = strtolower($slug);
    return preg_replace('/[^a-z0-9]+/', '', $slug);
  }
}

if (!function_exists('body_classes_for_subject')) {
  function body_classes_for_subject(?array $subject = null, bool $is_public = true): string {
    $classes = [$is_public ? 'public-subjects' : 'staff-subjects'];
    if ($subject && !empty($subject['slug'])) {
      $classes[] = 'subject';
      $classes[] = subject_css_class_from_slug((string)$subject['slug']);
    }
    return implode(' ', $classes);
  }
}

/**
 * NOTE: we intentionally do NOT define subject_logo_url() here.
 * Your real implementation lives in private/functions/subject_functions.php
 * and expects an ARRAY ($subject). We will always call it with the full row.
 */

/* 3) schema-tolerant DB helpers */
if (!function_exists('db_column_exists')) {
  function db_column_exists(string $table, string $column): bool {
    static $cache = [];
    $k = strtolower("$table.$column");
    if (array_key_exists($k, $cache)) {
      return $cache[$k];
    }

    try {
      global $db;
      $sql = "SELECT 1
                FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME   = :t
                 AND COLUMN_NAME  = :c
               LIMIT 1";
      $st = $db->prepare($sql);
      $st->execute([':t' => $table, ':c' => $column]);
      $cache[$k] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
      $cache[$k] = false;
    }

    return $cache[$k];
  }
}

/** Get visible pages for a subject; tolerant to nav_order/is_active/visible/position missing. */
function fetch_public_pages_for_subject(int $subject_id): array {
  global $db;

  // ORDER BY: prefer nav_order; else position; else id
  $has_nav      = db_column_exists('pages', 'nav_order');
  $has_position = db_column_exists('pages', 'position');

  if ($has_nav) {
    $order_by = 'p.nav_order ASC, p.slug ASC';
  } elseif ($has_position) {
    $order_by = 'p.position ASC, p.slug ASC';
  } else {
    $order_by = 'p.id ASC, p.slug ASC';
  }

  // Visibility: tolerate missing is_active / visible
  $has_is_active = db_column_exists('pages', 'is_active');
  $has_visible   = db_column_exists('pages', 'visible');

  if ($has_is_active && $has_visible) {
    $visible_pred = 'COALESCE(p.visible,1)=1 AND COALESCE(p.is_active,1)=1';
  } elseif ($has_is_active) {
    $visible_pred = 'COALESCE(p.is_active,1)=1';
  } elseif ($has_visible) {
    $visible_pred = 'COALESCE(p.visible,1)=1';
  } else {
    $visible_pred = '1=1';
  }

  // Build SELECT only with columns that may exist
  $cols = ['p.id', 'p.subject_id', 'p.slug'];
  if (db_column_exists('pages', 'menu_name')) { $cols[] = 'p.menu_name'; }
  if ($has_position)                           { $cols[] = 'p.position'; }

  $sql = "SELECT " . implode(', ', $cols) . "
            FROM pages p
           WHERE p.subject_id = :sid
             AND {$visible_pred}
        ORDER BY {$order_by}";
  $st = $db->prepare($sql);
  $st->execute([':sid' => $subject_id]);
  return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** List all public subjects; tolerant to nav_order/is_public/visible/position missing. */
function fetch_public_subjects(): array {
  global $db;

  // ORDER BY: prefer nav_order; else position; else id
  $has_nav      = db_column_exists('subjects', 'nav_order');
  $has_position = db_column_exists('subjects', 'position');

  if ($has_nav) {
    $order_by = 's.nav_order ASC, s.slug ASC';
  } elseif ($has_position) {
    $order_by = 's.position ASC, s.slug ASC';
  } else {
    $order_by = 's.id ASC, s.slug ASC';
  }

  // Visibility: tolerate is_public / visible
  $has_is_public = db_column_exists('subjects', 'is_public');
  $has_visible   = db_column_exists('subjects', 'visible');

  if ($has_is_public && $has_visible) {
    $visible_pred = 'COALESCE(s.is_public,1)=1 AND COALESCE(s.visible,1)=1';
  } elseif ($has_is_public) {
    $visible_pred = 'COALESCE(s.is_public,1)=1';
  } elseif ($has_visible) {
    $visible_pred = 'COALESCE(s.visible,1)=1';
  } else {
    $visible_pred = '1=1';
  }

  // Build SELECT only with existing columns
  $cols = ['s.id', 's.slug'];
  if (db_column_exists('subjects', 'menu_name')) { $cols[] = 's.menu_name'; }
  if (db_column_exists('subjects', 'name'))      { $cols[] = 's.name'; }
  if ($has_position)                             { $cols[] = 's.position'; }

  $sql = "SELECT " . implode(', ', $cols) . "
            FROM subjects s
           WHERE {$visible_pred}
        ORDER BY {$order_by}";
  $st = $db->query($sql);
  return $st ? ($st->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
}

/* 4) request vars + chrome/header selection */
$subjectSlug = $_GET['subject'] ?? '';
$pageSlug    = $_GET['page'] ?? '';

$page_title  = 'Subjects';
$active_nav  = 'subjects';
$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}
if (!in_array('/lib/css/subjects.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/subjects.css';
}

// For body class we can already inject the slug (even before DB row)
$subjectContextForBody = $subjectSlug !== '' ? ['slug' => $subjectSlug] : null;
$body_class            = body_classes_for_subject($subjectContextForBody, true);

$subjectsHeader = (defined('SHARED_PATH'))
  ? SHARED_PATH . '/subjects/public_subjects_header.php'
  : PRIVATE_PATH . '/shared/subjects/public_subjects_header.php';

$publicHeader  = PRIVATE_PATH . '/shared/public_header.php';
$genericHeader = PRIVATE_PATH . '/shared/header.php';

if (is_file($subjectsHeader)) {
  require $subjectsHeader;
} elseif (is_file($publicHeader)) {
  require $publicHeader;
} else {
  require $genericHeader;
}

/* 5) display helpers */
$subjectDisplayName = function(array $s): string {
  if (!empty($s['menu_name'])) { return $s['menu_name']; }
  if (!empty($s['name']))      { return $s['name']; }
  if (!empty($s['slug']))      { return $s['slug']; }   // final fallback
  return 'Subject';
};

$pageDisplayName = function(array $p): string {
  if (!empty($p['menu_name'])) { return $p['menu_name']; }
  if (!empty($p['name']))      { return $p['name']; }   // if present in your schema
  if (!empty($p['title']))     { return $p['title']; }  // if present
  if (!empty($p['slug']))      { return $p['slug']; }   // final fallback
  return 'Page';
};

$pageBody = function(array $p): string {
  if (!empty($p['body']))    { return $p['body']; }
  if (!empty($p['content'])) { return $p['content']; }
  return '';
};

?>
<main class="container" style="padding:1rem 0;">
<?php
/* 6) No subject → list all subjects */
if ($subjectSlug === '') {

  // Prefer your function if present; otherwise use our tolerant helper
  if (function_exists('find_all_subjects')) {
    $rows = find_all_subjects();
  } else {
    $rows = fetch_public_subjects();
  }

  $subjects = [];
  foreach ($rows as $row) {
    $slug = $row['slug'] ?? '';
    if ($slug === '') {
      $slug = 'subject-' . ($row['id'] ?? uniqid());
    }
    $subjects[$slug] = $row;
  }
  ?>
  <section class="subjects-hero">
    <h1>Subjects</h1>
    <p>Explore the Mkomigbo knowledge base.</p>
  </section>

  <?php if (empty($subjects)): ?>
    <p>No subjects yet.</p>
  <?php else: ?>
    <ul class="grid-subjects"
        style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;list-style:none;padding:0;">
      <?php foreach ($subjects as $slug => $s): ?>
        <?php
          $name       = $subjectDisplayName($s);
          $slug_class = subject_css_class_from_slug((string)$slug);
          $url        = url_for('/subjects/' . rawurlencode((string)$slug) . '/');
        ?>
        <li class="<?= h($slug_class) ?>"
            style="border:1px solid #e2e8f0;border-radius:12px;padding:.9rem;">
          <a href="<?= h($url) ?>"
             style="text-decoration:none;color:#0b63bd;display:block;">
            <strong><?= h($name) ?></strong><br>
            <small class="muted"><?= h((string)$slug) ?></small>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

<?php
/* 7) Subject (and optional page) */
} else {

  // === Single subject by slug (visibility tolerant) ===
  $has_is_public = db_column_exists('subjects', 'is_public');
  $has_visible   = db_column_exists('subjects', 'visible');

  if ($has_is_public && $has_visible) {
    $visible_sql = 'COALESCE(is_public,1)=1 AND COALESCE(visible,1)=1';
  } elseif ($has_is_public) {
    $visible_sql = 'COALESCE(is_public,1)=1';
  } elseif ($has_visible) {
    $visible_sql = 'COALESCE(visible,1)=1';
  } else {
    $visible_sql = '1=1';
  }

  $scols = ['id', 'slug'];
  if (db_column_exists('subjects', 'menu_name')) { $scols[] = 'menu_name'; }
  if (db_column_exists('subjects', 'name'))      { $scols[] = 'name'; }
  if (db_column_exists('subjects', 'position'))  { $scols[] = 'position'; }

  $stmt = $db->prepare("
    SELECT " . implode(', ', $scols) . "
      FROM subjects
     WHERE slug = :slug
       AND {$visible_sql}
     LIMIT 1
  ");
  $stmt->execute([':slug' => $subjectSlug]);
  $subject = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$subject) {
    http_response_code(404);
    echo "<p>Subject not found: " . h($subjectSlug) . "</p>";
  } else {
    $subjectId        = (int)$subject['id'];
    $subjectName      = $subjectDisplayName($subject);
    $subjectSlugLower = strtolower((string)$subject['slug']);

    // Try to resolve logo URL using your real helper (expects ARRAY)
    $logoUrl = null;
    if (function_exists('subject_logo_url')) {
      try {
        $logoUrl = subject_logo_url($subject); // ARRAY, not string
      } catch (Throwable $e) {
        $logoUrl = null;
      }
    } else {
      // Fallback convention if your helper is missing
      $logoUrl = '/lib/images/subjects/' . $subjectSlugLower . '-logo.png';
    }

    if ($pageSlug !== '') {
      /* 7a) Page view (visibility tolerant; select only existing columns) */
      $has_is_active = db_column_exists('pages', 'is_active');
      $has_visible   = db_column_exists('pages', 'visible');

      if ($has_is_active && $has_visible) {
        $visible_page = 'COALESCE(visible,1)=1 AND COALESCE(is_active,1)=1';
      } elseif ($has_is_active) {
        $visible_page = 'COALESCE(is_active,1)=1';
      } elseif ($has_visible) {
        $visible_page = 'COALESCE(visible,1)=1';
      } else {
        $visible_page = '1=1';
      }

      $pcols = ['id', 'slug'];
      if (db_column_exists('pages', 'menu_name')) { $pcols[] = 'menu_name'; }
      if (db_column_exists('pages', 'name'))      { $pcols[] = 'name'; }
      if (db_column_exists('pages', 'title'))     { $pcols[] = 'title'; }
      if (db_column_exists('pages', 'body'))      { $pcols[] = 'body'; }
      if (db_column_exists('pages', 'content'))   { $pcols[] = 'content'; }
      if (db_column_exists('pages', 'position'))  { $pcols[] = 'position'; }

      $stmt = $db->prepare("
        SELECT " . implode(', ', $pcols) . "
          FROM pages
         WHERE subject_id = :sid
           AND slug       = :pslug
           AND {$visible_page}
         LIMIT 1
      ");
      $stmt->execute([':sid' => $subjectId, ':pslug' => $pageSlug]);
      $page = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$page) {
        http_response_code(404);
        echo "<p>Page not found for subject " . h($subjectSlug) . ": " . h($pageSlug) . "</p>";
      } else {
        $pageTitle = $page['title']
          ?? $page['menu_name']
          ?? $page['name']
          ?? $page['slug'];

        ?>
        <div class="subject-page-layout subject-<?= h($subjectSlugLower) ?>">
          <!-- Breadcrumb -->
          <nav class="mk-breadcrumb">
            <a href="<?= h(url_for('/')) ?>">Home</a>
            <span>&raquo;</span>
            <a href="<?= h(url_for('/subjects/')) ?>">Subjects</a>
            <span>&raquo;</span>
            <a href="<?= h(url_for('/subjects/' . $subjectSlugLower . '/')) ?>">
              <?= h($subjectDisplayName($subject)) ?>
            </a>
            <span>&raquo;</span>
            <span><?= h($pageTitle) ?></span>
          </nav>

          <?php
            $allSubjectsUrl  = url_for('/subjects/');
            $subjectPagesUrl = url_for('/subjects/' . rawurlencode($subject['slug']) . '/');
          ?>
          <nav class="subject-page-breadcrumb">
            <a href="<?= h($allSubjectsUrl); ?>" class="crumb-link">
              &larr; Back to all subjects
            </a>

            <a href="<?= h($subjectPagesUrl); ?>" class="crumb-link">
              &larr; Back to all <?= h($subject['name'] ?? $subjectDisplayName($subject)); ?> pages
            </a>
          </nav>

          <!-- Hero -->
          <header class="subject-hero">
            <div class="subject-hero-text">
              <p class="subject-kicker">
                Subject: <?= h($subjectDisplayName($subject)) ?>
              </p>

              <h1 class="subject-page-title">
                <?= h($pageTitle) ?>
              </h1>

              <p class="subject-hero-tagline">
                <?= h("Articles and knowledge about " . $subjectDisplayName($subject)) ?>
              </p>
            </div>

            <?php if (!empty($logoUrl)): ?>
              <div class="subject-hero-media">
                <img src="<?= h(url_for($logoUrl)) ?>"
                     alt="<?= h($subjectDisplayName($subject)) ?> logo"
                     class="subject-hero-logo"
                     onerror="this.style.display='none';">
              </div>
            <?php endif; ?>
          </header>

          <!-- Article body -->
          <article class="subject-article">
            <?= $pageBody($page) ?>
          </article>

          <?php
          // Attachments for this page
          if (function_exists('page_files_for_page')) {
            $files = page_files_for_page((int)$page['id']);
          } else {
            $files = [];
          }

          if (!empty($files)):
          ?>
            <section class="page-attachments">
              <h2 class="page-attachments-heading">Attachments</h2>
              <ul class="page-attachments-list">
                <?php foreach ($files as $f): ?>
                  <?php
                    $fileUrl = url_for('/lib/uploads/pages/' . $f['stored_filename']);
                    $label   = $f['title'] ?? $f['original_filename'];
                    $mime    = (string)($f['mime_type'] ?? '');
                    $isImage = function_exists('str_starts_with')
                      ? str_starts_with($mime, 'image/')
                      : (strpos($mime, 'image/') === 0);
                  ?>
                  <li class="page-attachment-item">
                    <?php if ($isImage): ?>
                      <figure class="page-attachment-figure">
                        <img src="<?= h($fileUrl) ?>"
                             alt="<?= h($label) ?>"
                             class="page-attachment-image" />
                        <?php if (!empty($f['caption'])): ?>
                          <figcaption class="page-attachment-caption">
                            <?= h($f['caption']) ?>
                          </figcaption>
                        <?php endif; ?>
                      </figure>
                    <?php else: ?>
                      <a href="<?= h($fileUrl) ?>"
                         target="_blank"
                         rel="noopener"
                         class="page-attachment-link">
                        <?= h($label) ?>
                      </a>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endif; // attachments ?>
        </div>
        <?php
      }

    } else {
      /* 7b) Subject landing (list pages) — use tolerant helper */
      $pages = fetch_public_pages_for_subject($subjectId);
      ?>
      <section class="subject-landing subject-<?= h($subjectSlugLower) ?>">
        <header class="subject-hero">
          <div class="subject-hero-text">
            <h1><?= h($subjectName) ?></h1>
            <p class="subject-hero-tagline">
              <?= h("Explore pages in the " . $subjectName . " subject.") ?>
            </p>
          </div>

          <?php if (!empty($logoUrl)): ?>
            <div class="subject-hero-media">
              <img src="<?= h(url_for($logoUrl)) ?>"
                   alt="<?= h($subjectName) ?> logo"
                   class="subject-hero-logo"
                   onerror="this.style.display='none';">
            </div>
          <?php endif; ?>
        </header>

        <?php if (!$pages): ?>
          <p>No pages yet for this subject.</p>
        <?php else: ?>
          <ul style="padding-left:1rem;">
            <?php foreach ($pages as $p):
              $pName = $pageDisplayName($p);
              $pUrl  = url_for('/subjects/' . $subject['slug'] . '/' . $p['slug'] . '/'); ?>
              <li><a href="<?= h($pUrl) ?>"><?= h($pName) ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
      <?php
    }
  }
}
?>
</main>
<?php
/* 8) footer (mirrors your logic) */
$subjectsFooter = (defined('SHARED_PATH'))
  ? SHARED_PATH . '/subjects/public_subjects_footer.php'
  : PRIVATE_PATH . '/shared/subjects/public_subjects_footer.php';

if (is_file($subjectsFooter)) {
  require $subjectsFooter;
} else {
  require PRIVATE_PATH . '/shared/footer.php';
}
