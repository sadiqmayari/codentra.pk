<?php
/**
 * @var \Seo   $seo
 * @var string $content
 *
 * Minimal layout for /admin/login. No header/footer/nav, no Three.js,
 * no main.js — login should be instant.
 */
$cssVer  = @filemtime(PUBLIC_PATH . '/css/style.css') ?: time();
$cssHref = '/public/css/style.css?v=' . $cssVer;
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#0A1C28">
  <meta name="robots" content="noindex,nofollow">

  <?= $seo->render() ?>

  <link rel="icon" type="image/svg+xml" href="/public/images/favicon.svg">
  <link rel="preload" as="font" type="font/woff2" href="/public/fonts/ubuntu-400.woff2" crossorigin>

  <link rel="preload" href="<?= $cssHref ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="<?= $cssHref ?>"></noscript>
</head>
<body class="is-auth">
  <main id="main" role="main">
    <?= $content ?>
  </main>
</body>
</html>
