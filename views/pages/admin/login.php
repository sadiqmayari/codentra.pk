<?php
/**
 * @var array|null $flash
 * @var array      $old
 * @var string     $return
 */
$old   = $old ?? [];
$email = htmlspecialchars($old['email'] ?? '');
?>
<section class="auth">
  <div class="auth__panel glass">

    <a href="/" class="brand brand--auth" aria-label="Codentra — home">
      <span class="brand__mark">C</span><span class="brand__rest">odentra</span>
    </a>
    <p class="auth__caption">Admin</p>

    <h1 class="auth__title">Sign in</h1>
    <p class="auth__sub">Enter your credentials to access the dashboard.</p>

    <?php if ($flash): ?>
      <div class="alert alert--<?= htmlspecialchars($flash['type']) ?>" role="alert">
        <?= htmlspecialchars($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <form class="auth__form" action="/admin/login" method="POST" novalidate>
      <?= \Core\Csrf::field() ?>
      <?php if ($return !== ''): ?>
        <input type="hidden" name="return" value="<?= htmlspecialchars($return) ?>">
      <?php endif; ?>

      <label class="form-field">
        <span class="form-field__label">Email</span>
        <input
          type="email"
          name="email"
          autocomplete="username"
          required
          maxlength="160"
          value="<?= $email ?>"
          autofocus>
      </label>

      <label class="form-field">
        <span class="form-field__label">Password</span>
        <input
          type="password"
          name="password"
          autocomplete="current-password"
          required
          minlength="8"
          maxlength="200">
      </label>

      <label class="auth__remember">
        <input type="checkbox" name="remember" value="1">
        <span>Remember me on this device</span>
      </label>

      <button type="submit" class="btn btn--primary auth__submit">Sign in</button>
    </form>

    <p class="auth__back">
      <a href="/">&larr; Back to site</a>
    </p>

  </div>
</section>
