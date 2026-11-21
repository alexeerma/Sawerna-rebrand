// Visual regression: full-page screenshots of every key template.
// Create baseline: npm run test:baseline   Compare: npm run test:visual
const { test, expect } = require('@playwright/test');
const { pages, SIMPLE_PRODUCT_ID } = require('./pages');
const { settle } = require('./helpers');

// Content that changes per request (autoplay hero slider, randomized sale products).
const MASK = (page) => [page.locator('.sawerna-homepage__hero'), page.locator('.sawerna-homepage__sales')];

for (const [name, path] of Object.entries(pages)) {
	test(`visual: ${name}`, async ({ page }) => {
		await page.goto(path, { waitUntil: 'load' });
		await settle(page);
		// Randomized sale products change this section's height; pin it so content below stays aligned.
		await page.addStyleTag({ content: '.sawerna-homepage__sales{height:560px!important;overflow:hidden!important}' });
		await expect(page).toHaveScreenshot(`${name}.png`, { fullPage: true, timeout: 30_000, mask: MASK(page) });
	});
}

for (const name of ['cart', 'checkout']) {
	test(`visual: ${name}_with_item`, async ({ page }) => {
		await page.goto(`/cart/?add-to-cart=${SIMPLE_PRODUCT_ID}`, { waitUntil: 'load' });
		await page.goto(`/${name}/`, { waitUntil: 'load' });
		await page.waitForLoadState('networkidle').catch(() => {});
		await settle(page);
		await expect(page).toHaveScreenshot(`${name}_with_item.png`, { fullPage: true, timeout: 30_000 });
	});
}
