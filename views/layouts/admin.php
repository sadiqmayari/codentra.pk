<?php
/**
 * @var \Seo   $seo
 * @var string $content
 * @var string|null $pageTitle  Optional, overrides SEO title in topbar
 */
$cssVer  = @filemtime(PUBLIC_PATH . '/css/style.css') ?: time();
$cssHref = '/public/css/style.css?v=' . $cssVer;

$flash = $_SESSION['_flash'] ?? null;
unset($_SESSION['_flash']);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0A1C28">
  <meta name="robots" content="noindex,nofollow">

  <?= $seo->render() ?>

  <link rel="icon" type="image/svg+xml" href="/public/images/favicon.svg">
  <link rel="preload" as="font" type="font/woff2" href="/public/fonts/ubuntu-400.woff2" crossorigin>

  <link rel="preload" href="<?= $cssHref ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="<?= $cssHref ?>"></noscript>
</head>
<body class="is-admin">

  <button class="admin-drawer-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" data-admin-drawer-toggle>
    <span></span><span></span><span></span>
  </button>

  <div class="admin-shell">
    <?php require VIEW_PATH . '/partials/admin-sidebar.php'; ?>

    <div class="admin-main">
      <?php require VIEW_PATH . '/partials/admin-topbar.php'; ?>

      <div class="admin-content">
        <?php if ($flash): ?>
          <div class="admin-flash admin-flash--<?= htmlspecialchars($flash['type']) ?>" role="status">
            <?= htmlspecialchars($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <?= $content ?>
      </div>
    </div>
  </div>

  <script>
    (function () {
      var btn  = document.querySelector('[data-admin-drawer-toggle]');
      var body = document.body;
      if (!btn) return;
      btn.addEventListener('click', function () {
        var open = body.classList.toggle('admin-drawer-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
      // Close drawer on link click (mobile UX)
      document.querySelectorAll('.admin-sidebar__link').forEach(function (a) {
        a.addEventListener('click', function () {
          if (window.matchMedia('(max-width: 1023px)').matches) {
            body.classList.remove('admin-drawer-open');
            btn.setAttribute('aria-expanded', 'false');
          }
        });
      });
    })();
  </script>
</body>
</html>
