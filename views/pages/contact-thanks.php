<?php
/**
 * @var string|null $name
 * @var string|null $service
 */
$serviceLabel = $service ? ucwords(str_replace('-', ' ', $service)) : null;
?>

<section class="thanks">
  <div class="container thanks__inner">

    <div class="thanks__icon glass" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>

    <p class="section__eyebrow" data-reveal>Got it.</p>

    <h1 class="thanks__title" data-reveal data-reveal-delay="100">
      <?php if ($name): ?>
        Thanks, <?= htmlspecialchars($name) ?> — your message is in.
      <?php else: ?>
        Thanks — your message is in.
      <?php endif; ?>
    </h1>

    <p class="thanks__sub" data-reveal data-reveal-delay="200">
      <?php if ($serviceLabel): ?>
        We've logged your enquiry about <strong><?= htmlspecialchars($serviceLabel) ?></strong>
        and will reply within one business day, Pakistan time.
      <?php else: ?>
        We'll reply within one business day, Pakistan time. If it's urgent, you can also
        reach us directly on <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', SITE_PHONE)) ?>"><?= htmlspecialchars(SITE_PHONE) ?></a>.
      <?php endif; ?>
    </p>

    <ul class="thanks__next" data-reveal data-reveal-delay="300">
      <li><a class="btn btn--cta"   href="/">Back to home</a></li>
      <li><a class="btn btn--ghost" href="/services">Explore services</a></li>
      <li><a class="btn btn--ghost" href="/blog">Read the blog</a></li>
    </ul>

    <p class="thanks__small">
      Wrong email? Send a follow-up to
      <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>"><?= htmlspecialchars(SITE_EMAIL) ?></a>
      — we'll match it up.
    </p>

  </div>
</section>
