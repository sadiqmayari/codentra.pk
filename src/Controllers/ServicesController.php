<?php
declare(strict_types=1);

namespace Controllers;

class ServicesController extends \Core\Controller
{
    public function index(): void
    {
        $this->seo->set([
            'title'       => 'Services — Web Development, Shopify, E-commerce & Automation | Codentra',
            'description' => 'Codentra delivers high-performance websites, Shopify storefronts, e-commerce operations, and business automation. Outcomes-first engagements for ambitious teams.',
            'canonical'   => SITE_URL . '/services',
        ])
        ->addJsonLd([
            '@context' => 'https://schema.org',
            '@type'    => 'Service',
            'provider' => \Seo::organizationSchema(),
            'serviceType' => ['Web Development', 'Shopify', 'E-commerce Management', 'Business Automation'],
            'areaServed' => ['PK', 'Worldwide'],
        ])
        ->addJsonLd(\Seo::breadcrumbSchema([
            ['Home', '/'],
            ['Services', '/services'],
        ]));

        $services = [
            [
                'anchor'   => 'web-dev',
                'title'    => 'Web Development',
                'lede'     => 'Custom websites and web apps built with modern PHP and progressive JS — engineered to score 95+ on Lighthouse out of the box.',
                'outcomes' => [
                    'Lighthouse 95+ across Performance, Accessibility, Best Practices, and SEO',
                    'Clean URLs, sitemap, structured data, and SEO-ready by default',
                    'Type-safe PHP 8.x with prepared statements and CSRF on every form',
                    'Deploys to Hostinger / VPS / Docker with one-command release scripts',
                    'Component-driven UI with design tokens and a documented component library',
                ],
                'stack' => ['PHP 8.3', 'PDO + MySQL', 'Vanilla JS / TypeScript', 'Three.js', 'Apache / nginx', 'Hostinger', 'GitHub Actions'],
            ],
            [
                'anchor'   => 'shopify',
                'title'    => 'Shopify',
                'lede'     => 'Conversion-driven Shopify storefronts — custom themes, app integrations, and post-purchase journeys that turn visitors into customers.',
                'outcomes' => [
                    'Custom Liquid theme work tuned for Core Web Vitals',
                    'Headless storefront option with Hydrogen / custom front-end',
                    'Post-purchase upsells, abandoned-cart recovery, review automation',
                    'A/B tested PDP and checkout flows with measurable lift',
                    'Inventory + order automation between Shopify and your back-office',
                ],
                'stack' => ['Shopify Liquid', 'Shopify CLI', 'Hydrogen', 'Storefront API', 'Klaviyo', 'Judge.me', 'Recharge'],
            ],
            [
                'anchor'   => 'ecommerce',
                'title'    => 'E-commerce Management',
                'lede'     => 'End-to-end store operations — catalogue, listings, fulfilment, analytics, and growth — handled by an embedded ops team.',
                'outcomes' => [
                    'Catalogue and listing optimisation across Shopify, Amazon, and Etsy',
                    'Fulfilment routing, returns, and customer support workflows',
                    'Weekly KPI reports: revenue, AOV, CR, ROAS, LTV, churn',
                    'Paid acquisition (Meta, Google, TikTok) with creative iteration',
                    'Email and SMS automation flows in Klaviyo or Omnisend',
                ],
                'stack' => ['Shopify Admin API', 'Amazon Seller Central', 'Klaviyo', 'Google Ads', 'Meta Ads', 'TikTok Ads', 'Looker Studio'],
            ],
            [
                'anchor'   => 'automation',
                'title'    => 'Business Automation',
                'lede'     => 'Reclaim 10+ hours per week per ops person. We map the workflow, build the integration, and own the maintenance.',
                'outcomes' => [
                    'Order tagging, fraud flagging, and fulfilment routing',
                    'CRM sync between Shopify, HubSpot, and your accounting system',
                    'AI-powered customer support triage and reply drafts',
                    'Custom webhook receivers and queue workers for bespoke logic',
                    'Slack alerts on inventory, refund spikes, and SLA breaches',
                ],
                'stack' => ['n8n', 'Make.com', 'Zapier', 'OpenAI / Claude API', 'PHP webhook receivers', 'Redis queues', 'Slack API'],
            ],
        ];

        $this->render('services', compact('services'));
    }
}
