<?php
// project-root/private/tools/generate_subject_snippets.php
declare(strict_types=1);

// Define your subject registry (id => [slug, name]) or load your existing registry
$subjects = [
    1  => ['slug'=>'history',      'name'=>'History'],
    2  => ['slug'=>'slavery',      'name'=>'Slavery'],
    3  => ['slug'=>'people',       'name'=>'People'],
    4  => ['slug'=>'persons',      'name'=>'Persons'],
    5  => ['slug'=>'culture',      'name'=>'Culture'],
    6  => ['slug'=>'religion',     'name'=>'Religion'],
    7  => ['slug'=>'spirituality', 'name'=>'Spirituality'],
    8  => ['slug'=>'tradition',    'name'=>'Tradition'],
    9  => ['slug'=>'language1',    'name'=>'Language1'],
    10 => ['slug'=>'language2',    'name'=>'Language2'],
    11 => ['slug'=>'struggles',    'name'=>'Struggles'],
    12 => ['slug'=>'biafra',       'name'=>'Biafra'],
    13 => ['slug'=>'nigeria',      'name'=>'Nigeria'],
    14 => ['slug'=>'ipob',         'name'=>'IPOB'],
    15 => ['slug'=>'africa',       'name'=>'Africa'],
    16 => ['slug'=>'uk',           'name'=>'UK'],
    17 => ['slug'=>'europe',       'name'=>'Europe'],
    18 => ['slug'=>'arabs',        'name'=>'Arabs'],
    19 => ['slug'=>'about',        'name'=>'About'],
];

// Directory where snippets will be created
$targetDir = __DIR__ . '/../shared/';

foreach ($subjects as $id => $info) {
    $slug = $info['slug'];
    $name = $info['name'];

    // Header file
    $headerFile = $targetDir . "subjects_header_{$slug}.php";
    $headerContent = <<<PHP
<?php
// subjects_header_{$slug}.php
declare(strict_types=1);

\$subject_slug = '{$slug}';
\$page_title = \$page_title ?? '{$name}';
\$page_description = \$page_description ?? '';
\$page_keywords = \$page_keywords ?? '{$slug}, mkomigbo';

require_once __DIR__ . '/public_header.php';
?>
<link rel="stylesheet" href="<?php echo url_for('/lib/css/theme_variables.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/lib/css/subjects_base.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/lib/css/subjects/{$slug}.css'); ?>">
<body class="subject-{$slug}" data-theme="<?php echo h(\$current_theme ?? 'light'); ?>">

<header class="site-header layout-subjects subject-{$slug}">
  <div class="header-wrapper">
    <div class="logo">
      <a href="<?php echo url_for('/'); ?>">
        <img src="<?php echo url_for('/lib/images/logo/mk-logo.png'); ?>" alt="MKOMIGBO Logo">
        <span>MKOMIGBO</span>
      </a>
    </div>
    <nav class="main-nav">
      <ul>
        <li><a href="<?php echo url_for('/'); ?>">Home</a></li>
        <li><a href="<?php echo url_for('/subjects/'); ?>">Subjects</a></li>
        <li><a href="<?php echo url_for('/staff/'); ?>">Staff</a></li>
        <li><a href="<?php echo url_for('/staff/contributors/'); ?>">Contributors</a></li>
        <li><a href="<?php echo url_for('/staff/platforms/'); ?>">Platforms</a></li>
      </ul>
    </nav>
    <button id="themeToggleBtn" class="theme-toggle-btn" aria-label="Toggle theme">🌓</button>
  </div>
</header>
<main>

PHP;
    file_put_contents($headerFile, $headerContent);
    echo "Created header snippet for {$slug} => {$headerFile}\n";

    // Footer file
    $footerFile = $targetDir . "subjects_footer_{$slug}.php";
    $footerContent = <<<PHP
<?php
// subjects_footer_{$slug}.php
declare(strict_types=1);
?>
</main>

<footer class="site-footer layout-subjects subject-{$slug}">
  <div class="footer-wrapper">
    <div class="footer-nav">
      <a href="<?php echo url_for('/'); ?>">Home</a> •
      <a href="<?php echo url_for('/subjects/'); ?>">All Subjects</a> •
      <a href="<?php echo url_for('/staff/'); ?>">Staff Dashboard</a>
    </div>
    <div class="footer-branding">
      &copy; <?php echo date('Y'); ?> MKOMIGBO. All rights reserved.
    </div>
    <div class="footer-theme-toggle">
      <button id="themeToggleBtnFooter" class="theme-toggle-btn" aria-label="Toggle theme">🌓</button>
    </div>
    <a href="#top" id="backToTopBtn" class="back-to-top" aria-label="Back to top">↑</a>
  </div>

<link rel="stylesheet" href="<?php echo url_for('/lib/css/footer_base.css'); ?>">
<script src="<?php echo url_for('/lib/js/footer_common.js'); ?>"></script>

<script>
(function(){
  const root = document.documentElement;
  const storageKey = 'mkomigbo_color_mode';

  const applyMode = mode => {
    root.setAttribute('data-theme', mode);
    localStorage.setItem(storageKey, mode);
  };

  const initMode = () => {
    const saved = localStorage.getItem(storageKey);
    if (saved) return applyMode(saved);
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      applyMode('dark');
    } else {
      applyMode('light');
    }
  };
  initMode();

  const toggleHandler = e => {
    e.preventDefault();
    const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    applyMode(current);
  };

  document.getElementById('themeToggleBtn').addEventListener('click', toggleHandler);
  document.getElementById('themeToggleBtnFooter').addEventListener('click', toggleHandler);

  const btn = document.getElementById('backToTopBtn');
  const showOffset = 300;
  window.addEventListener('scroll', () => {
    if (window.scrollY > showOffset) {
      btn.classList.add('visible');
    } else {
      btn.classList.remove('visible');
    }
  });

  btn.addEventListener('click', e => {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();
</script>

</footer>
</body>
</html>
PHP;
    file_put_contents($footerFile, $footerContent);
    echo "Created footer snippet for {$slug} => {$footerFile}\n";
}

echo "Done generating snippet files.\n";
