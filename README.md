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
