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
        $stmt = $this->db->prepare(
            "INSERT INTO `settings` (`key_name`, `value`, `updated_at`)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`)"
        );
        $ok = $stmt->execute([$key, $value, date('Y-m-d H:i:s')]);

        // Invalidate cache so next read sees the new value
        self::$cache[$key] = $value;
        return $ok;
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
