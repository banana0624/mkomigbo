<?php
// project-root/public/staff/admins/audit/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['audit.view']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

require_once PRIVATE_PATH . '/functions/audit_log_functions.php';
require_once PRIVATE_PATH . '/common/pagination.php';

$page_title    = 'Audit Log';
$active_nav    = 'admins';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Audit'],
];

// ---- filters (GET) ----
$filters = [
  'action' => trim((string)($_GET['action'] ?? '')),
  'user'   => (int)($_GET['user'] ?? 0),
  'from'   => trim((string)($_GET['from'] ?? '')),
  'to'     => trim((string)($_GET['to']   ?? '')),
];
[$limit,$offset,$page] = pager_input($_GET, 25);

// search (re-use your function; it accepts per/page)
$out = audit_log_search([
  'action' => $filters['action'],
  'user'   => $filters['user'],
  'from'   => $filters['from'],
  'to'     => $filters['to'],
  'page'   => $page,
  'per'    => $limit,
]);
$rows  = $out['rows'];
$total = $out['total'];

// build base URL for pager
$keep = array_filter([
  'action'=>$filters['action'],
  'user'  =>$filters['user'] ?: null,
  'from'  =>$filters['from'] ?: null,
  'to'    =>$filters['to']   ?: null,
]);
$base = url_for('/staff/admins/audit/') . ( $keep ? ('?' . http_build_query($keep)) : '' );

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<h1>Audit Log</h1>

<form method="get" class="filters" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.5rem;align-items:end">
  <div><label>Action starts with</label><input class="input" type="text" name="action" placeholder="e.g. page." value="<?= h($filters['action']) ?>"></div>
  <div><label>User ID</label><input class="input" type="number" name="user" min="0" value="<?= (int)$filters['user'] ?>"></div>
  <div><label>From</label><input class="input" type="date" name="from" value="<?= h($filters['from']) ?>"></div>
  <div><label>To</label><input class="input" type="date" name="to" value="<?= h($filters['to']) ?>"></div>
  <div><label>&nbsp;</label><button class="btn btn-primary" type="submit">Filter</button></div>
</form>

<div class="actions" style="margin:.75rem 0;display:flex;gap:.5rem;flex-wrap:wrap">
  <a class="btn" href="<?= h(url_for('/staff/admins/audit/export.csv.php') . ($keep ? ('?' . http_build_query($keep)) : '')) ?>">Export CSV</a>
</div>

<?php if (!$rows): ?>
  <p class="muted">No audit entries.</p>
<?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>#</th><th>When</th><th>User</th><th>Action</th><th>Entity</th><th>Meta</th><th>IP</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= h($r['created_at']) ?></td>
            <td><?= (int)($r['user_id'] ?? 0) ?><?= $r['username'] ? ' ('.h($r['username']).')' : '' ?></td>
            <td><?= h($r['action']) ?></td>
            <td><?= h(($r['entity_type'] ?? '') . ':' . ($r['entity_id'] ?? '')) ?></td>
            <td class="muted" style="max-width:32rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?= h(is_string($r['meta_json']) ? $r['meta_json'] : json_encode($r['meta_json'])) ?>
            </td>
            <td><?= h($r['ip_addr'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?= pager_render($total, $page, $limit, $base) ?>
<?php endif; ?>

<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
