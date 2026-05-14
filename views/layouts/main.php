<?php
/**
 * @var \Seo    $seo      Configured SEO instance from controller
 * @var string  $content  Captured view output
 */
$cssVer = @filemtime(PUBLIC_PATH . '/css/style.css') ?: time();
$jsVer  = @filemtime(PUBLIC_PATH . '/js/main.js')    ?: time();
$cssHref = '/public/css/style.css?v=' . $cssVer;
$jsHref  = '/public/js/main.js?v=' . $jsVer;
$isHome = ($_SERVER['REQUEST_URI'] ?? '/') === '/' || ($_SERVER['REQUEST_URI'] ?? '/') === '';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#0A1C28">
  <meta name="format-detection" content="telephone=no">

  <?php
    // Site-wide Organization JSON-LD on every public page. Per-page
    // controllers add Article / Breadcrumb / LocalBusiness on top via
    // $seo->addJsonLd(). We only add Organization here if a controller
    // hasn't already — keeps the LD blob deduplicated.
    if (!$seo->hasJsonLdType('Organization')) {
        $seo->addJsonLd(\Seo::organizationSchema());
    }
  ?>
  <?= $seo->render() ?>

  <link rel="icon" type="image/svg+xml" href="/public/images/favicon.svg">
  <link rel="apple-touch-icon" href="/public/images/apple-touch-icon.png">

  <?php if ($isHome): ?>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
  <?php endif; ?>

  <link rel="preload" as="font" type="font/woff2" href="/public/fonts/ubuntu-400.woff2" crossorigin>

  <?php require VIEW_PATH . '/partials/critical-css.php'; ?>

  <link rel="preload" href="<?= $cssHref ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="<?= $cssHref ?>"></noscript>
</head>
<body class="<?= $isHome ? 'is-home' : '' ?>">

  <a class="skip-link" href="#main">Skip to main content</a>

  <?php require VIEW_PATH . '/partials/header.php'; ?>

  <main id="main" role="main">
    <?= $content ?>
  </main>

  <?php require VIEW_PATH . '/partials/footer.php'; ?>

  <script src="<?= $jsHref ?>" defer></script>

  <?php if ($isHome): ?>
    <script>
      // Defer Three.js until after LCP. Static CSS gradient is the LCP background;
      // canvas fades in with .is-ready when WebGL is initialized.
      (function () {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        var canvas = document.getElementById('hero-canvas');
        if (!canvas) return;

        var start = function () {
          import('/public/js/three-scene.js')
            .then(function (m) {
              return m.init(canvas);
            })
            .then(function () {
              canvas.classList.add('is-ready');
            })
            .catch(function (err) { console.warn('three-scene load failed:', err); });
        };

        var schedule = function () {
          if ('requestIdleCallback' in window) {
            requestIdleCallback(start, { timeout: 1500 });
          } else {
            setTimeout(start, 100);
          }
        };

        if (document.readyState === 'complete') {
          schedule();
        } else {
          window.addEventListener('load', schedule, { once: true });
        }
      })();
    </script>
  <?php endif; ?>

</body>
</html>
