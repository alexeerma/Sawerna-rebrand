// Admin UI: menu screen links, HTML block list, templates, block editor, Elementor.
// Needs a saved admin session: run `npm run auth` once (log in, close the window).
const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const AUTH = path.join(__dirname, '..', '.auth', 'admin.json');
test.skip(!fs.existsSync(AUTH), 'no admin session; run `npm run auth` first');
test.skip(({ isMobile }) => isMobile, 'admin is tested on desktop');
test.use({ storageState: fs.existsSync(AUTH) ? AUTH : undefined });
test.describe.configure({ mode: 'serial' });

test('menus screen: block select with edit and new links', async ({ page }) => {
	await page.goto('/wp-admin/nav-menus.php');
	const select = page.locator('select[id^="edit-menu-item-html-block-"]').first();
	await expect(select).toBeAttached();
	const id = (await select.getAttribute('id')).replace('edit-menu-item-html-block-', '');
	await expect(page.locator(`#edit-menu-item-html-block-${id}-new`)).toHaveAttribute('href', /post-new\.php\?post_type=sawerna_html_block/);

	// A menu item that already has a block shows a working edit link.
	const withBlock = page.locator('select[id^="edit-menu-item-html-block-"]').filter({ has: page.locator('option[selected]:not([value=""]):not([value="0"])') }).first();
	const wid = (await withBlock.getAttribute('id')).replace('edit-menu-item-html-block-', '');
	const edit = page.locator(`#edit-menu-item-html-block-${wid}-edit`);
	await expect(edit).toHaveAttribute('href', /post\.php\?post=\d+&action=(edit|elementor)/);

	// Changing the select updates the link (no save).
	const other = await withBlock.locator('option:not([selected]):not([value=""]):not([value="0"])').first().getAttribute('value');
	if (other) {
		await withBlock.selectOption(other, { force: true });
		await expect(edit).toHaveAttribute('href', new RegExp(`post=${other}&`));
	}
});

test('blocks list shows where each block is used', async ({ page }) => {
	await page.goto('/wp-admin/edit.php?post_type=sawerna_html_block');
	await expect(page.locator('th#menu_usage')).toBeVisible();
	await expect(page.locator('td.column-menu_usage li[data-menu-item-id]').first()).toBeVisible();
	await expect(page.locator('#sawerna-html-block-from-template')).toBeVisible();
});

test('usage notice on the edit screen of a used block', async ({ page }) => {
	await page.goto('/wp-admin/edit.php?post_type=sawerna_html_block');
	const row = page.locator('tr', { has: page.locator('td.column-menu_usage li[data-menu-item-id]') }).first();
	const id = (await row.getAttribute('id')).replace('post-', '');
	await page.goto(`/wp-admin/post.php?post=${id}&action=edit`);
	await expect(page.getByText(/Kasutusel/).first()).toBeVisible({ timeout: 20_000 });
});

test('create from template opens Elementor with Sawerna widgets, then clean up', async ({ page }) => {
	await page.goto('/wp-admin/edit.php?post_type=sawerna_html_block&page=sawerna-html-block-templates');
	await expect(page.locator('#sawerna-html-block-templates form[data-template]')).toHaveCount(3);
	await page.locator('form[data-template="products-banner"] button[type=submit]').click();
	await page.waitForURL(/action=elementor/, { timeout: 30_000 });
	const postId = new URL(page.url()).searchParams.get('post');
	try {
		const preview = page.frameLocator('#elementor-preview-iframe');
		await expect(preview.locator('.elementor-widget-sawerna_products').first()).toBeVisible({ timeout: 45_000 });
		await expect(preview.locator('body.sawerna-html-block-canvas')).toHaveCount(1);
	} finally {
		// Delete the draft created by this test.
		await page.goto('/wp-admin/edit.php?post_type=sawerna_html_block');
		await page.evaluate(async (id) => {
			await window.fetch(`/wp-json/wp/v2/sawerna_html_block/${id}?force=true`, { method: 'DELETE', headers: { 'X-WP-Nonce': window.wpApiSettings ? window.wpApiSettings.nonce : '' } });
		}, postId);
	}
});

test('block editor: Sawerna blocks, live preview, layout switch, patterns', async ({ page }) => {
	await page.goto('/wp-admin/post-new.php?post_type=sawerna_html_block');
	await page.waitForFunction(() => window.wp && window.wp.data && window.wp.data.select('core/block-editor'), null, { timeout: 30_000 });

	// Starter patterns are registered for this post type.
	const patterns = await page.evaluate(() => (window.wp.data.select('core').getBlockPatterns?.() || window.wp.data.select('core/block-editor').getSettings().__experimentalBlockPatterns || []).map((p) => p.name));
	expect(patterns).toEqual(expect.arrayContaining(['sawerna/menu-products-grid-banner', 'sawerna/menu-blog-product-list', 'sawerna/menu-featured-links']));
	await page.keyboard.press('Escape'); // Close the pattern modal if open.

	const types = await page.evaluate(() => window.wp.blocks.getBlockTypes().map((b) => b.name).filter((n) => n.startsWith('sawerna/')));
	expect(types.sort()).toEqual(['sawerna/banner', 'sawerna/blog-posts', 'sawerna/image-grid', 'sawerna/products']);

	const clientId = await page.evaluate(() => {
		const block = window.wp.blocks.createBlock('sawerna/products', { layout: 'compact', source: 'newest' });
		window.wp.data.dispatch('core/block-editor').insertBlocks(block);
		return block.clientId;
	});
	const canvas = page.frameLocator('iframe[name="editor-canvas"]');
	await expect(canvas.locator('.sawerna-menu-products--compact').first()).toBeVisible({ timeout: 30_000 });
	await page.evaluate((id) => window.wp.data.dispatch('core/block-editor').updateBlockAttributes(id, { layout: 'grid' }), clientId);
	await expect(canvas.locator('.sawerna-menu-products--grid a.sawerna-menu-product')).toHaveCount(4, { timeout: 30_000 });
	// Not saved: WordPress cleans up the auto-draft.
});
