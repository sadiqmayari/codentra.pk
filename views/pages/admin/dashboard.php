<?php /** @var string $pageTitle */ ?>

<div class="admin-placeholder glass">
  <div class="admin-placeholder__badge" aria-hidden="true">Phase 7</div>
  <h2 class="admin-placeholder__title">Dashboard coming soon.</h2>
  <p class="admin-placeholder__sub">
    Stat cards, lead trend chart, and a recent-activity feed will land in the
    next phase. Authentication and the admin shell are wired up — you're seeing
    this page because <code>AuthMiddleware</code> let you through.
  </p>

  <ul class="admin-placeholder__meta">
    <li><strong>Signed in as:</strong> <?= htmlspecialchars($_SESSION['user_name'] ?? '?') ?></li>
    <li><strong>Role:</strong> <?= htmlspecialchars($_SESSION['user_role'] ?? '?') ?></li>
    <li><strong>Session ID:</strong> <code><?= htmlspecialchars(substr(session_id(), 0, 12)) ?>…</code></li>
  </ul>
</div>
