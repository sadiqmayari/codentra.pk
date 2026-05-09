<?php /** @var string $lastUpdated */ ?>

<header class="page-hero">
  <div class="container">
    <p class="section__eyebrow" data-reveal>Legal</p>
    <h1 class="page-hero__title" data-reveal data-reveal-delay="100">Terms of Service</h1>
    <p class="page-hero__sub" data-reveal data-reveal-delay="200">
      Last updated: <?= htmlspecialchars($lastUpdated) ?>
    </p>
  </div>
</header>

<section class="section legal">
  <div class="container">
    <article class="prose legal__body">

      <h2>1. Agreement</h2>
      <p>
        These Terms of Service (&quot;Terms&quot;) govern your use of
        <a href="<?= htmlspecialchars(SITE_URL) ?>"><?= htmlspecialchars(SITE_URL) ?></a>
        (the &quot;site&quot;) and any services Codentra (&quot;we&quot;, &quot;us&quot;) provides
        through it. By using the site, you agree to these Terms.
      </p>

      <h2>2. Use of the site</h2>
      <p>You agree not to:</p>
      <ul>
        <li>Use the site for any unlawful purpose or in violation of these Terms.</li>
        <li>Attempt to gain unauthorised access to any part of the site, its systems, or its data.</li>
        <li>Probe, scan, or test the vulnerability of the site without prior written consent.</li>
        <li>Send automated traffic, spam, or abusive submissions through forms or APIs.</li>
        <li>Use the site to transmit malware, viruses, or any harmful code.</li>
      </ul>

      <h2>3. Intellectual property</h2>
      <p>
        All site content — code, copy, design, logos, and imagery — is owned by Codentra or
        its licensors, and is protected by applicable intellectual-property laws. You may not
        copy, redistribute, or create derivative works without prior written permission.
      </p>

      <h2>4. Services and engagements</h2>
      <p>
        Specific services we deliver are governed by a separate written agreement (Statement
        of Work or contract). In case of conflict between these Terms and a signed agreement,
        the signed agreement controls.
      </p>

      <h2>5. Third-party links</h2>
      <p>
        The site may link to third-party websites or services. We don't control and aren't
        responsible for the content, privacy practices, or availability of those third parties.
      </p>

      <h2>6. Disclaimer</h2>
      <p>
        The site is provided on an &quot;as is&quot; and &quot;as available&quot; basis without
        warranties of any kind, whether express or implied, including merchantability, fitness
        for a particular purpose, or non-infringement.
      </p>

      <h2>7. Limitation of liability</h2>
      <p>
        To the fullest extent permitted by law, Codentra is not liable for any indirect,
        incidental, special, consequential, or punitive damages arising out of or related to
        your use of the site, even if we've been advised of the possibility of such damages.
        Our total liability for any claim related to the site is limited to USD 100 or the
        amount you've paid us in the prior 12 months, whichever is greater.
      </p>

      <h2>8. Indemnification</h2>
      <p>
        You agree to indemnify and hold Codentra harmless from any claims, losses, or expenses
        arising from your breach of these Terms or misuse of the site.
      </p>

      <h2>9. Termination</h2>
      <p>
        We may suspend or terminate your access to the site at any time, with or without
        notice, for any reason, including violation of these Terms.
      </p>

      <h2>10. Governing law</h2>
      <p>
        These Terms are governed by the laws of the Islamic Republic of Pakistan. Disputes
        arising under these Terms are subject to the exclusive jurisdiction of the courts of
        Pakistan, unless a separate written agreement specifies otherwise.
      </p>

      <h2>11. Changes</h2>
      <p>
        We may update these Terms from time to time. Continued use of the site after changes
        constitutes acceptance of the updated Terms.
      </p>

      <h2>12. Contact</h2>
      <p>
        Questions about these Terms:
        <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>"><?= htmlspecialchars(SITE_EMAIL) ?></a>
        · <?= htmlspecialchars(SITE_PHONE) ?>.
      </p>

    </article>
  </div>
</section>
