-- ============================================================================
--  Codentra — Seed Data
--  Run AFTER schema.sql.
--
--  Default admin credentials:
--    Email:    admin@codentra.pk
--    Password: Cdt!ra#9X$bozJ5nl8
--    (change immediately after first login)
-- ============================================================================

SET NAMES utf8mb4;
SET @now := NOW();

-- ── Admin user ──────────────────────────────────────────────────────────────
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
('Codentra Admin', 'admin@codentra.pk',
 '$argon2id$v=19$m=65536,t=3,p=4$CR087QHVUsOZJ5bF0Yyq+A$+dP4caDIU3Aeb2TzdJl6Q1BNO/GM/dqTKmid+YGPXdM',
 'admin', @now, @now);

SET @admin_id := LAST_INSERT_ID();

-- ── Categories (one per service) ────────────────────────────────────────────
INSERT INTO `categories` (`slug`, `name`, `description`, `created_at`, `updated_at`) VALUES
('web-development',     'Web Development',       'Modern, performant websites and web applications built with current best practices.', @now, @now),
('shopify',             'Shopify',               'Shopify storefronts, theme development, and conversion-focused e-commerce builds.',   @now, @now),
('ecommerce-management','E-commerce Management', 'End-to-end store ops: catalogue, listings, fulfilment, analytics, and growth.',       @now, @now),
('business-automation', 'Business Automation',   'Workflow automation, integrations, and AI-driven process optimization.',              @now, @now);

SET @cat_web        := (SELECT `id` FROM `categories` WHERE `slug` = 'web-development');
SET @cat_shopify    := (SELECT `id` FROM `categories` WHERE `slug` = 'shopify');
SET @cat_ecom       := (SELECT `id` FROM `categories` WHERE `slug` = 'ecommerce-management');
SET @cat_automation := (SELECT `id` FROM `categories` WHERE `slug` = 'business-automation');

-- ── Sample published posts ──────────────────────────────────────────────────
INSERT INTO `posts`
  (`slug`, `title`, `excerpt`, `content`, `featured_image`, `image_alt`, `category_id`, `author_id`, `status`, `views`, `published_at`, `created_at`, `updated_at`) VALUES

('why-core-web-vitals-still-matter-in-2026',
 'Why Core Web Vitals still matter in 2026',
 'Performance is no longer a nice-to-have. Here is how Codentra ships sites that hit 95+ Lighthouse scores out of the gate.',
 '<p>Google''s Core Web Vitals — LCP, INP, and CLS — remain core ranking signals in 2026. We walk through the architecture choices, asset pipelines, and rendering strategies that let our PHP-built sites score 95+ in Lighthouse on a cold cache.</p><h2>What we measure</h2><p>LCP under 2.0s. CLS under 0.05. INP under 200ms. We treat these as ship-blockers, not aspirations.</p><h2>What we ship</h2><ul><li>Self-hosted fonts with <code>font-display: swap</code></li><li>WebP imagery with <code>&lt;picture&gt;</code> fallbacks</li><li>OPcache + file-based page caching</li><li>Lazy-loaded JavaScript modules</li></ul>',
 '/public/images/blog/cwv-2026.webp', 'Core Web Vitals dashboard',
 @cat_web, @admin_id, 'published', 142, @now, @now, @now),

('shopify-conversion-checklist-the-12-fixes-that-move-the-needle',
 'Shopify conversion checklist: the 12 fixes that move the needle',
 'A pragmatic audit list we run on every Shopify storefront — usually worth a 1.5–3x conversion lift in the first month.',
 '<p>Most Shopify stores leak revenue at predictable points. Below is the audit we run on day one of every engagement.</p><h2>Above the fold</h2><ol><li>Hero load time</li><li>Trust badges placement</li><li>Single, unambiguous CTA</li></ol><h2>Product page</h2><ol start="4"><li>Image compression and zoom</li><li>Variant selectors that don''t reload</li><li>Reviews above the fold on mobile</li><li>Sticky add-to-cart</li></ol><h2>Checkout</h2><ol start="8"><li>Express payment buttons</li><li>Shipping calculator pre-checkout</li><li>Trust signals at payment step</li><li>Cart abandonment recovery</li><li>Post-purchase upsells</li></ol>',
 '/public/images/blog/shopify-checklist.webp', 'Shopify conversion funnel',
 @cat_shopify, @admin_id, 'published', 87, @now, @now, @now),

('automating-order-ops-when-spreadsheets-stop-scaling',
 'Automating order ops: when spreadsheets stop scaling',
 'You hit ~200 orders a week and the Google Sheet starts breaking. Here is the practical automation stack we deploy.',
 '<p>There''s a clear inflection point in e-commerce ops where manual coordination starts costing more than the tooling. We see it around 200 orders/week.</p><h2>The stack we standardize on</h2><ul><li>Shopify Admin API for order ingestion</li><li>n8n or Make.com for workflow orchestration</li><li>A lightweight PHP webhook receiver for custom logic</li><li>Google Sheets only as a read-only reporting layer</li></ul><h2>What we automate first</h2><p>Order tagging, fraud flagging, fulfilment routing, and customer follow-up sequences. These four moves recover ~10 hours/week per ops person.</p>',
 '/public/images/blog/order-automation.webp', 'Automated order workflow diagram',
 @cat_automation, @admin_id, 'published', 54, @now, @now, @now);

-- ── Default site settings ───────────────────────────────────────────────────
INSERT INTO `settings` (`key_name`, `value`, `updated_at`) VALUES
('site_title',       'Codentra — Code · Automate · Scale',                                                      @now),
('site_tagline',     'Code · Automate · Scale',                                                                 @now),
('meta_description', 'Codentra is a premium web development, Shopify, e-commerce management & business automation agency based in Pakistan.', @now),
('contact_email',    'info@codentra.pk',                                                                        @now),
('contact_phone',    '+92 317 1263292',                                                                         @now),
('contact_address',  'Pakistan',                                                                                @now),
('social_facebook',  '',                                                                                        @now),
('social_instagram', '',                                                                                        @now),
('social_linkedin',  '',                                                                                        @now),
('social_twitter',   '',                                                                                        @now),
('social_github',    '',                                                                                        @now),
('og_image',         '/public/images/og-default.webp',                                                          @now),
('analytics_id',     '',                                                                                        @now),
('maintenance_mode', '0',                                                                                       @now);
