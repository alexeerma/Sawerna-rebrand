// Newsletter sign-up: providers (outgoing requests intercepted, nothing is sent), spam protection,
// rate limits, coupon, failure handling. Test data uses @sawerna-test.invalid and is removed afterwards.
const { test, expect } = require('@playwright/test');
const crypto = require('crypto');

test.skip(({ isMobile }) => isMobile, 'server behaviour is viewport independent');
test.describe.configure({ mode: 'serial', retries: 1 }); // One retry: timing-based checks can flake under heavy parallel load.

const FORM = 'form.rm-footer__newsletterForm[data-sawerna-newsletter]';
const STATUS = '#rm-footer-newsletter-status';
let n = 0;
const email = () => `t${Date.now()}-${n++}@sawerna-test.invalid`;

async function setup(context, settings = {}, httpStatus = 200) {
	const url = test.info().project.use.baseURL;
	await context.addCookies([
		{ name: 'sawerna_test_nl', value: encodeURIComponent(JSON.stringify({ provider: 'none', coupon_enabled: false, consent_mode: 'text', ...settings })), url },
		{ name: 'sawerna_mock_http', value: String(httpStatus), url },
	]);
}

async function subscribe(page, address, { wait = 2200, before } = {}) {
	await page.goto('/');
	await page.locator(FORM).scrollIntoViewIfNeeded();
	await page.waitForTimeout(wait); // The handler rejects submissions faster than 2 s after render.
	await page.locator(`${FORM} input[name="email"]`).fill(address);
	if (before) await before(page);
	const response = page.waitForResponse((r) => r.url().includes('admin-ajax.php') && r.request().method() === 'POST');
	await page.locator(`${FORM} button[type="submit"]`).click();
	return response;
}

const subscriber = async (request, address) => (await request.get(`/?sawerna_test=nl_subscriber&email=${encodeURIComponent(address)}`)).json();
const httpLog = async (request) => (await request.get('/?sawerna_test=http_log')).json();

test.beforeAll(async ({ request }) => { await request.get('/?sawerna_test=nl_cleanup'); });
test.afterAll(async ({ request }) => { await request.get('/?sawerna_test=nl_cleanup'); });
test.beforeEach(async ({ request }) => {
	await request.get('/?sawerna_test=http_log'); // Clear the log.
	await request.get('/?sawerna_test=nl_cleanup'); // Reset rate limits between tests.
});

test('no provider: stored locally, success shown, nothing sent, no raw IP stored', async ({ page, context, request }) => {
	await setup(context);
	const address = email();
	expect((await subscribe(page, address)).status()).toBe(200);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/);
	const s = await subscriber(request, address);
	expect(s.count).toBe(1);
	expect(s.raw_ip).toBe(false);
	expect(await httpLog(request)).toEqual([]);
});

test('MailerLite request shape', async ({ page, context, request }) => {
	await setup(context, { provider: 'mailerlite', list_id: '12345', api_key: 'ml-test-key' });
	const address = email();
	await subscribe(page, address);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/);
	const [req] = await httpLog(request);
	expect(req.url).toBe('https://connect.mailerlite.com/api/subscribers');
	expect(req.headers.Authorization || req.headers.authorization).toBe('Bearer ml-test-key');
	const body = JSON.parse(req.body);
	expect(body.email).toBe(address);
	expect(body.groups).toEqual(['12345']);
	expect((await subscriber(request, address)).status).toBe('synced');
});

test('Mailchimp request shape (datacenter from key, upsert by md5)', async ({ page, context, request }) => {
	await setup(context, { provider: 'mailchimp', list_id: 'abc123', api_key: 'deadbeef-us21' });
	const address = email();
	await subscribe(page, address);
	const [req] = await httpLog(request);
	const hash = crypto.createHash('md5').update(address.toLowerCase()).digest('hex');
	expect(req.url).toBe(`https://us21.api.mailchimp.com/3.0/lists/abc123/members/${hash}`);
	expect(req.method).toBe('PUT');
	expect(JSON.parse(req.body).email_address).toBe(address);
});

