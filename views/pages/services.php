<?php /** @var array $services */ ?>

<header class="page-hero">
  <div class="container">
    <p class="section__eyebrow" data-reveal>Services</p>
    <h1 class="page-hero__title" data-reveal data-reveal-delay="100">
      Outcomes-first engineering across the full e-commerce stack.
    </h1>
    <p class="page-hero__sub" data-reveal data-reveal-delay="200">
      Each engagement is scoped around the metric you want to move — revenue, conversion,
      or hours saved — not a checklist of deliverables.
    </p>
  </div>
</header>

<?php foreach ($services as $i => $svc): ?>
  <section
    id="<?= htmlspecialchars($svc['anchor']) ?>"
    class="service-block <?= $i % 2 === 1 ? 'service-block--alt' : '' ?>"
    aria-labelledby="<?= htmlspecialchars($svc['anchor']) ?>-title">
    <div class="container service-block__inner">

      <header class="service-block__head" data-reveal>
        <p class="section__eyebrow"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></p>
        <h2 id="<?= htmlspecialchars($svc['anchor']) ?>-title" class="service-block__title">
          <?= htmlspecialchars($svc['title']) ?>
        </h2>
        <p class="service-block__lede"><?= htmlspecialchars($svc['lede']) ?></p>
      </header>

      <div class="service-block__body">
        <div class="service-block__col" data-reveal>
          <h3 class="service-block__sub">What you get</h3>
          <ul class="service-block__outcomes">
            <?php foreach ($svc['outcomes'] as $o): ?>
              <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <span><?= htmlspecialchars($o) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="service-block__col" data-reveal data-reveal-delay="100">
          <h3 class="service-block__sub">Tech stack</h3>
          <ul class="tech-chips">
            <?php foreach ($svc['stack'] as $t): ?>
              <li class="tech-chip"><?= htmlspecialchars($t) ?></li>
            <?php endforeach; ?>
          </ul>

          <a class="btn btn--primary service-block__cta" href="/contact?service=<?= urlencode($svc['anchor']) ?>">
            Discuss a <?= htmlspecialchars($svc['title']) ?> project
          </a>
        </div>
      </div>

    </div>
  </section>
<?php endforeach; ?>


<section class="section lead-cta" aria-labelledby="services-cta">
  <div class="container">
    <div class="lead-cta__panel glass" data-reveal>
      <div class="lead-cta__copy">
        <h2 id="services-cta" class="section__title">Not sure which service fits?</h2>
        <p class="section__lead">Tell us your goal in one sentence — we'll point you to the right starting point.</p>
      </div>
      <div class="lead-cta__copy" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
        <a class="btn btn--cta" href="/contact">Start a conversation</a>
        <a class="btn btn--ghost" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', SITE_PHONE)) ?>">Call <?= htmlspecialchars(SITE_PHONE) ?></a>
      </div>
    </div>
  </div>
</section>
