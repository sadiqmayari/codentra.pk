# Codentra Website - Project Context

> **This file is auto-loaded by Claude Code. It contains everything needed to build & maintain the site.**
> **Do not re-explain brand, colors, or structure in prompts — it's all here.**

---

## 🏢 Brand

| Field | Value |
|------|-------|
| **Name** | Codentra |
| **Domain** | codentra.pk |
| **Tagline** | Code · Automate · Scale |
| **Niche** | Web Development, Shopify, E-commerce Management & Business Automation Agency |
| **Tone** | Premium · Modern · Tech-forward · Trustworthy |

---

## 🎨 Design System (LOCKED — do not change without permission)

### Colors
```css
:root {
  --clr-bg:        #0A1C28;  /* Midnight Navy — primary background */
  --clr-accent:    #2A9D8F;  /* Tech Teal — primary accent (logo, links, highlights) */
  --clr-text:      #FFFFFF;  /* Pure White — primary text */
  --clr-secondary: #4F5D75;  /* Slate Blue — depth, headings, icons */
  --clr-cta:       #F4A261;  /* Digital Gold — CTAs only, sparingly */

  /* Derived */
  --clr-surface:   rgba(255, 255, 255, 0.04);
  --clr-border:    rgba(255, 255, 255, 0.10);
  --clr-muted:     rgba(255, 255, 255, 0.65);
  --clr-bg-2:      #0F2533;  /* Slightly lighter for cards/sections */

  /* Gradients */
  --grad-accent:   linear-gradient(135deg, #2A9D8F 0%, #4F5D75 100%);
  --grad-cta:      linear-gradient(135deg, #F4A261 0%, #E76F51 100%);
  --grad-glow:     radial-gradient(ellipse at center, rgba(42,157,143,0.25) 0%, transparent 70%);
}
```

### Typography
- **Family**: Ubuntu (self-hosted in `public/fonts/`, with `font-display: swap`)
- **Weights**: 300, 400, 500, 700
- **Headings**: Ubuntu 700, tight letter-spacing
- **Body**: Ubuntu 400, line-height 1.65
- **Scale**: 0.875rem · 1rem · 1.25rem · 1.5rem · 2rem · 2.75rem · 3.75rem

### Visual Language
- **Backgrounds**: Midnight navy with subtle radial teal glow accents
- **Cards**: Glassmorphism — `rgba(255,255,255,0.04)` bg, `1px solid var(--clr-border)`, `backdrop-filter: blur(12px)`, soft hover lift + teal glow
- **Buttons**:
  - Primary: solid teal → darker teal on hover, subtle glow
  - CTA: gold gradient, used sparingly (hero, contact)
  - Secondary: transparent + teal border
- **Animations**: Smooth, purposeful — no excessive motion
  - Scroll reveal (Intersection Observer)
  - Stagger children
  - Card hover lift (`translateY(-4px)`)
  - Subtle background particle/grid via Three.js on hero only
- **Borders**: 1px hairline rgba whites; rounded `0.75rem` standard, `1.25rem` large

---

## 🗂️ Project Structure

```
/
├── index.php                 # Front controller (router entry)
├── .htaccess                 # Rewrite + caching + security headers
├── robots.txt
├── sitemap.xml               # Auto-generated
├── config/
│   ├── database.php          # PDO singleton (env-driven)
│   ├── constants.php         # SITE_NAME, SITE_URL, etc.
│   └── seo.php               # SEO meta renderer
├── src/
│   ├── Core/                 # Router, base Controller, base Model, PageCache, CSRF
│   ├── Controllers/          # HomeController, ServicesController, BlogController,
│   │                         # ContactController, AdminController, AuthController, LeadController
│   ├── Models/               # Lead, Post, Category, User, Setting
│   └── Middleware/           # AuthMiddleware, CsrfMiddleware, RateLimitMiddleware
├── views/
│   ├── layouts/
│   │   ├── main.php          # Public layout
│   │   └── admin.php         # Dashboard layout
│   ├── partials/             # header, footer, nav, admin-sidebar
│   └── pages/
│       ├── home.php
│       ├── services.php
│       ├── about.php
│       ├── blog/
│       │   ├── index.php
│       │   └── single.php
│       ├── contact.php
│       ├── privacy.php
│       ├── terms.php
│       ├── errors/404.php
│       └── admin/
│           ├── login.php
│           ├── dashboard.php
│           ├── leads.php
│           ├── lead-detail.php
│           ├── posts.php
│           ├── post-edit.php
│           └── settings.php
├── public/
│   ├── css/style.css
│   ├── css/animations.css
│   ├── js/main.js
│   ├── js/three-scene.js     # Hero 3D scene
│   ├── js/admin.js
│   ├── images/               # WebP only
│   └── fonts/                # Ubuntu WOFF2
├── api/v1/                   # JSON endpoints (lead submission, etc.)
├── cache/pages/              # File-based page cache (1h TTL)
├── uploads/                  # User uploads (chmod 755)
├── sql/
│   ├── schema.sql
│   └── seed.sql
└── .env.example
```