test('Mailchimp key with a malicious datacenter is rejected (no SSRF)', async ({ page, context, request }) => {
	await setup(context, { provider: 'mailchimp', list_id: 'abc123', api_key: 'deadbeef-evil.example.com/x' });
	const address = email();
	await subscribe(page, address);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/); // Stored locally regardless.
	const log = await httpLog(request);
	expect(log.every((r) => /^https:\/\/[a-z]+\d+\.api\.mailchimp\.com\//.test(r.url))).toBe(true);
	expect((await subscriber(request, address)).status).not.toBe('synced');
});

test('Brevo request shape', async ({ page, context, request }) => {
	await setup(context, { provider: 'brevo', list_id: '7', api_key: 'brevo-key' });
	const address = email();
	await subscribe(page, address);
	const [req] = await httpLog(request);
	expect(req.url).toBe('https://api.brevo.com/v3/contacts');
	expect(req.headers['api-key']).toBe('brevo-key');
	const body = JSON.parse(req.body);
	expect(body.email).toBe(address);
	expect(body.listIds).toEqual([7]);
});

test('webhook is signed with HMAC-SHA256', async ({ page, context, request }) => {
	await setup(context, { provider: 'webhook', webhook_url: 'https://example.com/hook', webhook_secret: 's3cret' });
	const address = email();
	await subscribe(page, address);
	const [req] = await httpLog(request);
	expect(req.url).toBe('https://example.com/hook');
	expect(JSON.parse(req.body).email).toBe(address);
	const signature = req.headers['X-Sawerna-Signature'] || req.headers['x-sawerna-signature'];
	expect(signature).toContain(crypto.createHmac('sha256', 's3cret').update(req.body).digest('hex'));
});

test('provider failure: user still sees success, record marked failed for retry', async ({ page, context, request }) => {
	await setup(context, { provider: 'mailerlite', list_id: '1', api_key: 'k' }, 500);
	const address = email();
	await subscribe(page, address);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/);
	expect((await subscriber(request, address)).status).toBe('failed');
});

test('coupon: single use, restricted to the email, expires, not issued twice', async ({ page, context, request }) => {
	await setup(context, { coupon_enabled: true, coupon_percent: 10, coupon_days: 30 });
	const address = email();
	await subscribe(page, address);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/);
	const s = await subscriber(request, address);
	expect(s.code).toMatch(/^UUDIS-/);
	expect(s.coupon.type).toBe('percent');
	expect(s.coupon.amount).toBe(10);
	expect(s.coupon.limit).toBe(1);
	expect(s.coupon.emails).toEqual([address]);
	const days = (s.coupon.expires - Date.now() / 1000) / 86400;
	expect(days).toBeGreaterThan(29);
	expect(days).toBeLessThan(31);

	// Same email again: success, still one record, same code.
	await subscribe(page, address);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/);
	const again = await subscriber(request, address);
	expect(again.count).toBe(1);
	expect(again.code).toBe(s.code);
});

test('honeypot filled: rejected, nothing stored', async ({ page, context, request }) => {
	await setup(context);
	const address = email();
	const res = await subscribe(page, address, { before: (p) => p.evaluate(() => { document.querySelector('#rm-footer-newsletter-website').value = 'http://spam.example'; }) });
	expect(res.status()).toBe(400);
	await expect(page.locator(STATUS)).toHaveClass(/is-error/);
	expect((await subscriber(request, address)).count).toBe(0);
});

test('submitted too fast after page load: rejected', async ({ page, context, request }) => {
	await setup(context);
	const address = email();
	const res = await subscribe(page, address, { wait: 0 });
	expect(res.status()).toBe(400);
	expect((await subscriber(request, address)).count).toBe(0);
});

