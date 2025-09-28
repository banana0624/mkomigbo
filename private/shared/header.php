<?php
// project-root/private/shared/header.php
// NOTE: Pages must include initialize.php BEFORE including this header.
$navSubjects = [];
if (isset($db) && $db instanceof mysqli) {
  if ($res = $db->query("SELECT name, slug FROM subjects ORDER BY id ASC")) {
    while ($row = $res->fetch_assoc()) { $navSubjects[] = $row; }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($page_title ?? 'Mkomigbo'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php if (!empty($meta_description)): ?>
    <meta name="description" content="<?= htmlspecialchars($meta_description); ?>" />
  <?php endif; ?>
  <?php if (!empty($meta_keywords)): ?>
    <meta name="keywords" content="<?= htmlspecialchars($meta_keywords); ?>" />
  <?php endif; ?>

  <link rel="stylesheet" href="/lib/css/normalize.css" />
  <link rel="stylesheet" href="/lib/css/main.css" />
  <?php if (!empty($page_css) && is_array($page_css)): ?>
    <?php foreach ($page_css as $href): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($href); ?>" />
    <?php endforeach; ?>
  <?php endif; ?>
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="/index.php">Mkomigbo</a>
    <nav class="main-nav">
      <ul>
        <li><a href="/index.php">Home</a></li>
        <?php foreach ($navSubjects as $s): ?>
          <li><a href="/<?= urlencode($s['slug']); ?>/"><?= htmlspecialchars(strtoupper($s['name'])); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</header>

<main class="site-main container">
