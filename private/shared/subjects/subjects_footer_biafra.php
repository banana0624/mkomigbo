<?php
// subjects_footer_biafra.php
declare(strict_types=1);
?>
</main>

<footer class="site-footer layout-subjects subject-biafra">
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