# Codentra Website — Project Context Pack

> Paste this as message #1 in a fresh Claude Code session and the assistant
> will have full context for UI/UX work — frontend or backend — without
> re-onboarding.

Working dir: `C:\Users\sadiq\Desktop\Website\codentra.pk\`
Git: `main` branch, push to origin auto-deploys to Hostinger
Live: https://codentra.pk
Stack: PHP 8.1+ (no framework, custom router/MVC) · MySQL 8 (InnoDB, utf8mb4) · vanilla JS + Three.js · Apache (.htaccess) · Cloudflare in front

---

## 1. Brand & Design System (LOCKED — do not change)

**Tagline:** Code · Automate · Scale
**Niche:** Web dev, Shopify, e-commerce mgmt & business automation agency
**Tone:** Premium, modern, tech-forward, trustworthy

### Color tokens (in `public/css/style.css`)
```css
--clr-bg:        #0A1C28;  /* Midnight navy — primary bg */
--clr-bg-2:      #0F2533;  /* Slightly lighter — cards/sections */
--clr-accent:    #2A9D8F;  /* Tech teal — links, highlights, logo */
--clr-text:      #FFFFFF;
--clr-secondary: #4F5D75;  /* Slate blue — depth, headings */
--clr-cta:       #F4A261;  /* Digital gold — CTAs ONLY, sparingly */
--clr-surface:   rgba(255,255,255,0.04);
--clr-border:    rgba(255,255,255,0.10);
--clr-muted:     rgba(255,255,255,0.65);
--grad-accent:   linear-gradient(135deg, #2A9D8F 0%, #4F5D75 100%);
--grad-cta:      linear-gradient(135deg, #F4A261 0%, #E76F51 100%);
--grad-glow:     radial-gradient(ellipse at center, rgba(42,157,143,0.25) 0%, transparent 70%);
```

### Typography
- **Family:** Ubuntu, self-hosted WOFF2 in `public/fonts/`
- **Weights:** 300, 400, 500, 700
- **Preload:** ONLY `ubuntu-400.woff2` (don't add more — perf-locked)
- **font-display:** `swap` for 400/700, `optional` for 300/500
- **Scale:** 0.875rem · 1rem · 1.25rem · 1.5rem · 2rem · 2.75rem · 3.75rem

### Visual language
- Glassmorphism cards: `rgba(255,255,255,0.04)` bg, `1px solid var(--clr-border)`, `backdrop-filter: blur(12px)`, hover lift `translateY(-4px)` + teal glow
- Buttons:
  - `.btn--primary` → solid teal, darken on hover
  - `.btn--cta` → gold gradient, used sparingly (hero, contact)
  - `.btn--secondary` → transparent + teal border
- Borders: 1px hairline rgba whites; radius `0.75rem` standard, `1.25rem` large
- Scroll reveals via IntersectionObserver (`[data-reveal]`, `[data-reveal="stagger"]`)
- Three.js: hero-only, deferred past LCP (see §6)

---

## 2. Project Structure

```
codentra.pk/
├── index.php                 # Front controller / router entry
├── .htaccess                 # Rewrites, compression, immutable cache, security headers
├── robots.txt
├── config/
│   ├── constants.php         # SITE_URL, SITE_EMAIL, SITE_PHONE, ROOT_PATH, etc.
│   ├── database.php          # PDO singleton (env-driven via .env)
│   └── seo.php               # Seo class — meta + 4 JSON-LD factories
├── src/
│   ├── Core/                 # Router, Controller, Model, PageCache, Csrf, Request
│   ├── Controllers/          # Home, Services, About, Blog, Contact, Lead, Legal,
│   │                         # Auth, Sitemap, Admin\{Dashboard,Leads,Posts,Settings}
│   ├── Models/               # Lead, Post, Category, User, Setting
│   └── Middleware/           # AuthMiddleware, CsrfMiddleware, RateLimitMiddleware
├── views/
│   ├── layouts/main.php      # Public layout (inlines critical CSS, defers full CSS)
│   ├── layouts/admin.php     # Admin shell
│   ├── partials/             # header, footer, nav, critical-css, admin-sidebar
│   └── pages/                # home, services, about, contact, privacy, terms,
│                             # blog/{index,single}, admin/{login,dashboard,leads,
│                             # lead-detail,posts,post-edit,settings}, errors/404
├── public/
│   ├── css/style.css         # Main stylesheet (async-loaded)
│   ├── css/animations.css
│   ├── js/main.js            # Nav toggle, scroll reveal, form helpers
│   ├── js/three-scene.js     # Hero 3D scene (deferred load)
│   ├── js/admin-shared.js    # AdminUI: form dirty tracking, flash, helpers
│   ├── images/               # WebP only
│   └── fonts/                # Ubuntu WOFF2 (latin)
├── api/v1/                   # Reserved
├── cache/pages/              # File-based public-page cache (1h TTL)
├── cache/sitemap.xml         # Runtime — gitignored
├── uploads/posts/            # Blog featured images (WebP, EXIF-stripped)
├── sql/
│   ├── schema.sql            # Full schema
│   ├── seed.sql              # Initial admin + settings
│   └── migrations/           # 001_*, 002_*, 003_settings_extended.sql
└── tools/                    # Maintenance scripts (seed-test-leads.php, minify.php)
```

---

## 3. Database

Engine: InnoDB · Collation: utf8mb4_unicode_ci · Every table has `id` (BIGINT UNSIGNED AI PK), `created_at`, `updated_at`. Soft deletes via `deleted_at TIMESTAMP NULL`.

| Table | Key columns | Notes |
|---|---|---|
| `users` | email (unique), password_hash (Argon2id), role ENUM, last_login_at, remember_selector, remember_validator | Admin accounts |
| `leads` | name, email, phone, company, service ENUM, budget, message, source, status ENUM('new','contacted','qualified','converted','lost'), notes, ip_address, user_agent | Indexed: email, status, created_at |
| `lead_history` | lead_id FK, event_type ENUM, payload JSON, user_id | Audit trail for status/notes changes |
| `posts` | slug (unique), title, excerpt, content (LONGTEXT, markdown), featured_image, category_id FK, author_id FK, status ENUM('draft','published'), views, published_at, deleted_at | Indexed: slug, status, published_at |
| `categories` | slug, name, description | |
| `settings` | key_name (unique), value (TEXT), updated_at | 24 keys — see §7 |
| `sessions` | id PK VARCHAR(128), user_id, payload, last_activity | DB-backed PHP sessions |
| `rate_limits` | key_hash, attempts, expires_at | /contact + /admin/login |

---

## 4. Routing & Pages

All routes registered in `index.php`. Router: `\Core\Router`, supports `{param}` placeholders + middleware chain.

### Public (live)
- `GET /` — Hero (Three.js deferred), services preview, mini lead form
- `GET /services` — 4 service blocks with stacks + outcomes
- `GET /about` — Story, values, mission
- `GET /blog` — Paginated grid (9/page)
- `GET /blog/{slug}` — Single post, increments views (skips bots)
- `GET /contact` + `POST /contact` — Full lead form → `LeadController::submit`
- `GET /privacy`, `GET /terms` — Legal
- `GET /sitemap.xml` — Dynamic, file-cached 1h

### Admin (all behind `AuthMiddleware`)
- `GET|POST /admin/login`, `POST /admin/logout`
- `GET /admin/dashboard` — KPI cards, leads/day chart (Chart.js)
- `GET /admin/leads` (+ filter/sort/paginate/export), `GET /admin/leads/{id}`, `POST` for status/notes/delete
- `GET /admin/posts` (+ `new`, `{id}/edit`), `POST /admin/posts`, `POST /admin/posts/{id}` (update + delete)
- `GET|POST /admin/settings` — 24-key settings catalogue

---

## 5. Frontend Conventions

### CSS architecture
- All tokens defined in `:root` at top of `public/css/style.css`
- BEM-ish naming (`block`, `block__elem`, `block--mod`) — see `.site-header`, `.lead-card`, `.settings-section` etc.
- Mobile-first; breakpoints: 640, 768, 1024, 1280
- One file (`style.css`) — no preprocessor. ~2200 lines, sectioned with `/* === Section === */` headers

### Critical CSS pattern
- `views/partials/critical-css.php` (~3.5KB) inlined in `<head>`
- Covers: tokens, reset, header, hero, buttons, skip-link, `[data-reveal]` base
- Full `style.css` loads async via `<link rel="preload" … onload="this.rel='stylesheet'">`
- **Don't** let critical-css grow past ~5KB; don't add synchronous stylesheet link

### JS
- Vanilla, no framework. Defer all `<script>` tags.
- `public/js/main.js` — nav toggle, IntersectionObserver reveals, form helpers
- `public/js/admin-shared.js` — exposes `window.AdminUI` with `markFormDirty(form)`, flash helpers, fetch wrappers
- Three.js: dynamic `import()` from `https://cdn.jsdelivr.net/npm/three@…`, **home hero only**, deferred until after `window.load` + `requestIdleCallback`
- Chart.js: CDN-pinned on dashboard only (CSP-allowed)
- EasyMDE + marked.js: CDN-pinned on `/admin/posts/edit` only

### Animations
- `[data-reveal]` → fade+slide up on scroll-into-view
- `[data-reveal="stagger"]` → cascades children (nth-child delays in CSS)
- Card hover: `translateY(-4px)` + teal glow shadow
- Respect `prefers-reduced-motion: reduce` — skips Three.js entirely

---

## 6. Performance Contract (locked, don't regress)

1. **Three.js deferred past LCP** — hero LCP is pure CSS gradient. Canvas fades in via `.is-ready` class after init. 800 desktop / 400 mobile particles, pixelRatio capped 1.5, pauses on IntersectionObserver + visibilitychange.
2. **Critical CSS inlined, full stylesheet async.**
3. **Only `ubuntu-400.woff2` preloaded.** 300/500 use `font-display: optional`.
4. **Immutable caching** in `.htaccess`: `Cache-Control: public, max-age=31536000, immutable` for `.css|.js|.woff2|.webp|.svg|.jpg|.png|.ico`. Cache-busted via `?v=filemtime`.
5. **File-based page cache** in `cache/pages/` (1h TTL). Flushed on any admin write (settings, post create/update/delete).
6. **WebP everywhere**, `loading="lazy"` below fold. Featured-image upload pipeline auto-converts to WebP + strips EXIF.

Lighthouse targets: Perf ≥90 mobile, SEO/A11y/BP ≥95.

---

## 7. Settings Catalogue (24 keys)

Single source of truth: `\Controllers\Admin\SettingsController::CATALOG`. All public pages read live values via `(new \Models\Setting())->get($key, $default)` with constant fallback.

**General:** site_title, site_tagline, site_description (≤160), site_logo, timezone
**Contact:** contact_phone, contact_email, contact_address, business_hours, response_time_promise
**Social:** social_facebook, social_instagram, social_linkedin, social_twitter, social_youtube (all URLs, optional)
**SEO:** seo_default_title_suffix, seo_og_image, google_analytics_id, google_search_console
**Business:** business_name, business_legal_name, business_founded_year, business_country, business_city

Per-type validation: email/url/phone/integer/text/textarea. Empty allowed for non-required keys.

On save: `Setting::flushCache()` → `PageCache::flush()` → `unlink(cache/sitemap.xml)`.

---

## 8. SEO / Structured Data

`config/seo.php` `Seo` class exposes:
- `set([title, description, canonical, ogImage, ogType, noindex])` per-page
- `addJsonLd($schema)`
- `render()` — emits title, meta description, OG, Twitter Card, canonical, GSC verification, GA snippet, all JSON-LD blocks

Schema factories (all read live settings):
- `Seo::organizationSchema()` — site-wide, auto-injected by layout
- `Seo::articleSchema($post)` — blog single
- `Seo::localBusinessSchema()` — `/contact` only
- `Seo::breadcrumbSchema([[name, url], …])` — services, about, blog, blog single, contact

Sitemap: dynamic `/sitemap.xml` via `SitemapController` — static pages + published posts, 1h file cache.

---

## 9. Security Contract

- All DB access via PDO prepared statements (no string interpolation)
- CSRF on every POST: `\Core\Csrf::field()` in form, `Csrf::verify()` in controller
- Argon2id password hashing
- Rate limits: `/contact` 5/hour/IP, `/admin/login` 5/15min — DB-backed with session fallback
- Sessions: httponly, secure (prod), samesite=Strict, regenerate on login, DB-backed
- File upload: MIME via `finfo`, whitelist, randomize filename, force WebP
- Session-based remember-me: selector/validator split (timing-safe compare)
- `.env` never committed; `.env.example` is
- `.htaccess` blocks web access to `/cache`, `/sql`, `/config`, `/src`, `/tools`, `/uploads/*` except `/uploads/posts/`
- Security headers: HSTS, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, CSP
- Cloudflare in front — use `\Core\Request::clientIp()`, NEVER `$_SERVER['REMOTE_ADDR']`

---

## 10. Backend Conventions

### Adding a new controller
1. `src/Controllers/Foo/BarController.php` namespace `Controllers\Foo`
2. Extend `\Core\Controller`
3. Use `$this->render('view/path', $data, 'main'|'admin')`
4. `$this->seo` is pre-instantiated — call `$this->seo->set([...])`
5. `$this->redirect('/path')`, `$this->abort(404)`, `$this->flashSuccess('…')`, `$this->flashError('…')`
6. Register in `index.php` `$router->get/post(…)` with middleware array

### Models
- Extend `\Core\Model`, set `protected string $table`
- `$this->db` is PDO instance
- Use `$db->prepare()->execute([…])` always
- Soft delete: `protected bool $softDeletes = true`

### Settings access from anywhere
```php
$val = (new \Models\Setting())->get('site_title', 'Codentra');
```
Cached in static after first call. Call `Setting::flushCache()` after any write.

---

## 11. Contact Info (everywhere)

- Phone: +92 317 1263292
- Email: info@codentra.pk
- Country: Pakistan / City: Islamabad

All editable from `/admin/settings` — header/footer/contact page/JSON-LD all pull from there with constants as fallback.

---

## 12. Phase Status (as of 2026-05-14)

All 10 phases live:
1. Foundation · 2. Database · 3. Layout + Home · 3.5 Performance · 4. Public pages · 5. Lead capture + email · 6. Admin auth · 7. Dashboard · 8. Leads mgmt · 9. Blog mgmt · 10. Settings + polish

### Deferred (post-launch)
- Change admin seed password (shared in chat history — compromised)
- Email deliverability: SPF + DKIM + DMARC in Cloudflare DNS, target mail-tester 9/10+
- Scheduled blog publishing (cron auto-publish on future `published_at`)
- Tags & full-text search on /blog
- Multi-author support
- Media library (separate from per-post uploads)
- Blog comments + moderation
- Bulk lead actions (8.5)
- Blog post SEO field overrides

---

## 13. Working Rules

- **No git worktrees on this project** — always work directly on `main`
- `php -S` ignores `.htaccess` — local dev cannot catch Apache routing bugs. Push to Hostinger to verify rewrites/headers.
- Hostinger filesystem is case-sensitive — lowercase all asset filenames
- `.htaccess` rewrite-block ORDER matters — see CLAUDE.md "Production gotchas"
- Brief, terse responses; no trailing summaries; no unsolicited refactors
- Phase-based work — one feature per session ideally
