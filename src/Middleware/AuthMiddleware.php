<?php
declare(strict_types=1);

namespace Middleware;

/**
 * Guards admin routes. Pass-through when authenticated; redirect to
 * /admin/login?return=<original> otherwise.
 *
 * Authentication priority:
 *   1. Existing $_SESSION['user_id'] (with idle timeout enforcement)
 *   2. "Remember me" cookie → look up + verify validator → re-establish session
 *   3. Redirect to login
 */
class AuthMiddleware
{
    public const REMEMBER_COOKIE   = 'codentra_remember';
    public const SESSION_IDLE_SECS = 3600; // 60 minutes

    public function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // ── 1. Live session ─────────────────────────────────────────────────
        if (!empty($_SESSION['user_id'])) {
            $last = (int) ($_SESSION['_last_activity'] ?? 0);
            if ($last > 0 && (time() - $last) > self::SESSION_IDLE_SECS) {
                error_log('[AUTH] session-expired user_id=' . $_SESSION['user_id']);
                $this->destroySession();
                $this->redirectToLogin('Your session timed out. Please sign in again.');
            }
            $_SESSION['_last_activity'] = time();
            return;
        }

        // ── 2. Remember-me cookie ───────────────────────────────────────────
        if (!empty($_COOKIE[self::REMEMBER_COOKIE])) {
            $cookie = (string) $_COOKIE[self::REMEMBER_COOKIE];
            $parts  = explode(':', $cookie, 2);

            if (count($parts) === 2) {
                [$selector, $validator] = $parts;
                try {
                    $user = (new \Models\User())->verifyRememberToken($selector, $validator);
                } catch (\Throwable $e) {
                    error_log(
                        '[AUTH] remember-verify-exception class=' . $e::class
                        . ' msg=' . ($e->getMessage() !== '' ? $e->getMessage() : '<empty>')
                    );
                    $user = null;
                }

                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['user_id']        = (int)    $user['id'];
                    $_SESSION['user_role']      = (string) $user['role'];
                    $_SESSION['user_name']      = (string) $user['name'];
                    $_SESSION['_last_activity'] = time();
                    error_log('[AUTH] remember-restored user_id=' . $user['id']);
                    return;
                }
            }

            // Cookie present but invalid — burn it.
            $this->clearRememberCookie();
        }

        // ── 3. Not authenticated ────────────────────────────────────────────
        $this->redirectToLogin();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function redirectToLogin(?string $flash = null): void
    {
        if ($flash !== null) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['_flash'] = ['type' => 'error', 'msg' => $flash];
        }

        $return = $_SERVER['REQUEST_URI'] ?? '/admin/dashboard';
        $url    = '/admin/login?return=' . rawurlencode($return);
        header('Location: ' . $url, true, 302);
        exit;
    }

    private function destroySession(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Strict',
            ]);
        }
        session_destroy();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function clearRememberCookie(): void
    {
        setcookie(self::REMEMBER_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => APP_ENV === 'production',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }
}
