// Gutenberg blocks: server-side rendering and attribute sanitizing (front-end context).
// Uses the fixture endpoint ?sawerna_test=blocks (installed by global-setup.js).
const { test, expect } = require('@playwright/test');

test.skip(({ isMobile }) => isMobile, 'server-side rendering is viewport independent');

const render = async (request, markup) => (await request.get('/?sawerna_test=blocks&markup=' + encodeURIComponent(markup))).text();
const count = (html, cls) => (html.match(new RegExp(`class="${cls}"`, 'g')) || []).length;

test('products block renders each layout with the right item count', async ({ request }) => {
	for (const [layout, expected] of [['compact', 3], ['grid', 4], ['list', 3], ['featured', 1]]) {
		const html = await render(request, `<!-- wp:sawerna/products {"source":"newest","layout":"${layout}","limit":3} /-->`);
		expect(html, layout).toContain('wp-block-sawerna-products');
		expect(html, layout).toContain(`sawerna-menu-products--${layout}`);
		expect(count(html, 'sawerna-menu-product'), layout).toBe(expected);
		expect(html, layout).not.toContain('sawerna-wishlist-button');
	}
});

test('products block: category source (stored as term IDs) matches the category', async ({ request }) => {
	const [term] = await (await request.get('/wp-json/wp/v2/product_cat?slug=diivanid')).json();
	const html = await render(request, `<!-- wp:sawerna/products {"source":"category","categories":[${term.id}],"layout":"list","limit":4} /-->`);
	const viaShortcode = await (await request.get('/?sawerna_test=shortcode&layout=list&limit=4&category=diivanid')).text();
	const only = (h) => (h.includes('id="sawerna-test-output"') ? h.split('id="sawerna-test-output"')[1] : h);
	const links = (h) => [...only(h).matchAll(/class="sawerna-menu-product" href="([^"]+)"/g)].map((m) => m[1]);
	expect(links(html).length).toBeGreaterThan(0);
	expect(links(html)).toEqual(links(viaShortcode));
});

test('blog posts block renders, without dates', async ({ request }) => {
	await request.get('/?sawerna_test=flush');
	const html = await render(request, '<!-- wp:sawerna/blog-posts {"limit":2} /-->');
	expect(html).toContain('sawerna-blog-post-card');
	expect(html).not.toContain('sawerna-blog-post-card__date');
});

test('invalid attributes are sanitized, not trusted', async ({ request }) => {
	const html = await render(request, '<!-- wp:sawerna/products {"layout":"<script>x</script>","limit":99999,"columns":-5,"orderby":"DROP TABLE"} /-->');
	expect(html).not.toContain('<script>x</script>');
	expect(count(html, 'sawerna-menu-product') + count(html, 'sawerna-product-card')).toBeLessThanOrEqual(24);
});

