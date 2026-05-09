<?php
declare(strict_types=1);

namespace Middleware;

/**
 * Lightweight rate limiter.
 *
 * Tries the `rate_limits` DB table first; falls back to per-session if the DB
 * is unavailable. Both modes use a sliding window keyed by IP + scope.
 */
class RateLimit
{
    /**
     * @return array{ok: bool, remaining: int, retry_after: int}
     */
    public static function check(string $scope, int $max, int $windowSec, ?string $ip = null): array
    {
        $ip   = $ip ?? \Core\Request::clientIp();
        $key  = hash('sha256', $scope . '|' . $ip);
        $now  = time();

        // Try DB-backed first
        try {
            $db = \Database::getInstance();
            return self::checkDb($db, $key, $max, $windowSec, $now);
        } catch (\Throwable) {
            return self::checkSession($scope, $max, $windowSec, $now);
        }
    }

    private static function checkDb(\PDO $db, string $key, int $max, int $windowSec, int $now): array
    {
        $expiresAt = date('Y-m-d H:i:s', $now + $windowSec);

        // Cleanup expired rows for this key
        $db->prepare("DELETE FROM rate_limits WHERE key_hash = ? AND expires_at < NOW()")
           ->execute([$key]);

        // Read current attempts
        $stmt = $db->prepare("SELECT id, attempts, expires_at FROM rate_limits WHERE key_hash = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();

        if (!$row) {
            $db->prepare("INSERT INTO rate_limits (key_hash, attempts, expires_at) VALUES (?, 1, ?)")
               ->execute([$key, $expiresAt]);
            return ['ok' => true, 'remaining' => $max - 1, 'retry_after' => 0];
        }

        $attempts = (int) $row['attempts'];
        if ($attempts >= $max) {
            $retryAfter = max(0, strtotime($row['expires_at']) - $now);
            return ['ok' => false, 'remaining' => 0, 'retry_after' => $retryAfter];
        }

        $db->prepare("UPDATE rate_limits SET attempts = attempts + 1 WHERE id = ?")
           ->execute([$row['id']]);

        return ['ok' => true, 'remaining' => $max - ($attempts + 1), 'retry_after' => 0];
    }

    private static function checkSession(string $scope, int $max, int $windowSec, int $now): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $bucket = '_rl_' . preg_replace('/[^a-z0-9]/i', '_', $scope);
        $hits   = $_SESSION[$bucket] ?? [];
        $hits   = array_values(array_filter($hits, fn($t) => $t > $now - $windowSec));

        if (count($hits) >= $max) {
            return ['ok' => false, 'remaining' => 0, 'retry_after' => max(0, ($hits[0] + $windowSec) - $now)];
        }

        $hits[] = $now;
        $_SESSION[$bucket] = $hits;

        return ['ok' => true, 'remaining' => $max - count($hits), 'retry_after' => 0];
    }
}
