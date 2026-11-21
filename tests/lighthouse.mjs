// Lighthouse performance runner. Usage: node lighthouse.mjs [label]
// Writes reports/<label>/summary.json. Runs each URL RUNS times and reports the median score.
import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import fs from 'node:fs';

const BASE = process.env.BASE_URL || 'https://sawernau.local';
const RUNS = Number(process.env.RUNS || 3);
const label = process.argv[2] || 'latest';
const urls = { home: '/', shop: '/shop/', category: '/product-category/diivanid/', product: '/product/diivanvoodi-tibu/', contact: '/kontakt/' };
const forms = ['mobile', 'desktop'];
const median = (a) => { const s = [...a].sort((x, y) => x - y); return s[Math.floor(s.length / 2)]; };

const chrome = await chromeLauncher.launch({ chromeFlags: ['--headless=new', '--ignore-certificate-errors'] });
const out = {};
for (const [name, path] of Object.entries(urls)) {
	for (const form of forms) {
		const runs = [];
		for (let i = 0; i < RUNS; i++) {
			const cfg = form === 'desktop'
				? { extends: 'lighthouse:default', settings: { formFactor: 'desktop', screenEmulation: { mobile: false, width: 1350, height: 940, deviceScaleFactor: 1, disabled: false }, throttling: { rttMs: 40, throughputKbps: 10240, cpuSlowdownMultiplier: 1 } } }
				: undefined;
			const r = await lighthouse(BASE + path, { port: chrome.port, onlyCategories: ['performance'], output: 'json', logLevel: 'error' }, cfg);
			const a = r.lhr.audits;
			runs.push({
				score: Math.round(r.lhr.categories.performance.score * 100 || 0),
				FCP: Math.round(a['first-contentful-paint']?.numericValue ?? -1),
				LCP: Math.round(a['largest-contentful-paint']?.numericValue ?? -1),
				TBT: Math.round(a['total-blocking-time']?.numericValue ?? -1),
				CLS: +a['cumulative-layout-shift'].numericValue?.toFixed(3) ?? -1,
				SI: Math.round(a['speed-index']?.numericValue ?? -1),
				TTFB: Math.round(a['server-response-time']?.numericValue ?? -1),
				bytes: Math.round(a['total-byte-weight'].numericValue / 1024),
				requests: a['network-requests'].details?.items?.length,
			});
			if (i === RUNS - 1) {
				fs.mkdirSync(`reports/${label}`, { recursive: true });
				fs.writeFileSync(`reports/${label}/${name}-${form}.json`, r.report);
			}
		}
		const m = {}; for (const k of Object.keys(runs[0])) m[k] = median(runs.map((x) => x[k]));
		out[`${name}-${form}`] = m;
		console.log(`${name}-${form}`.padEnd(18), JSON.stringify(m));
	}
}
await chrome.kill();
fs.writeFileSync(`reports/${label}/summary.json`, JSON.stringify(out, null, 2));
