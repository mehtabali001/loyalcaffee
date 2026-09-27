# Loyal Restaurant & Cafe — Website + CMS

Bilingual (English / Arabic, full RTL) website and admin panel for Loyal Restaurant & Cafe, Al Marami St 8, Madinat Zayed, Abu Dhabi.
Built with **Laravel 11** (API, SEO shell, admin auth) and **Vue 3** (public site SPA + admin SPA). Everything visitors see is editable from `/admin`.

Design follows the *Loyal Brand Design System*: Emerald `#1B4332`, Antique Gold `#C9A227`, Ivory `#F5F0E6`, Charcoal `#21201F`, Terracotta `#A85C32`; Playfair Display + Montserrat (EN), Noto Kufi Arabic + Cairo (AR); mashrabiya lattice, thin gold rules and the smoke-line motif.

## Requirements

PHP 8.2+ (extensions: gd, pdo_sqlite or pdo_mysql, mbstring, intl recommended), Composer 2, Node 18+.

## Install

```bash
composer install
cp .env.example .env
php artisan key:generate

# SQLite (default). For MySQL, set DB_* in .env instead.
touch database/database.sqlite

php artisan migrate --seed          # creates tables, business info, menu, sample fixtures, admin user
php artisan storage:link            # serves uploaded images from /storage
npm install && npm run build        # or `npm run dev` while developing

php artisan serve                   # http://localhost:8000
```

Admin: `http://localhost:8000/admin` with `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env` (defaults `admin@loyalcafe.ae` / `ChangeMe!2026`). **Change the password after first sign-in** (My account).

### Production

- `APP_ENV=production`, `APP_DEBUG=false`, correct `APP_URL` (used for canonical URLs, hreflang and the sitemap).
- `php artisan config:cache route:cache view:cache`
- Scheduler cron (marks unconfirmed past bookings as no-show at 06:00 and prunes expired admin tokens):
  `* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1`
- Optional staff email alerts: configure `MAIL_*` in `.env`, then set the address in Admin → Settings → Reservations.
- Point the document root at `public/`. Submit `https://your-domain/sitemap.xml` in Google Search Console.

## What's in it

**Public site** (`/en/…` and `/ar/…`): Home, Menu, Gallery, Match nights, Reserve, Contact, plus 404.

- The home hero follows the cafe's clock (Asia/Dubai): ivory "by day" layout until 5 PM, full-bleed emerald "by night" after; visitors can flip it.
- Live "Open now until 3 AM" status that understands shifts running past midnight, on every page.
- WhatsApp and click-to-call on every page (sticky bar on mobile), Google Map + directions.
- Menu with sticky category rail, search (Arabic-diacritic aware), printed-menu leader lines, tags and prices in AED.
- Gallery with filters and an RTL-aware lightbox. Match nights grouped by week with flags, big-match highlighting, per-match offers and seat capacity.
- Reservations validated server-side against opening hours, lead time, party size and match capacity; each gets a reference (`LY-XXXXXX`) and the guest is confirmed on WhatsApp.
- SEO: server-rendered titles/descriptions per page and language, canonical + hreflang, Open Graph, `Restaurant` and `Event` JSON-LD, crawlable HTML fallback, sitemap.xml, robots.txt. GA4 and Meta Pixel IDs from settings.

**Admin** (`/admin`): dashboard, reservations (filters, status, one-tap WhatsApp confirmation in the guest's language, staff notes, CSV export that opens correctly in Excel with Arabic), messages inbox, homepage sections, menu categories/items (drag to reorder, in-stock and featured toggles), gallery (multi-upload, auto-converted to WebP), match nights/events, offers with start/end dates, reviews, settings (contact, map, hours, amenities, rating, reservations, brand colours with live preview, SEO per page, analytics, announcement bar, logos), website text (override any interface string in both languages), staff accounts (owner/editor roles).

## Architecture

```
app/
  Http/Controllers/Api/      public JSON API (cached, locale via X-Locale header)
  Http/Controllers/Admin/    CMS API (Sanctum tokens); CrudController drives most resources
  Http/Controllers/SpaController.php   HTML shell: meta, JSON-LD, inlined bootstrap data
  Services/SiteContent.php   read models for the site, cached with version-key busting
  Services/Seo.php           per-page, per-language meta + structured data
  Services/MediaUploader.php resize + WebP conversion + EXIF rotation
  Support/                   HasTranslations ({en, ar} JSON columns), ContentVersion, Locale
resources/js/
  site/                      public Vue app (router, pinia store, vue-i18n, pages, sections)
  admin/                     admin Vue app; resources.js = schema for list/form screens
  shared/                    Icon set (thin-line, per brand guide), HTTP helpers
```

- Translatable fields are JSON columns (`{"en": "...", "ar": "..."}`); adding a third language means adding it to `config/loyal.php` and a JSON file under `resources/js/site/i18n/`.
- Any content save bumps a cache version, so the public API is served from cache yet is never stale.
- The first page load ships with its data inlined, so the site paints without an extra API round trip; later navigation is client-side.
- Brand colours are CSS variables written by the server from settings, so re-theming needs no rebuild.
- Forms are protected by rate limiting (5/min, 40/day per IP) and a honeypot field.

## Before launch — content to replace

1. **Photos**: the site ships with licensed stock photography in `public/images/photos` (Unsplash License, free for commercial use; sources in `CREDITS.txt`). Existing installs pick it up with `php artisan migrate`, which only replaces values still pointing at the old SVG placeholders. Swap in the cafe's own shoot when ready (Admin → Homepage, Offers, Gallery); the Gallery and Reserve page banners are set in `GalleryPage.vue` / `ReservePage.vue`.
2. **Menu prices**: seeded prices are placeholders. Update every item in Admin → Menu items.
3. **Match fixtures**: three sample fixtures are seeded relative to the install date. Delete them and add the real schedule. Note: the FIFA World Cup 2026 final was played on 19 July 2026, so the module is built for year-round football (league, Champions League, national team games) and themed nights like "Loyal Nights".
4. **Offers**: three brand-strategy campaigns (Golden Hour, Bring the Table, First Cup's On Us) are seeded; confirm terms and dates, and add Loyal Nights themes as events.
5. **Reviews**: none are published by default. Add genuine Google reviews only.
6. **Map pin**: set exact latitude/longitude and the embed URL from Google Maps (Admin → Settings → Contact & map), and the Google review link.
7. **Logo, social links, email, GA4/Pixel IDs, social sharing image.**
