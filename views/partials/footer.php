<?php
// Pull editable values from settings — fall back to constants if DB empty/unavailable
$contactEmail = SITE_EMAIL;
$contactPhone = SITE_PHONE;
$social = ['facebook' => '', 'instagram' => '', 'linkedin' => '', 'twitter' => '', 'github' => ''];

try {
    $settings = new \Models\Setting();
    $contactEmail = $settings->get('contact_email', SITE_EMAIL) ?: SITE_EMAIL;
    $contactPhone = $settings->get('contact_phone', SITE_PHONE) ?: SITE_PHONE;
    foreach (array_keys($social) as $k) {
        $social[$k] = (string) $settings->get('social_' . $k, '');
    }
} catch (\Throwable $e) {
    // DB not configured yet — use defaults
}

$year = date('Y');
?>
<footer class="site-footer" role="contentinfo">
  <div class="container">

    <div class="site-footer__grid">

      <div class="site-footer__col site-footer__col--brand">
        <a class="brand brand--footer" href="/" aria-label="Codentra — home">
          <span class="brand__mark">C</span><span class="brand__rest">odentra</span>
        </a>
        <p class="site-footer__tagline">Code · Automate · Scale</p>
        <p class="site-footer__blurb">
          Premium web development, Shopify, e-commerce management &amp; business automation —
          built for teams that want to ship faster without sacrificing quality.
        </p>
      </div>

      <div class="site-footer__col">
        <h2 class="site-footer__heading">Company</h2>
        <ul class="site-footer__list">
          <li><a href="/">Home</a></li>
          <li><a href="/about">About</a></li>
          <li><a href="/blog">Blog</a></li>
          <li><a href="/contact">Contact</a></li>
        </ul>
      </div>

      <div class="site-footer__col">
        <h2 class="site-footer__heading">Services</h2>
        <ul class="site-footer__list">
          <li><a href="/services#web-dev">Web Development</a></li>
          <li><a href="/services#shopify">Shopify</a></li>
          <li><a href="/services#ecommerce">E-commerce Management</a></li>
          <li><a href="/services#automation">Business Automation</a></li>
        </ul>
      </div>

      <div class="site-footer__col">
        <h2 class="site-footer__heading">Get in touch</h2>
        <ul class="site-footer__list site-footer__list--contact">
          <li>
            <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $contactPhone)) ?>">
              <?= htmlspecialchars($contactPhone) ?>
            </a>
          </li>
          <li>
            <a href="mailto:<?= htmlspecialchars($contactEmail) ?>">
              <?= htmlspecialchars($contactEmail) ?>
            </a>
          </li>
        </ul>

        <ul class="site-footer__social" aria-label="Social media">
          <?php
          $socialLabels = [
            'linkedin'  => 'LinkedIn',
            'twitter'   => 'Twitter',
            'instagram' => 'Instagram',
            'facebook'  => 'Facebook',
            'github'    => 'GitHub',
          ];
          foreach ($socialLabels as $key => $label):
            $url = $social[$key] ?? '';
            if (!$url) continue;
          ?>
            <li>
              <a href="<?= htmlspecialchars($url) ?>" rel="noopener noreferrer" target="_blank" aria-label="<?= $label ?>">
                <?= $label ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>

    <div class="site-footer__bottom">
      <p class="site-footer__copy">© <?= $year ?> Codentra. All rights reserved.</p>
      <ul class="site-footer__legal">
        <li><a href="/privacy">Privacy</a></li>
        <li><a href="/terms">Terms</a></li>
      </ul>
    </div>

  </div>
</footer>
