<?php
/**
 * @var string                                               $pageTitle
 * @var array{total:int,newThisWeek:int,conversion:?float,publishedPosts:int,weekDelta:float} $kpis
 * @var array<int,array{date:string,count:int}>              $chartData
 * @var array<int,array<string,mixed>>                       $recentLeads
 * @var array<int,array<string,mixed>>                       $recentPosts
 */
$userName = $_SESSION['user_name'] ?? 'there';

$delta      = $kpis['weekDelta'];
$deltaArrow = $delta > 0 ? '↑' : ($delta < 0 ? '↓' : '·');
$deltaClass = $delta > 0 ? 'kpi-delta--up' : ($delta < 0 ? 'kpi-delta--down' : 'kpi-delta--flat');

$conversionDisplay = $kpis['conversion'] === null ? '—' : (rtrim(rtrim(number_format($kpis['conversion'], 1), '0'), '.') . '%');

$badgeFor = function (string $status): string {
    return 'badge badge--' . preg_replace('/[^a-z]/', '', strtolower($status));
};
?>

<header class="dash-header">
  <h2 class="dash-header__title">Dashboard</h2>
  <p class="dash-header__welcome">Welcome back, <strong><?= htmlspecialchars($userName) ?></strong>. Here's what's happening.</p>
</header>


<!-- ── KPI cards ────────────────────────────────────────────────────────── -->
<section class="kpi-grid" aria-label="Key metrics">
  <article class="kpi">
    <span class="kpi__rule" aria-hidden="true"></span>
    <p class="kpi__label">Active leads</p>
    <p class="kpi__value"><?= number_format($kpis['total']) ?></p>
    <p class="kpi__delta <?= $deltaClass ?>">
      <span aria-hidden="true"><?= $deltaArrow ?></span>
      <?= $delta === 0.0 ? '0%' : (($delta > 0 ? '+' : '') . number_format(abs($delta), 1) . '%') ?>
      <span class="kpi__delta-context">vs last week</span>
    </p>
  </article>

  <article class="kpi">
    <span class="kpi__rule" aria-hidden="true"></span>
    <p class="kpi__label">New this week</p>
    <p class="kpi__value"><?= number_format($kpis['newThisWeek']) ?></p>
    <p class="kpi__caption">status = new, last 7 days</p>
  </article>

  <article class="kpi">
    <span class="kpi__rule" aria-hidden="true"></span>
    <p class="kpi__label">Conversion</p>
    <p class="kpi__value"><?= htmlspecialchars($conversionDisplay) ?></p>
    <p class="kpi__caption">converted ÷ (qualified + converted)</p>
  </article>

  <article class="kpi">
    <span class="kpi__rule" aria-hidden="true"></span>
    <p class="kpi__label">Published posts</p>
    <p class="kpi__value"><?= number_format($kpis['publishedPosts']) ?></p>
    <p class="kpi__caption">visible on /blog</p>
  </article>
</section>


<!-- ── Chart ────────────────────────────────────────────────────────────── -->
<section class="dash-chart glass" aria-labelledby="chart-title">
  <header class="dash-panel__head">
    <h3 id="chart-title" class="dash-panel__title">Leads — last 30 days</h3>
  </header>

  <div class="dash-chart__canvas-wrap">
    <canvas id="leadsChart" height="220"
            data-chart='<?= htmlspecialchars(json_encode($chartData, JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'></canvas>
  </div>
</section>


