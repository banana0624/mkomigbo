<?php
declare(strict_types=1);
/**
 * project-root/public/test_common.php
 *
 * Quick smoke test for the /private/common/* layer.
 *
 * Verifies that:
 *  1) we can reach /private/common/_common_boot.php
 *  2) SHARED_PATH is defined there
 *  3) common_open() / common_close() work
 *  4) link_new()/link_show()/link_edit()/link_delete() are available
 *  5) page-related links can be generated
 *
 * Use:
 *  - Visit: http://mkomigbo.local/test_common.php
 *  - Click the links; they should route to your /common/... handlers
 *  - Even if record id=1 doesn’t exist, we’re testing ROUTING, not data
 */

// 1. Locate project root (we are in /public/)
$rootDir = dirname(__DIR__); // .../project-root

// 2. Locate common boot
$commonBoot = $rootDir . '/private/common/_common_boot.php';
if (!is_file($commonBoot)) {
    http_response_code(500);
    echo 'Cannot find common boot at: ' . htmlspecialchars($commonBoot);
    exit;
}

// 3. Load it (this should also load initialize.php if needed)
require_once $commonBoot;

// 4. Choose context + entity to test
$ctx    = 'staff';     // change to 'public' to test public header
$entity = 'subject';   // this is what we’ve been wiring
$title  = 'Common layer smoke test';

// 5. Open layout (prefer your common_open)
if (function_exists('common_open')) {
    common_open($ctx, $entity, $title);
} else {
    // fallback: plain header
    require PRIVATE_PATH . '/shared/header.php';
    echo '<h2>' . h($title) . '</h2>';
}
?>
<main class="container" style="padding:1rem 0;">

  <p>If you can see this, <code>_common_boot.php</code> was loaded successfully ✅</p>

  <h3>1. Basic common links (subjects)</h3>
  <p>These should all point to your <code>/common/*.php</code> handlers:</p>
  <ul>
    <li>
      New subject:
      <?php if (function_exists('link_new')): ?>
        <a href="<?= h(link_new('subject', 'staff')) ?>">/common/new.php?e=subject&ctx=staff</a>
      <?php else: ?>
        <em>link_new() not defined</em>
      <?php endif; ?>
    </li>
    <li>
      Show subject #1:
      <?php if (function_exists('link_show')): ?>
        <a href="<?= h(link_show('subject', 1, 'staff')) ?>">/common/show.php?e=subject&id=1&ctx=staff</a>
      <?php else: ?>
        <em>link_show() not defined</em>
      <?php endif; ?>
    </li>
    <li>
      Edit subject #1:
      <?php if (function_exists('link_edit')): ?>
        <a href="<?= h(link_edit('subject', 1, 'staff')) ?>">/common/edit.php?e=subject&id=1&ctx=staff</a>
      <?php else: ?>
        <em>link_edit() not defined</em>
      <?php endif; ?>
    </li>
    <li>
      Delete subject #1:
      <?php if (function_exists('link_delete')): ?>
        <a href="<?= h(link_delete('subject', 1, 'staff')) ?>">/common/delete.php?e=subject&id=1&ctx=staff</a>
      <?php else: ?>
        <em>link_delete() not defined</em>
      <?php endif; ?>
    </li>
  </ul>

  <h3>2. Page routes (to test the page side)</h3>
  <p>
    These will work fully after the staff-pages front controller &
    <code>staff_subject_pages/*.php</code> are in place, but you can already
    click them to see if routing hits your <code>/common</code> handlers.
  </p>
  <ul>
    <li>
      New page (generic):
      <?php if (function_exists('link_new')): ?>
        <a href="<?= h(link_new('page', 'staff')) ?>">/common/new.php?e=page&ctx=staff</a>
      <?php else: ?>
        <em>link_new() not defined</em>
      <?php endif; ?>
    </li>
    <li>
      Show page #1:
      <?php if (function_exists('link_show')): ?>
        <a href="<?= h(link_show('page', 1, 'staff')) ?>">/common/show.php?e=page&id=1&ctx=staff</a>
      <?php else: ?>
        <em>link_show() not defined</em>
      <?php endif; ?>
    </li>
  </ul>

  <h3>3. Debug</h3>
  <pre><?php
    echo 'PRIVATE_PATH: ' . (defined('PRIVATE_PATH') ? PRIVATE_PATH : 'NOT DEFINED') . PHP_EOL;
    echo 'SHARED_PATH : ' . (defined('SHARED_PATH')  ? SHARED_PATH  : 'NOT DEFINED') . PHP_EOL;
    echo 'CTX         : ' . $ctx . PHP_EOL;
    echo 'ENTITY      : ' . $entity . PHP_EOL;
  ?></pre>

  <p>
    If <code>SHARED_PATH</code> says <strong>NOT DEFINED</strong>, it means
    <code>_common_boot.php</code> didn’t define it. Open
    <code>/private/common/_common_boot.php</code> and make sure it has:
  </p>
  <pre>define('SHARED_PATH', PRIVATE_PATH . '/shared');</pre>

  <p>
    Also check your Apache vhost for <code>AllowOverride All</code> on the
    <code>/public</code> directory, so your routes and rewrites work.
  </p>

</main>
<?php
// 6. Close page (subject-aware)
if (function_exists('common_close')) {
    common_close($ctx, $entity);
} else {
    require PRIVATE_PATH . '/shared/footer.php';
}
