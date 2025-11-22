<?php
// project-root/public/staff/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

// Auth guard
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

// Optional: get current user if helper exists
$current_user = null;
if (function_exists('current_user')) {
  $current_user = current_user();
}

// Small helper for URLs
if (!function_exists('mk_url')) {
  function mk_url(string $path): string {
    if (function_exists('url_for')) {
      return url_for($path);
    }
    // Fallback: just return the path
    return $path;
  }
}

$page_title = 'Staff Dashboard';

?>
<?php
// Header (staff layout)
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/staff_header.php')) {
  include PRIVATE_PATH . '/shared/staff_header.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/staff_header.php')) {
  include SHARED_PATH . '/staff_header.php';
}
?>

<main class="mk-main mk-container mk-container--narrow">
  <header class="mk-page-header">
    <h1>Staff Dashboard</h1>
    <p class="mk-muted">
      Central control panel for managing subjects, pages, contributors and platforms.
    </p>

    <?php if (!empty($current_user) && is_array($current_user)): ?>
      <p class="mk-user-greeting">
        Signed in as
        <strong>
          <?= htmlspecialchars($current_user['username'] ?? ($current_user['email'] ?? 'staff'), ENT_QUOTES, 'UTF-8'); ?>
        </strong>
      </p>
    <?php endif; ?>
  </header>

  <!-- ==========================
       Content & Taxonomy
       ========================== -->
  <section class="mk-section">
    <h2>Content &amp; Taxonomy</h2>
    <p class="mk-muted">
      Use these tools to control the subjects and all their pages.
    </p>

    <div class="mk-grid mk-grid--cards">
      <!-- Main Subjects & Pages console -->
      <article class="mk-card mk-card--primary">
        <h3>Subjects &amp; Pages</h3>
        <p>
          Manage all pages under each subject (Overview + deeper content pages).
          This is where most of your writing work will happen.
        </p>

        <div class="mk-card-actions">
          <a class="mk-btn mk-btn--primary"
             style="display:inline-block;margin-bottom:0.5rem;"
             href="<?= htmlspecialchars(mk_url('/staff/subjects/pgs/'), ENT_QUOTES, 'UTF-8'); ?>">
            Go to Subject Pages
          </a>
          <br />
          <a class="mk-btn mk-btn--outline"
             style="display:inline-block;margin-top:0.25rem;"
             href="<?= htmlspecialchars(mk_url('/staff/subjects/'), ENT_QUOTES, 'UTF-8'); ?>">
            Go to Subjects
          </a>
        </div>

        <p class="mk-muted" style="margin-top: .75rem;">
          <strong>Tip:</strong> Use <em>Subjects</em> to manage names, slugs and menu order,
          then use <em>Subject Pages</em> to create and edit real content.
        </p>
      </article>

      <!-- Quick link: Public Subjects -->
      <article class="mk-card">
        <h3>Public Subjects</h3>
        <p>
          View how subjects appear on the public site. Use this to quickly check
          the public navigation and landing pages after changes.
        </p>
        <div class="mk-card-actions">
          <a class="mk-btn mk-btn--ghost"
             style="display:inline-block;margin-top:0.25rem;"
             href="<?= htmlspecialchars(mk_url('/subjects/'), ENT_QUOTES, 'UTF-8'); ?>"
             target="_blank" rel="noopener">
            View public subjects
          </a>
        </div>
      </article>
    </div>
  </section>

  <!-- ==========================
       Contributors & Platforms
       ========================== -->
  <section class="mk-section">
    <h2>Contributors &amp; Platforms</h2>
    <p class="mk-muted">
      Control who appears as a contributor and which platforms (blogs, forums, etc.) exist.
    </p>

    <div class="mk-grid mk-grid--cards">
      <article class="mk-card">
        <h3>Contributors</h3>
        <p>
          Manage contributor profiles, slugs and visibility for the public contributors directory.
        </p>
        <div class="mk-card-actions">
          <a class="mk-btn mk-btn--primary"
             style="display:inline-block;margin-bottom:0.5rem;"
             href="<?= htmlspecialchars(mk_url('/staff/contributors/'), ENT_QUOTES, 'UTF-8'); ?>">
            Go to Contributors
          </a>
        </div>
        <p class="mk-muted" style="margin-top: .5rem;">
          Public directory:
          <a href="<?= htmlspecialchars(mk_url('/contributors/'), ENT_QUOTES, 'UTF-8'); ?>"
             target="_blank" rel="noopener">
            View public contributors
          </a>
        </p>
      </article>

      <article class="mk-card">
        <h3>Platforms</h3>
        <p>
          Define high-level platforms (e.g. Blogs, Forums, Posts) that will host content and activity.
        </p>
        <div class="mk-card-actions">
          <a class="mk-btn mk-btn--primary"
             style="display:inline-block;margin-bottom:0.5rem;"
             href="<?= htmlspecialchars(mk_url('/staff/platforms/'), ENT_QUOTES, 'UTF-8'); ?>">
            Go to Platforms
          </a>
        </div>
        <p class="mk-muted" style="margin-top: .5rem;">
          Public platforms:
          <a href="<?= htmlspecialchars(mk_url('/platforms/'), ENT_QUOTES, 'UTF-8'); ?>"
             target="_blank" rel="noopener">
            View public platforms
          </a>
        </p>
      </article>
    </div>
  </section>

  <!-- ==========================
       System & Progress Tools
       ========================== -->
  <section class="mk-section">
    <h2>System &amp; Progress Tools</h2>
    <p class="mk-muted">
      Technical panels to monitor database health, project progress and run internal scans.
    </p>

    <div class="mk-grid mk-grid--cards">
      <!-- Health Check -->
      <article class="mk-card">
        <h3>Health Check</h3>
        <p>
          Quick technical health overview (DB connection, key files, etc.).
        </p>
        <div class="mk-card-actions">
          <a class="mk-btn mk-btn--ghost"
             style="display:inline-block;margin-top:0.25rem;"
             href="<?= htmlspecialchars(mk_url('/test/health.php'), ENT_QUOTES, 'UTF-8'); ?>"
             target="_blank" rel="noopener">
            Open /test/health.php
          </a>
        </div>
      </article>

      <!-- Progress Panels -->
      <article class="mk-card">
        <h3>Progress Panels</h3>
        <p>
          Monitor subject counts, pages, contributors and platforms at a glance.
        </p>
        <ul class="mk-list mk-list--links" style="margin-top:0.5rem;">
          <li>
            <a href="<?= htmlspecialchars(mk_url('/test/progress2.php'), ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank" rel="noopener">
              /test/progress2.php
            </a>
          </li>
          <li>
            <a href="<?= htmlspecialchars(mk_url('/test/progress3.php'), ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank" rel="noopener">
              /test/progress3.php
            </a>
          </li>
        </ul>
      </article>

      <!-- Scan & Diagnostics -->
      <article class="mk-card">
        <h3>Scan &amp; Diagnostics</h3>
        <p>
          Run internal scans to verify paths, helper functions and htaccess rules.
          These are your deeper technical tools.
        </p>
        <ul class="mk-list mk-list--links" style="margin-top:0.5rem;">
          <li>
            <a href="<?= htmlspecialchars(mk_url('/test/scan_paths.php'), ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank" rel="noopener">
              Scan Paths
            </a>
          </li>
          <li>
            <a href="<?= htmlspecialchars(mk_url('/test/scan_helpers.php'), ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank" rel="noopener">
              Scan Helpers
            </a>
          </li>
          <li>
            <a href="<?= htmlspecialchars(mk_url('/test/scan_htaccess.php'), ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank" rel="noopener">
              Scan Htaccess
            </a>
          </li>
        </ul>
      </article>
    </div>
  </section>
</main>

<?php
// Footer (shared)
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  include PRIVATE_PATH . '/shared/footer.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
}