<!-- ── Recent leads + Recent posts ──────────────────────────────────────── -->
<section class="dash-row">

  <!-- Left: Recent leads -->
  <article class="dash-panel glass dash-panel--leads">
    <header class="dash-panel__head">
      <h3 class="dash-panel__title">Recent leads</h3>
      <a class="dash-panel__view-all" href="/admin/leads">View all &rarr;</a>
    </header>

    <?php if (empty($recentLeads)): ?>
      <div class="dash-empty">No leads yet. Submissions will appear here.</div>
    <?php else: ?>
      <div class="dash-table-wrap">
        <table class="dash-table">
          <thead>
            <tr>
              <th scope="col">Name</th>
              <th scope="col">Email</th>
              <th scope="col">Service</th>
              <th scope="col">Status</th>
              <th scope="col">Created</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentLeads as $lead): ?>
              <tr class="dash-table__row" tabindex="0" data-href="/admin/leads/<?= (int) $lead['id'] ?>">
                <td><strong><?= htmlspecialchars((string) ($lead['name'] ?? '—')) ?></strong></td>
                <td class="dash-table__email"><?= htmlspecialchars((string) ($lead['email'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($lead['service'] ?? '—')) ?></td>
                <td>
                  <span class="<?= htmlspecialchars($badgeFor((string) ($lead['status'] ?? 'new'))) ?>">
                    <?= htmlspecialchars((string) ($lead['status'] ?? 'new')) ?>
                  </span>
                </td>
                <td>
                  <time datetime="<?= htmlspecialchars((string) ($lead['created_iso'] ?? '')) ?>"
                        title="<?= htmlspecialchars((string) ($lead['created_at'] ?? '')) ?>">
                    <?= htmlspecialchars((string) ($lead['created_human'] ?? '')) ?>
                  </time>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </article>

  <!-- Right: Recent posts -->
  <article class="dash-panel glass dash-panel--posts">
    <header class="dash-panel__head">
      <h3 class="dash-panel__title">Recent posts</h3>
      <a class="dash-panel__view-all" href="/admin/posts">All posts &rarr;</a>
    </header>

    <?php if (empty($recentPosts)): ?>
      <div class="dash-empty">No posts yet. Create your first post.</div>
    <?php else: ?>
      <div class="dash-table-wrap">
        <table class="dash-table">
          <thead>
            <tr>
              <th scope="col">Title</th>
              <th scope="col">Status</th>
              <th scope="col">Published</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentPosts as $post): ?>
              <?php
                $publishedTs = $post['published_at'] ?? $post['created_at'] ?? null;
                $publishedHuman = \Core\Time::timeAgo($publishedTs);
                $publishedIso   = \Core\Time::iso($publishedTs);
              ?>
              <tr class="dash-table__row" tabindex="0" data-href="/admin/posts/<?= (int) ($post['id'] ?? 0) ?>/edit">
                <td><strong><?= htmlspecialchars((string) ($post['title'] ?? '—')) ?></strong></td>
                <td>
                  <span class="<?= htmlspecialchars($badgeFor((string) ($post['status'] ?? 'draft'))) ?>">
                    <?= htmlspecialchars((string) ($post['status'] ?? 'draft')) ?>
                  </span>
                </td>
                <td>
                  <time datetime="<?= htmlspecialchars($publishedIso) ?>"
                        title="<?= htmlspecialchars((string) $publishedTs) ?>">
                    <?= htmlspecialchars($publishedHuman) ?>
                  </time>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </article>

</section>


<!-- ── Quick actions ────────────────────────────────────────────────────── -->
<section class="dash-actions" aria-label="Quick actions">
  <a class="btn btn--cta"     href="/admin/posts/new">+ New Post</a>
  <a class="btn btn--primary" href="/admin/leads">View Leads</a>
  <a class="btn btn--ghost"   href="/admin/settings">Site Settings</a>
</section>


<!-- ── Chart.js (pinned version) ────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var canvas = document.getElementById('leadsChart');
    if (!canvas || typeof Chart === 'undefined') return;

    var raw = JSON.parse(canvas.getAttribute('data-chart') || '[]');
    var labels = raw.map(function (r) {
      // YYYY-MM-DD → "Mon DD"
      var parts = r.date.split('-');
      var d = new Date(Date.UTC(+parts[0], +parts[1] - 1, +parts[2]));
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    var counts = raw.map(function (r) { return r.count; });

    new Chart(canvas, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Leads',
          data: counts,
          backgroundColor: 'rgba(42, 157, 143, 0.85)',
          borderColor:     'rgba(42, 157, 143, 1)',
          borderWidth: 0,
          borderRadius: 4,
          maxBarThickness: 22,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(15, 37, 51, 0.96)',
            titleColor:   '#FFFFFF',
            bodyColor:    'rgba(255,255,255,.8)',
            borderColor:  'rgba(42,157,143,.4)',
            borderWidth:  1,
            padding: 10,
            displayColors: false,
            callbacks: {
              title: function (items) {
                var i = items[0].dataIndex;
                return raw[i] ? raw[i].date : items[0].label;
              },
              label: function (item) {
                var n = item.parsed.y;
                return n + ' lead' + (n === 1 ? '' : 's');
              }
            }
          }
        },
        scales: {
          x: {
            grid:   { display: false, drawBorder: false },
            ticks:  { color: 'rgba(255,255,255,.55)', maxRotation: 0, autoSkip: true, autoSkipPadding: 12 },
            border: { display: false }
          },
          y: {
            beginAtZero: true,
            grid:   { color: 'rgba(255,255,255,.06)', drawBorder: false },
            ticks:  { color: 'rgba(255,255,255,.55)', precision: 0, stepSize: 1 },
            border: { display: false }
          }
        }
      }
    });
  });

  // Make recent-* table rows behave like links (keyboard + click).
  document.addEventListener('click', function (e) {
    var row = e.target.closest('.dash-table__row[data-href]');
    if (row) window.location.href = row.dataset.href;
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    var row = e.target.closest('.dash-table__row[data-href]');
    if (row) window.location.href = row.dataset.href;
  });
</script>
