<?php
/**
 * @var array $lead
 * @var array $history
 * @var array $statuses
 */
$badgeFor   = fn(string $s): string => 'badge badge--' . preg_replace('/[^a-z]/', '', strtolower($s));
$status     = (string) ($lead['status'] ?? 'new');
$csrfToken  = \Core\Csrf::token();
$createdHuman = \Core\Time::timeAgo($lead['created_at'] ?? null);
$updatedHuman = \Core\Time::timeAgo($lead['updated_at'] ?? null);
?>

<header class="lead-detail__header">
  <div class="lead-detail__title-block">
    <a class="lead-detail__back" href="/admin/leads">&larr; Back to leads</a>
    <h2 class="lead-detail__title" data-lead-name>
      <?= htmlspecialchars((string) ($lead['name'] ?? 'Lead')) ?>
      <span class="<?= htmlspecialchars($badgeFor($status)) ?>" data-badge-for="status"><?= htmlspecialchars($status) ?></span>
    </h2>
    <p class="lead-detail__meta">
      Lead #<?= (int) $lead['id'] ?> ·
      Created <time datetime="<?= htmlspecialchars(\Core\Time::iso($lead['created_at'] ?? null)) ?>" title="<?= htmlspecialchars((string) ($lead['created_at'] ?? '')) ?>"><?= htmlspecialchars($createdHuman) ?></time>
      · Last updated <?= htmlspecialchars($updatedHuman) ?>
    </p>
  </div>

  <form action="/admin/leads/<?= (int) $lead['id'] ?>/delete" method="POST" data-archive-form>
    <?= \Core\Csrf::field() ?>
    <button type="submit" class="btn btn--ghost btn--small lead-detail__archive" data-archive-btn>Archive</button>
  </form>
</header>


