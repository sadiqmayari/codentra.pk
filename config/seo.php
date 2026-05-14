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
    private bool   $noindex     = false;

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

    public function hasJsonLdType(string $type): bool
    {
        foreach ($this->jsonLd as $s) {
            if (($s['@type'] ?? null) === $type) return true;
        }
        return false;
    }

    public function title(): string
    {
        return htmlspecialchars($this->title ?: self::$defaults['title'], ENT_QUOTES, 'UTF-8');
    }

    public function render(): string
    {
        $title       = $this->title();
        $desc        = htmlspecialchars($this->description ?: self::settingOr('site_description', self::$defaults['description']), ENT_QUOTES, 'UTF-8');
        $canonical   = htmlspecialchars($this->canonical ?: (SITE_URL . ($_SERVER['REQUEST_URI'] ?? '/')), ENT_QUOTES, 'UTF-8');
        $ogImage     = SITE_URL . ($this->ogImage ?: self::settingOr('seo_og_image', self::$defaults['ogImage']));

        $out  = "<title>{$title}</title>\n";
        $out .= "<meta name=\"description\" content=\"{$desc}\">\n";
        $out .= "<link rel=\"canonical\" href=\"{$canonical}\">\n";

        if ($this->noindex) {
            $out .= "<meta name=\"robots\" content=\"noindex,follow\">\n";
        }

        // Open Graph
        $out .= "<meta property=\"og:type\"        content=\"{$this->ogType}\">\n";
        $out .= "<meta property=\"og:title\"       content=\"{$title}\">\n";
        $out .= "<meta property=\"og:description\" content=\"{$desc}\">\n";
        $out .= "<meta property=\"og:url\"         content=\"{$canonical}\">\n";
        $out .= "<meta property=\"og:image\"       content=\"{$ogImage}\">\n";
        $out .= "<meta property=\"og:site_name\"   content=\"" . htmlspecialchars(self::settingOr('site_title', 'Codentra'), ENT_QUOTES, 'UTF-8') . "\">\n";

        // Twitter Card
        $out .= "<meta name=\"twitter:card\"        content=\"summary_large_image\">\n";
        $out .= "<meta name=\"twitter:title\"       content=\"{$title}\">\n";
        $out .= "<meta name=\"twitter:description\" content=\"{$desc}\">\n";
        $out .= "<meta name=\"twitter:image\"       content=\"{$ogImage}\">\n";

        // Search Console verification token (when configured)
        $gsc = self::settingOr('google_search_console', '');
        if ($gsc !== '') {
            $out .= "<meta name=\"google-site-verification\" content=\"" . htmlspecialchars($gsc, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        // JSON-LD blocks
        foreach ($this->jsonLd as $schema) {
            $out .= '<script type="application/ld+json">'
                  . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                  . "</script>\n";
        }

        // Google Analytics — appended to head (gtag.js is allow-listed via
        // the existing CSP `script-src` for cdn.jsdelivr.net? No — gtag is
        // googletagmanager.com. Only emit the snippet if the ID is set,
        // and remember to add the host to script-src in .htaccess if you
        // ever turn this on.)
        $ga = self::settingOr('google_analytics_id', '');
        if ($ga !== '' && preg_match('/^G-[A-Z0-9]+$/', $ga)) {
            $gaSafe = htmlspecialchars($ga, ENT_QUOTES, 'UTF-8');
            $out .= "<!-- gtag.js requires googletagmanager.com in CSP script-src -->\n";
            $out .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$gaSafe}\"></script>\n";
            $out .= "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}"
                  . "gtag('js', new Date());gtag('config','{$gaSafe}');</script>\n";
        }

        return $out;
    }

    // ── Schema presets ────────────────────────────────────────────────────

    public static function organizationSchema(): array
    {
        $sameAs = [];
        foreach (['social_facebook', 'social_instagram', 'social_linkedin', 'social_twitter', 'social_youtube'] as $key) {
            $url = self::settingOr($key, '');
            if ($url !== '') $sameAs[] = $url;
        }

        return array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            'name'        => self::settingOr('business_name', 'Codentra'),
            'url'         => SITE_URL,
            'logo'        => SITE_URL . self::settingOr('site_logo', '/public/images/logo.webp'),
            'description' => self::settingOr('site_description', self::$defaults['description']),
            'sameAs'      => $sameAs,
            'contactPoint' => [
                '@type'           => 'ContactPoint',
                'telephone'       => self::settingOr('contact_phone', SITE_PHONE),
                'email'           => self::settingOr('contact_email', SITE_EMAIL),
                'contactType'     => 'customer service',
                'areaServed'      => 'Worldwide',
                'availableLanguage' => ['English', 'Urdu'],
            ],
            'foundingDate' => self::settingOr('business_founded_year', '2025'),
            'address'      => [
                '@type'           => 'PostalAddress',
                'addressCountry'  => self::settingOr('business_country', 'Pakistan'),
                'addressLocality' => self::settingOr('business_city',    'Islamabad'),
            ],
        ]);
    }

    public static function articleSchema(array $post): array
    {
        $publishedAt = $post['published_at'] ?? $post['created_at'] ?? null;
        $updatedAt   = $post['updated_at']   ?? $publishedAt;

        $imageAbs = !empty($post['featured_image'])
            ? SITE_URL . $post['featured_image']
            : null;

        return array_filter([
            '@context'         => 'https://schema.org',
            '@type'            => 'BlogPosting',
            'headline'         => $post['title'] ?? '',
            'description'      => $post['excerpt'] ?? '',
            'image'            => $imageAbs ? [$imageAbs] : null,
            'datePublished'    => $publishedAt ? date('c', strtotime((string) $publishedAt)) : null,
            'dateModified'    => $updatedAt   ? date('c', strtotime((string) $updatedAt))   : null,
            'author'           => [
                '@type' => 'Person',
                'name'  => $post['author_name'] ?? self::settingOr('business_name', 'Codentra'),
            ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => self::settingOr('business_name', 'Codentra'),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => SITE_URL . self::settingOr('site_logo', '/public/images/logo.webp'),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => SITE_URL . '/blog/' . ($post['slug'] ?? ''),
            ],
        ]);
    }

    public static function localBusinessSchema(): array
    {
        return array_filter([
            '@context'  => 'https://schema.org',
            '@type'     => 'ProfessionalService',
            'name'      => self::settingOr('business_name', 'Codentra'),
            'telephone' => self::settingOr('contact_phone', SITE_PHONE),
            'email'     => self::settingOr('contact_email', SITE_EMAIL),
            'url'       => SITE_URL,
            'address'   => [
                '@type'           => 'PostalAddress',
                'addressCountry'  => self::settingOr('business_country', 'Pakistan'),
                'addressLocality' => self::settingOr('business_city',    'Islamabad'),
                'streetAddress'   => self::settingOr('contact_address',  ''),
            ],
            'openingHours'    => self::parseOpeningHours(self::settingOr('business_hours', '')),
            'priceRange'      => '$$',
            'areaServed'      => 'Worldwide',
            'hasOfferCatalog' => null, // reserved
        ]);
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

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Read a setting with a graceful fallback. Wraps the model so the
     * static schema methods don't have to instantiate one each time, and
     * so a missing-DB scenario degrades cleanly to the supplied default.
     */
    private static function settingOr(string $key, string $default): string
    {
        try {
            $val = (new \Models\Setting())->get($key, $default);
            return $val !== null && $val !== '' ? $val : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Best-effort parser for the free-form business_hours field. Returns
     * the string verbatim wrapped in an array — schema.org accepts
     * free-form opening hours as strings; LD validators tolerate this.
     */
    private static function parseOpeningHours(string $val): ?array
    {
        $val = trim($val);
        return $val === '' ? null : [$val];
    }
}
