// Menu product layouts, "menu category" source, shortcode cache and Elementor widgets.
// Uses the test fixture endpoints (?sawerna_test=...), installed by global-setup.js.
const { test, expect } = require('@playwright/test');
const { settle } = require('./helpers');

const shortcode = (q) => '/?sawerna_test=shortcode&' + new URLSearchParams(q).toString();
const hrefs = async (page) => page.locator('#sawerna-test-output a.sawerna-menu-product').evaluateAll((a) => a.map((x) => x.getAttribute('href')));

const LAYOUTS = [
	{ layout: 'compact', limit: 3, expected: 3 },
	{ layout: 'grid', limit: 9, expected: 4 }, // Always 2x2.
	{ layout: 'list', limit: 4, expected: 4 },
	{ layout: 'featured', limit: 5, expected: 1 }, // Always one product.
];

test.describe('layouts', () => {
	for (const { layout, limit, expected } of LAYOUTS) {
		test(`${layout}: markup, item count, no wishlist, image attributes, look`, async ({ page }) => {
			await page.goto(shortcode({ layout, limit, category: 'diivanid' }));
			const wrap = page.locator(`#sawerna-test-output .sawerna-menu-products--${layout}`);
			await expect(wrap).toBeVisible();
			await expect(wrap.locator('a.sawerna-menu-product')).toHaveCount(expected);
			await expect(wrap.locator('.sawerna-wishlist-button')).toHaveCount(0);
			await expect(wrap.locator('.sawerna-menu-product__price').first()).toContainText('€');
			for (const img of await wrap.locator('img').all()) {
				expect(await img.getAttribute('alt')).toBeTruthy();
				expect(await img.getAttribute('width')).toBeTruthy();
				expect(await img.getAttribute('height')).toBeTruthy();
			}
			if (layout === 'featured') await expect(wrap.locator('.sawerna-menu-product__button')).toBeVisible();
			await settle(page);
			await expect(page.locator('#sawerna-test-output')).toHaveScreenshot(`menu-products-${layout}.png`);
		});
	}

	test('cards layout still renders shop cards', async ({ page }) => {
		await page.goto(shortcode({ layout: 'cards', limit: 3, category: 'diivanid' }));
		await expect(page.locator('#sawerna-test-output .sawerna-shortcode-product-grid .sawerna-product-card')).toHaveCount(3);
	});

	test('show_price and show_delivery switches', async ({ page }) => {
		await page.goto(shortcode({ layout: 'compact', limit: 3, show_delivery: 'yes' }));
		await expect(page.locator('#sawerna-test-output .sawerna-menu-product__delivery').first()).toBeVisible();
	});
});

test.describe('menu category source', () => {
	test('category="menu" follows the menu item category', async ({ page }) => {
		await page.goto(shortcode({ layout: 'list', limit: 4, category: 'menu', menu_cat: 'diivanid' }));
		const viaMenu = await hrefs(page);
		await page.goto(shortcode({ layout: 'list', limit: 4, category: 'diivanid' }));
		expect(viaMenu.length).toBeGreaterThan(0);
		expect(viaMenu).toEqual(await hrefs(page));
	});

	test('different menu items get different products', async ({ page }) => {
		await page.goto(shortcode({ layout: 'list', limit: 4, category: 'menu', menu_cat: 'diivanid' }));
		const sofas = await hrefs(page);
		await page.goto(shortcode({ layout: 'list', limit: 4, category: 'menu', menu_cat: 'moobel' }));
		expect(await hrefs(page)).not.toEqual(sofas);
	});

	test('outside a menu it falls back to all products', async ({ page }) => {
		await page.goto(shortcode({ layout: 'list', limit: 4, category: 'menu' }));
		const viaMenu = await hrefs(page);
		await page.goto(shortcode({ layout: 'list', limit: 4 }));
		expect(viaMenu).toEqual(await hrefs(page));
	});

	test('live mega menu renders the chosen layout under its category', async ({ page, context, isMobile }) => {
		test.skip(isMobile, 'desktop mega menu');
		await context.addCookies([{ name: 'sawerna_layout_preview', value: 'grid', url: test.info().project.use.baseURL }]);
		await page.goto('/');
		await page.hover('.sawerna-mega-menu > li.menu-item-object-product_cat > a');
		const grid = page.locator('.sawerna-mega-menu .sawerna-menu-products--grid:visible').first();
		await expect(grid).toBeVisible();
		await expect(grid.locator('a.sawerna-menu-product')).toHaveCount(4);
	});
});

test.describe('cache', () => {
	test.describe.configure({ mode: 'serial' });
	// Server-side cache is shared: run once, not in parallel per viewport.
	test.skip(({ isMobile }) => isMobile, 'viewport independent');

	test('miss, hit, invalidated after flush', async ({ request }) => {
		const url = shortcode({ layout: 'compact', limit: 3, category: 'diivanid' });
		await request.get('/?sawerna_test=flush');
		expect((await request.get(url)).headers()['x-sawerna-cache']).toBe('MISS');
		expect((await request.get(url)).headers()['x-sawerna-cache']).toBe('HIT');
		await request.get('/?sawerna_test=flush');
		expect((await request.get(url)).headers()['x-sawerna-cache']).toBe('MISS');
	});

	test('cards with wishlist buttons are never cached', async ({ request }) => {
		const url = shortcode({ layout: 'cards', limit: 2 });
		await request.get(url);
		expect((await request.get(url)).headers()['x-sawerna-cache']).toBe('MISS');
	});

	test('cache is per URL scheme: https-cached HTML is never served on http (broken images)', async ({ request }) => {
		await request.get('/?sawerna_test=flush');
		const path = '/?sawerna_test=shortcode&layout=compact&limit=3&category=diivanid';
		await request.get('https://sawernau.local' + path); // Warm the cache over https.
		const html = await (await request.get('http://sawernau.local' + path)).text();
		const output = html.split('id="sawerna-test-output"')[1] || '';
		const schemes = [...output.matchAll(/<img[^>]+src="(https?):\/\//g)].map((m) => m[1]);
		expect(schemes.length).toBeGreaterThan(0);
		expect(new Set(schemes)).toEqual(new Set(['http']));
	});

	test('cache is per menu category', async ({ request }) => {
		await request.get('/?sawerna_test=flush');
		await request.get(shortcode({ layout: 'list', category: 'menu', menu_cat: 'diivanid' }));
		expect((await request.get(shortcode({ layout: 'list', category: 'menu', menu_cat: 'moobel' }))).headers()['x-sawerna-cache']).toBe('MISS');
	});
});

test.describe('elementor widgets', () => {
	test.skip(({ isMobile }) => isMobile, 'viewport independent');
	test('all Sawerna widgets are registered', async ({ request }) => {
		const names = await (await request.get('/?sawerna_test=widgets')).json();
		expect(names.sort()).toEqual(['sawerna_banner', 'sawerna_blog_posts', 'sawerna_image_grid', 'sawerna_image_text', 'sawerna_products']);
	});

	test('products widget defaults to compact layout', async ({ request }) => {
		const html = await (await request.get('/?sawerna_test=widget&type=sawerna_products')).text();
		expect(html).toContain('sawerna-menu-products--compact');
	});

	test('blog widget renders posts', async ({ request }) => {
		const html = await (await request.get('/?sawerna_test=widget&type=sawerna_blog_posts')).text();
		expect(html).toContain('sawerna-blog-post-card');
	});
});
