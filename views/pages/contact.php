<?php
/**
 * @var array|null $flash   ['type'=>'success'|'error', 'msg'=>string] or null
 * @var array      $old     Old form values to repopulate
 * @var array      $errors  Field => message
 */
$old    = $old    ?? [];
$errors = $errors ?? [];
$preselectedService = htmlspecialchars($_GET['service'] ?? ($old['service'] ?? ''));
$v = fn(string $f, string $default = '') => htmlspecialchars((string)($old[$f] ?? $default));
$err = fn(string $f) => isset($errors[$f]) ? '<span class="form-field__error">' . htmlspecialchars($errors[$f]) . '</span>' : '';
?>

<header class="page-hero">
  <div class="container">
    <p class="section__eyebrow" data-reveal>Contact</p>
    <h1 class="page-hero__title" data-reveal data-reveal-delay="100">
      Tell us about your project.
    </h1>
    <p class="page-hero__sub" data-reveal data-reveal-delay="200">
      Send the form below or email <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>"><?= htmlspecialchars(SITE_EMAIL) ?></a>
      directly. We reply within one business day, Pakistan time.
    </p>
  </div>
</header>


<section id="contact-form" class="section contact" aria-labelledby="contact-title">
  <div class="container contact__grid">

    <aside class="contact__aside" data-reveal>
      <h2 id="contact-title" class="visually-hidden">Get in touch</h2>

      <ul class="contact__details">
        <li>
          <span class="contact__label">Email</span>
          <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>"><?= htmlspecialchars(SITE_EMAIL) ?></a>
        </li>
        <li>
          <span class="contact__label">Phone</span>
          <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', SITE_PHONE)) ?>"><?= htmlspecialchars(SITE_PHONE) ?></a>
        </li>
        <li>
          <span class="contact__label">Hours</span>
          <span>Mon–Fri, 09:00–18:00 PKT</span>
        </li>
      </ul>

      <p class="contact__note">
        Prefer a quick call? Drop your number in the form and we'll set up a 20-minute
        intro at a time that works for you.
      </p>
    </aside>

    <div class="contact__form-wrap" data-reveal data-reveal-delay="100">

      <?php if ($flash): ?>
        <div class="alert alert--<?= htmlspecialchars($flash['type']) ?>" role="status">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <form class="contact-form" action="/contact" method="POST" novalidate>
        <?= \Core\Csrf::field() ?>
        <input type="hidden" name="source" value="contact-page">

        <!-- Honeypot — must remain empty. Hidden from humans + screen readers. -->
        <div class="hp-field" aria-hidden="true">
          <label>Website (leave blank)
            <input type="text" name="website" tabindex="-1" autocomplete="off">
          </label>
        </div>

        <div class="form-row">
          <label class="form-field">
            <span class="form-field__label">Your name <abbr title="required">*</abbr></span>
            <input
              type="text"
              name="name"
              required
              minlength="2"
              maxlength="120"
              autocomplete="name"
              value="<?= $v('name') ?>"
              <?= isset($errors['name']) ? 'aria-invalid="true"' : '' ?>>
            <?= $err('name') ?>
          </label>

          <label class="form-field">
            <span class="form-field__label">Email <abbr title="required">*</abbr></span>
            <input
              type="email"
              name="email"
              required
              maxlength="160"
              autocomplete="email"
              value="<?= $v('email') ?>"
              <?= isset($errors['email']) ? 'aria-invalid="true"' : '' ?>>
            <?= $err('email') ?>
          </label>
        </div>

        <div class="form-row">
          <label class="form-field">
            <span class="form-field__label">Phone</span>
            <input
              type="tel"
              name="phone"
              maxlength="40"
              autocomplete="tel"
              pattern="[0-9 +()\-]{6,40}"
              value="<?= $v('phone') ?>"
              <?= isset($errors['phone']) ? 'aria-invalid="true"' : '' ?>>
            <?= $err('phone') ?>
          </label>

          <label class="form-field">
            <span class="form-field__label">Company</span>
            <input
              type="text"
              name="company"
              maxlength="160"
              autocomplete="organization"
              value="<?= $v('company') ?>">
            <?= $err('company') ?>
          </label>
        </div>

        <div class="form-row">
          <label class="form-field">
            <span class="form-field__label">Service</span>
            <select name="service">
              <option value="">— choose one —</option>
              <option value="web-dev"        <?= $preselectedService === 'web-dev'        ? 'selected' : '' ?>>Web Development</option>
              <option value="shopify"        <?= $preselectedService === 'shopify'        ? 'selected' : '' ?>>Shopify</option>
              <option value="ecommerce-mgmt" <?= $preselectedService === 'ecommerce-mgmt' || $preselectedService === 'ecommerce' ? 'selected' : '' ?>>E-commerce Management</option>
              <option value="automation"     <?= $preselectedService === 'automation'     ? 'selected' : '' ?>>Business Automation</option>
              <option value="other"          <?= $preselectedService === 'other'          ? 'selected' : '' ?>>Something else</option>
            </select>
            <?= $err('service') ?>
          </label>

          <label class="form-field">
            <span class="form-field__label">Budget (USD)</span>
            <select name="budget">
              <option value="">— optional —</option>
              <?php foreach (['< $5k', '$5k–$15k', '$15k–$50k', '$50k+', 'Not sure yet'] as $b): ?>
                <option value="<?= htmlspecialchars($b) ?>" <?= ($old['budget'] ?? '') === $b ? 'selected' : '' ?>>
                  <?= htmlspecialchars($b) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
        </div>

        <label class="form-field">
          <span class="form-field__label">Message <abbr title="required">*</abbr></span>
          <textarea
            name="message"
            rows="6"
            required
            minlength="10"
            maxlength="2000"
            placeholder="Tell us what you're trying to build, the timeline, and any context that helps."
            <?= isset($errors['message']) ? 'aria-invalid="true"' : '' ?>><?= $v('message') ?></textarea>
          <?= $err('message') ?>
        </label>

        <div class="contact-form__actions">
          <button type="submit" class="btn btn--cta">Send message</button>
          <p class="contact-form__legal">
            By sending this form, you agree to our
            <a href="/privacy">privacy policy</a>.
          </p>
        </div>
      </form>

    </div>
  </div>
</section>
