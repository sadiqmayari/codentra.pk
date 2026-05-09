<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$navLinks = [
  ['href' => '/admin/dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
  ['href' => '/admin/leads',     'label' => 'Leads',     'icon' => 'inbox'],
  ['href' => '/admin/posts',     'label' => 'Blog Posts','icon' => 'document'],
  ['href' => '/admin/settings',  'label' => 'Settings',  'icon' => 'cog'],
];
$icons = [
  'grid'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
  'inbox'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>',
  'document' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/></svg>',
  'cog'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
];
$isActive = fn(string $href): bool => $currentPath === $href || str_starts_with($currentPath ?? '', $href . '/');
?>
<aside class="admin-sidebar" aria-label="Admin navigation">
  <div class="admin-sidebar__brand">
    <a href="/admin/dashboard" class="brand brand--admin" aria-label="Codentra admin">
      <span class="brand__mark">C</span><span class="brand__rest">odentra</span>
    </a>
    <p class="admin-sidebar__caption">Admin</p>
  </div>

  <nav class="admin-sidebar__nav">
    <ul>
      <?php foreach ($navLinks as $link): ?>
        <li>
          <a
            class="admin-sidebar__link <?= $isActive($link['href']) ? 'is-active' : '' ?>"
            href="<?= htmlspecialchars($link['href']) ?>"
            <?= $isActive($link['href']) ? 'aria-current="page"' : '' ?>>
            <span class="admin-sidebar__icon" aria-hidden="true"><?= $icons[$link['icon']] ?? '' ?></span>
            <span><?= htmlspecialchars($link['label']) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <form class="admin-sidebar__logout" action="/admin/logout" method="POST">
    <?= \Core\Csrf::field() ?>
    <button type="submit" class="admin-sidebar__logout-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      <span>Sign out</span>
    </button>
  </form>
</aside>
