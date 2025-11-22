<?php
// project-root/public/index.php
declare(strict_types=1);

$init = __DIR__ . '/../private/assets/initialize.php';
if (!is_file($init)) {
    die('Init not found at: ' . $init);
}
require_once $init;

$page_title    = 'Mkomigbo';
$body_class    = 'role--public home';
$page_logo     = '/lib/images/logo/mk-logo.png';
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

require_once PRIVATE_PATH . '/shared/header.php';
require_once PRIVATE_PATH . '/shared/subjects_header.php';

/** ---------- HERO (optional partial) ---------- */
$hero = [
    'kicker' => 'Welcome',
    'title'  => 'Mkomigbo',
    'intro'  => 'Explore subjects, platforms, and contributions — organized with calm, distinct theming.',
    'class'  => 'role--public'
];
$hero_partial = PRIVATE_PATH . '/common/ui/hero.php';
if (is_file($hero_partial)) {
    require $hero_partial;
} else {
    ?>
    <section class="hero container" style="padding:1.25rem 0;">
      <p class="kicker muted" style="margin:0 0 .25rem 0;"><?= h($hero['kicker']) ?></p>
      <h1 style="margin:.1rem 0 .35rem 0;"><?= h($hero['title']) ?></h1>
      <p class="muted" style="margin:0"><?= h($hero['intro']) ?></p>
    </section>
    <?php
}

/** ---------- TILES (staff-first IA) ---------- */
$tiles = [
    [
        'href'  => url_for('/staff/'),
        'title' => 'Staff',
        'desc'  => 'Staff console & tools',
        'class' => 'role--staff',
        'img'   => asset_url('/lib/images/subjects/about.svg'),
    ],
    [
        'href'  => url_for('/staff/admins/'),
        'title' => 'Admins',
        'desc'  => 'Admin console',
        'class' => 'role--admin',
        'img'   => asset_url('/lib/images/icons/shield.svg'),
    ],
    [
        'href'  => url_for('/staff/subjects/'),
        'title' => 'Subjects',
        'desc'  => 'Explore 19 subjects',
        'class' => 'subject--history',
        'img'   => asset_url('/lib/images/subjects/history.svg'),
    ],
    [
        'href'  => url_for('/staff/platforms/'),
        'title' => 'Platforms',
        'desc'  => 'Blogs, forums, reels, more',
        'class' => 'platform--blogs',
        'img'   => asset_url('/lib/images/icons/book.svg'),
    ],
    [
        'href'  => url_for('/staff/contributors/'),
        'title' => 'Contributors',
        'desc'  => 'People who build here',
        'class' => 'role--contrib',
        'img'   => asset_url('/lib/images/icons/users.svg'),
    ],
];

$tiles_partial = PRIVATE_PATH . '/common/ui/tiles.php';
if (is_file($tiles_partial)) {
    require $tiles_partial;
} else {
    ?>
    <section class="container" style="padding:1rem 0;">
      <ul class="home-links"
          style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;list-style:none;padding:0;margin:0;">
        <?php foreach ($tiles as $t): ?>
          <li>
            <a class="card <?= h($t['class']) ?>" href="<?= h($t['href']) ?>"
               style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;">
              <div style="display:flex;gap:10px;align-items:center;">
                <?php if (!empty($t['img'])): ?>
                  <img src="<?= h($t['img']) ?>" alt="" width="28" height="28" style="flex:0 0 auto;">
                <?php endif; ?>
                <div style="min-width:0;">
                  <div style="font-weight:600;"><?= h($t['title']) ?></div>
                  <?php if (!empty($t['desc'])): ?>
                    <div class="muted" style="font-size:.9rem;"><?= h($t['desc']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php
}

require_once PRIVATE_PATH . '/shared/footer.php';
