<?php
declare(strict_types=1);

namespace Core;

class Router
{
    private array $routes = [];
    private array|null $fallbackHandler = null;

    // ── Registration ──────────────────────────────────────────────────────────

    public function fallback(array $handler): void
    {
        $this->fallbackHandler = $handler;
    }

    public function get(string $pattern, array|callable $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, array|callable $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    public function any(string $pattern, array|callable $handler): void
    {
        $this->addRoute('ANY', $pattern, $handler);
    }

    private function addRoute(string $method, string $pattern, array|callable $handler): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler');
    }

    // ── Dispatch ──────────────────────────────────────────────────────────────

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') continue;

            $params = $this->match($route['pattern'], $uri);
            if ($params === null) continue;

            $this->call($route['handler'], $params);
            return;
        }

        $this->notFound();
    }

    // ── Pattern matching ──────────────────────────────────────────────────────

    private function match(string $pattern, string $uri): ?array
    {
        // Normalize pattern
        $pattern = '/' . trim($pattern, '/');
        if ($pattern === '/') $pattern = '';

        // Convert {param} placeholders to named captures
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $uri === '/' ? '' : $uri, $matches)) {
            return null;
        }

        // Return only string-keyed (named) captures
        return array_filter($matches, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY);
    }

    // ── Handler invocation ────────────────────────────────────────────────────

    private function call(array|callable $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        [$class, $method] = $handler;
        $controller = new $class();
        call_user_func_array([$controller, $method], $params);
    }

    // ── Fallback ──────────────────────────────────────────────────────────────

    private function notFound(): void
    {
        http_response_code(404);

        if ($this->fallbackHandler !== null) {
            $this->call($this->fallbackHandler, []);
            return;
        }

        $view = VIEW_PATH . '/pages/errors/404.php';
        if (file_exists($view)) {
            require $view;
        } else {
            echo '<!DOCTYPE html><html><head><title>404 — Not Found</title></head><body>'
               . '<h1>404 — Page Not Found</h1><p>No route matched this request.</p>'
               . '</body></html>';
        }
    }
}
