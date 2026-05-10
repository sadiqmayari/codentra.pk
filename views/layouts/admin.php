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

  <?php
    // FontAwesome 4 — only on the post-edit screen. EasyMDE's toolbar uses
    // FA glyph classes; we load FA ourselves (autoDownloadFontAwesome: false
    // in admin-posts.js) so:
    //   1. CSP only has to allow cdn.jsdelivr.net (no maxcdn.bootstrapcdn.com)
    //   2. The version is pinned (4.7.0) and verified by SRI — bumping
    //      the version means re-computing the integrity hash:
    //        curl -s https://cdn.jsdelivr.net/npm/font-awesome@<VER>/css/font-awesome.min.css \
    //          | openssl dgst -sha384 -binary | openssl base64 -A
    //   3. No supply-chain risk from a "latest" tag.
    if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/posts')):
  ?>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css"
      integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN"
      crossorigin="anonymous"
      referrerpolicy="no-referrer">
  <?php endif; ?>
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

  <?php
    // Per-route admin JS modules. Vanilla, no framework — kept narrow.
    // admin-shared.js loads on every admin route so AdminUI is always
    // there before per-route modules try to use it.
    $adminUri  = $_SERVER['REQUEST_URI'] ?? '';
    $sharedVer = @filemtime(PUBLIC_PATH . '/js/admin-shared.js') ?: time();
  ?>
    <script src="/public/js/admin-shared.js?v=<?= $sharedVer ?>" defer></script>
  <?php if (str_starts_with($adminUri, '/admin/leads')):
      $leadsJsVer = @filemtime(PUBLIC_PATH . '/js/admin-leads.js') ?: time();
  ?>
    <script src="/public/js/admin-leads.js?v=<?= $leadsJsVer ?>" defer></script>
  <?php endif; ?>
  <?php if (str_starts_with($adminUri, '/admin/posts')):
      $postsJsVer = @filemtime(PUBLIC_PATH . '/js/admin-posts.js') ?: time();
  ?>
    <script src="/public/js/admin-posts.js?v=<?= $postsJsVer ?>" defer></script>
  <?php endif; ?>

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
