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

    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `leads` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function newThisWeek(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM `leads`
             WHERE `deleted_at` IS NULL AND `created_at` >= (NOW() - INTERVAL 7 DAY)"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function dailyCounts(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE(`created_at`) AS `d`, COUNT(*) AS `n`
             FROM `leads`
             WHERE `deleted_at` IS NULL AND `created_at` >= (CURDATE() - INTERVAL ? DAY)
             GROUP BY DATE(`created_at`) ORDER BY `d` ASC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function conversionRate(): float
    {
        $total = (int) $this->db->query(
            "SELECT COUNT(*) FROM `leads` WHERE `deleted_at` IS NULL"
        )->fetchColumn();
        if ($total === 0) return 0.0;

        $converted = (int) $this->db->query(
            "SELECT COUNT(*) FROM `leads` WHERE `deleted_at` IS NULL AND `status` = 'converted'"
        )->fetchColumn();

        return round(($converted / $total) * 100, 1);
    }
}
