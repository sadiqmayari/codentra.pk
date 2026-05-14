<?php
declare(strict_types=1);

namespace Controllers\Admin;

class SettingsController extends \Core\Controller
{
    /**
     * Settings catalogue — single source of truth for valid keys, types,
     * defaults, sections, and per-key validation. Anything not in this
     * map is rejected on save (no arbitrary key-stuffing).
     *
     * type: 'text' | 'email' | 'url' | 'phone' | 'integer' | 'textarea'
     */
    public const CATALOG = [
        // ── General ────────────────────────────────────────────────────────
        'site_title'              => ['type' => 'text',     'section' => 'general',  'default' => 'Codentra',                                       'max' => 120, 'required' => true,  'label' => 'Site title',         'help' => 'Used in the brand mark, meta titles, and emails.'],
        'site_tagline'            => ['type' => 'text',     'section' => 'general',  'default' => 'Code · Automate · Scale',                       'max' => 120, 'label' => 'Tagline',           'help' => 'Short positioning line.'],
        'site_description'        => ['type' => 'textarea', 'section' => 'general',  'default' => '',                                              'max' => 160, 'label' => 'Site description',  'help' => 'Default meta description for pages without one of their own. Keep under 160 chars.'],
        'site_logo'               => ['type' => 'text',     'section' => 'general',  'default' => '/public/images/logo.webp',                      'max' => 500, 'label' => 'Logo path',         'help' => 'Absolute path or URL. A media-library picker arrives in a later phase.'],
        'timezone'                => ['type' => 'text',     'section' => 'general',  'default' => 'Asia/Karachi',                                  'max' => 60,  'label' => 'Timezone',          'help' => 'IANA name. Drives date display.'],

        // ── Contact ────────────────────────────────────────────────────────
        'contact_phone'           => ['type' => 'phone',    'section' => 'contact',  'default' => '+92 317 1263292',                               'max' => 40,  'label' => 'Phone',             'help' => 'Shown in header, footer, and contact page.'],
        'contact_email'           => ['type' => 'email',    'section' => 'contact',  'default' => 'info@codentra.pk',                              'max' => 160, 'label' => 'Email',             'help' => 'Receives lead notifications.'],
        'contact_address'         => ['type' => 'textarea', 'section' => 'contact',  'default' => '',                                              'max' => 400, 'label' => 'Address',           'help' => 'Optional. Shown on /contact when populated.'],
        'business_hours'          => ['type' => 'textarea', 'section' => 'contact',  'default' => 'Mon–Fri 9:00–18:00 PKT',                        'max' => 400, 'label' => 'Business hours',    'help' => 'Free text — also used to derive openingHours in the LocalBusiness schema.'],
        'response_time_promise'   => ['type' => 'text',     'section' => 'contact',  'default' => 'within 24 hours',                               'max' => 80,  'label' => 'Response promise',  'help' => 'Shown on /contact and the thank-you page.'],

        // ── Social ─────────────────────────────────────────────────────────
        'social_facebook'         => ['type' => 'url',      'section' => 'social',   'default' => '',                                              'max' => 300, 'label' => 'Facebook URL'],
        'social_instagram'        => ['type' => 'url',      'section' => 'social',   'default' => '',                                              'max' => 300, 'label' => 'Instagram URL'],
        'social_linkedin'         => ['type' => 'url',      'section' => 'social',   'default' => '',                                              'max' => 300, 'label' => 'LinkedIn URL'],
        'social_twitter'          => ['type' => 'url',      'section' => 'social',   'default' => '',                                              'max' => 300, 'label' => 'Twitter / X URL'],
        'social_youtube'          => ['type' => 'url',      'section' => 'social',   'default' => '',                                              'max' => 300, 'label' => 'YouTube URL'],

        // ── SEO ────────────────────────────────────────────────────────────
        'seo_default_title_suffix'=> ['type' => 'text',     'section' => 'seo',      'default' => ' | Codentra',                                   'max' => 60,  'label' => 'Title suffix',      'help' => 'Appended to per-page titles (include the leading separator).'],
        'seo_og_image'            => ['type' => 'text',     'section' => 'seo',      'default' => '/public/images/og-default.webp',                'max' => 500, 'label' => 'Default OG image',  'help' => 'Path or URL of the social-share image used when a page does not specify one.'],
        'google_analytics_id'     => ['type' => 'text',     'section' => 'seo',      'default' => '',                                              'max' => 40,  'label' => 'Google Analytics ID', 'help' => 'Format G-XXXXXXXXXX.'],
        'google_search_console'   => ['type' => 'text',     'section' => 'seo',      'default' => '',                                              'max' => 200, 'label' => 'Search Console verification', 'help' => 'The bare token from the meta-tag verification method.'],

        // ── Business ───────────────────────────────────────────────────────
        'business_name'           => ['type' => 'text',     'section' => 'business', 'default' => 'Codentra',                                      'max' => 120, 'label' => 'Business name'],
        'business_legal_name'     => ['type' => 'text',     'section' => 'business', 'default' => 'Codentra',                                      'max' => 200, 'label' => 'Legal entity name'],
        'business_founded_year'   => ['type' => 'integer',  'section' => 'business', 'default' => '2025',                                          'max' => 4,   'label' => 'Founded year'],
        'business_country'        => ['type' => 'text',     'section' => 'business', 'default' => 'Pakistan',                                      'max' => 80,  'label' => 'Country'],
        'business_city'           => ['type' => 'text',     'section' => 'business', 'default' => 'Islamabad',                                     'max' => 80,  'label' => 'City'],
    ];

