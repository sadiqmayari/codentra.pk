<?php
/**
 * @var array $filters
 * @var array{rows: array, total: int, pages: int, page: int, per_page: int} $result
 * @var array $statusCounts
 * @var array $services
 * @var array $statuses
 */
$f          = $filters;
$activeStatus  = $f['status']  ?? '';
$activeService = $f['service'] ?? '';

$badgeFor = fn(string $s): string => 'badge badge--' . preg_replace('/[^a-z]/', '', strtolower($s));

// Build a query-string for pagination/sort links that preserves filters.
$qs = function (array $overrides = []) use ($f): string {
    $merged = array_merge([
        'q'       => $f['q'],       'status'  => $f['status'],
        'service' => $f['service'], 'from'    => $f['from'],   'to' => $f['to'],
        'sort'    => $f['sort'],    'dir'     => $f['dir'],
        'page'    => $f['page'],
    ], $overrides);
    $merged = array_filter($merged, fn($v) => $v !== '' && $v !== null);
    return $merged ? '?' . http_build_query($merged) : '';
};

// Sort header link — toggles dir if clicking the active column.
$sortLink = function (string $col, string $label) use ($f, $qs): string {
    $isActive = ($f['sort'] === $col);
    $newDir   = $isActive ? ($f['dir'] === 'asc' ? 'desc' : 'asc') : 'asc';
    $arrow    = $isActive ? ($f['dir'] === 'asc' ? '↑' : '↓') : '';
    $href     = $qs(['sort' => $col, 'dir' => $newDir, 'page' => 1]);
    $cls      = 'leads-th-sort' . ($isActive ? ' is-active' : '');
    return "<a class=\"{$cls}\" href=\"" . htmlspecialchars($href, ENT_QUOTES) . "\">"
         . htmlspecialchars($label) . " <span class=\"leads-th-arrow\" aria-hidden=\"true\">{$arrow}</span></a>";
};

$hasFilter = $f['q'] !== '' || $f['status'] !== '' || $f['service'] !== '' || $f['from'] !== '' || $f['to'] !== '';
$total     = $result['total'];
$showFrom  = $total === 0 ? 0 : (($result['page'] - 1) * $result['per_page'] + 1);
$showTo    = min($total, $showFrom + $result['per_page'] - 1);
?>

<header class="leads-header">
  <div>
    <h2 class="dash-header__title">Leads <span class="leads-header__count">(<?= number_format($total) ?> total)</span></h2>
  </div>
  <a class="btn btn--ghost btn--small" href="/admin/leads/export<?= htmlspecialchars($qs(['page' => null]), ENT_QUOTES) ?>">
    Export CSV
  </a>
</header>


<!-- ── Filter bar ───────────────────────────────────────────────────────── -->
<form class="leads-filter" method="GET" action="/admin/leads" data-leads-filter>
  <input
    class="leads-filter__search"
    type="search"
    name="q"
    placeholder="Search name, email, or company…"
    value="<?= htmlspecialchars((string) $f['q']) ?>"
    autocomplete="off"
    data-leads-search>

  <select class="leads-filter__select" name="service">
    <option value="">All services</option>
    <?php foreach ($services as $svc): ?>
      <option value="<?= htmlspecialchars($svc) ?>" <?= $activeService === $svc ? 'selected' : '' ?>>
        <?= htmlspecialchars(ucwords(str_replace('-', ' ', $svc))) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <input class="leads-filter__date" type="date" name="from" value="<?= htmlspecialchars((string) $f['from']) ?>" aria-label="From">
  <input class="leads-filter__date" type="date" name="to"   value="<?= htmlspecialchars((string) $f['to']) ?>"   aria-label="To">

  <input type="hidden" name="sort" value="<?= htmlspecialchars((string) $f['sort']) ?>">
  <input type="hidden" name="dir"  value="<?= htmlspecialchars((string) $f['dir']) ?>">

  <noscript><button type="submit" class="btn btn--ghost btn--small">Apply</button></noscript>

  <?php if ($hasFilter): ?>
    <a class="leads-filter__clear" href="/admin/leads">Clear filters</a>
  <?php endif; ?>
