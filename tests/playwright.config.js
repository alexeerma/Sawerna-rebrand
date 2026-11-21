// Sawerna theme tests. Run against a local WP install: BASE_URL=https://sawernau.local npm test
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
	testDir: './specs',
	globalSetup: require.resolve('./global-setup'),
	globalTeardown: require.resolve('./global-teardown'),
	timeout: 90_000,
	workers: 3,
	retries: 0,
	reporter: [['list']],
	snapshotPathTemplate: '{testDir}/__screenshots__/{projectName}/{arg}{ext}',
	expect: { toHaveScreenshot: { maxDiffPixelRatio: 0.01, animations: 'disabled', caret: 'hide' } },
	use: {
		baseURL: process.env.BASE_URL || 'https://sawernau.local',
		ignoreHTTPSErrors: true,
		channel: 'chrome',
	},
	projects: [
		{ name: 'desktop', use: { viewport: { width: 1440, height: 900 } } },
		{ name: 'mobile', use: { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 } },
	],
});
