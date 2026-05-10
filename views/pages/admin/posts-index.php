<?php
/**
 * @var array $filters
 * @var array{rows: array, total: int, pages: int, page: int, per_page: int} $result
 * @var array $statusCounts
 * @var array $categories
 */
$f = $filters;
$activeStatus     = $f['status'] ?? '';
$activeCategoryId = (int) ($f['category_id'] ?? 0);

$badgeFor = fn(string $s): string => 'badge badge--' . preg_replace('/[^a-z]/', '', strtolower($s));

$qs = function (array $overrides = []) use ($f): string {
    $merged = array_merge([
        'q' => $f['q'], 'status' => $f['status'], 'category_id' => $f['category_id'] ?: '',
        'sort' => $f['sort'], 'dir' => $f['dir'], 'page' => $f['page'],
    ], $overrides);
    $merged = array_filter($merged, fn($v) => $v !== '' && $v !== null && $v !== 0);
    return $merged ? '?' . http_build_query($merged) : '';
};

$sortLink = function (string $col, string $label) use ($f, $qs): string {
    $isActive = ($f['sort'] === $col);
    $newDir   = $isActive ? ($f['dir'] === 'asc' ? 'desc' : 'asc') : 'asc';
    $arrow    = $isActive ? ($f['dir'] === 'asc' ? '↑' : '↓') : '';
    $href     = $qs(['sort' => $col, 'dir' => $newDir, 'page' => 1]);
    $cls      = 'leads-th-sort' . ($isActive ? ' is-active' : '');
    return "<a class=\"{$cls}\" href=\"" . htmlspecialchars($href, ENT_QUOTES) . "\">"
         . htmlspecialchars($label) . " <span class=\"leads-th-arrow\" aria-hidden=\"true\">{$arrow}</span></a>";
};

$total    = $result['total'];
$showFrom = $total === 0 ? 0 : (($result['page'] - 1) * $result['per_page'] + 1);
$showTo   = min($total, $showFrom + $result['per_page'] - 1);
?>

<header class="leads-header">
  <div>
    <h2 class="dash-header__title">Blog Posts <span class="leads-header__count">(<?= number_format($total) ?> total)</span></h2>
  </div>
  <a class="btn btn--cta btn--small" href="/admin/posts/new">+ New Post</a>
</header>


<form class="leads-filter" method="GET" action="/admin/posts" data-leads-filter>
  <input
    class="leads-filter__search"
    type="search" name="q"
    placeholder="Search title or slug…"
    value="<?= htmlspecialchars((string) $f['q']) ?>"
    autocomplete="off" data-leads-search>

  <select class="leads-filter__select" name="category_id">
    <option value="">All categories</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= (int) $c['id'] ?>" <?= $activeCategoryId === (int) $c['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars((string) $c['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <input type="hidden" name="status" value="<?= htmlspecialchars((string) $f['status']) ?>">
  <input type="hidden" name="sort"   value="<?= htmlspecialchars((string) $f['sort']) ?>">
  <input type="hidden" name="dir"    value="<?= htmlspecialchars((string) $f['dir']) ?>">

  <noscript><button type="submit" class="btn btn--ghost btn--small">Apply</button></noscript>
</form>


<div class="leads-pills" role="tablist" aria-label="Filter by status">
  <?php
    $totalAll = ($statusCounts['draft'] ?? 0) + ($statusCounts['published'] ?? 0);
    $statusOptions = [
        ''           => ['All',       $totalAll],
        'draft'      => ['Drafts',    $statusCounts['draft']     ?? 0],
        'published'  => ['Published', $statusCounts['published'] ?? 0],
    ];
    foreach ($statusOptions as $val => [$label, $count]):
      $isActive = $activeStatus === $val;
      $cls = 'leads-pill' . ($isActive ? ' is-active' : '');
      if ($val !== '') $cls .= ' leads-pill--' . $val;
  ?>
    <a class="<?= $cls ?>" href="<?= htmlspecialchars($qs(['status' => $val ?: null, 'page' => 1]), ENT_QUOTES) ?>" role="tab" aria-selected="<?= $isActive ? 'true' : 'false' ?>">
      <span><?= htmlspecialchars($label) ?></span>
      <span class="leads-pill__count"><?= number_format($count) ?></span>
    </a>
  <?php endforeach; ?>
</div>


<?php if (empty($result['rows'])): ?>
  <div class="dash-empty leads-empty glass">
    No posts yet. <a href="/admin/posts/new">Create your first post</a>.
  </div>
<?php else: ?>
  <div class="leads-table-wrap glass">
    <table class="dash-table leads-table">
      <thead>
        <tr>
          <th scope="col"><?= $sortLink('title',  'Title') ?></th>
          <th scope="col"><?= $sortLink('status', 'Status') ?></th>
          <th scope="col">Category</th>
          <th scope="col">Author</th>
          <th scope="col"><?= $sortLink('updated_at', 'Updated') ?></th>
          <th scope="col"><?= $sortLink('views', 'Views') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
          <tr class="dash-table__row" tabindex="0" data-href="/admin/posts/<?= (int) $row['id'] ?>/edit">
            <td>
              <strong><?= htmlspecialchars((string) ($row['title'] ?? '—')) ?></strong>
              <span class="posts-table__slug">/<?= htmlspecialchars((string) ($row['slug'] ?? '')) ?></span>
            </td>
            <td><span class="<?= htmlspecialchars($badgeFor((string) ($row['status'] ?? 'draft'))) ?>"><?= htmlspecialchars((string) ($row['status'] ?? 'draft')) ?></span></td>
            <td><?= htmlspecialchars((string) ($row['category_name'] ?? '—')) ?></td>
            <td><?= htmlspecialchars((string) ($row['author_name']   ?? '—')) ?></td>
            <td><time datetime="<?= htmlspecialchars((string) ($row['updated_iso'] ?? '')) ?>" title="<?= htmlspecialchars((string) ($row['updated_at'] ?? '')) ?>"><?= htmlspecialchars((string) ($row['updated_human'] ?? '')) ?></time></td>
            <td><?= number_format((int) ($row['views'] ?? 0)) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

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
