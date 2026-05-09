<?php
$pageTitle = $pageTitle ?? null;
$titleText = $pageTitle ?: 'Admin';

$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? '';
$initial  = strtoupper(substr((string) $userName, 0, 1) ?: 'U');
?>
<header class="admin-topbar" role="banner">
  <div class="admin-topbar__title">
    <h1 class="admin-topbar__h1"><?= htmlspecialchars($titleText) ?></h1>
  </div>

  <div class="admin-topbar__user" data-user-menu>
    <button
      type="button"
      class="admin-topbar__avatar"
      aria-haspopup="menu"
      aria-expanded="false"
      data-user-menu-toggle>
      <span class="admin-topbar__avatar-circle" aria-hidden="true"><?= htmlspecialchars($initial) ?></span>
      <span class="admin-topbar__user-meta">
        <span class="admin-topbar__user-name"><?= htmlspecialchars($userName) ?></span>
        <?php if ($userRole !== ''): ?>
          <span class="admin-topbar__user-role"><?= htmlspecialchars(ucfirst($userRole)) ?></span>
        <?php endif; ?>
      </span>
    </button>

    <div class="admin-topbar__menu" role="menu">
      <p class="admin-topbar__menu-head">
        <strong><?= htmlspecialchars($userName) ?></strong><br>
        <span><?= htmlspecialchars(ucfirst((string) $userRole)) ?></span>
      </p>
      <form action="/admin/logout" method="POST" class="admin-topbar__menu-logout">
        <?= \Core\Csrf::field() ?>
        <button type="submit" role="menuitem">Sign out</button>
      </form>
    </div>
  </div>
</header>

<script>
  (function () {
    var menu   = document.querySelector('[data-user-menu]');
    var toggle = document.querySelector('[data-user-menu-toggle]');
    if (!menu || !toggle) return;
    toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = menu.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) {
      if (!menu.contains(e.target)) {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  })();
</script>
