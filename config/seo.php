<?php
declare(strict_types=1);

class Seo
{
    private string $title       = '';
    private string $description = '';
    private string $canonical   = '';
    private string $ogImage     = '';
    private string $ogType      = 'website';
    private array  $jsonLd      = [];

    private static array $defaults = [
        'title'       => 'Codentra — Code · Automate · Scale',
        'description' => 'Codentra is a premium web development, Shopify, e-commerce management & business automation agency based in Pakistan.',
        'ogImage'     => '/public/images/og-default.webp',
    ];

    public function set(array $meta): self
    {
        foreach ($meta as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
        return $this;
    }

    public function addJsonLd(array $schema): self
    {
        $this->jsonLd[] = $schema;
        return $this;
    }

    public function title(): string
    {
        return htmlspecialchars($this->title ?: self::$defaults['title'], ENT_QUOTES, 'UTF-8');
    }

    public function render(): string
    {
        $title       = $this->title();
        $desc        = htmlspecialchars($this->description ?: self::$defaults['description'], ENT_QUOTES, 'UTF-8');
        $canonical   = htmlspecialchars($this->canonical ?: (SITE_URL . $_SERVER['REQUEST_URI']), ENT_QUOTES, 'UTF-8');
        $ogImage     = SITE_URL . ($this->ogImage ?: self::$defaults['ogImage']);

        $out  = "<title>{$title}</title>\n";
        $out .= "<meta name=\"description\" content=\"{$desc}\">\n";
        $out .= "<link rel=\"canonical\" href=\"{$canonical}\">\n";

        // Open Graph
        $out .= "<meta property=\"og:type\"        content=\"{$this->ogType}\">\n";
        $out .= "<meta property=\"og:title\"       content=\"{$title}\">\n";
        $out .= "<meta property=\"og:description\" content=\"{$desc}\">\n";
        $out .= "<meta property=\"og:url\"         content=\"{$canonical}\">\n";
        $out .= "<meta property=\"og:image\"       content=\"{$ogImage}\">\n";
        $out .= "<meta property=\"og:site_name\"   content=\"Codentra\">\n";

        // Twitter Card
        $out .= "<meta name=\"twitter:card\"        content=\"summary_large_image\">\n";
        $out .= "<meta name=\"twitter:title\"       content=\"{$title}\">\n";
        $out .= "<meta name=\"twitter:description\" content=\"{$desc}\">\n";
        $out .= "<meta name=\"twitter:image\"       content=\"{$ogImage}\">\n";

        // JSON-LD
        foreach ($this->jsonLd as $schema) {
            $out .= '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
        }

        return $out;
    }

    // ── Preset schemas ────────────────────────────────────────────────────────

    public static function organizationSchema(): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            'name'        => 'Codentra',
            'url'         => SITE_URL,
            'logo'        => SITE_URL . '/public/images/logo.webp',
            'email'       => SITE_EMAIL,
            'telephone'   => SITE_PHONE,
            'description' => 'Web development, Shopify, e-commerce management & business automation agency.',
            'sameAs'      => [],
        ];
    }

    public static function breadcrumbSchema(array $crumbs): array
    {
        $items = [];
        foreach ($crumbs as $i => [$name, $url]) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $name,
                'item'     => SITE_URL . $url,
            ];
        }
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}
