-- ============================================================================
--  Migration 003 — Phase 10 settings extension
--
--  Adds the keys the new SettingsController expects. Existing rows from
--  schema.sql / seed.sql are preserved (INSERT IGNORE on the unique
--  `key_name` column).
-- ============================================================================

SET NAMES utf8mb4;
SET @now := NOW();

INSERT IGNORE INTO `settings` (`key_name`, `value`, `updated_at`) VALUES
  -- General
  ('site_title',                'Codentra',                                                                                       @now),
  ('site_tagline',              'Code · Automate · Scale',                                                                        @now),
  ('site_description',          'Codentra is a premium web development, Shopify, e-commerce management & business automation agency based in Pakistan.', @now),
  ('site_logo',                 '/public/images/logo.webp',                                                                       @now),
  ('timezone',                  'Asia/Karachi',                                                                                   @now),

  -- Contact
  ('contact_phone',             '+92 317 1263292',                                                                                @now),
  ('contact_email',             'info@codentra.pk',                                                                               @now),
  ('contact_address',           '',                                                                                               @now),
  ('business_hours',            'Mon–Fri 9:00–18:00 PKT',                                                                         @now),
  ('response_time_promise',     'within 24 hours',                                                                                @now),

  -- Social
  ('social_facebook',           '',                                                                                               @now),
  ('social_instagram',          '',                                                                                               @now),
  ('social_linkedin',           '',                                                                                               @now),
  ('social_twitter',            '',                                                                                               @now),
  ('social_youtube',            '',                                                                                               @now),

  -- SEO
  ('seo_default_title_suffix',  ' | Codentra',                                                                                    @now),
  ('seo_og_image',              '/public/images/og-default.webp',                                                                  @now),
  ('google_analytics_id',       '',                                                                                               @now),
  ('google_search_console',     '',                                                                                               @now),

  -- Business
  ('business_name',             'Codentra',                                                                                       @now),
  ('business_legal_name',       'Codentra',                                                                                       @now),
  ('business_founded_year',     '2025',                                                                                           @now),
  ('business_country',          'Pakistan',                                                                                       @now),
  ('business_city',             'Islamabad',                                                                                      @now);
