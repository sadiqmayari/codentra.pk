<?php
declare(strict_types=1);

namespace Controllers;

class SitemapController extends \Core\Controller
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_REL = '/cache/sitemap.xml';

    public function index(): void
    {
        $cachePath = ROOT_PATH . self::CACHE_REL;
        $xml       = $this->readCache($cachePath);

        if ($xml === null) {
            $xml = $this->generate();
            $this->writeCache($cachePath, $xml);
        }

        // Cache-Control on the response too, so any reverse proxy / CF can
        // hold a copy. Bot hits are unauthenticated so this is safe.
        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo $xml;
        exit;
    }

    // ── Generation ──────────────────────────────────────────────────────────

    private function generate(): string
    {
        $base = rtrim(SITE_URL, '/');

        // Static pages — lastmod from the view file's mtime so a deploy
        // that touched copy automatically refreshes the value.
        $statics = [
            ['/',          'home.php',           '1.0', 'weekly'],
            ['/services',  'services.php',       '0.9', 'monthly'],
            ['/about',     'about.php',          '0.8', 'monthly'],
            ['/blog',      'blog/index.php',     '0.8', 'daily'],
            ['/contact',   'contact.php',        '0.7', 'monthly'],
            ['/privacy',   'privacy.php',        '0.3', 'yearly'],
            ['/terms',     'terms.php',          '0.3', 'yearly'],
        ];

        $urls = [];
        foreach ($statics as [$path, $viewRel, $priority, $changefreq]) {
            $abs = VIEW_PATH . '/pages/' . $viewRel;
            $mt  = @filemtime($abs) ?: time();
            $urls[] = $this->urlEntry(
                $base . $path,
                date('Y-m-d', $mt),
                $changefreq,
                $priority
            );
        }

        // Blog posts — published, not soft-deleted.
        try {
            $stmt = \Database::getInstance()->prepare(
                "SELECT `slug`, `updated_at`
                 FROM `posts`
                 WHERE `deleted_at` IS NULL
                   AND `status`     = 'published'
                   AND `published_at` <= CURRENT_TIMESTAMP
                 ORDER BY `updated_at` DESC"
            );
            $stmt->execute();
            foreach ($stmt->fetchAll() as $row) {
                $slug = (string) ($row['slug'] ?? '');
                if ($slug === '') continue;
                $lastmod = !empty($row['updated_at'])
                    ? date('Y-m-d', strtotime((string) $row['updated_at']))
                    : date('Y-m-d');
                $urls[] = $this->urlEntry(
                    $base . '/blog/' . $slug,
                    $lastmod,
                    'monthly',
                    '0.7'
                );
            }
        } catch (\Throwable $e) {
            // No DB? Static-only sitemap is still a valid sitemap.
            error_log('[SITEMAP] post-list-failed msg=' . $e->getMessage());
        }

        $body  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $body .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $body .= implode("\n", $urls) . "\n";
        $body .= "</urlset>\n";
        return $body;
    }

    private function urlEntry(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        $loc = htmlspecialchars($loc, ENT_QUOTES | ENT_XML1, 'UTF-8');
        return "  <url>"
             . "<loc>{$loc}</loc>"
             . "<lastmod>{$lastmod}</lastmod>"
             . "<changefreq>{$changefreq}</changefreq>"
             . "<priority>{$priority}</priority>"
             . "</url>";
    }

    // ── Caching ─────────────────────────────────────────────────────────────

    private function readCache(string $path): ?string
    {
        if (!file_exists($path)) return null;
        if (time() - filemtime($path) > self::CACHE_TTL) return null;
        $contents = @file_get_contents($path);
        return $contents !== false ? $contents : null;
    }

    private function writeCache(string $path, string $xml): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        @file_put_contents($path, $xml, LOCK_EX);
    }
}
