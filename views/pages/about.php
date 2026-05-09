<?php /** @var array $values */ ?>

<header class="page-hero">
  <div class="container">
    <p class="section__eyebrow" data-reveal>About Codentra</p>
    <h1 class="page-hero__title" data-reveal data-reveal-delay="100">
      A small, senior team building the systems your business runs on.
    </h1>
    <p class="page-hero__sub" data-reveal data-reveal-delay="200">
      We started Codentra to do the kind of work agencies promise but rarely deliver:
      premium engineering, transparent pricing, and a partnership that lasts past launch day.
    </p>
  </div>
</header>


<section class="section story" aria-labelledby="story-title">
  <div class="container story__grid">

    <div class="story__copy" data-reveal>
      <p class="section__eyebrow">Story</p>
      <h2 id="story-title" class="section__title">Founded on the simple belief that good software is rarer than it should be.</h2>
      <p>
        Codentra was started by engineers who were tired of seeing the same patterns:
        slow sites that nobody profiled, e-commerce stores leaking revenue at predictable
        bottlenecks, and businesses doing in spreadsheets what should have been an integration.
      </p>
      <p>
        We built the agency we wanted to hire — small enough to give every project genuine
        attention, senior enough to make the right architectural calls early, and disciplined
        enough to ship on time without cutting corners on security or performance.
      </p>
      <p>
        We're based in Pakistan, work with teams worldwide, and treat our clients' codebases
        as carefully as we treat our own.
      </p>
    </div>

    <aside class="story__mission glass" data-reveal data-reveal-delay="100">
      <p class="section__eyebrow">Mission</p>
      <h2 class="story__mission-title">Code <span class="dot">·</span> Automate <span class="dot">·</span> Scale</h2>
      <p>
        Three words that map directly to how we deliver value: <strong>code</strong> the
        product, <strong>automate</strong> the operations, and <strong>scale</strong> what works.
        Every engagement moves through one or more of these stages.
      </p>
    </aside>

  </div>
</section>


<section class="section values" aria-labelledby="values-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow">Values</p>
      <h2 id="values-title" class="section__title">How we work — and what we say no to.</h2>
    </header>

    <ul class="values-grid">
      <?php foreach ($values as $i => $v): ?>
        <li class="value-card glass" data-reveal data-reveal-delay="<?= $i * 60 ?>">
          <h3 class="value-card__title"><?= htmlspecialchars($v['title']) ?></h3>
          <p class="value-card__desc"><?= htmlspecialchars($v['desc']) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>


<section class="section team" aria-labelledby="team-title">
  <div class="container">
    <header class="section__header" data-reveal>
      <p class="section__eyebrow">Team</p>
      <h2 id="team-title" class="section__title">Senior engineers and operators, end to end.</h2>
      <p class="section__lead">
        Full team page coming soon. In the meantime, you'll work directly with the engineer
        leading your project — no account-management layer in between.
      </p>
    </header>
  </div>
</section>


<section class="section lead-cta" aria-labelledby="about-cta">
  <div class="container">
    <div class="lead-cta__panel glass" data-reveal>
      <div class="lead-cta__copy">
        <h2 id="about-cta" class="section__title">Want to work together?</h2>
        <p class="section__lead">We take on a small number of new projects each quarter. Get on the list.</p>
      </div>
      <div class="lead-cta__copy" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
        <a class="btn btn--cta" href="/contact">Start a project</a>
        <a class="btn btn--ghost" href="/services">See services</a>
      </div>
    </div>
  </div>
</section>
