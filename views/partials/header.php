<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$navLinks = [
  ['href' => '/',         'label' => 'Home'],
  ['href' => '/services', 'label' => 'Services'],
  ['href' => '/about',    'label' => 'About'],
  ['href' => '/blog',     'label' => 'Blog'],
  ['href' => '/contact',  'label' => 'Contact'],
];
$isActive = fn(string $href): bool =>
  $href === '/' ? $currentPath === '/' : str_starts_with($currentPath, $href);

// Brand display is the literal brand name — NOT site_title (which is the SEO meta).
// site_title may be "Codentra — Code · Automate · Scale" for search engines;
// here we only ever show "Codentra" with the accent split.
$brandTitleHeader = 'Codentra';
$brandFirstHeader = mb_substr($brandTitleHeader, 0, 1);
$brandRestHeader  = mb_substr($brandTitleHeader, 1);
?>
<header class="site-header" data-header role="banner">
  <div class="container site-header__inner">

    <a class="brand" href="/" aria-label="<?= htmlspecialchars($brandTitleHeader) ?> — home">
      <span class="brand__mark"><?= htmlspecialchars($brandFirstHeader) ?></span><span class="brand__rest"><?= htmlspecialchars($brandRestHeader) ?></span>
    </a>

    <nav class="primary-nav" aria-label="Primary">
      <button
        class="nav-toggle"
        type="button"
        aria-expanded="false"
        aria-controls="primary-menu"
        aria-label="Toggle navigation menu"
        data-nav-toggle>
        <span class="nav-toggle__bar"></span>
        <span class="nav-toggle__bar"></span>
        <span class="nav-toggle__bar"></span>
      </button>

      <ul id="primary-menu" class="primary-nav__list" data-nav-menu>
        <?php foreach ($navLinks as $link): ?>
          <li class="primary-nav__item">
            <a
              class="primary-nav__link <?= $isActive($link['href']) ? 'is-active' : '' ?>"
              href="<?= htmlspecialchars($link['href']) ?>"
              <?= $isActive($link['href']) ? 'aria-current="page"' : '' ?>>
              <?= htmlspecialchars($link['label']) ?>
            </a>
          </li>
        <?php endforeach; ?>
        <li class="primary-nav__item primary-nav__item--cta">
          <a class="btn btn--cta" href="/contact">Get Started</a>
        </li>
      </ul>
    </nav>

  </div>
</header>
