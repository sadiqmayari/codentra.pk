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
}
