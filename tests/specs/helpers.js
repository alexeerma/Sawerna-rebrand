// Make pages deterministic before screenshots: load lazy images, stop motion.
async function settle(page) {
	await page.addStyleTag({ content: '*,*::before,*::after{transition:none!important;animation:none!important;scroll-behavior:auto!important} video{visibility:hidden!important}' });
	await page.evaluate(async () => {
		document.querySelectorAll('img[loading="lazy"]').forEach((i) => (i.loading = 'eager'));
		for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 40)); }
		window.scrollTo(0, 0);
		await Promise.all([...document.images].filter((i) => !i.complete).map((i) => new Promise((r) => { i.onload = i.onerror = r; setTimeout(r, 5000); })));
		if (document.fonts) {
			// Force-load the theme font weights; fonts.ready alone can resolve before a lazily used face loads.
			await Promise.all(['400', '500', '600', '700'].map((w) => document.fonts.load(`${w} 16px Inter`).catch(() => {})));
			await document.fonts.ready;
		}
	});
	await page.waitForTimeout(300);
}
// Collect JS errors and failed same-origin requests.
function watchErrors(page) {
	const errors = [];
	page.on('pageerror', (e) => errors.push('pageerror: ' + e.message));
	page.on('console', (m) => {
		if (m.type() !== 'error') return;
		// The 404 test page itself logs its own 404 status; that is expected.
		if (page.url().includes('this-page-does-not-exist') && m.text().includes('status of 404')) return;
		errors.push('console: ' + m.text());
	});
	page.on('response', (r) => { const u = r.url(); if (r.status() >= 400 && u.includes('sawernau.local') && !u.includes('this-page-does-not-exist') && !u.includes('favicon')) errors.push(r.status() + ' ' + u); });
	return errors;
}
module.exports = { settle, watchErrors };