test('bad nonce: 403 and a readable error', async ({ page, context, request }) => {
	await setup(context);
	const address = email();
	const res = await subscribe(page, address, { before: (p) => p.evaluate((f) => { document.querySelector(`${f} input[name="nonce"]`).value = 'bad'; }, FORM) });
	expect(res.status()).toBe(403);
	await expect(page.locator(STATUS)).toHaveClass(/is-error/);
	await expect(page.locator(STATUS)).toHaveText('Leht on aegunud. Palun laadi leht uuesti ja proovi uuesti.');
	await expect(page.locator(`${FORM} button[type="submit"]`)).toBeEnabled();
	expect((await subscriber(request, address)).count).toBe(0);
});

test('server validates email and checkbox consent (not only the browser)', async ({ page, context }) => {
	await setup(context, { consent_mode: 'checkbox' });
	await page.goto('/');
	await expect(page.locator(`${FORM} input[type="checkbox"][name="consent"]`)).toHaveAttribute('required', '');
	await page.waitForTimeout(2200);
	const fields = await page.locator(FORM).evaluate((f) => Object.fromEntries(new FormData(f)));
	const post = (extra) => page.request.post('/wp-admin/admin-ajax.php', { form: { ...fields, ...extra } });
	expect((await post({ email: 'not-an-email', consent: '1' })).status()).toBe(400);
	expect((await post({ email: email(), consent: '' })).status()).toBe(400);
	expect((await post({ email: email(), consent: '1' })).status()).toBe(200);
});

test('rate limit: 6th sign-up from the same visitor within an hour gets 429', async ({ page, context }) => {
	await setup(context);
	await page.goto('/');
	await page.waitForTimeout(2200);
	const fields = await page.locator(FORM).evaluate((f) => Object.fromEntries(new FormData(f)));
	const statuses = [];
	for (let i = 0; i < 6; i++) statuses.push((await page.request.post('/wp-admin/admin-ajax.php', { form: { ...fields, email: email() } })).status());
	expect(statuses.slice(0, 5)).toEqual([200, 200, 200, 200, 200]);
	expect(statuses[5]).toBe(429);
});

test('coupon dedupe: plus-addressed and plain variants of one mailbox get one coupon', async ({ page, context, request }) => {
	await setup(context, { coupon_enabled: true });
	const local = `t${Date.now()}-${n++}`;
	const tagged = `${local}+promo@sawerna-test.invalid`;
	const plain = `${local.toUpperCase()}@sawerna-test.invalid`;
	await subscribe(page, tagged);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/);
	const first = await subscriber(request, tagged);
	expect(first.code).toMatch(/^UUDIS-/);
	expect(first.normalized).toBe(`${local}@sawerna-test.invalid`);

	await subscribe(page, plain);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/); // Membership not revealed.
	expect((await subscriber(request, plain)).count).toBe(0);
	expect((await subscriber(request, tagged)).code).toBe(first.code);
});

test('double opt-in: coupon withheld at sign-up', async ({ page, context, request }) => {
	await setup(context, { coupon_enabled: true, double_optin: true });
	const address = email();
	await subscribe(page, address);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/);
	const s = await subscriber(request, address);
	expect(s.count).toBe(1);
	expect(s.code).toBe('');
	expect(s.skipped).toBe('double_optin');
});

test('daily coupon cap reached: sign-up succeeds, no coupon', async ({ page, context, request }) => {
	await setup(context, { coupon_enabled: true, coupon_daily_cap: 0 });
	const address = email();
	await subscribe(page, address);
	await expect(page.locator(STATUS)).toHaveClass(/is-success/);
	const s = await subscriber(request, address);
	expect(s.count).toBe(1);
	expect(s.code).toBe('');
	expect(s.skipped).toBe('cap');
});

test('stale cached page (old "started") is accepted', async ({ page, context, request }) => {
	await setup(context);
	const address = email();
	const old = String(Math.floor(Date.now() / 1000) - 3 * 86400);
	const res = await subscribe(page, address, { before: (p) => p.evaluate(([f, v]) => { document.querySelector(`${f} input[name="started"]`).value = v; }, [FORM, old]) });
	expect(res.status()).toBe(200);
	expect((await subscriber(request, address)).count).toBe(1);
});