---

## 🗄️ Database (Hostinger MySQL)

Engine: `InnoDB` · Collation: `utf8mb4_unicode_ci`

### Tables

**users** (admin accounts)
- `id`, `name`, `email` (unique), `password_hash`, `role` ENUM('admin','editor'), `last_login_at`, `created_at`, `updated_at`, `deleted_at`

**leads** (contact form submissions)
- `id`, `name`, `email`, `phone`, `company`, `service` ENUM('web-dev','shopify','ecommerce-mgmt','automation','other'), `budget`, `message` TEXT, `source` (default 'website'), `status` ENUM('new','contacted','qualified','converted','lost') default 'new', `notes` TEXT, `ip_address`, `user_agent`, `created_at`, `updated_at`
- Indexes: `email`, `status`, `created_at`

**posts** (blog)
- `id`, `slug` (unique), `title`, `excerpt`, `content` LONGTEXT, `featured_image`, `category_id` FK, `author_id` FK→users, `status` ENUM('draft','published'), `views` INT default 0, `published_at`, `created_at`, `updated_at`, `deleted_at`
- Indexes: `slug`, `status`, `published_at`, `category_id`

**categories**
- `id`, `slug`, `name`, `description`, `created_at`, `updated_at`

**settings** (key-value site config)
- `id`, `key_name` (unique), `value` TEXT, `updated_at`

**sessions** (DB-backed sessions)
- `id` VARCHAR(128) PK, `user_id` nullable, `payload` TEXT, `last_activity` INT, indexed

**rate_limits** (form abuse prevention)
- `id`, `key_hash`, `attempts`, `expires_at`, indexed on `key_hash`

---

## 📄 Pages & Behavior

### Public
1. **Home (`/`)** — Hero with 3D Three.js scene, services preview (4 glassmorphism cards), why-us section, process steps, mini lead-capture CTA, footer
2. **Services (`/services`)** — Detailed breakdown: Web Development · Shopify · E-commerce Management · Business Automation. Each with bullet outcomes + tech stack.
3. **About (`/about`)** — Story, mission ("Code · Automate · Scale"), values, team placeholder
4. **Blog (`/blog`)** — Paginated grid of posts pulled from DB. Single post at `/blog/{slug}`.
5. **Contact (`/contact`)** — Full lead form (name, email, phone, company, service dropdown, budget, message), CSRF + rate-limited, writes to `leads` table, sends email to `info@codentra.pk` via PHP `mail()` (or PHPMailer if SMTP available)
6. **Privacy Policy (`/privacy`)** — Full PK-compliant policy
7. **Terms of Service (`/terms`)** — Full ToS
8. **404** — On-brand error page

### Admin (`/admin`)
1. **Login** — Email + password, Argon2id, session-based, CSRF
2. **Dashboard** — Stat cards (total leads, new this week, conversion rate, recent posts), latest leads table, simple bar chart of leads/day (last 30d) using Chart.js
3. **Leads** — Sortable/filterable table, status update, notes, CSV export, soft delete
4. **Lead Detail** — All fields, status history, internal notes
5. **Blog Posts** — CRUD (list, create, edit with simple WYSIWYG/markdown, publish/draft toggle, image upload to `uploads/`)
6. **Settings** — Site title, meta description, contact email/phone, social links

---

## 📞 Contact Information (use everywhere)
- **Phone**: +92 317 1263292
- **Email**: info@codentra.pk
- **Domain**: https://codentra.pk
- **Social**: (placeholders, editable from settings)

---

## ⚡ Performance Requirements

- Lighthouse target: **95+** on all categories
- LCP < 2.0s, CLS < 0.05, INP < 200ms
- All images **WebP** with `<picture>` JPG fallback, `loading="lazy"` below fold
- Fonts self-hosted, `font-display: swap`, preload critical weight
- CSS/JS minified for production (build script `tools/minify.php`)
- File-based page cache for public pages (1h TTL, bust on admin update)
- OPcache enabled (note in DEPLOYMENT.md for Hostinger config)
- `.htaccess`: gzip, browser cache 1y for static, security headers
- Three.js loaded **only on home hero**, lazy via dynamic `import()`

