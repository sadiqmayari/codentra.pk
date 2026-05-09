<?php
declare(strict_types=1);

namespace Controllers;

class AboutController extends \Core\Controller
{
    public function index(): void
    {
        $this->seo->set([
            'title'       => 'About — A Pakistan-based agency engineering for global teams | Codentra',
            'description' => 'Codentra is a small, senior team building premium websites, Shopify stores, and automation systems. Code · Automate · Scale.',
            'canonical'   => SITE_URL . '/about',
        ])
        ->addJsonLd(\Seo::organizationSchema())
        ->addJsonLd(\Seo::breadcrumbSchema([
            ['Home', '/'],
            ['About', '/about'],
        ]));

        $values = [
            ['title' => 'Outcomes over deliverables', 'desc' => 'We measure success in business outcomes — revenue, conversion, hours saved — not in pixels shipped.'],
            ['title' => 'Senior-only team',           'desc' => 'No juniors learning on your project. Every line of code is written by an engineer with five-plus years in the stack.'],
            ['title' => 'Performance is non-negotiable', 'desc' => 'Lighthouse 95+ is our floor, not our ceiling. Slow sites lose customers — we don\'t ship them.'],
            ['title' => 'Security by default',         'desc' => 'Prepared statements, CSRF, Argon2id, secure headers, and rate limiting are present from day one — never bolted on later.'],
            ['title' => 'Plain English communication', 'desc' => 'Weekly updates you can read on a phone. No jargon walls. No mystery meetings.'],
            ['title' => 'Long-term partnerships',     'desc' => 'Launch is the start. We maintain, monitor, and iterate after the site goes live — most clients stay with us for years.'],
        ];

        $this->render('about', compact('values'));
    }
}
