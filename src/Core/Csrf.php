<?php
declare(strict_types=1);

namespace Core;

class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    // ── Token management ──────────────────────────────────────────────────────

    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public static function verify(): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $submitted = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $stored    = $_SESSION[self::SESSION_KEY] ?? '';

        if (empty($submitted) || empty($stored)) return false;

        return hash_equals($stored, $submitted);
    }

    public static function verifyOrFail(int $failCode = 419): never
    {
        if (!self::verify()) {
            http_response_code($failCode);
            echo json_encode(['error' => 'CSRF token mismatch']);
            exit;
        }
        // Rotate token after successful validation
        self::rotate();
    }

    public static function rotate(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }
}