---

## 🔍 SEO Requirements

- Unique `<title>` + meta description per page (managed via `config/seo.php`)
- Open Graph + Twitter Card on every page
- JSON-LD: Organization (sitewide), Article (blog single), BreadcrumbList, LocalBusiness
- Canonical URL on every page
- Auto-generated `sitemap.xml` (pages + blog posts)
- `robots.txt` allowing crawl, disallowing `/admin`
- Semantic HTML5 (`<main>`, `<article>`, `<section>`, `<nav>`)
- One `<h1>` per page, logical heading hierarchy
- Alt text required on all images (DB column for blog images)

---

## 🔒 Security Requirements

- All DB access via PDO prepared statements (no string interpolation)
- CSRF token on every form (sync token in session, validated on POST)
- Argon2id for password hashing
- Rate limiting on `/contact` (5/hour per IP) and `/admin/login` (5/15min)
- Input sanitization: `htmlspecialchars` on output, `strip_tags` on free text
- Session: `httponly`, `secure`, `samesite=Strict`, regenerate on login
- File upload: MIME check via `finfo`, whitelist extensions, randomize filenames
- `.env` for credentials, never committed (use `.env.example`)
- Security headers via `.htaccess`: HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP

---

## 🚀 Deployment (Hostinger via Git)

1. Hostinger Git auto-pulls on push to `main`
2. After first deploy, run `sql/schema.sql` then `sql/seed.sql` via hPanel → phpMyAdmin
3. Copy `.env.example` to `.env` on server, fill DB creds + `APP_ENV=production`
4. Set `cache/`, `uploads/` to chmod 755
5. Point domain to `public_html` (which is the project root)
6. See `DEPLOYMENT.md` for full step-by-step

---

## 🧠 Working with Claude Code (Token-Efficient Rules)

1. **Reference, don't repeat** — Just say "build the contact page per spec." Spec is here.
2. **Phase-based prompts** — See `BUILD_PROMPTS.md` for ordered prompts.
3. **One feature per session** when possible — keeps context window small.
4. **Trust the structure** — folders/files defined above are the contract.
5. **No unsolicited refactors** — only change what's asked.
6. **Read this file first** — every Claude Code session starts here.


## ⚠️ Production-Specific Gotchas (learned the hard way)

1. **`.htaccess` rewrite order matters** — Apache applies `RewriteCond` only to the immediately following `RewriteRule`. The "serve real files directly" check MUST be its own block right before the route-to-PHP rule, like this:
```apache
   # Real file/dir? Serve it as-is and stop.
   RewriteCond %{REQUEST_FILENAME} -f [OR]
   RewriteCond %{REQUEST_FILENAME} -d
   RewriteRule ^ - [L]

   # Everything else → PHP router
   RewriteRule ^ index.php [QSA,L]
```

2. **`php -S` ignores `.htaccess`** — local dev cannot catch Apache routing bugs. After any change to `.htaccess`, security headers, or rewrites, push to Hostinger and verify before considering the phase done.

3. **CSP `connect-src`** — must include `https://cdnjs.cloudflare.com` if Three.js or any CDN script is used.

4. **Hostinger filesystem case-sensitive** — Linux filesystem; `Image.PNG` ≠ `image.png`. Always lowercase asset filenames.

---

## Performance Architecture

> **Locked in Phase 3.5 (commit `9b72ce2`).** These patterns lifted home-page mobile Lighthouse from ~77 toward 90+. Future phases must preserve them — adding pages, components, or services should *follow* this architecture, never replace it. If a real performance win requires changing one of these, measure first, then update both this section and the `project_perf_optimizations.md` memory.

### 1. Deferred Three.js (LCP-first hero)

The hero's LCP background is a pure-CSS radial gradient on `.hero` — Three.js is purely decorative on top.

- `views/layouts/main.php` schedules `import('/public/js/three-scene.js')` on `window.load` → `requestIdleCallback(start, { timeout: 1500 })`, falling back to `setTimeout(start, 100)`.
- The canvas (`#hero-canvas`) starts at `opacity: 0` and gets `.is-ready` once `init()` resolves, fading in over 600ms.
- `prefers-reduced-motion: reduce` skips the import entirely and force-shows the canvas (no animation).
- `public/js/three-scene.js` itself is tuned: 800 desktop / 400 mobile particles, `IcosahedronGeometry(_, 0)`, `pixelRatio` capped at 1.5, first paint deferred one `rAF`, and the loop pauses on both `IntersectionObserver` (off-screen) and `visibilitychange` (`document.hidden`).

