<?php require __DIR__.'/_head.php';
$id=(int)($_GET['id']??0); $r=review_find($pdo,$id); if(!$r){die('Not found');}
$page_title='Review #'.$id; include SHARED_PATH.'/staff_header.php'; echo display_session_message(); ?>
<h1>Review #<?= (int)$r['id'] ?></h1>
<dl>
  <dt>Subject</dt><dd><?= h($r['subject']) ?></dd>
  <dt>Rating</dt><dd><?= (int)$r['rating'] ?></dd>
  <dt>Comment</dt><dd><pre><?= h((string)($r['comment'] ?? '')) ?></pre></dd>
  <dt>Created</dt><dd><?= h($r['created_at']) ?></dd>
</dl>
<p><a class="btn" href="<?= h(url_for('/staff/contributors/reviews/')) ?>">Back</a></p>
<?php include SHARED_PATH.'/staff_footer.php'; ?>
