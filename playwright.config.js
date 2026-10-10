import { defineConfig, devices } from '@playwright/test';

// Runs against a live CivicConnect instance (local `php artisan serve`, CI, or staging).
// No automatic retries on purpose: a test that only passes on a re-run is a finding, not a pass.
export default defineConfig({
    testDir: './e2e',
    timeout: 30_000,
    retries: 0,
    workers: 1,
    reporter: [['list'], ['html', { open: 'never' }], ['junit', { outputFile: 'test-results/e2e-junit.xml' }]],
    use: {
        baseURL: process.env.E2E_BASE_URL ?? 'http://127.0.0.1:8000',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
