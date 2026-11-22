// Multi-column mega-menu HTML blocks: columns line up at the top, and "Täida veeru kõrgus" (fill)
// stretches image-text / banner to the height of the neighbouring column.
// Uses the fixture endpoints ?sawerna_test=blocks|widget and the sawerna_menu_preview cookie.
const { test, expect } = require('@playwright/test');

test.skip(({ isMobile }) => isMobile, 'desktop mega menu');

const render = async (request, markup) => (await request.get('/?sawerna_test=blocks&markup=' + encodeURIComponent(markup))).text();
const firstMediaId = async (request) => (await (await request.get('/wp-json/wp/v2/media?per_page=1&media_type=image')).json())[0].id;

test('fill adds the modifier class to image-text and banner blocks, and only when on', async ({ request }) => {
	const id = await firstMediaId(request);
	for (const [block, cls, extra] of [['image-text', 'sawerna-image-text--fill', ''], ['banner', 'sawerna-banner-shortcode--fill', ',"buttonText":"Osta"']]) {
		const on = await render(request, `<!-- wp:sawerna/${block} {"imageId":${id},"title":"T"${extra},"fill":true} /-->`);
		expect(on, block).toContain(cls);
		for (const off of ['"fill":false', '"fill":0']) {
			const html = await render(request, `<!-- wp:sawerna/${block} {"imageId":${id},"title":"T"${extra},${off}} /-->`);
			expect(html, `${block} ${off}`).not.toContain(cls);
			expect(html, `${block} ${off}`).toContain(cls.replace('--fill', ''));
		}
		const none = await render(request, `<!-- wp:sawerna/${block} {"imageId":${id},"title":"T"${extra}} /-->`);
		expect(none, `${block} default`).not.toContain(cls);
	}
});

test('fill shortcode attribute only accepts "yes"', async ({ request }) => {
	const id = await firstMediaId(request);
	const yes = await render(request, `<!-- wp:shortcode -->[sawerna_image_text image="${id}" title="T" fill="yes"]<!-- /wp:shortcode -->`);
	expect(yes).toContain('sawerna-image-text--fill');
	const evil = await render(request, `<!-- wp:shortcode -->[sawerna_image_text image="${id}" title="T" fill="&quot; onmouseover=&quot;x"]<!-- /wp:shortcode -->`);
	expect(evil).not.toContain('--fill');
	expect(evil).not.toContain('onmouseover');
});

test('combination templates with image-text / banner next to another column use fill (both variants)', async ({ request }) => {
	const id = await firstMediaId(request);
	const data = await (await request.get(`/?sawerna_test=templates&image=${id}`)).json();
	const bySlug = Object.fromEntries(data.templates.map((t) => [t.slug, t]));
	for (const [slug, cls] of [['products-2x2-image-text', 'sawerna-image-text--fill'], ['products-grid-banner', 'sawerna-banner-shortcode--fill']]) {
		expect(bySlug[slug].block_html, slug).toContain(cls);
		expect(bySlug[slug].elementor_html, slug).toContain(cls);
	}
	// Single components and the equal-height trio stay unfilled.
	for (const slug of ['image-text', 'banner-cta', 'image-text-trio']) {
		expect(bySlug[slug].block_html + bySlug[slug].elementor_html, slug).not.toMatch(/--fill\b/);
	}
});

test('Elementor widgets pass fill to the shortcode', async ({ request }) => {
	const id = await firstMediaId(request);
	const widget = async (type, settings) => (await request.get(`/?sawerna_test=widget&type=${type}&settings=${encodeURIComponent(JSON.stringify(settings))}`)).text();
	const image = { id, url: '' };
	for (const [type, cls, base] of [['sawerna_image_text', 'sawerna-image-text--fill', { image, title: 'T' }], ['sawerna_banner', 'sawerna-banner-shortcode--fill', { image, title: 'T', button_text: 'Osta' }]]) {
		expect(await widget(type, { ...base, fill: 'yes' }), type).toContain(cls);
		expect(await widget(type, { ...base, fill: '' }), type).not.toContain(cls);
	}
});

// Live Diivanid mega menu with a two-column block: products 2x2 grid + image-text.
const twoColumns = (id, fill) =>
	'<!-- wp:columns {"className":"sawerna-menu-columns"} --><div class="wp-block-columns sawerna-menu-columns">' +
	'<!-- wp:column --><div class="wp-block-column"><!-- wp:sawerna/products {"source":"menu","layout":"grid"} /--></div><!-- /wp:column -->' +
	`<!-- wp:column --><div class="wp-block-column"><!-- wp:sawerna/image-text {"imageId":${id},"title":"Inspiratsioon","ratio":"4/5"${fill ? ',"fill":true' : ''}} /--></div><!-- /wp:column -->` +
	'</div><!-- /wp:columns -->';

const openMenuColumns = async (page, context, request, fill) => {
	const id = await firstMediaId(request);
	await context.addCookies([{ name: 'sawerna_menu_preview', value: encodeURIComponent(twoColumns(id, fill)), url: test.info().project.use.baseURL }]);
	await page.goto('/');
	const item = page.locator('.sawerna-mega-menu > li').filter({ has: page.locator('> a', { hasText: 'Diivanid' }) });
	await item.locator('> a').hover();
	const columns = item.locator('.sawerna-html-block-dropdown .wp-block-column');
	await expect(columns).toHaveCount(2);
	await expect(columns.first().locator('.sawerna-menu-products--grid')).toBeVisible();
	await expect(columns.nth(1).locator('.sawerna-image-text')).toBeVisible();
	await page.waitForTimeout(500); // Dropdown open transition.
	const boxes = [];
	for (const col of [columns.first().locator('> *').first(), columns.nth(1).locator('.sawerna-image-text')]) boxes.push(await col.boundingBox());
	return boxes;
};

test('without fill: both columns start at the same top', async ({ page, context, request }) => {
	const [left, right] = await openMenuColumns(page, context, request, false);
	expect(Math.abs(left.y - right.y)).toBeLessThanOrEqual(2);
	expect(right.height).toBeLessThan(left.height); // 4:5 image is shorter than the grid: proves it is not stretched.
});

test('with fill: both columns share top and height', async ({ page, context, request }) => {
	const [left, right] = await openMenuColumns(page, context, request, true);
	expect(Math.abs(left.y - right.y)).toBeLessThanOrEqual(2);
	expect(Math.abs(left.height - right.height)).toBeLessThanOrEqual(2);
});
