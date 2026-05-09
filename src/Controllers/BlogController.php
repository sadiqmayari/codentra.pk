<?php
declare(strict_types=1);

namespace Controllers;

class BlogController extends \Core\Controller
{
    private const PER_PAGE = 9;

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $posts      = [];
        $total      = 0;
        $totalPages = 0;
        $dbError    = false;

        try {
            $postModel  = new \Models\Post();
            $posts      = $postModel->published($page, self::PER_PAGE);
            $total      = $postModel->countPublished();
            $totalPages = max(1, (int) ceil($total / self::PER_PAGE));
        } catch (\Throwable $e) {
            $dbError = true;
        }

        $this->seo->set([
            'title'       => 'Blog — Engineering & e-commerce notes from Codentra',
            'description' => 'Practical writeups on web performance, Shopify conversion, e-commerce ops, and business automation from the Codentra team.',
            'canonical'   => SITE_URL . '/blog' . ($page > 1 ? '?page=' . $page : ''),
        ])
        ->addJsonLd(\Seo::breadcrumbSchema([
            ['Home', '/'],
            ['Blog', '/blog'],
        ]));

        $this->render('blog/index', [
            'posts'      => $posts,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
            'dbError'    => $dbError,
        ]);
    }

    public function single(string $slug): void
    {
        try {
            $postModel = new \Models\Post();
            $post = $postModel->findBySlug($slug);
        } catch (\Throwable) {
            $this->abort(404);
        }

        if (!$post) {
            $this->abort(404);
        }

        // Don't count crawler hits in views
        if (!$this->isLikelyBot()) {
            try { $postModel->incrementViews((int) $post['id']); } catch (\Throwable) {}
        }

        $publishedAt = $post['published_at'] ?? $post['created_at'];
        $iso         = date('c', strtotime($publishedAt));
        $imageAbs    = !empty($post['featured_image']) ? SITE_URL . $post['featured_image'] : null;

        $this->seo->set([
            'title'       => $post['title'] . ' | Codentra',
            'description' => $post['excerpt'] ?? '',
            'canonical'   => SITE_URL . '/blog/' . $post['slug'],
            'ogImage'     => $post['featured_image'] ?? '',
            'ogType'      => 'article',
        ])
        ->addJsonLd([
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => $post['title'],
            'description'   => $post['excerpt'] ?? '',
            'image'         => $imageAbs ? [$imageAbs] : [],
            'datePublished' => $iso,
            'dateModified'  => date('c', strtotime($post['updated_at'] ?? $publishedAt)),
            'author'        => [
                '@type' => 'Person',
                'name'  => $post['author_name'] ?? 'Codentra',
            ],
            'publisher'     => \Seo::organizationSchema(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => SITE_URL . '/blog/' . $post['slug'],
            ],
        ])
        ->addJsonLd(\Seo::breadcrumbSchema([
            ['Home', '/'],
            ['Blog', '/blog'],
            [$post['title'], '/blog/' . $post['slug']],
        ]));

        $this->render('blog/single', ['post' => $post]);
    }

    private function isLikelyBot(): bool
    {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua === '') return true;
        return (bool) preg_match('/(bot|crawl|spider|slurp|preview|fetch|monitor|headless)/', $ua);
    }
}
