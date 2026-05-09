<?php
declare(strict_types=1);

namespace Models;

class User extends \Core\Model
{
    protected string $table = 'users';

    public const ROLES = ['admin', 'editor'];

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `users` WHERE `email` = ? AND `deleted_at` IS NULL LIMIT 1"
        );
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function verify(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;

        if (!password_verify($password, $user['password_hash'])) return null;

        // Re-hash if PHP's defaults have stronger params now
        if (password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID)) {
            $this->update((int) $user['id'], [
                'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
            ]);
        }

        return $user;
    }

    public function recordLogin(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE `users` SET `last_login_at` = ? WHERE `id` = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $id]);
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    public function createUser(string $name, string $email, string $password, string $role = 'editor'): int
    {
        if (!in_array($role, self::ROLES, true)) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }
        return $this->insert([
            'name'          => $name,
            'email'         => strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
            'role'          => $role,
        ]);
    }

    public function changePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, [
            'password_hash' => password_hash($newPassword, PASSWORD_ARGON2ID),
        ]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `users` WHERE `id` = ? AND `deleted_at` IS NULL LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Remember-me tokens (selector / validator pattern) ────────────────────
    //
    // Selector: 16 random bytes, hex-encoded (32 chars). Stored plaintext in
    //           the DB; used as the row lookup key. Opaque to the user.
    // Validator: 32 random bytes, hex-encoded (64 chars). NEVER stored
    //            plaintext — only its sha256 is persisted. We compare with
    //            hash_equals() to defeat timing attacks.
    // Cookie:   "<selector>:<validator>" — both halves are required for
    //           verification. Knowing the selector alone gets you nothing.

    public function issueRememberToken(int $userId, int $ttlSeconds = 2592000): array
    {
        $selector  = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hashed    = hash('sha256', $validator);

        $stmt = $this->db->prepare(
            "INSERT INTO `remember_tokens`
                (`user_id`, `selector`, `hashed_validator`, `expires_at`, `created_at`)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $userId,
            $selector,
            $hashed,
            date('Y-m-d H:i:s', time() + $ttlSeconds),
            date('Y-m-d H:i:s'),
        ]);

        return [
            'selector'  => $selector,
            'validator' => $validator,
            'cookie'    => $selector . ':' . $validator,
            'ttl'       => $ttlSeconds,
        ];
    }

    public function verifyRememberToken(string $selector, string $validator): ?array
    {
        if ($selector === '' || $validator === '') return null;

        $stmt = $this->db->prepare(
            "SELECT * FROM `remember_tokens`
             WHERE `selector` = ? AND `expires_at` > ?
             LIMIT 1"
        );
        $stmt->execute([$selector, date('Y-m-d H:i:s')]);
        $token = $stmt->fetch();
        if (!$token) return null;

        $candidate = hash('sha256', $validator);
        if (!hash_equals((string) $token['hashed_validator'], $candidate)) {
            // Wrong validator for an existing selector → possible compromise.
            // Defensively invalidate so the attacker can't keep guessing.
            $this->invalidateRemember($selector);
            return null;
        }

        return $this->findById((int) $token['user_id']);
    }

    public function invalidateRemember(string $selector): void
    {
        if ($selector === '') return;
        $this->db->prepare("DELETE FROM `remember_tokens` WHERE `selector` = ?")
                 ->execute([$selector]);
    }

    public function invalidateAllRememberForUser(int $userId): void
    {
        $this->db->prepare("DELETE FROM `remember_tokens` WHERE `user_id` = ?")
                 ->execute([$userId]);
    }

    public function purgeExpiredRememberTokens(): int
    {
        $stmt = $this->db->prepare("DELETE FROM `remember_tokens` WHERE `expires_at` <= ?");
        $stmt->execute([date('Y-m-d H:i:s')]);
        return $stmt->rowCount();
    }
}
