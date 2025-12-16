<?php
declare(strict_types=1);

/**
 * project-root/public/contributors/list.php
 *
 * Read-only list of contributors, powered by the `contributors` table.
 * URL: /contributors/list.php
 */

/* 1) Bootstrap initialize.php */
if (!defined('PRIVATE_PATH')) {
  $init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
  if (!is_file($init)) {
    http_response_code(500);
    echo "<h1>FATAL: initialize.php missing</h1>";
    echo "<p>Expected at: " . htmlspecialchars($init, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>";
    exit;
  }
  require_once $init;
}

/* 2) Ensure contributor helpers are loaded */
if (!function_exists('contributors_find_public')) {
  $cf_path = PRIVATE_PATH . '/functions/contributor_functions.php';
  if (is_file($cf_path)) {
    require_once $cf_path;
  }
}

/* 3) Page context */
$page_title  = 'Contributors list';
$body_class  = 'contributors-body contributors-list-body';
$active_nav  = 'contributors';

$breadcrumbs = [
  ['label' => 'Home',        'url' => '/'],
  ['label' => 'Contributors','url' => '/contributors/'],
  ['label' => 'List'],
];

$meta = [
  'description' => 'Browse contributors to Mkomigbo – people who help research, write, translate and curate materials.',
];

/* 4) Fetch contributors (read-only) */
$contributors = [];
if (function_exists('contributors_find_public')) {
  $contributors = contributors_find_public();
}

/* 5) Small page-specific styles */
$extra_head = <<<'HTML'
<style>
  .contributors-list-main {
    padding: 1.3rem 0 2rem;
  }

  .contributors-list-header {
    margin-bottom: 1rem;
  }
  .contributors-list-title {
    font-size: 1.4rem;
    margin: 0 0 .3rem;
  }
  .contributors-list-lead {
    font-size: .9rem;
    color: #4b5563;
    margin: 0;
  }

  .contributors-cards {
    margin-top: 1.1rem;
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.2fr);
    gap: .85rem;
  }

  .contributors-card {
    border-radius: .85rem;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    padding: .75rem .9rem .85rem;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
    font-size: .9rem;
  }

  .contributors-card-header {
    margin-bottom: .35rem;
  }
  .contributors-card-name {
    font-weight: 600;
    font-size: .96rem;
    margin: 0 0 .1rem;
  }
  .contributors-card-meta {
    font-size: .8rem;
    color: #6b7280;
  }

  .contributors-card-bio {
    font-size: .86rem;
    color: #4b5563;
    margin-top: .35rem;
  }

  .contributors-list-empty {
    margin-top: 1rem;
    font-size: .9rem;
    color: #4b5563;
  }

  @media (max-width: 800px) {
    .contributors-cards {
      grid-template-columns: minmax(0, 1fr);
    }
  }
</style>
HTML;

require_once PRIVATE_PATH . '/shared/header.php';
?>

<main class="mk-container contributors-list-main">
  <header class="contributors-list-header">
    <h1 class="contributors-list-title">Contributors on Mkomigbo</h1>
    <p class="contributors-list-lead">
      This is an early, read-only list of people who contribute to Mkomigbo.
      Over time, contributors will have richer profiles and stronger links
      to specific subjects, pages and platforms.
    </p>
  </header>

  <?php if (empty($contributors)): ?>
    <p class="contributors-list-empty">
      There are no public contributors listed yet. As the project grows and
      contributions are confirmed, names will begin to appear here.
    </p>
  <?php else: ?>
    <section class="contributors-cards" aria-label="Contributors">
      <?php foreach ($contributors as $c): ?>
        <?php
          $name  = contributor_display_name($c);
          $role  = contributor_role_label($c);
          $bio   = contributor_short_bio($c);
          $area  = $c['subject_area'] ?? $c['primary_subject'] ?? null;
        ?>
        <article class="contributors-card">
          <header class="contributors-card-header">
            <p class="contributors-card-name"><?= h($name) ?></p>
            <p class="contributors-card-meta">
              <?php if ($role): ?>
                <?= h($role) ?>
                <?php if ($area): ?>
                  · <?= h($area) ?>
                <?php endif; ?>
              <?php elseif ($area): ?>
                <?= h($area) ?>
              <?php endif; ?>
            </p>
          </header>

          <?php if ($bio): ?>
            <p class="contributors-card-bio">
              <?= h($bio) ?>
            </p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</main>

<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>