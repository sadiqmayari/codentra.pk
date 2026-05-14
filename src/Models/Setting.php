<?php
declare(strict_types=1);

namespace Models;

class Setting extends \Core\Model
{
    protected string $table       = 'settings';
    protected bool   $softDeletes = false;
    protected bool   $timestamps  = false;

    private static array $cache = [];

    // ── Reads ─────────────────────────────────────────────────────────────────

    public function get(string $key, ?string $default = null): ?string
    {
        if (empty(self::$cache)) $this->warm();
        return self::$cache[$key] ?? $default;
    }

    public function allKeyValue(): array
    {
        if (empty(self::$cache)) $this->warm();
        return self::$cache;
    }

    private function warm(): void
    {
        $stmt = $this->db->query("SELECT `key_name`, `value` FROM `settings`");
        foreach ($stmt->fetchAll() as $row) {
            self::$cache[$row['key_name']] = $row['value'];
        }
    }

    // ── Writes ────────────────────────────────────────────────────────────────

    public function set(string $key, ?string $value): bool
    {
        $now = date('Y-m-d H:i:s');

        // UPDATE first (covers the common case — the row already exists
        // for every key in the migration). If nothing was updated, insert.
        // Portable across MySQL + SQLite — no ON DUPLICATE / ON CONFLICT
        // dialect needed.
        $stmt = $this->db->prepare(
            "UPDATE `settings` SET `value` = ?, `updated_at` = ? WHERE `key_name` = ?"
        );
        $stmt->execute([$value, $now, $key]);

        if ($stmt->rowCount() === 0) {
            $insert = $this->db->prepare(
                "INSERT INTO `settings` (`key_name`, `value`, `updated_at`) VALUES (?, ?, ?)"
            );
            $insert->execute([$key, $value, $now]);
        }

        // Invalidate cache so next read sees the new value
        self::$cache[$key] = $value;
        return true;
    }

    public function setMany(array $kv): void
    {
        foreach ($kv as $k => $v) {
            $this->set((string) $k, $v === null ? null : (string) $v);
        }
    }

    public static function flushCache(): void
    {
        self::$cache = [];
    }
}
