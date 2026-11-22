# Sawerna

Custom WordPress + WooCommerce theme for Sawerna, a furniture manufacturer. Built from a bespoke design on top of the [Underscores (`_s`)](https://underscores.me/) starter.

## Requirements

- WordPress 6.x, WooCommerce (classic checkout, not Blocks)
- **Sawerna Core** plugin. It holds the business logic (product meta, wishlist, shop filters, swatches, mini-cart, HTML blocks, claims, AJAX search). The theme shows an admin notice when it is missing.
- WPML for the Estonian/English translations
- Elementor (used to edit the HTML blocks in the header mega menu)
- ThemeHigh Extra Product Options (optional, integrated on the single product page)

## Structure

| Path | Contents |
|---|---|
| `functions.php` | Theme setup, menus, conditional CSS/JS enqueueing |
| `inc/` | Homepage, contact and about options, customizer, WooCommerce setup |
| `woocommerce/` | Template overrides: product card, single product, archive, cart, checkout, my account |
| `template-parts/`, `*-page.php`, `template-*.php` | Page templates |
| `assets/css/` | One stylesheet per page or feature, loaded only where needed |
| `js/` | Front-end scripts (vanilla JS) |
| `assets/js/` | Admin scripts (swatches, product card, custom tabs, PDF, footer slider) |
| `assets/fonts/` | Inter (SIL Open Font License) |

Feature code lives in the Sawerna Core plugin, and the theme provides the templates and all CSS/JS. The files `inc/wishlist.php`, `inc/shop-filters.php`, `inc/product-card.php`, `inc/single-product.php`, `inc/attribute-swatches.php`, `inc/html-blocks.php` and `inc/menu-html-blocks.php` are not loaded. Their live versions are in the plugin.

The header mega menu and HTML blocks are documented in `HTML-BLOCKS-README.md` and `HTML-BLOCK-STYLING-GUIDE.md`.

## Development

There is no build step. CSS and JS are edited directly.

- With `WP_DEBUG` on, asset versions use the file modification time. In production they use `_S_VERSION` in `functions.php`, so **bump it on every release** to bust caches.
- Default WooCommerce styles are disabled. All shop styling is in `assets/css/`.

Optional linting:

```bash
composer install && composer lint:wpcs   # PHP coding standards
npm install && npm run lint:js           # JavaScript
```

## Installation

Zip the theme folder, upload it under Appearance > Themes > Add New, then activate it together with the Sawerna Core plugin.

## License

GPLv2 or later. Based on Underscores, (C) 2012-2020 Automattic, Inc.

## Quality checks

Coding standards (WPCS 3 + WooCommerce sniffs, rulesets: `phpcs.xml.dist` here and in `sawerna-core`):

```bash
composer global require wp-coding-standards/wpcs:"^3.1" woocommerce/woocommerce-sniffs
phpcs -q .            # theme
phpcbf .              # auto-fix formatting (check rendered HTML afterwards)
```

PHP compatibility before PHP/WP upgrades: `bash tests/php/compat-check.sh` (install notes inside).

Front-end tests (Playwright + Lighthouse, needs the local site at https://sawernau.local with the store **Live**, not "coming soon"):

```bash
cd tests && npm install
npm run test:visual     # full-page screenshot comparison, 18 templates x desktop/mobile
npm run test:baseline   # accept current look as the new baseline (only after a deliberate design change)
npm run test:smoke      # page health, PHP/JS errors, search, wishlist, filters, cart, checkout, menus
npm run lighthouse      # Core Web Vitals, writes tests/reports/<label>/summary.json
npx playwright test menu-products  # menu product layouts, menu category, cache, Elementor widgets
npx playwright test blocks         # Gutenberg blocks: rendering, sanitizing, security regressions
npm run auth && npm run test:admin # admin UI (menu links, usage column, templates, both editors); auth saves a login session to tests/.auth (gitignored, delete when done)
```

The tests install `tests/fixtures/sawerna-test-fixture.php` into `mu-plugins` for the run and remove it afterwards. It only activates on `*.local` hosts with `WP_DEBUG` on. Never deploy it.

## Menu content (HTML blocks)

In the block editor, the "Sawerna" category has the same four as blocks (`sawerna/products`, `sawerna/blog-posts`, `sawerna/banner`, `sawerna/image-grid`) plus patterns in "Sawerna menüü". In Elementor, the "Sawerna" widget category has Tooted, Blogipostitused, Bänner and Pildiruudustik. They build the shortcodes below, so both stay interchangeable.

`[sawerna_products layout="compact|grid|list|featured|cards" category="menu|<slugs>" ids="" limit="3" columns="3" on_sale="yes" orderby="date|popularity|..." show_price="yes" show_delivery="no"]`

- `category="menu"` uses the product category of the menu item the block is attached to, so one block serves every menu item.
- `grid` is always 2x2, `featured` always one product.
- Output of `sawerna_products` and `sawerna_blog_posts` is cached for 6 hours and invalidated on product, post or HTML block saves and stock changes. Output containing wishlist buttons (`cards` layout) is not cached.

`[sawerna_blog_posts layout="grid|single" id="" ids="" category="<slug>" ...]`: `single` shows one post (id, else newest from category, else newest; published, no password).

`[sawerna_banner ... button_style="primary|light|outline" overlay="0-80" valign="top|center|bottom"]` (legacy `light="yes"` = `button_style="light"`).

`[sawerna_image_text image="<attachment ID>" title="" text="" link="" position="bottom-left|bottom-center|center|top-left" overlay="25" ratio="4/5|1/1|16/9|3/2"]`, block `sawerna/image-text`, widget "Sawerna: Pilt tekstiga". Block variation "Tooted 2x2" = `sawerna/products` with `layout: grid`.

When updating WooCommerce, compare the `@version` of each file in `woocommerce/` with the same file in `plugins/woocommerce/templates/` and port upstream changes.

## Maintenance page

`maintenance/maintenance.php` replaces WordPress's generic "Briefly unavailable for scheduled maintenance" screen shown during updates. It is a WordPress drop-in, not a theme template: WordPress loads it from `wp-content/maintenance.php` before the theme, plugins and database are available, so it is fully self-contained (inline styles and logo, theme fonts loaded by URL with a system-font fallback).

- Install: copy `maintenance/maintenance.php` to `wp-content/maintenance.php` (once per server, and again after changing it).
- It returns `503 Service Unavailable` with `Retry-After: 120` and `noindex`, and reloads itself every 60 seconds.
- Test locally: create `.maintenance` in the WordPress root containing `<?php $upgrading = time(); ?>`, open the site, then delete the file (WordPress also ignores it after 10 minutes).
