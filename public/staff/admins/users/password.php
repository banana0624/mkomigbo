<?php
// project-root/public/staff/admins/users/password.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['users.edit']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

require_once PRIVATE_PATH . '/functions/user_functions.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$u  = $id ? user_find($id) : null;
if (!$u) { http_response_code(404); die('User not found'); }

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check();
  $pw = (string)($_POST['password'] ?? '');
  $pw2 = (string)($_POST['password2'] ?? '');
  if ($pw !== '' && $pw === $pw2) {
    $ok = user_update_secure($id, ['password'=>$pw]);
    flash($ok?'success':'error', $ok?'Password updated.':'Update failed.');
    header('Location: ' . url_for('/staff/admins/users/')); exit;
  }
  flash('error','Passwords must match and cannot be empty.');
}

$page_title = 'Change Password';
$active_nav = 'admins';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Users','url'=>'/staff/admins/users/'],
  ['label'=>'Change Password'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Change Password <small class="muted">for <?= h($u['username']) ?></small></h1>
  <form method="post" id="pwform">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="field">
      <label>New Password</label>
      <div style="display:flex;gap:.5rem;align-items:center">
        <input class="input" type="password" id="pw" name="password" autocomplete="new-password" required>
        <button class="btn" type="button" id="toggler" aria-pressed="false">Show</button>
      </div>
      <div id="meter" class="muted" style="margin-top:.25rem">Strength: —</div>
    </div>
    <div class="field">
      <label>Confirm Password</label>
      <input class="input" type="password" name="password2" autocomplete="new-password" required>
    </div>
    <div class="actions">
      <button class="btn btn-primary">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/users/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>

<script>
(function(){
  const pw = document.getElementById('pw');
  const meter = document.getElementById('meter');
  const toggle = document.getElementById('toggler');
  function score(s){
    let c=0;
    if (s.length >= 12) c++;
    if (/[A-Z]/.test(s)) c++;
    if (/[a-z]/.test(s)) c++;
    if (/\d/.test(s)) c++;
    if (/[^A-Za-z0-9]/.test(s)) c++;
    return c; // 0..5
  }
  pw.addEventListener('input', ()=>{
    const s = score(pw.value);
    const labels = ['Very weak','Weak','Fair','Good','Strong','Excellent'];
    meter.textContent = 'Strength: ' + labels[s];
  });
  toggle.addEventListener('click', ()=>{
    const showing = pw.type === 'text';
    pw.type = showing ? 'password' : 'text';
    toggle.textContent = showing ? 'Show' : 'Hide';
    toggle.setAttribute('aria-pressed', String(!showing));
  });
})();
</script>
