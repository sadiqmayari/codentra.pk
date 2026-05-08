<?php
declare(strict_types=1);

// ── Load .env ────────────────────────────────────────────────────────────────
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
        putenv(trim($key) . '=' . trim($val));
    }
}

// ── App ───────────────────────────────────────────────────────────────────────
define('APP_ENV',   $_ENV['APP_ENV']   ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

// ── Site ──────────────────────────────────────────────────────────────────────
define('SITE_NAME',    'Codentra');
define('SITE_TAGLINE', 'Code · Automate · Scale');
define('SITE_URL',     rtrim($_ENV['SITE_URL'] ?? 'https://codentra.pk', '/'));
define('SITE_EMAIL',   $_ENV['ADMIN_EMAIL'] ?? 'info@codentra.pk');
define('SITE_PHONE',   '+92 317 1263292');

// ── Paths ─────────────────────────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__));
define('VIEW_PATH',    ROOT_PATH . '/views');
define('CACHE_PATH',   ROOT_PATH . '/cache/pages');
define('UPLOAD_PATH',  ROOT_PATH . '/uploads');
define('PUBLIC_PATH',  ROOT_PATH . '/public');

// ── Cache ─────────────────────────────────────────────────────────────────────
define('PAGE_CACHE_TTL', 3600); // 1 hour

// ── Rate Limiting ─────────────────────────────────────────────────────────────
define('RATE_CONTACT_LIMIT',   5);
define('RATE_CONTACT_WINDOW',  3600);  // 1 hour
define('RATE_LOGIN_LIMIT',     5);
define('RATE_LOGIN_WINDOW',    900);   // 15 min
