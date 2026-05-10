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
        return $this->insert([
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
    }

    // ── Admin queries ─────────────────────────────────────────────────────────

    public function search(?string $status = null, ?string $q = null, int $page = 1, int $perPage = 25): array
    {
        $where    = ['`deleted_at` IS NULL'];
        $bindings = [];

        if ($status !== null && in_array($status, self::STATUSES, true)) {
            $where[]    = '`status` = ?';
            $bindings[] = $status;
        }
        if ($q !== null && $q !== '') {
            $where[]    = '(`name` LIKE ? OR `email` LIKE ? OR `company` LIKE ?)';
            $like       = '%' . $q . '%';
            $bindings[] = $like;
            $bindings[] = $like;
            $bindings[] = $like;
        }

        $sql = "SELECT * FROM `leads` WHERE " . implode(' AND ', $where) . " ORDER BY `created_at` DESC";
        return $this->paginate($sql, $bindings, $page, $perPage);
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): bool
    {
        if (!in_array($status, self::STATUSES, true)) return false;
        $data = ['status' => $status];
        if ($notes !== null) $data['notes'] = $notes;
        return $this->update($id, $data);
    }

    public function countByStatus(): array
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
