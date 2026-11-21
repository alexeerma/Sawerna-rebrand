// Functional smoke tests for theme features. Run: npm run test:smoke
const { test, expect } = require('@playwright/test');
const { pages, SIMPLE_PRODUCT_ID } = require('./pages');
const { watchErrors } = require('./helpers');

const PHP_ERRORS = /(Fatal error|Parse error|Warning|Notice|Deprecated)<\/b>:|<b>(Fatal error|Warning|Notice|Deprecated)/;

test.describe('page health', () => {
	for (const [name, path] of Object.entries(pages)) {
		test(`${name}: loads, no PHP or JS errors`, async ({ page }) => {
			const errors = watchErrors(page);
			const res = await page.goto(path, { waitUntil: 'load' });
			expect(res.status(), 'HTTP status').toBe(name === 'notfound' ? 404 : 200);
			expect(await page.content()).not.toMatch(PHP_ERRORS);
			await expect(page.locator('.sawerna-header, header').first()).toBeVisible();
			await expect(page.locator('footer').first()).toBeAttached();
			await page.waitForTimeout(500);
			expect(errors, errors.join('\n')).toEqual([]);
		});
	}
});

test.describe('features', () => {
	test('ajax search returns results', async ({ page, isMobile }) => {
		test.skip(isMobile, 'desktop header search');
		await page.goto('/');
		const input = page.locator('[data-sawerna-search-input]:visible').first();
		await input.fill('diivan');
		await expect(page.locator('[data-sawerna-search-results]:visible').first()).toContainText(/\S/, { timeout: 10_000 });
	});

	test('wishlist toggle updates button state', async ({ page }) => {
		await page.goto(pages.shop);
		const btn = page.locator('.sawerna-product-card .sawerna-wishlist-button').first();
		const before = await btn.getAttribute('aria-pressed');
		const resp = page.waitForResponse((r) => r.url().includes('admin-ajax.php') && r.request().postData()?.includes('sawerna_wishlist_toggle'));
		await btn.click();
		expect((await resp).ok()).toBeTruthy();
		await expect(btn).not.toHaveAttribute('aria-pressed', before || 'false');
	});

	test('shop filter updates product grid via ajax', async ({ page, isMobile }) => {
		test.skip(isMobile, 'mobile uses overlay apply flow');
		await page.goto(pages.category);
		// Options sit in collapsible groups: open the first group that has checkboxes, then click an option.
		const group = page.locator('.sawerna-filter__group:has(input[type="checkbox"][data-filter-key])').first();
		const label = group.locator('label:has(input[data-filter-key])').first();
		if (!(await label.isVisible())) await group.locator('button').first().click();
		await expect(label).toBeVisible();
		const resp = page.waitForResponse((r) => r.url().includes('admin-ajax.php') && r.request().postData()?.includes('sawerna_shop_filter_products'));
		await label.click();
		const r = await resp;
		expect(r.ok()).toBeTruthy();
		const json = await r.json();
		expect(json.success).toBeTruthy();
		await expect(page).toHaveURL(/\?/);
	});

	test('add to cart from product page, cart and checkout render', async ({ page }) => {
		const errors = watchErrors(page);
		await page.goto(pages.product_simple);
		await page.locator('.single_add_to_cart_button').first().click();
		await page.waitForLoadState('networkidle').catch(() => {});
		await page.goto('/cart/');
		await expect(page.locator('form.woocommerce-cart-form')).toBeVisible();
		await expect(page.locator('.woocommerce-cart-form').first()).toContainText(/Tekk|tekk/);
		await expect(page.locator('.checkout-button, a[href*="checkout"]').first()).toBeVisible();

		await page.goto('/checkout/');
		await expect(page.locator('form.checkout')).toBeVisible();
		for (const f of ['#billing_first_name', '#billing_email', '#billing_phone']) await expect(page.locator(f)).toBeAttached();
		await expect(page.locator('#order_review')).toBeVisible();
		await expect(page.locator('#place_order')).toBeAttached({ timeout: 15_000 });
		expect(errors, errors.join('\n')).toEqual([]);
	});

	test('cart quantity update and remove item', async ({ page }) => {
		await page.goto(`/cart/?add-to-cart=${SIMPLE_PRODUCT_ID}`);
		await page.goto('/cart/');
		// The theme updates the cart through its +/- stepper (the core "Update cart" button is visually hidden).
		const qty = page.locator('.woocommerce-cart-form input[name$="[qty]"]').first();
		await expect(qty).toHaveValue('1');
		await page.locator('.sawerna-cart__quantity-button--increase').first().click();
		await page.waitForLoadState('networkidle').catch(() => {});
		await expect(page.locator('.woocommerce-cart-form input[name$="[qty]"]').first()).toHaveValue('2');
		await page.locator('.woocommerce-cart-form .sawerna-cart__remove').first().click();
		await expect(page.locator('.wc-empty-cart-message, .cart-empty, .woocommerce-info').first()).toBeVisible({ timeout: 15_000 });
	});

	test('mini cart opens', async ({ page }) => {
		await page.goto(`/cart/?add-to-cart=${SIMPLE_PRODUCT_ID}`);
		await page.goto('/');
		const trigger = page.locator('[data-mini-cart-trigger]:visible').first();
		test.skip((await trigger.count()) === 0, 'no visible mini cart trigger');
		await trigger.click();
		await expect(page.locator('.sawerna-mini-cart__panel').first()).toBeVisible();
	});

	test('mobile menu opens', async ({ page, isMobile }) => {
		test.skip(!isMobile, 'mobile only');
		await page.goto('/');
		await page.locator('.sawerna-header__mobile-toggle').first().click();
		await expect(page.locator('.sawerna-mobile-menu--main').first()).toBeVisible();
	});

	test('login form renders', async ({ page }) => {
		await page.goto(pages.account_login);
		await expect(page.locator('form.woocommerce-form-login, form.login').first()).toBeVisible();
		await expect(page.locator('#username')).toBeAttached();
	});
});
