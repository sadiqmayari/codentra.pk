<?php
declare(strict_types=1);

// ── Bootstrap ─────────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/seo.php';
require_once __DIR__ . '/src/Core/Router.php';
require_once __DIR__ . '/src/Core/Controller.php';
require_once __DIR__ . '/src/Core/Model.php';
require_once __DIR__ . '/src/Core/PageCache.php';
require_once __DIR__ . '/src/Core/Csrf.php';

// ── Session ───────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => APP_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ── Error display ─────────────────────────────────────────────────────────────
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// ── Autoload controllers & models ─────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    // Strip namespace prefix if present
    $class = str_replace(['Controllers\\', 'Models\\', 'Middleware\\', 'Core\\'], '', $class);

    $locations = [
        __DIR__ . '/src/Controllers/' . $class . '.php',
        __DIR__ . '/src/Models/'      . $class . '.php',
        __DIR__ . '/src/Middleware/'  . $class . '.php',
        __DIR__ . '/src/Core/'        . $class . '.php',
    ];

    foreach ($locations as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ── Router ────────────────────────────────────────────────────────────────────
$router = new \Core\Router();

// ── Routes ────────────────────────────────────────────────────────────────────
// (Populated as pages are built — see BUILD_PROMPTS.md)
// $router->get('/',          [\Controllers\HomeController::class,     'index']);
// $router->get('/services',  [\Controllers\ServicesController::class, 'index']);
// $router->get('/about',     [\Controllers\HomeController::class,     'about']);
// $router->get('/blog',      [\Controllers\BlogController::class,     'index']);
// $router->get('/blog/{slug}',[\Controllers\BlogController::class,    'single']);
// $router->get('/contact',   [\Controllers\ContactController::class,  'index']);
// $router->post('/contact',  [\Controllers\ContactController::class,  'submit']);

// ── Dispatch ──────────────────────────────────────────────────────────────────
$router->dispatch();
