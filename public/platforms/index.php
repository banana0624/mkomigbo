<?php
declare(strict_types=1);

/**
 * project-root/public/platforms/index.php
 *
 * Public Platforms hub:
 *   /platforms/
 *
 * Introduces the different interactive platforms on Mkomigbo
 * (Forum, Blog, Media, etc.), with Community Forum already active
 * and others marked as "coming soon".
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

/* 2) Basic page context for header */
$page_title  = 'Platforms';
$body_class  = 'platforms-body';
$active_nav  = 'platforms';

$meta = [
  'description' => 'Explore the platforms of Mkomigbo – forums, communities, and media spaces that deepen how Ndi Mkomigbo and friends discuss and share knowledge.',
];

// Optional: extra small styles just for this hub
$extra_head = <<<'HTML'
<style>
  .platforms-hero {
    padding: 1.5rem 0 1.25rem;
  }
  .platforms-hero-eyebrow {
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .16em;
    color: #6b7280;
    margin: 0 0 .25rem;
  }
  .platforms-hero-title {
    font-size: 1.6rem;
    margin: 0 0 .4rem;
  }
  .platforms-hero-lead {
    max-width: 46rem;
    font-size: .95rem;
    color: #4b5563;
    margin: 0;
  }

  .platforms-grid {
    margin-top: 1.6rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }

  .platform-card {
    border-radius: .9rem;
    border: 1px solid #e5e7eb;
    padding: .9rem .95rem 1rem;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .platform-card-header {
    margin-bottom: .6rem;
  }
  .platform-card-eyebrow {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .14em;
    color: #9ca3af;
    margin: 0 0 .15rem;
  }
  .platform-card-title {
    font-size: 1.05rem;
    margin: 0 0 .1rem;
  }
  .platform-card-status {
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: #059669;
  }
  .platform-card-status.badge-soon {
    color: #b45309;
  }

  .platform-card-body {
    font-size: .9rem;
    color: #4b5563;
    margin-bottom: .7rem;
  }

  .platform-card-footer {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    align-items: center;
  }

  .platform-card-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .35rem .8rem;
    border-radius: .6rem;
    border: 1px solid #111827;
    background: #111827;
    color: #ffffff;
    font-size: .86rem;
    text-decoration: none;
  }
  .platform-card-link.secondary {
    background: transparent;
    color: #111827;
    border-color: #d1d5db;
  }

  .platform-card-meta {
    font-size: .78rem;
    color: #9ca3af;
  }

  @media (max-width: 640px) {
    .platforms-hero-title {
      font-size: 1.35rem;
    }
  }
</style>
HTML;

require_once PRIVATE_PATH . '/shared/header.php';
?>

<main class="mk-container platforms-main" style="padding:1.25rem 0 2rem;">

  <section class="platforms-hero">
    <p class="platforms-hero-eyebrow">Platforms</p>
    <h1 class="platforms-hero-title">Interactive spaces on Mkomigbo</h1>
    <p class="platforms-hero-lead">
      Beyond static articles, Mkomigbo hosts and will grow several platforms where
      people can ask questions, share interpretations, and contribute materials
      around History, Language, Culture, Struggles and more.
    </p>
  </section>

  <section class="platforms-grid" aria-label="Mkomigbo platforms">
    <!-- Community Forum -->
    <article class="platform-card">
      <div>
        <header class="platform-card-header">
          <p class="platform-card-eyebrow">Discussion</p>
          <h2 class="platform-card-title">Community Forum</h2>
          <p class="platform-card-status">Now available</p>
        </header>
        <div class="platform-card-body">
          Join structured discussions connected to the subjects and articles
          on Mkomigbo. Threads are grouped into categories such as
          <em>History &amp; interpretation</em>, <em>Language &amp; expression</em>,
          and <em>Culture &amp; belief</em>.
        </div>
      </div>
      <footer class="platform-card-footer">
        <a class="platform-card-link"
           href="<?= h(url_for('/platforms/forum/')) ?>">
          Enter Community Forum
        </a>
        <span class="platform-card-meta">
          Early phase • read-only for visitors
        </span>
      </footer>
    </article>

    <!-- Blog -->
    <article class="platform-card">
      <div>
        <header class="platform-card-header">
          <p class="platform-card-eyebrow">Essays</p>
          <h2 class="platform-card-title">Blog &amp; commentary</h2>
          <p class="platform-card-status badge-soon">Coming soon</p>
        </header>
        <div class="platform-card-body">
          A curated stream of essays, reflections and commentary that
          expand on the core subjects. Longer-form writing from contributors
          and invited voices will live here.
        </div>
      </div>
      <footer class="platform-card-footer">
        <span class="platform-card-link secondary" aria-disabled="true">
          Not yet open
        </span>
        <span class="platform-card-meta">
          Structure and workflow still in design.
        </span>
      </footer>
    </article>

    <!-- Media / Library -->
    <article class="platform-card">
      <div>
        <header class="platform-card-header">
          <p class="platform-card-eyebrow">Media</p>
          <h2 class="platform-card-title">Media &amp; resources</h2>
          <p class="platform-card-status badge-soon">Coming soon</p>
        </header>
        <div class="platform-card-body">
          A focused place for images, documents, audio and video related to
          the subjects: archival materials, interviews, maps, and teaching
          resources that complement the written pages.
        </div>
      </div>
      <footer class="platform-card-footer">
        <span class="platform-card-link secondary" aria-disabled="true">
          Not yet open
        </span>
        <span class="platform-card-meta">
          Will connect to your uploads &amp; attachments.
        </span>
      </footer>
    </article>
  </section>

</main>

<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>