<div class="lead-detail__grid"
     data-lead-id="<?= (int) $lead['id'] ?>"
     data-csrf="<?= htmlspecialchars($csrfToken) ?>">

  <!-- ── Left column ─────────────────────────────────────────────────── -->
  <div class="lead-detail__col-main">

    <article class="lead-card glass">
      <h3 class="lead-card__title">Contact</h3>
      <dl class="lead-card__list">
        <div class="lead-card__row">
          <dt>Name</dt>
          <dd><?= htmlspecialchars((string) ($lead['name'] ?? '')) ?></dd>
        </div>
        <div class="lead-card__row">
          <dt>Email</dt>
          <dd>
            <a href="mailto:<?= htmlspecialchars((string) ($lead['email'] ?? '')) ?>"><?= htmlspecialchars((string) ($lead['email'] ?? '')) ?></a>
            <button type="button" class="lead-copy" data-copy="<?= htmlspecialchars((string) ($lead['email'] ?? ''), ENT_QUOTES) ?>" aria-label="Copy email">Copy</button>
          </dd>
        </div>
        <?php if (!empty($lead['phone'])): ?>
          <div class="lead-card__row">
            <dt>Phone</dt>
            <dd>
              <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', (string) $lead['phone'])) ?>"><?= htmlspecialchars((string) $lead['phone']) ?></a>
              <button type="button" class="lead-copy" data-copy="<?= htmlspecialchars((string) $lead['phone'], ENT_QUOTES) ?>" aria-label="Copy phone">Copy</button>
            </dd>
          </div>
        <?php endif; ?>
        <?php if (!empty($lead['company'])): ?>
          <div class="lead-card__row">
            <dt>Company</dt>
            <dd><?= htmlspecialchars((string) $lead['company']) ?></dd>
          </div>
        <?php endif; ?>
      </dl>
    </article>

    <article class="lead-card glass">
      <h3 class="lead-card__title">Service &amp; budget</h3>
      <dl class="lead-card__list">
        <div class="lead-card__row"><dt>Service</dt><dd><?= htmlspecialchars((string) ($lead['service'] ?? '—')) ?></dd></div>
        <div class="lead-card__row"><dt>Budget</dt> <dd><?= htmlspecialchars((string) ($lead['budget']  ?? '—')) ?></dd></div>
        <div class="lead-card__row"><dt>Source</dt> <dd><?= htmlspecialchars((string) ($lead['source']  ?? '—')) ?></dd></div>
        <?php if (!empty($lead['ip_address'])): ?>
          <div class="lead-card__row"><dt>IP</dt><dd><code><?= htmlspecialchars((string) $lead['ip_address']) ?></code></dd></div>
        <?php endif; ?>
      </dl>
    </article>

    <article class="lead-card glass">
      <h3 class="lead-card__title">Message</h3>
      <p class="lead-message"><?= nl2br(htmlspecialchars((string) ($lead['message'] ?? ''))) ?></p>
    </article>

    <article class="lead-card glass">
      <header class="lead-card__head">
        <h3 class="lead-card__title">Notes</h3>
        <span class="lead-notes__indicator" data-notes-indicator aria-live="polite"></span>
      </header>
      <textarea
        class="lead-notes__textarea"
        data-notes
        rows="6"
        maxlength="5000"
        placeholder="Internal notes... (auto-saves)"><?= htmlspecialchars((string) ($lead['notes'] ?? '')) ?></textarea>
      <p class="lead-notes__hint">Auto-saves on blur or Ctrl+Enter. Max 5,000 characters.</p>
    </article>
  </div>


  <!-- ── Right column ────────────────────────────────────────────────── -->
  <aside class="lead-detail__col-side">

    <article class="lead-card glass">
      <h3 class="lead-card__title">Status</h3>
      <select class="lead-status-select" data-status-select aria-label="Change status">
        <?php foreach ($statuses as $s): ?>
          <option value="<?= htmlspecialchars($s) ?>" <?= $s === $status ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($s)) ?></option>
        <?php endforeach; ?>
      </select>
    </article>

    <article class="lead-card glass">
      <h3 class="lead-card__title">Activity</h3>
      <?php if (empty($history)): ?>
        <p class="lead-card__muted">No activity yet.</p>
      <?php else: ?>
        <ol class="lead-timeline">
          <?php foreach ($history as $event): ?>
            <li class="lead-timeline__item">
              <span class="lead-timeline__dot lead-timeline__dot--<?= htmlspecialchars(preg_replace('/[^a-z_]/', '', (string) $event['event_type'])) ?>" aria-hidden="true"></span>
              <div class="lead-timeline__body">
                <p class="lead-timeline__label">
                  <?php
                    $type = (string) $event['event_type'];
                    $data = $event['event_data_decoded'] ?? null;
                    if ($type === 'created') {
                        echo 'Lead created';
                    } elseif ($type === 'status_changed' && is_array($data) && isset($data['from'], $data['to'])) {
                        echo 'Status changed: '
                             . '<span class="' . htmlspecialchars($badgeFor((string) $data['from'])) . '">' . htmlspecialchars((string) $data['from']) . '</span>'
                             . ' → '
                             . '<span class="' . htmlspecialchars($badgeFor((string) $data['to']))   . '">' . htmlspecialchars((string) $data['to'])   . '</span>';
                    } elseif ($type === 'notes_updated') {
                        echo 'Notes updated';
                    } elseif ($type === 'archived') {
                        echo 'Archived';
                    } else {
                        echo htmlspecialchars($type);
                    }
                  ?>
                </p>
                <p class="lead-timeline__meta">
                  <?= !empty($event['actor_name']) ? 'by ' . htmlspecialchars((string) $event['actor_name']) . ' · ' : '' ?>
                  <time datetime="<?= htmlspecialchars((string) ($event['created_iso'] ?? '')) ?>" title="<?= htmlspecialchars((string) ($event['created_at'] ?? '')) ?>"><?= htmlspecialchars((string) ($event['created_human'] ?? '')) ?></time>
                </p>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>
    </article>

    <article class="lead-card glass">
      <h3 class="lead-card__title">Quick actions</h3>
      <div class="lead-quick-actions">
        <a class="btn btn--primary btn--small" href="mailto:<?= htmlspecialchars((string) ($lead['email'] ?? '')) ?>">Open Email</a>
        <?php if (!empty($lead['phone'])): ?>
          <a class="btn btn--ghost btn--small" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', (string) $lead['phone'])) ?>">Call</a>
        <?php endif; ?>
      </div>
    </article>

  </aside>
</div>
