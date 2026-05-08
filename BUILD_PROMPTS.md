# Codentra — Build Prompts (Copy-Paste Into Claude Code)

> Run these **in order**. Each is short because `CLAUDE.md` carries the full spec.
> After each phase, commit & push so Hostinger auto-deploys, then test before next phase.

---

## Phase 1 — Foundation (one session)

```
Read CLAUDE.md. Scaffold the full project structure: folders, .htaccess, config/ files (database.php with PDO singleton, constants.php, seo.php), src/Core (Router, Controller, Model, PageCache, Csrf), .env.example, robots.txt, and an empty index.php front controller wired to the Router. Use the design tokens from CLAUDE.md. Do not build any pages yet.
```

**Test**: `php -S localhost:8000` should serve a 404 page (router works, no routes yet).

---

## Phase 2 — Database

```
Read CLAUDE.md. Generate sql/schema.sql with every table from the spec (users, leads, posts, categories, settings, sessions, rate_limits) — InnoDB, utf8mb4, all indexes and FKs. Then sql/seed.sql with: 1 admin user (email admin@codentra.pk, password Codentra@2026 — show me the Argon2id hash), 4 services as blog categories, 3 sample published posts, default site settings. Then create the Models (Lead, Post, Category, User, Setting) with CRUD methods using the base Model class.
```

**Test**: Import `schema.sql` + `seed.sql` into Hostinger MySQL via phpMyAdmin.

---

## Phase 3 — Public Layout & Home Page

```
Read CLAUDE.md. Build views/layouts/main.php (HTML shell with SEO meta from config/seo.php, header partial, footer partial, Ubuntu fonts preloaded, CSS/JS linked). Build the Home page per spec: hero with Three.js particle scene + tagline "Code · Automate · Scale" + dual CTA, services preview (4 glassmorphism cards: Web Dev, Shopify, E-commerce Mgmt, Automation), why-us section (3-column with icons), 4-step process timeline, mini lead-capture form, footer. Wire HomeController and route. Production-grade animations on scroll. Mobile-first responsive.
```

**Test**: Visit `/`. Hero animates. Cards lift on hover. Mobile looks great.

---

## Phase 4 — Remaining Public Pages

```
Read CLAUDE.md. Build: Services page (detailed sections per service with outcomes + tech stack), About page (story, mission, values), Blog index (paginated grid from DB) + single post view (with JSON-LD Article schema), Contact page (full form: name/email/phone/company/service-dropdown/budget/message, CSRF, client+server validation), Privacy Policy, Terms of Service, custom 404. Add controllers, models usage, and routes. Each page must have unique SEO meta + OG tags.
```

**Test**: Click through every page. All link to each other. SEO source looks clean.

---

## Phase 5 — Lead Capture Backend

```
Read CLAUDE.md. Wire the contact form to LeadController: validate, CSRF check, rate-limit 5/hour/IP, insert into leads table, send notification email to info@codentra.pk (use PHP mail() with proper headers, fall back gracefully if mail fails — still save to DB), return success page. Same for the home mini-form. Log IP + user_agent. Show user-friendly error messages.
```

**Test**: Submit contact form. Row appears in DB. Email arrives (check Hostinger mail logs).

---

## Phase 6 — Admin Auth

```
Read CLAUDE.md. Build admin login: views/pages/admin/login.php (clean dark form), AuthController with login/logout, session regeneration on login, AuthMiddleware that protects everything under /admin/* (redirects to /admin/login if not authenticated), rate limit 5 attempts per 15 min. Build views/layouts/admin.php with sidebar nav (Dashboard, Leads, Blog, Settings, Logout) and topbar with user name.
```

**Test**: Try `/admin/dashboard` while logged out → redirects. Login with seed user → reaches dashboard placeholder.

---

## Phase 7 — Admin Dashboard

```
Read CLAUDE.md. Build the admin dashboard per spec: 4 stat cards (Total Leads, New This Week, Conversion %, Published Posts), 30-day leads bar chart using Chart.js (CDN), Latest 5 leads table with quick status update, Recent activity feed. Use the dark glassmorphism aesthetic adapted for data density. Real data from DB.
```

**Test**: Submit a few test leads → dashboard reflects them.

---

## Phase 8 — Leads Management

```
Read CLAUDE.md. Build /admin/leads: sortable + filterable table (filter by status, service, date range; search by name/email), pagination, click row → /admin/leads/{id} detail view with all fields, editable status dropdown, internal notes textarea (saves on blur), soft-delete button with confirm modal, CSV export button (current filtered set).
```

**Test**: Add 20 dummy leads, filter, edit, export.

---

## Phase 9 — Blog Management

```
Read CLAUDE.md. Build /admin/posts: list view with status badges, "New Post" button, edit page with: title, slug (auto from title, editable), excerpt, content (use EasyMDE markdown editor from CDN), featured image upload (validate MIME, randomize filename, save to uploads/, generate WebP), category dropdown, publish/draft toggle, save & publish buttons. CRUD complete.
```

**Test**: Create, edit, publish a post. Appears on /blog. Image displays.

---

## Phase 10 — Settings & Polish

```
Read CLAUDE.md. Build /admin/settings: editable site title, meta description, contact email, contact phone, social links (FB, IG, LinkedIn, Twitter). All values pulled from settings table and used everywhere. Then: generate dynamic sitemap.xml (pages + published posts). Add JSON-LD Organization to layout, Article to blog single, LocalBusiness to contact. Final pass: verify Lighthouse 95+, fix any issues.
```

**Test**: Change a setting → reflects sitewide. `/sitemap.xml` valid. Lighthouse audit.

---

## Phase 11 — Pre-Launch Checklist

```
Read CLAUDE.md. Run a final audit: 1) Verify all forms have CSRF + rate limiting, 2) Verify all DB queries are prepared statements, 3) Verify .env is gitignored and .env.example is committed, 4) Verify cache/ and uploads/ have .htaccess preventing PHP execution, 5) Add security headers to .htaccess if missing, 6) Verify mobile responsiveness at 375px, 768px, 1024px, 1440px, 7) Verify all alt text on images, 8) Verify 404 page works, 9) Generate a DEPLOYMENT.md with exact Hostinger steps. Report findings and fix issues.
```

---

## 🔧 Maintenance Prompts (Use Anytime)

- **Add a new page**: `Read CLAUDE.md. Add a /faq page with collapsible Q&A, follow site's design system, include in nav and sitemap.`
- **New service**: `Read CLAUDE.md. Add "Mobile App Development" service to the services page and the contact form dropdown.`
- **Tweak design**: `Read CLAUDE.md. The hero CTA button feels weak — make it more prominent using --clr-cta with a stronger glow on hover.`
- **Bug fix**: `Read CLAUDE.md. The blog single page returns 500 when the slug doesn't exist — make it 404 instead.`

---

## 💡 Token-Saving Habits

1. **Always start with**: `Read CLAUDE.md.`  → loads spec once, saves repetition.
2. **Phase per session** — close & reopen Claude Code between phases to clear context.
3. **Don't paste error logs in full** — paste only the relevant error message + file:line.
4. **Use `/clear`** in Claude Code between unrelated tasks to reset context.
5. **Reference files by path** — "fix views/pages/contact.php validation" beats describing the bug.
