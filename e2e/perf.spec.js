import { test } from '@playwright/test';
import { USERS, login } from './helpers.js';

// 14.8 performance check (NFR-004): request list response time with filters.
// Target: 50 rows per page, NFR-004 allows 1 s per 100 rows, so at most 500 ms per page.
const RUNS = 20;
const SCENARIOS = [
    { name: 'No filters (page 1)', url: '/requests' },
    { name: 'Status + category filter', url: '/requests?status=open&category_id=2' },
    { name: 'Text search', url: '/requests?q=room' },
];

test('request list response time (NFR-004)', async ({ page }) => {
    test.setTimeout(180_000);
    await login(page, USERS.management);

    for (const s of SCENARIOS) {
        await page.request.get(s.url); // warm-up, not counted
        const times = [];
        let errors = 0;
        for (let i = 0; i < RUNS; i++) {
            const start = Date.now();
            const res = await page.request.get(s.url);
            times.push(Date.now() - start);
            if (res.status() !== 200) errors++;
        }
        times.sort((a, b) => a - b);
        const median = times[Math.floor(RUNS / 2)];
        const p95 = times[Math.ceil(RUNS * 0.95) - 1];
        console.log(`${s.name}: median ${median} ms, 95th ${p95} ms, max ${times[RUNS - 1]} ms, errors ${errors}/${RUNS}`);
    }
});