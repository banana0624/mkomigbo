<?php 
// project-root/public/test_platforms.php

declare(strict_types=1);

// 1) Bootstrap
$init = __DIR__ . '/../private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: ' . $init); }
require_once $init;

// 2) Page chrome
$page_title = 'Platforms • Test';
$stylesheets[] = '/lib/css/ui.css'; // optional, for nicer look
require PRIVATE_PATH . '/shared/public_header.php';

// 3) Load platforms (via helpers if available)
$platforms = [];
if (function_exists('platforms_all')) {
    $platforms = platforms_all();
} else {
    // Fallback: include registry file directly (initialize.php should already have done this)
    $regFile = REGISTRY_PATH . '/platforms_register.php';
    if (is_file($regFile)) {
        require_once $regFile;
        if (isset($PLATFORMS_REGISTER) && is_array($PLATFORMS_REGISTER)) {
            $platforms = $PLATFORMS_REGISTER;
        }
    }
}

// 4) Render
?>
<h1>Platforms (test)</h1>

<?php if (empty($platforms)): ?>
  <div class="alert warn">No platforms found. Check <code>private/registry/platforms_register.php</code> is loaded.</div>
<?php else: ?>
  <p>Found <strong><?= count($platforms) ?></strong> platform(s).</p>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Key</th>
          <th>Name</th>
          <th>Enabled</th>
          <th>Route</th>
          <th>Children</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($platforms as $key => $p): ?>
          <tr>
            <td><code><?= h($key) ?></code></td>
            <td><?= h($p['name'] ?? '') ?></td>
            <td><?= !empty($p['enabled']) ? 'yes' : 'no' ?></td>
            <td><?= h($p['route'] ?? '') ?></td>
            <td>
              <?php
                $kids = isset($p['children']) && is_array($p['children']) ? array_keys($p['children']) : [];
                echo $kids ? h(implode(', ', $kids)) : '—';
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<p style="margin-top:1rem">
  <a class="btn" href="<?= h(url_for('/')) ?>">Back to Home</a>
</p>

<?php require PRIVATE_PATH . '/shared/public_footer.php'; ?>
