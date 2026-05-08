<?php
declare(strict_types=1);

namespace Core;

class PageCache
{
    private string $dir;
    private int    $ttl;

    public function __construct(string $dir = CACHE_PATH, int $ttl = PAGE_CACHE_TTL)
    {
        $this->dir = rtrim($dir, '/\\');
        $this->ttl = $ttl;
    }

    // ── Public API ────────────────────────────────────────────────────────────

    public function get(string $key): ?string
    {
        $file = $this->path($key);
        if (!file_exists($file)) return null;
        if (time() - filemtime($file) > $this->ttl) {
            unlink($file);
            return null;
        }
        return file_get_contents($file) ?: null;
    }

    public function set(string $key, string $content): void
    {
        if (!is_dir($this->dir)) mkdir($this->dir, 0755, true);
        file_put_contents($this->path($key), $content, LOCK_EX);
    }

    public function forget(string $key): void
    {
        $file = $this->path($key);
        if (file_exists($file)) unlink($file);
    }

    public function flush(): void
    {
        foreach (glob($this->dir . '/*.html') as $file) {
            unlink($file);
        }
    }

    // ── Cache-aside helper ────────────────────────────────────────────────────

    public function remember(string $key, callable $callback): string
    {
        $cached = $this->get($key);
        if ($cached !== null) return $cached;

        ob_start();
        $callback();
        $content = ob_get_clean();

        $this->set($key, $content);
        return $content;
    }

    public static function keyForUri(string $uri): string
    {
        $uri = trim($uri, '/');
        return preg_replace('/[^a-z0-9_\-]/', '_', $uri ?: 'home');
    }

    // ── Internal ─────────────────────────────────────────────────────────────

    private function path(string $key): string
    {
        return $this->dir . '/' . preg_replace('/[^a-z0-9_\-]/', '_', $key) . '.html';
    }
}
