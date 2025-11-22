Param(
  [string]$ProjectRoot
)

# If not passed, use the folder where this script lives (the project root)
if (-not $ProjectRoot -or -not (Test-Path $ProjectRoot)) {
  $ProjectRoot = Split-Path -Parent $PSCommandPath
}

Write-Host "Project root: $ProjectRoot"

$subjects = @(
  @{ Name = "History";         Slug = "history"         }
  @{ Name = "Slavery";         Slug = "slavery"         }
  @{ Name = "People";          Slug = "people"          }
  @{ Name = "Persons";         Slug = "persons"         }
  @{ Name = "Culture";         Slug = "culture"         }
  @{ Name = "Religion";        Slug = "religion"        }
  @{ Name = "Spirituality";    Slug = "spirituality"    }
  @{ Name = "Tradition";       Slug = "tradition"       }
  @{ Name = "Language 1";      Slug = "language1"       }
  @{ Name = "Language 2";      Slug = "language2"       }
  @{ Name = "Struggles";       Slug = "struggles"       }
  @{ Name = "Biafra";          Slug = "biafra"          }
  @{ Name = "Nigeria";         Slug = "nigeria"         }
  @{ Name = "IPOB";            Slug = "ipob"            }
  @{ Name = "Africa";          Slug = "africa"          }
  @{ Name = "United Kingdom";  Slug = "uk"              }
  @{ Name = "Europe";          Slug = "europe"          }
  @{ Name = "Arabs";           Slug = "arabs"           }
  @{ Name = "About";           Slug = "about"           }
)

# One generic PHP template for ALL subject landing pages.
# It auto-detects the slug from the directory name (basename(__DIR__)).
$template = @'
<?php
// project-root/public/subjects/<slug>/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
// __DIR__ = project-root/public/subjects/<slug>
// dirname(__DIR__, 3) = project-root
if (!is_file($init)) {
  die('Init not found at: ' . $init);
}
require_once $init;

global $db;

// 1) Determine subject slug from directory (history, slavery, etc.)
$subject_slug = basename(__DIR__);
if ($subject_slug === '') {
  http_response_code(400);
  echo 'Invalid subject directory.';
  exit;
}

// 2) Load the subject record (direct SQL)
$subject = null;
try {
  $sql = "SELECT *
            FROM subjects
           WHERE slug = :slug
           LIMIT 1";
  $st = $db->prepare($sql);
  $st->execute([':slug' => $subject_slug]);
  $subject = $st->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
  http_response_code(500);
  echo "DB error loading subject: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
  exit;
}

if (!$subject) {
  http_response_code(404);
  echo "Subject not found: " . htmlspecialchars($subject_slug, ENT_QUOTES, 'UTF-8');
  exit;
}

// 3) Load pages for this subject
$pages = [];
try {
  $sql = "SELECT id, subject_id, title, slug, visible, nav_order
            FROM pages
           WHERE subject_id = :sid
           ORDER BY COALESCE(nav_order, id), id";
  $st = $db->prepare($sql);
  $st->execute([':sid' => (int)$subject['id']]);
  $pages = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $pages = [];
}

// 4) Subject logo URL (if helper exists)
$subject_logo_url = null;
if (function_exists('subject_logo_url')) {
  $subject_logo_url = subject_logo_url($subject);
}

// 5) Page meta + CSS
$page_title = $subject['name'] ?? 'Subject';

$body_class  = 'public-subjects subject subject-' . htmlspecialchars($subject_slug, ENT_QUOTES, 'UTF-8');

// Ensure subjects.css is loaded
$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/subjects.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/subjects.css';
}

// Helper for h()
if (!function_exists('h')) {
  function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
  }
}
?>
<?php
// Public header (normal site header)
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/header.php')) {
  include PRIVATE_PATH . '/shared/header.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/header.php')) {
  include SHARED_PATH . '/header.php';
}
?>

<main class="subject-page subject <?= h($subject_slug); ?>">
  <!-- SUBJECT HERO WITH LOGO -->
  <header class="subject-hero subject-hero--<?= h($subject_slug); ?>">
    <?php if ($subject_logo_url): ?>
      <img
        src="<?= h($subject_logo_url); ?>"
        alt="<?= h(($subject['name'] ?? 'Subject') . ' logo'); ?>"
        class="subject-hero__logo"
      >
    <?php endif; ?>

    <div class="subject-hero__text">
      <h1><?= h($subject['name'] ?? 'Subject'); ?></h1>
      <p class="subject-hero__slug">
        <?= h($subject['slug'] ?? $subject_slug); ?>
      </p>
    </div>
  </header>

  <!-- SUBJECT PAGES LIST -->
  <section class="subject-article">
    <h2>Pages under <?= h($subject['name'] ?? 'Subject'); ?></h2>

    <?php if (!empty($pages)): ?>
      <ul>
        <?php foreach ($pages as $page): ?>
          <?php
            $detail_url = url_for('/subjects/page.php')
              . '?subject=' . rawurlencode($subject['slug'])
              . '&page='   . rawurlencode($page['slug']);
          ?>
          <li>
            <a href="<?= h($detail_url); ?>">
              <?= h($page['title'] ?? $page['slug']); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No pages have been published yet for this subject.</p>
    <?php endif; ?>
  </section>

  <div class="back">
    <a href="<?= h(url_for('/subjects/')); ?>">
      ← Back to all subjects
    </a>
  </div>
</main>

<?php
// Public footer
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  include PRIVATE_PATH . '/shared/footer.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
}
?>
'@

foreach ($s in $subjects) {
  $slug = $s.Slug
  $name = $s.Name

  $subjectDir = Join-Path $ProjectRoot ("public\subjects\" + $slug)
  if (-not (Test-Path $subjectDir)) {
    New-Item -ItemType Directory -Path $subjectDir -Force | Out-Null
    Write-Host "Created directory: $subjectDir"
  } else {
    Write-Host "Directory exists:  $subjectDir"
  }

  $target = Join-Path $subjectDir "index.php"
  Set-Content -LiteralPath $target -Value $template -Encoding UTF8
  Write-Host "Wrote landing index.php for [$name] at $target"
}

Write-Host "Done generating public subject landing pages."