    public const SECTIONS = [
        'general'  => ['label' => 'General',  'desc' => 'Site identity and the values that flow into every page.'],
        'contact'  => ['label' => 'Contact',  'desc' => 'How visitors reach you. Used in header, footer, and structured data.'],
        'social'   => ['label' => 'Social',   'desc' => 'External profile URLs. Empty fields are simply not rendered.'],
        'seo'      => ['label' => 'SEO',      'desc' => 'Defaults for meta tags and analytics — per-page overrides win where set.'],
        'business' => ['label' => 'Business', 'desc' => 'Used in the JSON-LD Organization / LocalBusiness schemas.'],
    ];

    // ── GET /admin/settings ─────────────────────────────────────────────────

    public function index(): void
    {
        $values = [];
        try {
            $values = (new \Models\Setting())->allKeyValue();
        } catch (\Throwable $e) {
            error_log('[SETTING] load-failed msg=' . $e->getMessage());
        }
        // Merge defaults so brand-new keys (added via migration but not yet
        // saved) still show their default in the form.
        foreach (self::CATALOG as $k => $meta) {
            if (!array_key_exists($k, $values)) $values[$k] = $meta['default'];
        }

        $this->seo->set([
            'title'   => 'Site Settings | Codentra Admin',
            'noindex' => true,
        ]);

        $this->render('admin/settings', [
            'pageTitle' => 'Site Settings',
            'catalog'   => self::CATALOG,
            'sections'  => self::SECTIONS,
            'values'    => $values,
            'errors'    => $this->popSession('_errors', []),
        ], 'admin');
    }

    // ── POST /admin/settings ────────────────────────────────────────────────

    public function save(): void
    {
        if (!\Core\Csrf::verify()) {
            $this->flashError('Your session expired. Please refresh and try again.');
            $this->redirect('/admin/settings');
        }

        [$validated, $errors] = $this->validateAll($_POST);

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $this->flashError('Some fields need attention — see highlighted rows.');
            $this->redirect('/admin/settings');
        }

        try {
            (new \Models\Setting())->setMany($validated);
            \Models\Setting::flushCache();

            // Settings affect every public page — drop the page cache
            // so visitors don't see stale title/footer/etc.
            (new \Core\PageCache())->flush();

            // Sitemap is built from settings + posts and cached separately
            // (see SitemapController). Nuke the cached file on settings
            // change so the next /sitemap.xml hit regenerates.
            $sitemapCache = ROOT_PATH . '/cache/sitemap.xml';
            if (file_exists($sitemapCache)) @unlink($sitemapCache);

            $userId = (int) ($_SESSION['user_id'] ?? 0) ?: null;
            error_log('[SETTING] updated keys=' . implode(',', array_keys($validated)) . ' by user_id=' . ($userId ?? '?'));
            $this->flashSuccess('Settings saved.');
        } catch (\Throwable $e) {
            error_log('[SETTING] save-failed msg=' . $e->getMessage());
            $this->flashError('Could not save settings. Please try again.');
        }

        $this->redirect('/admin/settings');
    }

    // ── Validation ──────────────────────────────────────────────────────────

    /**
     * @return array{0: array<string, string>, 1: array<string, string>}
     *         [validated key=>value, errors key=>message]
     */
    private function validateAll(array $src): array
    {
        $validated = [];
        $errors    = [];

        foreach (self::CATALOG as $key => $meta) {
            $raw = (string) ($src[$key] ?? '');
            $val = trim($raw);

            // Required check
            if (!empty($meta['required']) && $val === '') {
                $errors[$key] = ($meta['label'] ?? $key) . ' is required.';
                continue;
            }

            // Length cap
            if (isset($meta['max']) && strlen($val) > (int) $meta['max']) {
                $errors[$key] = ($meta['label'] ?? $key) . ' is too long (max ' . $meta['max'] . ' chars).';
                continue;
            }

            // Type-specific rules — empty strings are ALWAYS allowed for
            // optional fields, regardless of type.
            if ($val !== '') {
                $err = $this->validateType((string) $meta['type'], $val);
                if ($err !== null) {
                    $errors[$key] = ($meta['label'] ?? $key) . ' — ' . $err;
                    continue;
                }
            }

            // Per-type normalisation
            if ($meta['type'] === 'email') $val = strtolower($val);

            $validated[$key] = $val;
        }

        return [$validated, $errors];
    }

    private function validateType(string $type, string $val): ?string
    {
        switch ($type) {
            case 'email':
                return filter_var($val, FILTER_VALIDATE_EMAIL) === false
                    ? 'must be a valid email address'
                    : null;
            case 'url':
                if (!preg_match('#^https?://#', $val)) return 'must start with http:// or https://';
                return filter_var($val, FILTER_VALIDATE_URL) === false ? 'is not a valid URL' : null;
            case 'phone':
                return preg_match('/^[0-9 +()\-]{6,40}$/', $val) ? null : 'contains invalid characters';
            case 'integer':
                return preg_match('/^[0-9]+$/', $val) ? null : 'must be a whole number';
            case 'text':
            case 'textarea':
            default:
                return null;
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function popSession(string $key, $default)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $val = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $val;
    }

    private function flashSuccess(string $msg): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['_flash'] = ['type' => 'success', 'msg' => $msg];
    }

    private function flashError(string $msg): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['_flash'] = ['type' => 'error', 'msg' => $msg];
    }
}
