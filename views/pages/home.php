<?php
/** @var array $services */
/** @var array $testimonials */
?>

<!-- ── Hero ─────────────────────────────────────────────────────────────── -->
<section class="hero" aria-labelledby="hero-title">
  <canvas id="hero-canvas" class="hero__canvas" aria-hidden="true"></canvas>
  <div class="hero__overlay" aria-hidden="true"></div>

  <div class="container hero__inner">
    <p class="hero__eyebrow" data-reveal>Web · Shopify · Automation</p>
    <h1 id="hero-title" class="hero__title" data-reveal data-reveal-delay="100">
      Code <span class="dot">·</span> Automate <span class="dot">·</span> Scale
    </h1>
    <p class="hero__sub" data-reveal data-reveal-delay="200">
      Codentra is a Pakistan-based agency engineering premium websites, Shopify storefronts,
      and automation systems for teams that want to ship faster — without sacrificing quality.
    </p>
    <div class="hero__cta" data-reveal data-reveal-delay="300">
      <a class="btn btn--cta" href="/contact">Start a project</a>
      <a class="btn btn--ghost" href="/services">Explore services</a>
    </div>

    <div class="hero__stats" data-reveal data-reveal-delay="420">
      <div class="hero__stat">
        <span class="hero__stat-value">50+</span>
        <span class="hero__stat-label">Projects delivered</span>
      </div>
      <div class="hero__stat">
        <span class="hero__stat-value">95+</span>
        <span class="hero__stat-label">Lighthouse score</span>
      </div>
      <div class="hero__stat">
        <span class="hero__stat-value">&lt; 1 day</span>
        <span class="hero__stat-label">Response time</span>
      </div>
    </div>
  </div>

  <div class="hero__scroll-cue" aria-hidden="true">
    <span></span>
  </div>
</section>


<!-- ── Services Preview ─────────────────────────────────────────────────── -->
<section class="section services-preview" aria-labelledby="services-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow">What we do</p>
      <h2 id="services-title" class="section__title">Four services. One outcome — growth.</h2>
      <p class="section__lead">
        Each engagement is built around measurable business results, not deliverable checklists.
      </p>
    </header>

    <ul class="card-grid">
      <?php foreach ($services as $i => $svc): ?>
        <li class="card glass" data-reveal data-reveal-delay="<?= $i * 80 ?>">
          <div class="card__icon" aria-hidden="true"><?= $svc['icon'] ?></div>
          <h3 class="card__title"><?= htmlspecialchars($svc['title']) ?></h3>
          <p class="card__desc"><?= htmlspecialchars($svc['desc']) ?></p>
          <a class="card__link" href="<?= htmlspecialchars($svc['href']) ?>">
            Learn more <span aria-hidden="true">&rarr;</span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>


<!-- ── Why Us ───────────────────────────────────────────────────────────── -->
<section class="section why-us" aria-labelledby="why-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow">Why Codentra</p>
      <h2 id="why-title" class="section__title">Speed, quality, and scale — in that order.</h2>
    </header>

    <ul class="why-grid">
      <li class="why-card" data-reveal>
        <div class="why-card__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
        </div>
        <h3>Speed</h3>
        <p>Lighthouse 95+ as a baseline. We optimize fonts, images, and rendering before we write a single feature.</p>
      </li>
      <li class="why-card" data-reveal data-reveal-delay="100">
        <div class="why-card__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 4v6c0 5-3.5 9-9 10-5.5-1-9-5-9-10V6l9-4z"/><path d="M9 12l2 2 4-4"/></svg>
        </div>
        <h3>Quality</h3>
        <p>Type-safe PHP, prepared statements, CSRF on every form, Argon2id auth, secure headers — by default.</p>
      </li>
      <li class="why-card" data-reveal data-reveal-delay="200">
        <div class="why-card__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l4-4 4 4 4-4 6 6"/><path d="M14 8h7v7"/></svg>
        </div>
        <h3>Scale</h3>
        <p>From your first 100 orders to your first 100,000 — automation, caching, and queues that grow with you.</p>
      </li>
    </ul>
  </div>
</section>


<!-- ── Process ──────────────────────────────────────────────────────────── -->
<section class="section process" aria-labelledby="process-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow">How it works</p>
      <h2 id="process-title" class="section__title">A four-step path to launch.</h2>
    </header>

    <ol class="process-timeline">
      <li class="process-step" data-reveal>
        <span class="process-step__num">01</span>
        <h3>Discover</h3>
        <p>Goals, audience, success metrics. We translate your business outcomes into a technical brief.</p>
      </li>
      <li class="process-step" data-reveal data-reveal-delay="100">
        <span class="process-step__num">02</span>
        <h3>Design</h3>
        <p>Interactive prototypes — copy, layout, motion — reviewed before a single line of production code.</p>
      </li>
      <li class="process-step" data-reveal data-reveal-delay="200">
        <span class="process-step__num">03</span>
        <h3>Build</h3>
        <p>Weekly demos. CI on every push. You see progress in production-shaped staging from week one.</p>
      </li>
      <li class="process-step" data-reveal data-reveal-delay="300">
        <span class="process-step__num">04</span>
        <h3>Scale</h3>
        <p>Launch is the start. We monitor, iterate, and automate growth loops post-launch.</p>
      </li>
    </ol>
  </div>
</section>


<!-- ── Mini Lead Form ───────────────────────────────────────────────────── -->
<section class="section lead-cta" aria-labelledby="lead-title">
  <div class="container">
    <div class="lead-cta__panel glass" data-reveal>
      <div class="lead-cta__copy">
        <h2 id="lead-title" class="section__title">Have a project in mind?</h2>
        <p class="section__lead">Tell us a little about it and we'll reply within one business day.</p>
      </div>

      <form class="lead-form" action="/contact" method="POST" novalidate>
        <?= \Core\Csrf::field() ?>
        <input type="hidden" name="source" value="home-mini-form">

        <div class="form-row">
          <label class="form-field">
            <span class="form-field__label">Your name</span>
            <input type="text" name="name" required minlength="2" maxlength="120" autocomplete="name">
          </label>
          <label class="form-field">
            <span class="form-field__label">Email</span>
            <input type="email" name="email" required maxlength="160" autocomplete="email">
          </label>
        </div>

        <label class="form-field">
          <span class="form-field__label">Message</span>
          <textarea name="message" rows="3" required minlength="10" maxlength="2000"
                    placeholder="What are you trying to build?"></textarea>
        </label>

        <button type="submit" class="btn btn--cta">Send message</button>
      </form>
    </div>
  </div>
</section>


<!-- ── Testimonials ─────────────────────────────────────────────────────── -->
<section class="section testimonials" aria-labelledby="testimonials-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow">Trusted by founders</p>
      <h2 id="testimonials-title" class="section__title">Words from the people we work with.</h2>
    </header>

    <ul class="card-grid card-grid--3">
      <?php foreach ($testimonials as $i => $t):
        $initials = strtoupper(mb_substr(trim($t['name']), 0, 1));
      ?>
        <li class="testimonial glass" data-reveal data-reveal-delay="<?= $i * 100 ?>">
          <div class="testimonial__stars" aria-label="5 out of 5 stars">★★★★★</div>
          <p class="testimonial__quote"><?= htmlspecialchars($t['quote']) ?></p>
          <footer class="testimonial__by">
            <div class="testimonial__avatar" aria-hidden="true"><?= $initials ?></div>
            <div class="testimonial__attribution">
              <strong><?= htmlspecialchars($t['name']) ?></strong>
              <span><?= htmlspecialchars($t['role']) ?></span>
            </div>
          </footer>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
