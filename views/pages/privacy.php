<?php /** @var string $lastUpdated */ ?>

<header class="page-hero">
  <div class="container">
    <p class="section__eyebrow" data-reveal>Legal</p>
    <h1 class="page-hero__title" data-reveal data-reveal-delay="100">Privacy Policy</h1>
    <p class="page-hero__sub" data-reveal data-reveal-delay="200">
      Last updated: <?= htmlspecialchars($lastUpdated) ?>
    </p>
  </div>
</header>

<section class="section legal">
  <div class="container">
    <article class="prose legal__body">

      <h2>1. Who we are</h2>
      <p>
        Codentra (&quot;we&quot;, &quot;us&quot;, &quot;our&quot;) is a web development and
        automation agency operating from Pakistan. Our website is
        <a href="<?= htmlspecialchars(SITE_URL) ?>"><?= htmlspecialchars(SITE_URL) ?></a>.
        For privacy questions, contact
        <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>"><?= htmlspecialchars(SITE_EMAIL) ?></a>.
      </p>

      <h2>2. Information we collect</h2>
      <p>We collect only what we need to respond to your enquiry and operate the site:</p>
      <ul>
        <li><strong>Information you give us</strong> — name, email, phone, company, and message content when you fill out the contact form.</li>
        <li><strong>Technical information</strong> — IP address, browser user-agent, and basic request metadata, used for security (rate-limiting and abuse prevention) and aggregate analytics.</li>
        <li><strong>Cookies</strong> — a single PHP session cookie used for CSRF protection. We don't use third-party tracking cookies.</li>
      </ul>

      <h2>3. How we use your information</h2>
      <ul>
        <li>To respond to your enquiry and deliver services you've requested.</li>
        <li>To detect and prevent abusive or fraudulent activity on the site.</li>
        <li>To improve the site's performance, content, and security.</li>
        <li>To send service-related communications. We don't send marketing email without explicit opt-in.</li>
      </ul>

      <h2>4. Legal bases (GDPR / similar regimes)</h2>
      <p>
        We rely on (a) <em>consent</em> when you submit a contact form, (b) <em>legitimate interest</em>
        for security and analytics, and (c) <em>contract</em> when delivering services to clients.
      </p>

      <h2>5. Sharing</h2>
      <p>
        We don't sell personal data. We share information only with:
      </p>
      <ul>
        <li>Hosting and infrastructure providers (e.g., Hostinger) under contract.</li>
        <li>Email delivery providers when sending notifications.</li>
        <li>Authorities when required by law.</li>
      </ul>

      <h2>6. Data retention</h2>
      <p>
        Contact-form submissions are retained for up to 24 months unless you ask us to delete
        them sooner. Server logs are typically retained for 30–90 days.
      </p>

      <h2>7. Your rights</h2>
      <p>
        Depending on your jurisdiction, you may have the right to access, correct, delete, or
        port your personal data, and to object to certain processing. To exercise these
        rights, email <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>"><?= htmlspecialchars(SITE_EMAIL) ?></a>.
      </p>

      <h2>8. Security</h2>
      <p>
        We use HTTPS, hashed passwords (Argon2id), CSRF protection, parameterised SQL, rate
        limiting, and strict security headers. No system is 100% secure; we will notify
        affected users of a confirmed breach within applicable legal timeframes.
      </p>

      <h2>9. Children</h2>
      <p>
        Our services are not directed to children under 16, and we do not knowingly collect
        personal data from them.
      </p>

      <h2>10. Changes</h2>
      <p>
        We may update this policy. The &quot;last updated&quot; date at the top reflects the
        latest revision. Material changes will be communicated via the site or email.
      </p>

      <h2>11. Contact</h2>
      <p>
        Questions, requests, or complaints:
        <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>"><?= htmlspecialchars(SITE_EMAIL) ?></a>
        · <?= htmlspecialchars(SITE_PHONE) ?>.
      </p>

    </article>
  </div>
</section>