**Don't:** import `three-scene.js` from a `<script type="module">` in `<head>`, raise the particle count, raise the icosahedron detail, raise the pixel-ratio cap, or remove the visibility-pause.

### 2. Critical CSS pattern

- `views/partials/critical-css.php` (~3.5KB) is inlined in `<head>` and covers tokens, reset, header, hero, buttons, skip-link, and `[data-reveal]` base styles.
- Full `public/css/style.css` loads async: `<link rel="preload" href="…?v=…" as="style" onload="this.onload=null;this.rel='stylesheet'">` with a `<noscript>` fallback.
- The 400 and 700 `@font-face` declarations live in *both* the critical block and `style.css` so text renders correctly during the async window.

**Don't:** add a synchronous `<link rel="stylesheet">` for `style.css`, let `critical-css.php` grow past ~5KB (defeats the point), or duplicate non-critical rules in the inlined block.

**Do:** when adding a new above-the-fold component (e.g., a different landing-page hero), extend `critical-css.php` with the minimum rules to render it, *not* the full component CSS.

### 3. Font preload strategy

- Self-hosted Ubuntu WOFF2 in `public/fonts/` (latin range only).
- Only `ubuntu-400.woff2` is `<link rel="preload">`-ed in the layout.
- `font-display`: `swap` for 400 + 700 (visible above-the-fold text), `optional` for 300 + 500 (no FOUT/CLS if uncached — system fallback is acceptable).

**Don't:** preload more weights, switch 300/500 back to `swap`, or load fonts from Google's CDN.

### 4. Immutable caching

`.htaccess` policy (paired with `?v=filemtime` cache-busting in markup):

```apache
<FilesMatch "\.(?:css|js|woff2|webp|svg|jpg|jpeg|png|ico)$">
    Header set Cache-Control "public, max-age=31536000, immutable"
</FilesMatch>
<FilesMatch "\.(?:html|php)$">
    Header set Cache-Control "no-cache, must-revalidate"
</FilesMatch>
```

The `?v=` query string self-invalidates the URL whenever a file's mtime changes, so `immutable` is safe. Any new asset type that's referenced with `?v=` versioning should be added to the `FilesMatch` list.

**Don't:** drop `immutable`, shorten `max-age` for fingerprinted assets, or cache HTML responses (admin and CSRF-bearing pages must revalidate).

### 5. `.htaccess` restoration

The full `.htaccess` (compression, `mod_expires`, `mod_headers`, security headers, immutable cache, rewrite-to-`index.php`) was previously stripped in commit `8ff87fc deploy` and restored in `9b72ce2`. The block order matters: rewrite first, then compression, then expires, then headers.

Required headers (do not remove):

- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`

**Don't:** simplify `.htaccess` "to debug routing" — the full file is the production contract. If routing is broken, fix the rewrite block in place.

---

**Phase rule.** When a future phase touches the layout, the hero, asset pipeline, or `.htaccess`, re-read this section first. If the change appears to require undoing any of the above, stop and confirm with the user — performance regressions here are far more expensive than the change usually is.

## Lead Capture Contract (Phase 5)

- All lead submissions go to POST /contact → LeadController::submit()
- Both home mini-form and full contact form use this single endpoint
- Source field distinguishes them: 'contact-page' vs 'home-mini'
- DB save is canonical; email is best-effort (failures logged, never block success)
- Rate limit: 5/hour per IP (DB-backed, session fallback)
- Honeypot field: silent success (do not change this UX)
- Reply-To header set to lead's email — hitting reply in inbox replies to lead
- Logging tag: [LEAD] for all events (saved/honeypot/db-failed/email-failed)

DO NOT add another lead submission endpoint. Future forms (newsletter,
quote request, etc.) should either reuse this controller with a new
'source' value or be a separate controller for a different concept.

---

## 🔧 Deferred Tasks

- [ ] **Email deliverability** — Add SPF + DKIM + DMARC records to Cloudflare DNS (values from hPanel → Emails → info@codentra.pk → Configuration). Verify with mail-tester.com (target 9/10+). Optional: tighten lead notification email content per the prompt drafted in chat.