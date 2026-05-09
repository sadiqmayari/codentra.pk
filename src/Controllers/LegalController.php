<?php
declare(strict_types=1);

namespace Controllers;

class LegalController extends \Core\Controller
{
    public function privacy(): void
    {
        $this->seo->set([
            'title'       => 'Privacy Policy | Codentra',
            'description' => 'How Codentra collects, uses, and protects your personal information.',
            'canonical'   => SITE_URL . '/privacy',
        ])->addJsonLd(\Seo::breadcrumbSchema([
            ['Home', '/'],
            ['Privacy', '/privacy'],
        ]));

        $this->render('privacy', ['lastUpdated' => '2026-05-09']);
    }

    public function terms(): void
    {
        $this->seo->set([
            'title'       => 'Terms of Service | Codentra',
            'description' => 'Terms governing the use of Codentra\'s website and services.',
            'canonical'   => SITE_URL . '/terms',
        ])->addJsonLd(\Seo::breadcrumbSchema([
            ['Home', '/'],
            ['Terms', '/terms'],
        ]));

        $this->render('terms', ['lastUpdated' => '2026-05-09']);
    }
}
