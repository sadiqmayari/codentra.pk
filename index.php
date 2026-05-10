<?php
declare(strict_types=1);

// ── Built-in dev server: serve real files directly ───────────────────────────
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    if ($path !== '/' && is_file($file)) {
        return false; // let the built-in server serve the static asset
    }
}

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
    // Strip top-level namespace prefix if present, then translate any
    // remaining namespace separators into directory separators so that
    // sub-namespaces like Controllers\Admin\DashboardController resolve
    // to src/Controllers/Admin/DashboardController.php.
    $stripped = str_replace(['Controllers\\', 'Models\\', 'Middleware\\', 'Core\\'], '', $class);
    $stripped = str_replace('\\', '/', $stripped);

    $locations = [
        __DIR__ . '/src/Controllers/' . $stripped . '.php',
        __DIR__ . '/src/Models/'      . $stripped . '.php',
        __DIR__ . '/src/Middleware/'  . $stripped . '.php',
        __DIR__ . '/src/Core/'        . $stripped . '.php',
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
$router->get('/',            [\Controllers\HomeController::class,     'index']);
$router->get('/services',    [\Controllers\ServicesController::class, 'index']);
$router->get('/about',       [\Controllers\AboutController::class,    'index']);
$router->get('/blog',        [\Controllers\BlogController::class,     'index']);
$router->get('/blog/{slug}', [\Controllers\BlogController::class,     'single']);
$router->get('/contact',         [\Controllers\ContactController::class, 'index']);
$router->post('/contact',        [\Controllers\LeadController::class,    'submit']);
$router->get('/contact/thanks',  [\Controllers\LeadController::class,    'thanks']);
$router->get('/privacy',     [\Controllers\LegalController::class,    'privacy']);
$router->get('/terms',       [\Controllers\LegalController::class,    'terms']);

// ── Admin / Auth ─────────────────────────────────────────────────────────────
$router->get('/admin',           [\Controllers\AdminController::class, 'index']);
$router->get('/admin/login',     [\Controllers\AuthController::class,  'showLogin']);
$router->post('/admin/login',    [\Controllers\AuthController::class,  'login']);
$router->post('/admin/logout',   [\Controllers\AuthController::class,  'logout']);

// Authenticated admin routes — middleware runs before the controller method.
$router->get('/admin/dashboard',
    [\Controllers\Admin\DashboardController::class, 'index'],
    [\Middleware\AuthMiddleware::class]
);

// ── Admin: Leads management (Phase 8) — all behind AuthMiddleware ──────────
$router->get('/admin/leads',
    [\Controllers\Admin\LeadsController::class, 'index'],
    [\Middleware\AuthMiddleware::class]
);
$router->get('/admin/leads/export',
    [\Controllers\Admin\LeadsController::class, 'export'],
    [\Middleware\AuthMiddleware::class]
);
$router->get('/admin/leads/{id}',
    [\Controllers\Admin\LeadsController::class, 'show'],
    [\Middleware\AuthMiddleware::class]
);
$router->post('/admin/leads/{id}/status',
    [\Controllers\Admin\LeadsController::class, 'updateStatus'],
    [\Middleware\AuthMiddleware::class]
);
$router->post('/admin/leads/{id}/notes',
    [\Controllers\Admin\LeadsController::class, 'saveNotes'],
    [\Middleware\AuthMiddleware::class]
);
$router->post('/admin/leads/{id}/delete',
    [\Controllers\Admin\LeadsController::class, 'delete'],
    [\Middleware\AuthMiddleware::class]
);

// ── Admin: Blog Posts (Phase 9) — all behind AuthMiddleware ────────────────
$router->get('/admin/posts',
    [\Controllers\Admin\PostsController::class, 'index'],
    [\Middleware\AuthMiddleware::class]
);
$router->get('/admin/posts/new',
    [\Controllers\Admin\PostsController::class, 'create'],
    [\Middleware\AuthMiddleware::class]
);
$router->post('/admin/posts',
    [\Controllers\Admin\PostsController::class, 'store'],
    [\Middleware\AuthMiddleware::class]
);
$router->get('/admin/posts/{id}/edit',
    [\Controllers\Admin\PostsController::class, 'edit'],
    [\Middleware\AuthMiddleware::class]
);
$router->post('/admin/posts/{id}',
    [\Controllers\Admin\PostsController::class, 'update'],
    [\Middleware\AuthMiddleware::class]
);
$router->post('/admin/posts/{id}/delete',
    [\Controllers\Admin\PostsController::class, 'delete'],
    [\Middleware\AuthMiddleware::class]
);

$router->fallback([\Controllers\ErrorController::class, 'notFound']);

// ── Dispatch ──────────────────────────────────────────────────────────────────
$router->dispatch();
