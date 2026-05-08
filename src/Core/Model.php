<?php
declare(strict_types=1);

namespace Core;

abstract class Model
{
    protected \PDO    $db;
    protected string  $table        = '';
    protected string  $pk           = 'id';
    protected bool    $softDeletes  = true;
    protected bool    $timestamps   = true;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    // ── Basic queries ─────────────────────────────────────────────────────────

    public function find(int $id): ?array
    {
        $where = "`{$this->pk}` = ?" . ($this->softDeletes ? " AND `deleted_at` IS NULL" : '');
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE {$where} LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(string $orderBy = 'id DESC', int $limit = 100, int $offset = 0): array
    {
        $where = $this->softDeletes ? "WHERE `deleted_at` IS NULL " : '';
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` {$where}ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(array $where = []): int
    {
        [$sql, $bindings] = $this->buildWhere($where);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` {$sql}");
        $stmt->execute($bindings);
        return (int) $stmt->fetchColumn();
    }

    // ── Writes ────────────────────────────────────────────────────────────────

    public function insert(array $data): int
    {
        if ($this->timestamps) {
            $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $cols     = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` ({$cols}) VALUES ({$placeholders})");
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $sets = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET {$sets} WHERE `{$this->pk}` = ?");
        return $stmt->execute([...array_values($data), $id]);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `deleted_at` = ? WHERE `{$this->pk}` = ?");
        return $stmt->execute([date('Y-m-d H:i:s'), $id]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function buildWhere(array $conditions): array
    {
        $base     = $this->softDeletes ? ['`deleted_at` IS NULL'] : [];
        $bindings = [];
        foreach ($conditions as $col => $val) {
            $base[]     = "`{$col}` = ?";
            $bindings[] = $val;
        }
        $sql = empty($base) ? '' : 'WHERE ' . implode(' AND ', $base);
        return [$sql, $bindings];
    }

    protected function paginate(string $sql, array $bindings, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt   = $this->db->prepare($sql . " LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }
}
