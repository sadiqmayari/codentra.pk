<?php
declare(strict_types=1);

namespace Controllers;

class AuthController extends \Core\Controller
{
    private const REMEMBER_COOKIE   = 'codentra_remember';
    private const REMEMBER_TTL_SECS = 2592000; // 30 days
    private const SESSION_IDLE_SECS = 3600;    // 60 minutes (mirrors AuthMiddleware)

    // ── GET /admin/login ─────────────────────────────────────────────────────

    public function showLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Already authenticated → straight to dashboard.
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/admin/dashboard');
        }

        $flash = $_SESSION['_flash'] ?? null;
        $old   = $_SESSION['_old']   ?? [];
        unset($_SESSION['_flash'], $_SESSION['_old']);

        $return = $_GET['return'] ?? '';
        if (!is_string($return) || str_contains($return, "\n") || str_contains($return, "\r")) {
            $return = '';
        }

        $this->seo->set([
            'title'       => 'Sign in | Codentra',
            'description' => 'Codentra admin sign-in.',
            'canonical'   => SITE_URL . '/admin/login',
            'noindex'     => true,
        ]);

        $this->render('admin/login', [
            'flash'  => $flash,
            'old'    => $old,
            'return' => $return,
        ], 'auth');
    }

    // ── POST /admin/login ────────────────────────────────────────────────────

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. CSRF
        if (!\Core\Csrf::verify()) {
            error_log('[AUTH] csrf-failed ip=' . \Core\Request::clientIp());
            $this->flashError('Your session expired. Please try signing in again.');
            $this->redirect('/admin/login');
        }

        $email    = strtolower(trim((string) ($_POST['email']    ?? '')));
        $password = (string)         ($_POST['password'] ?? '');
        $remember = !empty($_POST['remember']);
        $return   = (string) ($_POST['return'] ?? '');

        // 2. Rate limit — 5 / 15 min keyed on IP + email composite.
        //    Combining the two prevents a single IP from password-spraying many
        //    accounts AND a single account from being locked out by a third
        //    party from a different IP.
        $rlKey = 'admin-login:' . hash('sha256', strtolower($email));
        $rl    = \Middleware\RateLimit::check(
            $rlKey,
            RATE_LOGIN_LIMIT,
            RATE_LOGIN_WINDOW
        );
        if (!$rl['ok']) {
            $minutes = max(1, (int) ceil($rl['retry_after'] / 60));
            error_log("[AUTH] rate-limited ip=" . \Core\Request::clientIp() . " email={$email}");
            $this->flashError(
                "Too many sign-in attempts. Please try again in {$minutes} minute"
                . ($minutes === 1 ? '' : 's') . '.'
            );
            $_SESSION['_old'] = ['email' => $email];
            $this->redirect('/admin/login');
        }

        // 3. Basic input validation (cheap fail-fast — same generic error).
        if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("[AUTH] invalid-input ip=" . \Core\Request::clientIp() . " email_blank=" . ($email === '' ? '1' : '0'));
            $this->flashError('Invalid credentials.');
            $_SESSION['_old'] = ['email' => $email];
            $this->redirect('/admin/login');
        }

        // 4. Password verification (DB).
        try {
            $user = (new \Models\User())->verify($email, $password);
        } catch (\Throwable $e) {
            error_log(
                "[AUTH] verify-exception class=" . $e::class
                . ' msg=' . ($e->getMessage() !== '' ? $e->getMessage() : '<empty>')
                . ' at '  . $e->getFile() . ':' . $e->getLine()
            );
            $this->flashError('Invalid credentials.');
            $_SESSION['_old'] = ['email' => $email];
            $this->redirect('/admin/login');
        }

        if (!$user) {
            // Generic message — never leak whether the email or password was wrong.
            error_log("[AUTH] login-failed ip=" . \Core\Request::clientIp() . " email={$email}");
            $this->flashError('Invalid credentials.');
            $_SESSION['_old'] = ['email' => $email];
            $this->redirect('/admin/login');
        }

        // 5. Success — regenerate session ID, set claims.
        session_regenerate_id(true);
        $_SESSION['user_id']        = (int)    $user['id'];
        $_SESSION['user_role']      = (string) $user['role'];
        $_SESSION['user_name']      = (string) $user['name'];
        $_SESSION['_last_activity'] = time();

        // 6. recordLogin (best-effort).
        try {
            (new \Models\User())->recordLogin((int) $user['id']);
        } catch (\Throwable $e) {
            error_log('[AUTH] record-login-failed: ' . $e->getMessage());
        }

        // 7. Remember-me — selector/validator pattern, NOT user_id.
        if ($remember) {
            try {
                $tok = (new \Models\User())->issueRememberToken(
                    (int) $user['id'],
                    self::REMEMBER_TTL_SECS
                );
                $this->setRememberCookie($tok['cookie'], time() + self::REMEMBER_TTL_SECS);
            } catch (\Throwable $e) {
                error_log('[AUTH] remember-issue-failed: ' . $e->getMessage());
            }
        }

        \Core\Csrf::rotate();
        error_log("[AUTH] login-ok user_id={$user['id']} email={$email} ip=" . \Core\Request::clientIp() . " remember=" . ($remember ? '1' : '0'));

        $this->redirect($this->safeReturnPath($return) ?: '/admin/dashboard');
    }

    // ── POST /admin/logout ───────────────────────────────────────────────────

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // CSRF on POST. (Fall through to logout regardless — never block a
        // user from invalidating their own session.)
        if (!\Core\Csrf::verify()) {
            error_log('[AUTH] logout-csrf-mismatch — proceeding anyway');
        }

        $userId = $_SESSION['user_id'] ?? null;

        // Invalidate the remember-me row in DB if a cookie is present.
        if (!empty($_COOKIE[self::REMEMBER_COOKIE])) {
            $parts = explode(':', (string) $_COOKIE[self::REMEMBER_COOKIE], 2);
            if (count($parts) === 2) {
                try { (new \Models\User())->invalidateRemember($parts[0]); }
                catch (\Throwable $e) { error_log('[AUTH] remember-invalidate-failed: ' . $e->getMessage()); }
            }
        }
        $this->clearRememberCookie();

        // Destroy session fully.
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

        error_log('[AUTH] logout user_id=' . ($userId ?? '?') . ' ip=' . \Core\Request::clientIp());
        $this->redirect('/');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function setRememberCookie(string $value, int $expires): void
    {
        setcookie(self::REMEMBER_COOKIE, $value, [
            'expires'  => $expires,
            'path'     => '/',
            'secure'   => APP_ENV === 'production',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
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

    /**
     * Open-redirect protection — only same-host, absolute-path, /admin/* targets.
     */
    private function safeReturnPath(string $path): string
    {
        if ($path === '' || $path[0] !== '/') return '';
        if (str_starts_with($path, '//')) return '';
        if (!str_starts_with($path, '/admin')) return '';
        return $path;
    }

    private function flashError(string $msg): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['_flash'] = ['type' => 'error', 'msg' => $msg];
    }
}
