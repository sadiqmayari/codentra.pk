<?php
declare(strict_types=1);

namespace Core;

abstract class Controller
{
    protected Seo $seo;

    public function __construct()
    {
        $this->seo = new \Seo();
    }

    // ── View rendering ────────────────────────────────────────────────────────

    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);
        $seo = $this->seo;

        $viewFile   = VIEW_PATH . '/pages/' . ltrim($view, '/') . '.php';
        $layoutFile = VIEW_PATH . '/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        // Capture view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render within layout
        require $layoutFile;
    }

    protected function renderPartial(string $partial, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $file = VIEW_PATH . '/partials/' . $partial . '.php';
        if (file_exists($file)) require $file;
    }

    // ── Redirects ─────────────────────────────────────────────────────────────

    protected function redirect(string $path, int $code = 302): never
    {
        $url = str_starts_with($path, 'http') ? $path : SITE_URL . '/' . ltrim($path, '/');
        header('Location: ' . $url, true, $code);
        exit;
    }

    // ── JSON response ─────────────────────────────────────────────────────────

    protected function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ── Abort ─────────────────────────────────────────────────────────────────

    protected function abort(int $code = 404): never
    {
        http_response_code($code);
        $view = VIEW_PATH . '/pages/errors/' . $code . '.php';
        if (file_exists($view)) require $view;
        exit;
    }
}
