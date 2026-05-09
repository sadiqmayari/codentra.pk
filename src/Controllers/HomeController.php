<?php
declare(strict_types=1);

namespace Controllers;

class HomeController extends \Core\Controller
{
    public function index(): void
    {
        $this->seo->set([
            'title'       => 'Codentra — Code · Automate · Scale | Web, Shopify & Automation Agency',
            'description' => 'Codentra builds premium websites, Shopify storefronts, and business automation systems for ambitious teams. Pakistan-based, globally focused.',
            'canonical'   => SITE_URL . '/',
        ])->addJsonLd(\Seo::organizationSchema());

        $services = [
            [
                'title' => 'Web Development',
                'desc'  => 'Performant, type-safe PHP & modern JS. Sites that score 95+ on Lighthouse out of the box.',
                'href'  => '/services#web-dev',
                'icon'  => $this->icon('code'),
            ],
            [
                'title' => 'Shopify',
                'desc'  => 'Custom themes and conversion-driven storefronts. Built to sell, not just to look good.',
                'href'  => '/services#shopify',
                'icon'  => $this->icon('cart'),
            ],
            [
                'title' => 'E-commerce Management',
                'desc'  => 'End-to-end ops: catalogue, listings, fulfilment, analytics, and growth. We run your store.',
                'href'  => '/services#ecommerce',
                'icon'  => $this->icon('chart'),
            ],
            [
                'title' => 'Business Automation',
                'desc'  => 'Workflow automation, integrations, and AI-driven optimization. Reclaim 10+ hours per week.',
                'href'  => '/services#automation',
                'icon'  => $this->icon('bolt'),
            ],
        ];

        $testimonials = [
            [
                'quote' => 'Codentra rebuilt our Shopify store and we saw a 2.3x lift in conversion within six weeks.',
                'name'  => 'Sarah K.',
                'role'  => 'Founder, Apparel DTC brand',
            ],
            [
                'quote' => 'They automated our order ops end-to-end. The team got back nearly two days a week.',
                'name'  => 'Faisal R.',
                'role'  => 'COO, B2B distribution',
            ],
            [
                'quote' => 'Best engineering partner we have worked with. Fast, communicative, and zero drama.',
                'name'  => 'Maya P.',
                'role'  => 'Product Lead, SaaS',
            ],
        ];

        $this->render('home', compact('services', 'testimonials'));
    }

    private function icon(string $name): string
    {
        $icons = [
            'code'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
            'cart'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>',
            'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 14l4-4 4 4 6-6"/></svg>',
            'bolt'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
        ];
        return $icons[$name] ?? '';
    }
}