test('banner block escapes text and blocks script URLs', async ({ request }) => {
	const payload = JSON.stringify({ title: '"][sawerna_products]<img src=x onerror=alert(1)>', text: '<script>alert(1)</script>', buttonText: 'Go', url: 'javascript:alert(1)' });
	const html = await render(request, `<!-- wp:sawerna/banner ${payload} /-->`);
	expect(html).not.toMatch(/<img src=x onerror/i);
	expect(html).not.toContain('<script>alert(1)</script>');
	expect(html).not.toMatch(/href="javascript:/i);
	expect(html).not.toContain('sawerna-menu-product'); // Injected shortcode must not execute.
});

test('unknown image IDs in image grid do not error', async ({ request }) => {
	const html = await render(request, '<!-- wp:sawerna/image-grid {"items":[{"id":999999999,"link":"javascript:alert(1)","caption":"<b>x</b>"}]} /-->');
	expect(html).not.toMatch(/Fatal error|Warning<\/b>/);
	expect(html).not.toMatch(/href="javascript:/i);
});

// Regression tests for the security review fixes.
test('html block shortcode refuses non-HTML-block posts', async ({ request }) => {
	const [page] = await (await request.get('/wp-json/wp/v2/pages?slug=muugitingimused')).json();
	const html = await render(request, `<!-- wp:shortcode -->[sawerna_html_block id="${page.id}"]<!-- /wp:shortcode -->`);
	expect(html.replace(/<\/?p>/g, '').trim()).toBe('');
});

test('price sorting really sorts (WooCommerce sort names are mapped)', async ({ request }) => {
	const prices = async (order) => {
		const page = await (await request.get(`/?sawerna_test=shortcode&layout=list&limit=6&orderby=price&order=${order}`)).text();
		const html = page.split('id="sawerna-test-output"')[1] || ''; // Ignore products in the site's mega menu.
		return [...html.matchAll(/sawerna-menu-product__price">([\s\S]*?)<\/span>\s*<\/span>|sawerna-menu-product__price">([\s\S]*?)<\/span>/g)]
			.map((m) => (m[1] || m[2]).replace(/<del[\s\S]*?<\/del>/g, '').replace(/<[^>]+>/g, ''))
			.map((t) => parseFloat(t.replace(/&nbsp;|\s/g, '').replace(/[^0-9,]/g, '').replace(',', '.')));
	};
	const asc = await prices('ASC');
	const desc = await prices('DESC');
	expect(asc.length).toBeGreaterThan(1);
	expect(asc[0]).toBeLessThanOrEqual(asc[asc.length - 1]);
	expect(desc[0]).toBeGreaterThanOrEqual(desc[desc.length - 1]);
	expect(asc[0]).toBeLessThan(desc[0]);
});

test('banner image URL cannot break out of the CSS url()', async ({ request }) => {
	const html = await render(request, `<!-- wp:shortcode -->[sawerna_banner title="T" image="https://example.com/a.jpg');background:url(https://evil.example/x.png"]<!-- /wp:shortcode -->`);
	expect(html).not.toContain("a.jpg');");
	expect(html).toMatch(/min-height:\s*\d+px/);
});

test('banner height is capped', async ({ request }) => {
	const html = await render(request, '<!-- wp:shortcode -->[sawerna_banner title="T" image="https://example.com/a.jpg" height="999999"]<!-- /wp:shortcode -->');
	expect(html).toContain('min-height: 1200px');
});

// New components: single blog post, banner CTA options, image with text.
const firstMediaId = async (request) => (await (await request.get('/wp-json/wp/v2/media?per_page=1&media_type=image')).json())[0].id;

test('single blog post renders one post with the contract markup', async ({ request }) => {
	const html = await render(request, '<!-- wp:sawerna/blog-posts {"layout":"single","showExcerpt":true} /-->');
	expect(count(html, 'sawerna-post-feature')).toBe(1);
	for (const part of ['__link', '__media', '__body', '__title', '__excerpt', '__more']) expect(html).toContain(`sawerna-post-feature${part}`);
	expect(html).not.toContain('sawerna-post-feature__meta'); // No publish date in menu blocks.
});

test('single blog post never shows unpublished posts', async ({ request }) => {
	const html = await render(request, '<!-- wp:sawerna/blog-posts {"layout":"single","source":"id","postId":999999999} /-->');
	// A non-existent ID must not fall back to some other, possibly private, post: either nothing or the newest public post.
	expect(html).not.toMatch(/Fatal error|Warning<\/b>/);
	expect(count(html, 'sawerna-post-feature')).toBeLessThanOrEqual(1);
});

test('banner CTA style, vertical position and overlay clamp', async ({ request }) => {
	const id = await firstMediaId(request);
	const html = await render(request, `<!-- wp:sawerna/banner {"imageId":${id},"title":"T","buttonText":"Osta","buttonStyle":"outline","overlay":500,"valign":"bottom"} /-->`);
	expect(html).toContain('sawerna-banner-shortcode__button--outline');
	expect(html).toContain('sawerna-banner-shortcode--v-bottom');
	const overlay = parseFloat((html.match(/--sawerna-overlay:\s*([0-9.]+)/) || [])[1]);
	expect(overlay).toBeLessThanOrEqual(0.8);
	const bad = await render(request, `<!-- wp:sawerna/banner {"imageId":${id},"title":"T","buttonText":"Osta","buttonStyle":"<x>","valign":"evil"} /-->`);
	expect(bad).not.toContain('<x>');
	expect(bad).not.toContain('--v-evil');
});

test('image with text: markup, ratio, overlay, link safety', async ({ request }) => {
	const id = await firstMediaId(request);
	const html = await render(request, `<!-- wp:sawerna/image-text {"imageId":${id},"title":"Pealkiri","text":"Tekst","url":"/kontakt/","position":"center","overlay":40,"ratio":"16/9"} /-->`);
	expect(html).toContain('sawerna-image-text--v-center');
	expect(html).toContain('sawerna-image-text--h-center');
	expect(html).toMatch(/--sawerna-image-text-ratio:\s*16\s*\/\s*9/);
	expect(html).toMatch(/--sawerna-overlay:\s*0?\.4/);
	expect(html).toMatch(/<img[^>]+srcset=/);
	expect(html).toMatch(/<img[^>]+alt=/);
	const bad = await render(request, `<!-- wp:sawerna/image-text {"imageId":${id},"title":"<script>x</script>","url":"javascript:alert(1)","position":"evil","ratio":"1/0;}"} /-->`);
	expect(bad).not.toContain('<script>x</script>');
	expect(bad).not.toMatch(/href="javascript:/i);
	expect(bad).not.toContain('evil');
	expect(bad).not.toContain('1/0;}');
});

test('image with text refuses raw image URLs', async ({ request }) => {
	const html = await render(request, '<!-- wp:shortcode -->[sawerna_image_text image="https://evil.example/x.jpg" title="T"]<!-- /wp:shortcode -->');
	expect(html).not.toContain('evil.example');
});

test('image with text: every vertical x horizontal text position', async ({ request }) => {
	const id = await firstMediaId(request);
	for (const valign of ['top', 'center', 'bottom']) {
		for (const align of ['left', 'center', 'right']) {
			const html = await render(request, `<!-- wp:sawerna/image-text {"imageId":${id},"title":"T","valign":"${valign}","align":"${align}"} /-->`);
			expect(html, `${valign}/${align}`).toContain(`sawerna-image-text--v-${valign} sawerna-image-text--h-${align}`);
		}
	}
});

test('image with text: blocks saved with the old single position keep their placement', async ({ request }) => {
	const id = await firstMediaId(request);
	const expected = { 'bottom-left': 'v-bottom sawerna-image-text--h-left', 'bottom-center': 'v-bottom sawerna-image-text--h-center', center: 'v-center sawerna-image-text--h-center', 'top-left': 'v-top sawerna-image-text--h-left' };
	for (const [position, cls] of Object.entries(expected)) {
		const html = await render(request, `<!-- wp:sawerna/image-text {"imageId":${id},"title":"T","position":"${position}"} /-->`);
		expect(html, position).toContain(`sawerna-image-text--${cls}`);
	}
});

test('starter patterns: all 13 offered for new HTML blocks and each renders', async ({ request }) => {
	const patterns = await (await request.get('/?sawerna_test=patterns')).json();
	expect(patterns.length).toBe(13);
	for (const p of patterns) {
		expect(p.postTypes, p.name).toContain('sawerna_html_block');
		expect(p.blockTypes, p.name).toContain('core/post-content'); // Shown in the "Choose a pattern" modal.
		expect(p.html, p.name).not.toMatch(/Fatal error|Warning<\/b>|<!-- wp:sawerna/);
	}
	const byName = Object.fromEntries(patterns.map((p) => [p.name, p.html]));
	expect(byName['sawerna/menu-products-2x2']).toContain('sawerna-menu-products--grid');
	expect(byName['sawerna/menu-blog-single']).toContain('sawerna-post-feature');
	expect(byName['sawerna/menu-products-2x2-image-text']).toContain('sawerna-menu-products--grid');
});

// Hand-picked products: every picked product shows, in the picked order (block and Elementor widget).
const productIds = async (request, n) => (await (await request.get(`/wp-json/wp/v2/product?per_page=${n}&orderby=date&order=asc&_fields=id,link`)).json());
const linksIn = (html) => [...html.matchAll(/class="sawerna-menu-product" href="([^"]+)"/g)].map((m) => m[1]);

test('products block: hand-picked products in picked order, all shown', async ({ request }) => {
	const picked = (await productIds(request, 5)).reverse(); // Oldest products, reversed: proves order and "not only recent".
	const html = await render(request, `<!-- wp:sawerna/products {"source":"ids","ids":[${picked.map((p) => p.id).join(',')}],"layout":"list"} /-->`);
	expect(linksIn(html)).toEqual(picked.map((p) => p.link));
});

test('Elementor products widget: hand-picked products in picked order, beyond the newest 200', async ({ request }) => {
	const picked = (await productIds(request, 4)).reverse();
	const settings = { source: 'ids', ids: picked.map((p) => String(p.id)), layout: 'list', limit: 3 };
	const html = await (await request.get(`/?sawerna_test=widget&type=sawerna_products&settings=${encodeURIComponent(JSON.stringify(settings))}`)).text();
	expect(linksIn(html)).toEqual(picked.map((p) => p.link)); // 4 shown although the limit setting says 3.
});
