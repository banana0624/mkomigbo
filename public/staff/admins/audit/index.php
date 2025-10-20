<?php
// project-root/public/staff/admins/audit/index.php
declare(strict_types=1);

/* Robust init resolver (works for any vhost DocumentRoot under /public) */
$__dir = __DIR__; $__init = null;
for ($i=0; $i<10; $i++) {
  $cand = $__dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'initialize.php';
  $cand = realpath($cand) ?: $cand;
  if (is_file($cand)) { $__init = $cand; break; }
  $parent = dirname($__dir); if ($parent === $__dir) break; $__dir = $parent;
}
if (!$__init && !empty($_SERVER['DOCUMENT_ROOT'])) {
  $cand = dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'initialize.php';
  if (is_file($cand)) $__init = $cand;
}
if (!$__init) { http_response_code(500); die('Init not found at resolver'); }
require_once $__init;

// Gate: staff login + audit permission
define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['audit.view']);
require PRIVATE_PATH . '/middleware/guard.php';

require_once PRIVATE_PATH . '/functions/audit_log_functions.php';

$action = trim((string)($_GET['action'] ?? '')); // e.g., "page.%"
$user   = (int)($_GET['user']   ?? 0);
$from   = trim((string)($_GET['from']   ?? '')); // YYYY-MM-DD
$to     = trim((string)($_GET['to']     ?? ''));
$page   = max(1, (int)($_GET['page']    ?? 1));
$per    = min(200, max(10, (int)($_GET['per'] ?? 25)));

$search = audit_log_search(compact('action','user','from','to','page','per'));
$rows   = $search['rows'];
$total  = $search['total'];
$page   = $search['page'];
$per    = $search['per'];
$pages  = max(1, (int)ceil($total / $per));

function keep_params(array $extra): string {
  $q = $_GET;
  foreach ($extra as $k=>$v) { $q[$k] = $v; }
  return '?' . http_build_query($q);
}

$page_title    = 'Audit Log';
$active_nav    = 'staff';
$body_class    = 'role--staff role--admin';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Audit'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:1100px;padding:1.25rem 0">
  <header style="display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;flex-wrap:wrap;">
    <div>
      <h1 style="margin:0">Audit Log</h1>
      <p class="muted" style="margin:.25rem 0 0;"><?= (int)$total ?> event<?= $total===1?'':'s' ?> found</p>
    </div>
    <form method="get" class="filters" style="display:grid;grid-template-columns:repeat(5,minmax(150px,1fr)) 100px;gap:.5rem;align-items:end;">
      <div>
        <label class="muted" for="flt-action">Action</label>
        <input id="flt-action" class="input" type="text" name="action" placeholder="e.g. page.%"
               value="<?= h($action) ?>">
      </div>
      <div>
        <label class="muted" for="flt-user">User ID</label>
        <input id="flt-user" class="input" type="number" name="user" min="0" value="<?= $user ?: '' ?>">
      </div>
      <div>
        <label class="muted" for="flt-from">From</label>
        <input id="flt-from" class="input" type="date" name="from" value="<?= h($from) ?>">
      </div>
      <div>
        <label class="muted" for="flt-to">To</label>
        <input id="flt-to" class="input" type="date" name="to" value="<?= h($to) ?>">
      </div>
      <div>
        <label class="muted" for="flt-per">Per Page</label>
        <input id="flt-per" class="input" type="number" name="per" min="10" max="200" value="<?= (int)$per ?>">
      </div>
      <div>
        <button class="btn btn-primary" type="submit">Filter</button>
      </div>
    </form>
  </header>

  <div class="table-wrap" style="margin-top:1rem;">
    <table class="table">
      <thead>
        <tr>
          <th style="width:170px">Time</th>
          <th style="width:80px">User</th>
          <th style="width:160px">Action</th>
          <th style="width:110px">Entity</th>
          <th>Meta</th>
          <th style="width:120px">IP</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="muted">No results.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <?php
            $when  = h($r['created_at'] ?? '');
            $uid   = (int)($r['user_id'] ?? 0);
            $userL = $uid > 0
              ? h(($r['username'] ?? '') ?: ($r['email'] ?? "ID {$uid}"))
              : '—';
            $act   = h($r['action'] ?? '');
            $ety   = h((string)($r['entity_type'] ?? '')) . ' #' . (int)($r['entity_id'] ?? 0);
            $meta  = (string)($r['meta_json'] ?? '');
            $metaShort = $meta;
            if (mb_strlen($metaShort) > 140) { $metaShort = mb_substr($metaShort, 0, 140) . '…'; }
            $ip    = h((string)($r['ip_addr'] ?? ''));
          ?>
          <tr>
            <td><code><?= $when ?></code></td>
            <td><?= $userL ?></td>
            <td><code><?= $act ?></code></td>
            <td><?= $ety ?></td>
            <td>
              <?php if ($meta === '' || strtolower($meta) === 'null'): ?>
                <span class="muted">—</span>
              <?php else: ?>
                <details>
                  <summary><code><?= h($metaShort) ?></code></summary>
                  <pre style="white-space:pre-wrap;word-wrap:break-word;margin:.35rem 0 0;"><?= h($meta) ?></pre>
                </details>
              <?php endif; ?>
            </td>
            <td><code><?= $ip ?></code></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="pagination" style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.75rem;">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm" href="<?= h(keep_params(['page'=>$page-1])) ?>">&larr; Prev</a>
      <?php else: ?>
        <a class="btn btn-sm is-disabled" role="button" aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.55;">&larr; Prev</a>
      <?php endif; ?>

      <span class="muted" style="align-self:center;">Page <?= (int)$page ?> of <?= (int)$pages ?></span>

      <?php if ($page < $pages): ?>
        <a class="btn btn-sm" href="<?= h(keep_params(['page'=>$page+1])) ?>">Next &rarr;</a>
      <?php else: ?>
        <a class="btn btn-sm is-disabled" role="button" aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.55;">Next &rarr;</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>

  <p style="margin-top:1rem;">
    <a class="btn" href="<?= h(url_for('/staff/admins/')) ?>">&larr; Back to Admins</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
