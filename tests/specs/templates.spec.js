// "Loo mallist" templates: one list from the sawerna/menu-* patterns, each creatable in the block editor and Elementor.
// Uses the fixture endpoint ?sawerna_test=templates (installed by global-setup.js).
const { test, expect } = require('@playwright/test');

test.skip(({ isMobile }) => isMobile, 'server-side rendering is viewport independent');

// Components each template must contain (product entries are the layout class).
const EXPECTED = {
	'products-2x2': ['products:grid'],
	'products-compact': ['products:compact'],
	'products-list': ['products:list'],
	'featured-product': ['products:featured'],
	'banner-cta': ['banner'],
	'image-text': ['image-text'],
	'blog-single': ['blog-single'],
	'products-grid-banner': ['products:grid', 'banner'],
	'blog-product-list': ['blog-grid', 'products:list'],
	'featured-links': ['links', 'products:featured'],
	'products-2x2-image-text': ['products:grid', 'image-text'],
	'blog-single-products': ['blog-single', 'products:list'],
	'image-text-trio': ['image-text'],
};
const MARKER = {
	banner: 'sawerna-banner-shortcode',
	'image-text': 'sawerna-image-text',
	'blog-single': 'sawerna-post-feature',
	'blog-grid': 'sawerna-blog-post-card-grid',
	links: 'sawerna-menu-links',
};
const marker = (c) => (c.startsWith('products:') ? `sawerna-menu-products--${c.split(':')[1]}` : MARKER[c]);

let data;
test.beforeAll(async ({ request }) => {
	await request.get('/?sawerna_test=flush');
	const [media] = await (await request.get('/wp-json/wp/v2/media?per_page=1&media_type=image')).json();
	data = await (await request.get(`/?sawerna_test=templates&image=${media.id}`)).json();
});

test('13 templates from the patterns, singles first, every one with both variants', async () => {
	expect(data.elementor_active).toBe(true);
	expect(data.templates.map((t) => t.slug)).toEqual(Object.keys(EXPECTED));
	for (const t of data.templates) {
		expect(t.title, t.slug).not.toBe('');
		expect(t.has_block, t.slug).toBe(true);
		expect(t.has_elementor, t.slug).toBe(true);
	}
	const firstCombo = data.templates.findIndex((t) => t.combination);
	expect(data.templates.slice(firstCombo).every((t) => t.combination)).toBe(true);
});

test('old Elementor template slugs still resolve', async () => {
	expect(data.aliases).toEqual({ 'products-banner': 'products-grid-banner', 'blog-products': 'blog-product-list' });
	expect(data.templates.map((t) => t.slug)).toContain('featured-links'); // Unchanged old slug.
});

test('both variants render every component, without errors or raw block comments', async () => {
	for (const t of data.templates) {
		for (const [variant, html] of [['block', t.block_html], ['elementor', t.elementor_html]]) {
			const label = `${t.slug} (${variant})`;
			expect(html.trim().length, label).toBeGreaterThan(0);
			expect(html, label).not.toMatch(/Fatal error|Warning<\/b>|Notice<\/b>|Deprecated<\/b>/);
			expect(html, label).not.toMatch(/<!-- \/?wp:/);
			for (const c of EXPECTED[t.slug]) expect(html, `${label}: ${c}`).toContain(marker(c));
		}
	}
	const trio = data.templates.find((t) => t.slug === 'image-text-trio');
	for (const html of [trio.block_html, trio.elementor_html]) expect((html.match(/class="sawerna-image-text /g) || []).length).toBe(3);
});

test('Elementor variant uses Elementor containers and the Sawerna widgets', async () => {
	for (const t of data.templates) {
		expect(t.elementor_html, t.slug).toContain('e-con');
		expect(t.elementor_html, t.slug).toMatch(/elementor-widget-sawerna_/);
		if (t.combination) expect((t.elementor_html.match(/e-con-inner|e-child/g) || []).length, t.slug).toBeGreaterThan(0);
	}
});
