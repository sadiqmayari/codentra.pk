<?php
declare(strict_types=1);

namespace Models;

class Lead extends \Core\Model
{
    protected string $table = 'leads';

    public const SERVICES = ['web-dev', 'shopify', 'ecommerce-mgmt', 'automation', 'other'];
    public const STATUSES = ['new', 'contacted', 'qualified', 'converted', 'lost'];

    // ── Public form submission ────────────────────────────────────────────────

    public function submit(array $input): int
    {
        $id = $this->insert([
            'name'       => trim(strip_tags($input['name'] ?? '')),
            'email'      => strtolower(trim($input['email'] ?? '')),
            'phone'      => trim(strip_tags($input['phone'] ?? '')) ?: null,
            'company'    => trim(strip_tags($input['company'] ?? '')) ?: null,
            'service'    => in_array($input['service'] ?? '', self::SERVICES, true) ? $input['service'] : 'other',
            'budget'     => trim(strip_tags($input['budget'] ?? '')) ?: null,
            'message'    => trim(strip_tags($input['message'] ?? '')),
            'source'     => $input['source']     ?? 'website',
            'status'     => 'new',
            'ip_address' => \Core\Request::clientIp(),
            'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);

        // Append a 'created' history event so the activity timeline on
        // the lead-detail page has a starting point. Best-effort — never
        // block lead capture on history-table issues.
        try {
            $this->writeHistory($id, null, 'created', null);
        } catch (\Throwable $e) {
            error_log('[LEAD] history-create-failed lead_id=' . $id . ' msg=' . $e->getMessage());
        }

        return $id;
    }

    // ── Admin queries ─────────────────────────────────────────────────────────

    /**
     * Filterable, sortable, paginated lead search for /admin/leads.
     *
     * @param array{q?:string,status?:string,service?:string,from?:string,to?:string,sort?:string,dir?:string,page?:int,per_page?:int} $filters
     * @return array{rows: array, total: int, pages: int, page: int, per_page: int}
     */
    public function search(array $filters): array
    {
        [$where, $bindings] = $this->buildSearchWhere($filters);

        // Total count BEFORE applying limit/offset
        $countSql = "SELECT COUNT(*) FROM `leads` WHERE " . implode(' AND ', $where);
        $stmt     = $this->db->prepare($countSql);
        $stmt->execute($bindings);
        $total = (int) $stmt->fetchColumn();

        // Sort whitelist — never interpolate raw user input into ORDER BY.
        $sortMap = [
            'created_at' => '`created_at`',
            'name'       => '`name`',
            'status'     => '`status`',
        ];
        $sortKey = $filters['sort'] ?? 'created_at';
        $sortCol = $sortMap[$sortKey] ?? '`created_at`';
        $dir     = strtolower((string) ($filters['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $page    = max(1, (int) ($filters['page']     ?? 1));
        $perPage = max(1, min(200, (int) ($filters['per_page'] ?? 25)));
        $offset  = ($page - 1) * $perPage;

        $sql  = "SELECT * FROM `leads`
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY {$sortCol} {$dir}, `id` DESC
                 LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['created_human'] = \Core\Time::timeAgo($row['created_at'] ?? null);
            $row['created_iso']   = \Core\Time::iso($row['created_at'] ?? null);
        }
        unset($row);

        return [
            'rows'     => $rows,
            'total'    => $total,
            'pages'    => max(1, (int) ceil($total / $perPage)),
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Same WHERE clause as search() but no LIMIT/OFFSET — used by CSV export.
     * Returns a generator-friendly list; for now eager array since we're
     * dealing with admin volumes (thousands, not millions).
     */
    public function searchAll(array $filters): array
    {
        [$where, $bindings] = $this->buildSearchWhere($filters);

        $sortMap = ['created_at' => '`created_at`', 'name' => '`name`', 'status' => '`status`'];
        $sortKey = $filters['sort'] ?? 'created_at';
        $sortCol = $sortMap[$sortKey] ?? '`created_at`';
        $dir     = strtolower((string) ($filters['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $sql  = "SELECT * FROM `leads`
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY {$sortCol} {$dir}, `id` DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /** @return array{0: array<int, string>, 1: array<int, mixed>} */
    private function buildSearchWhere(array $filters): array
    {
        $where    = ['`deleted_at` IS NULL'];
        $bindings = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where[]    = '(`name` LIKE ? OR `email` LIKE ? OR `company` LIKE ?)';
            $like       = '%' . $q . '%';
            $bindings[] = $like; $bindings[] = $like; $bindings[] = $like;
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $where[]    = '`status` = ?';
            $bindings[] = $status;
        }

        $service = (string) ($filters['service'] ?? '');
        if ($service !== '' && in_array($service, self::SERVICES, true)) {
            $where[]    = '`service` = ?';
            $bindings[] = $service;
        }

        $from = (string) ($filters['from'] ?? '');
        if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $where[]    = '`created_at` >= ?';
            $bindings[] = $from . ' 00:00:00';
        }

        $to = (string) ($filters['to'] ?? '');
        if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $where[]    = '`created_at` <= ?';
            $bindings[] = $to . ' 23:59:59';
        }

        return [$where, $bindings];
    }

    /**
     * Writes a row + (transactionally) a status_changed history event.
     * Returns true on success, false on invalid status or DB failure.
     */
    public function updateStatus(int $id, string $status, ?int $userId = null): bool
    {
        if (!in_array($status, self::STATUSES, true)) return false;

        $existing = $this->find($id);
        if (!$existing) return false;
        $oldStatus = (string) ($existing['status'] ?? '');
        if ($oldStatus === $status) {
            return true; // no-op — don't pollute history with non-changes
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE `leads` SET `status` = ?, `updated_at` = ? WHERE `id` = ? AND `deleted_at` IS NULL"
            );
            $stmt->execute([$status, date('Y-m-d H:i:s'), $id]);

            $this->writeHistory($id, $userId, 'status_changed', [
                'from' => $oldStatus,
                'to'   => $status,
            ]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[LEAD] update-status-failed id=' . $id . ' msg=' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the notes column. Writes a history event ONLY if the
     * trimmed value actually changed (avoid timeline noise from
     * frequent auto-saves of the same content).
     *
     * @return array{saved_at: string}
     */
    public function updateNotes(int $id, string $notes, ?int $userId = null): array
    {
        $notes    = substr($notes, 0, 5000);
        $existing = $this->find($id);
        if (!$existing) {
            throw new \RuntimeException("Lead {$id} not found");
        }

        $changed = trim((string) ($existing['notes'] ?? '')) !== trim($notes);

        $stmt = $this->db->prepare(
            "UPDATE `leads` SET `notes` = ?, `updated_at` = ? WHERE `id` = ? AND `deleted_at` IS NULL"
        );
        $stmt->execute([$notes, $now = date('Y-m-d H:i:s'), $id]);

        if ($changed) {
            try {
                $this->writeHistory($id, $userId, 'notes_updated', null);
            } catch (\Throwable $e) {
                error_log('[LEAD] history-notes-failed id=' . $id . ' msg=' . $e->getMessage());
            }
        }

        return ['saved_at' => date('c', strtotime($now))];
    }

    public function softDelete(int $id, ?int $userId = null): bool
    {
        $existing = $this->find($id);
        if (!$existing) return false;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE `leads` SET `deleted_at` = ? WHERE `id` = ?");
            $stmt->execute([date('Y-m-d H:i:s'), $id]);

            $this->writeHistory($id, $userId, 'archived', null);
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[LEAD] soft-delete-failed id=' . $id . ' msg=' . $e->getMessage());
            return false;
        }
    }

    /**
     * Activity timeline rows for this lead, joined with the actor's name.
     *
     * @return array<int, array<string, mixed>>
     */
    public function history(int $leadId): array
    {
        $stmt = $this->db->prepare(
            "SELECT h.*, u.`name` AS `actor_name`
             FROM `lead_history` h
             LEFT JOIN `users` u ON u.`id` = h.`user_id`
             WHERE h.`lead_id` = ?
             ORDER BY h.`created_at` DESC, h.`id` DESC"
        );
        $stmt->execute([$leadId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['created_human'] = \Core\Time::timeAgo($row['created_at'] ?? null);
            $row['created_iso']   = \Core\Time::iso($row['created_at'] ?? null);
            // Decode JSON event_data into an array for the view (or null)
            if (!empty($row['event_data']) && is_string($row['event_data'])) {
                $decoded = json_decode($row['event_data'], true);
                $row['event_data_decoded'] = is_array($decoded) ? $decoded : null;
            } else {
                $row['event_data_decoded'] = null;
            }
        }
        unset($row);

        return $rows;
    }

    private function writeHistory(int $leadId, ?int $userId, string $eventType, ?array $eventData): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO `lead_history`
                (`lead_id`, `user_id`, `event_type`, `event_data`, `created_at`)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $leadId,
            $userId,
            $eventType,
            $eventData === null ? null : json_encode($eventData, JSON_UNESCAPED_SLASHES),
            date('Y-m-d H:i:s'),
        ]);
    }

    public function countByStatus(): array
    {
        return $this->statusCounts();
    }

    public function statusCounts(): array
    {
        $stmt = $this->db->query(
            "SELECT `status`, COUNT(*) AS `n` FROM `leads`
             WHERE `deleted_at` IS NULL GROUP BY `status`"
        );
        $out = array_fill_keys(self::STATUSES, 0);
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['status']] = (int) $row['n'];
        }
        return $out;
    }

    public function recent(int $n = 5): array
    {
        $n   = max(1, $n);
        $sql = "SELECT * FROM `leads`
                WHERE `deleted_at` IS NULL
                ORDER BY `created_at` DESC
                LIMIT {$n}";
        $rows = $this->db->query($sql)->fetchAll();

        // Decorate with a human-friendly relative time.
        foreach ($rows as &$row) {
            $row['created_human'] = \Core\Time::timeAgo($row['created_at'] ?? null);
            $row['created_iso']   = \Core\Time::iso($row['created_at'] ?? null);
        }
        return $rows;
    }

    public function countActive(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM `leads`
             WHERE `deleted_at` IS NULL AND `status` <> 'lost'"
        )->fetchColumn();
    }

    public function newThisWeek(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM `leads`
             WHERE `deleted_at` IS NULL
               AND `status`     = 'new'
               AND `created_at` >= ?"
        );
        $stmt->execute([date('Y-m-d H:i:s', time() - 7 * 86400)]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Daily lead counts for the last $days days, gap-filled so days
     * with zero leads still appear (otherwise Chart.js would skip them
     * and the x-axis would lie about the trend).
     *
     * @return array<int, array{date: string, count: int}>
     */
    public function dailyCounts(int $days = 30): array
    {
        $days  = max(1, $days);
        $start = strtotime("-" . ($days - 1) . " days");
        $today = strtotime('today');

        // 1. Build the full date series (date => 0).
        $series = [];
        for ($t = $start; $t <= $today; $t += 86400) {
            $series[date('Y-m-d', $t)] = 0;
        }

        // 2. One grouped query for the actual counts.
        $stmt = $this->db->prepare(
            "SELECT DATE(`created_at`) AS `date`, COUNT(*) AS `count`
             FROM `leads`
             WHERE `deleted_at` IS NULL AND `created_at` >= ?
             GROUP BY DATE(`created_at`)"
        );
        $stmt->execute([date('Y-m-d 00:00:00', $start)]);
        foreach ($stmt->fetchAll() as $row) {
            $key = (string) $row['date'];
            if (isset($series[$key])) {
                $series[$key] = (int) $row['count'];
            }
        }

        // 3. Reshape into a list of {date, count} objects.
        $out = [];
        foreach ($series as $date => $count) {
            $out[] = ['date' => $date, 'count' => $count];
        }
        return $out;
    }

    /**
     * converted / (qualified + converted) * 100, rounded to 1 decimal.
     * Returns null when the denominator is 0 — caller renders "—".
     */
    public function conversionRate(): ?float
    {
        $stmt = $this->db->query(
            "SELECT
                SUM(CASE WHEN `status` = 'qualified' THEN 1 ELSE 0 END) AS qualified,
                SUM(CASE WHEN `status` = 'converted' THEN 1 ELSE 0 END) AS converted
             FROM `leads`
             WHERE `deleted_at` IS NULL"
        );
        $row = $stmt->fetch();
        $qualified = (int) ($row['qualified'] ?? 0);
        $converted = (int) ($row['converted'] ?? 0);

        $denom = $qualified + $converted;
        if ($denom === 0) return null;

        return round(($converted / $denom) * 100, 1);
    }

    /**
     * Percent change in lead volume this week (last 7d) vs the prior
     * week (8–14d ago). Returns 0.0 when both windows are empty so the
     * KPI card can show a neutral "0%" without dividing by zero.
     */
    public function weekDelta(): float
    {
        $now      = time();
        $thisFrom = date('Y-m-d H:i:s', $now - 7  * 86400);
        $prevFrom = date('Y-m-d H:i:s', $now - 14 * 86400);
        $prevTo   = $thisFrom;

        $thisCount = (int) $this->db->query(
            "SELECT COUNT(*) FROM `leads`
             WHERE `deleted_at` IS NULL
               AND `created_at` >= " . $this->db->quote($thisFrom)
        )->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM `leads`
             WHERE `deleted_at` IS NULL
               AND `created_at` >= ?
               AND `created_at` <  ?"
        );
        $stmt->execute([$prevFrom, $prevTo]);
        $prevCount = (int) $stmt->fetchColumn();

        if ($thisCount === 0 && $prevCount === 0) return 0.0;
        if ($prevCount === 0) return 100.0; // came from nothing — present as +100%

        return round((($thisCount - $prevCount) / $prevCount) * 100, 1);
    }
}