</form>

<!-- Status pills — separate so a click navigates instantly without
     submitting the search form (server reads ?status= directly). -->
<div class="leads-pills" role="tablist" aria-label="Filter by status">
  <?php
    $statusOptions = ['' => 'All'] + array_combine($statuses, array_map('ucfirst', $statuses));
    foreach ($statusOptions as $val => $label):
      $isActive = ($activeStatus === $val);
      $count    = $val === ''
        ? array_sum($statusCounts)
        : ($statusCounts[$val] ?? 0);
      $cls = 'leads-pill' . ($isActive ? ' is-active' : '');
      if ($val !== '') $cls .= ' leads-pill--' . $val;
  ?>
    <a class="<?= $cls ?>" href="<?= htmlspecialchars($qs(['status' => $val ?: null, 'page' => 1]), ENT_QUOTES) ?>" role="tab" aria-selected="<?= $isActive ? 'true' : 'false' ?>">
      <span><?= htmlspecialchars($label) ?></span>
      <span class="leads-pill__count"><?= number_format($count) ?></span>
    </a>
  <?php endforeach; ?>
</div>


<!-- ── Results table ────────────────────────────────────────────────────── -->
<?php if (empty($result['rows'])): ?>
  <div class="dash-empty leads-empty glass">
    No leads match these filters.
    <?php if ($hasFilter): ?>
      <a href="/admin/leads">Clear filters</a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="leads-table-wrap glass">
    <table class="dash-table leads-table">
      <thead>
        <tr>
          <th scope="col"><?= $sortLink('name',       'Name') ?></th>
          <th scope="col">Email</th>
          <th scope="col">Phone</th>
          <th scope="col">Service</th>
          <th scope="col"><?= $sortLink('status',     'Status') ?></th>
          <th scope="col">Source</th>
          <th scope="col"><?= $sortLink('created_at', 'Created') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
          <tr class="dash-table__row" tabindex="0" data-href="/admin/leads/<?= (int) $row['id'] ?>">
            <td><strong><?= htmlspecialchars((string) ($row['name']  ?? '—')) ?></strong></td>
            <td class="dash-table__email"><?= htmlspecialchars((string) ($row['email'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string) ($row['phone'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string) ($row['service'] ?? '—')) ?></td>
            <td><span class="<?= htmlspecialchars($badgeFor((string) ($row['status'] ?? 'new'))) ?>"><?= htmlspecialchars((string) ($row['status'] ?? 'new')) ?></span></td>
            <td><?= htmlspecialchars((string) ($row['source'] ?? '')) ?></td>
            <td><time datetime="<?= htmlspecialchars((string) ($row['created_iso'] ?? '')) ?>" title="<?= htmlspecialchars((string) ($row['created_at'] ?? '')) ?>"><?= htmlspecialchars((string) ($row['created_human'] ?? '')) ?></time></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <nav class="leads-pagination" aria-label="Pagination">
    <p class="leads-pagination__status">
      Showing <?= number_format($showFrom) ?>&ndash;<?= number_format($showTo) ?> of <?= number_format($total) ?>
    </p>
    <?php if ($result['pages'] > 1): ?>
      <ul class="leads-pagination__pages">
        <?php if ($result['page'] > 1): ?>
          <li><a class="btn btn--ghost btn--small" href="<?= htmlspecialchars($qs(['page' => $result['page'] - 1]), ENT_QUOTES) ?>" rel="prev">&larr; Prev</a></li>
        <?php endif; ?>
        <li class="leads-pagination__current">Page <?= $result['page'] ?> of <?= $result['pages'] ?></li>
        <?php if ($result['page'] < $result['pages']): ?>
          <li><a class="btn btn--ghost btn--small" href="<?= htmlspecialchars($qs(['page' => $result['page'] + 1]), ENT_QUOTES) ?>" rel="next">Next &rarr;</a></li>
        <?php endif; ?>
      </ul>
    <?php endif; ?>
  </nav>
<?php endif; ?>
