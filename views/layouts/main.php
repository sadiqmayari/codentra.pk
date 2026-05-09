<?php
/**
 * @var \Seo    $seo      Configured SEO instance from controller
 * @var string  $content  Captured view output
 */
$cssVer = @filemtime(PUBLIC_PATH . '/css/style.css') ?: time();
$jsVer  = @filemtime(PUBLIC_PATH . '/js/main.js')    ?: time();
$isHome = ($_SERVER['REQUEST_URI'] ?? '/') === '/' || ($_SERVER['REQUEST_URI'] ?? '/') === '';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#0A1C28">
  <meta name="format-detection" content="telephone=no">

  <?= $seo->render() ?>

  <link rel="icon" type="image/svg+xml" href="/public/images/favicon.svg">
  <link rel="apple-touch-icon" href="/public/images/apple-touch-icon.png">

  <link rel="preload" as="font" type="font/woff2" href="/public/fonts/ubuntu-400.woff2" crossorigin>
  <link rel="preload" as="font" type="font/woff2" href="/public/fonts/ubuntu-700.woff2" crossorigin>

  <link rel="stylesheet" href="/public/css/style.css?v=<?= $cssVer ?>">
</head>
<body class="<?= $isHome ? 'is-home' : '' ?>">

  <a class="skip-link" href="#main">Skip to main content</a>

  <?php require VIEW_PATH . '/partials/header.php'; ?>

  <main id="main" role="main">
    <?= $content ?>
  </main>

  <?php require VIEW_PATH . '/partials/footer.php'; ?>

  <script src="/public/js/main.js?v=<?= $jsVer ?>" defer></script>

  <?php if ($isHome): ?>
    <script type="module">
      // Three.js scene loaded only on home, only when motion is allowed
      if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const heroCanvas = document.getElementById('hero-canvas');
        if (heroCanvas) {
          import('/public/js/three-scene.js')
            .then(m => m.init(heroCanvas))
            .catch(err => console.warn('three-scene load failed:', err));
        }
      }
    </script>
  <?php endif; ?>

</body>
</html>
