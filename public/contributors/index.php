<?php
declare(strict_types=1);

/**
 * project-root/public/contributors/index.php
 *
 * Public Contributors hub:
 *   /contributors/
 *
 * Explains the idea of contributors on Mkomigbo – researchers, writers,
 * translators, media partners – and sets the stage for future profiles
 * and contributor logins.
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
$page_title  = 'Contributors';
$body_class  = 'contributors-body';
$active_nav  = 'contributors';

$meta = [
  'description' => 'Learn about contributors to Mkomigbo – researchers, writers, translators and media partners who help document and interpret the life of Ndi Mkomigbo.',
];

$breadcrumbs = [
  ['label' => 'Home',        'url' => '/'],
  ['label' => 'Contributors'],
];

// Optional: extra small styles just for this hub
$extra_head = <<<'HTML'
<style>
  .contributors-hero {
    padding: 1.5rem 0 1.25rem;
  }
  .contributors-hero-eyebrow {
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .16em;
    color: #6b7280;
    margin: 0 0 .25rem;
  }
  .contributors-hero-title {
    font-size: 1.6rem;
    margin: 0 0 .4rem;
  }
  .contributors-hero-lead {
    max-width: 46rem;
    font-size: .95rem;
    color: #4b5563;
    margin: 0;
  }

  .contributors-layout {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1.3fr);
    gap: 1.5rem;
    margin-top: 1.75rem;
  }

  .contributors-section {
    margin-bottom: 1.5rem;
  }
  .contributors-section h2 {
    font-size: 1.05rem;
    margin: 0 0 .4rem;
  }
  .contributors-section p {
    font-size: .9rem;
    color: #4b5563;
    margin: 0 0 .4rem;
  }

  .contributors-roles-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: .4rem;
  }
  .contributors-roles-item {
    padding: .4rem .5rem;
    border-radius: .55rem;
    border: 1px solid #e5e7eb;
    background: #ffffff;
  }
  .contributors-roles-item strong {
    font-size: .9rem;
    display: block;
    margin-bottom: .1rem;
  }
  .contributors-roles-item span {
    font-size: .82rem;
    color: #6b7280;
  }

  .contributors-card {
    border-radius: .9rem;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    padding: .85rem .9rem 1rem;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
    margin-bottom: .9rem;
  }
  .contributors-card h3 {
    font-size: .98rem;
    margin: 0 0 .35rem;
  }
  .contributors-card p {
    font-size: .86rem;
    color: #4b5563;
    margin: 0 0 .4rem;
  }

  .contributors-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .15rem .55rem;
    border-radius: 999px;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .12em;
    border: 1px solid #d1d5db;
    color: #6b7280;
    background: #f9fafb;
    margin-bottom: .35rem;
  }

  .contributors-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
    margin-top: .3rem;
  }
  .contributors-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .35rem .8rem;
    border-radius: .6rem;
    font-size: .85rem;
    border: 1px solid #111827;
    background: #111827;
    color: #ffffff;
    text-decoration: none;
  }
  .contributors-btn.secondary {
    background: transparent;
    color: #111827;
    border-color: #d1d5db;
  }
  .contributors-meta {
    font-size: .78rem;
    color: #9ca3af;
  }

  @media (max-width: 768px) {
    .contributors-layout {
      grid-template-columns: minmax(0, 1fr);
    }
    .contributors-hero-title {
      font-size: 1.4rem;
    }
  }
</style>
HTML;

require_once PRIVATE_PATH . '/shared/header.php';
?>

<main class="mk-container contributors-main" style="padding:1.25rem 0 2.1rem;">

  <section class="contributors-hero">
    <p class="contributors-hero-eyebrow">Contributors</p>
    <h1 class="contributors-hero-title">People who help Mkomigbo speak</h1>
    <p class="contributors-hero-lead">
      Mkomigbo is designed to grow with the work of many hands: researchers,
      writers, translators, media partners and community members who help
      document and interpret the life of Ndi Mkomigbo.
    </p>
  </section>

  <section class="contributors-layout">
    <!-- LEFT: explanation + roles -->
    <div>
      <section class="contributors-section">
        <h2>What does it mean to be a contributor?</h2>
        <p>
          A contributor is anyone who actively adds to the knowledge stored on
          Mkomigbo – by researching sources, shaping narratives, translating
          materials, or curating media that supports the subjects.
        </p>
        <p>
          Over time, contributors will have profiles, clearer attribution on
          articles, and ways to collaborate around History, Language, Culture,
          Struggles and other themes.
        </p>
      </section>

      <section class="contributors-section">
        <h2>Typical contributor roles</h2>
        <ul class="contributors-roles-list">
          <li class="contributors-roles-item">
            <strong>Researcher</strong>
            <span>Helps locate, verify and organise historical or cultural sources.</span>
          </li>
          <li class="contributors-roles-item">
            <strong>Writer / editor</strong>
            <span>Drafts and refines the subject pages, articles and explanatory notes.</span>
          </li>
          <li class="contributors-roles-item">
            <strong>Translator</strong>
            <span>Works across languages (Igbo / English and others) to keep content accessible.</span>
          </li>
          <li class="contributors-roles-item">
            <strong>Media contributor</strong>
            <span>Shares images, documents, audio or video that illuminate a topic.</span>
          </li>
          <li class="contributors-roles-item">
            <strong>Community connector</strong>
            <span>Helps gather stories, oral histories and perspectives from the wider community.</span>
          </li>
        </ul>
      </section>
    </div>

    <!-- RIGHT: small cards about next steps -->
    <aside>
      <article class="contributors-card">
        <span class="contributors-badge">Phase 1</span>
        <h3>Contributor profiles (coming soon)</h3>
        <p>
          In a later phase, this page will list named contributors with short
          biographies and links to the pages or projects they have worked on.
        </p>
        <p class="contributors-meta">
          This will connect directly to the existing <code>contributors</code> table
          in your database.
        </p>
      </article>

      <article class="contributors-card">
        <span class="contributors-badge">In preparation</span>
        <h3>How to become a contributor</h3>
        <p>
          A simple onboarding flow will be added here: how to express interest,
          what kind of work is needed, and how materials are reviewed before
          they appear on the site.
        </p>
        <div class="contributors-actions">
          <span class="contributors-btn secondary" aria-disabled="true">
            Not yet open
          </span>
          <span class="contributors-meta">
            Workflow and permissions are still being designed.
          </span>
        </div>
      </article>

      <article class="contributors-card">
        <span class="contributors-badge">For staff</span>
        <h3>Managing contributors</h3>
        <p>
          Site staff will eventually have tools in the staff area to review
          contributor accounts and connect them to specific subjects or pages.
        </p>
        <div class="contributors-actions">
          <a class="contributors-btn secondary"
             href="<?= h(url_for('/staff/')) ?>">
            Go to staff area
          </a>
        </div>
      </article>
    </aside>
  </section>

</main>

<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>