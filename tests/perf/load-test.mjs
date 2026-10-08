// TC-PERF-01 - Load test of the staff request list with filters (the busiest read in the system).
// Usage:  BASE_URL=http://127.0.0.1:8000 CONNECTIONS=10 DURATION=30 npm run perf
// Needs seeded data:  php artisan db:seed --class=LoadTestSeeder   (LOAD_ROWS=5000)
// No third-party load tool: plain Node fetch with N concurrent virtual users, so there is nothing extra to install or audit.
import { mkdirSync, writeFileSync } from 'node:fs';
import os from 'node:os';

const BASE = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const EMAIL = process.env.PERF_EMAIL ?? 'staff1@civicconnect.test';
const PASSWORD = process.env.PERF_PASSWORD ?? process.env.SEED_PASSWORD ?? 'password';
const CONNECTIONS = Number(process.env.CONNECTIONS ?? 10);
const DURATION = Number(process.env.DURATION ?? 30);
// Target for p95 response time in ms. Align this with the NFR in the PED; if the PED has no target, say so.
const TARGET_P95_MS = Number(process.env.TARGET_P95_MS ?? 2000);
const PATH = process.env.PERF_PATH ?? '/requests?status=open&category_id=1';

function storeCookies(res, jar) {
    for (const line of res.headers.getSetCookie()) {
        const [pair] = line.split(';');
        const [name, ...rest] = pair.split('=');
        jar[name.trim()] = rest.join('=');
    }
}
const cookieHeader = (jar) =>
    Object.entries(jar)
        .map(([k, v]) => `${k}=${v}`)
        .join('; ');

async function signIn() {
    const jar = {};
    storeCookies(await fetch(`${BASE}/login`), jar);
    const res = await fetch(`${BASE}/login`, {
        method: 'POST',
        redirect: 'manual',
        headers: {
            'content-type': 'application/json',
            accept: 'text/html',
            cookie: cookieHeader(jar),
            'x-xsrf-token': decodeURIComponent(jar['XSRF-TOKEN'] ?? ''),
        },
        body: JSON.stringify({ email: EMAIL, password: PASSWORD }),
    });
    storeCookies(res, jar);
    if (res.status !== 302 && res.status !== 303) throw new Error(`Login failed (HTTP ${res.status}). Is the app running and seeded?`);
    return jar;
}

const percentile = (sorted, p) => (sorted.length ? sorted[Math.min(sorted.length - 1, Math.ceil((p / 100) * sorted.length) - 1)] : NaN);

const jar = await signIn();
const headers = { cookie: cookieHeader(jar), accept: 'text/html' };

// Dataset size, so the result can be interpreted
const html = await (await fetch(`${BASE}/requests`, { headers })).text();
const raw = html.match(/data-page="([^"]+)"/)?.[1] ?? '{}';
const totalRows = JSON.parse(raw.replaceAll('&quot;', '"').replaceAll('&amp;', '&').replaceAll('&#039;', "'"))?.props?.requests?.total ?? null;

// Warm-up (not measured): lets opcache / connection pools settle
for (let i = 0; i < 5; i++) await (await fetch(`${BASE}${PATH}`, { headers })).arrayBuffer();

console.log(`${CONNECTIONS} virtual users for ${DURATION}s against ${BASE}${PATH} (${totalRows} requests in the database)`);

const latencies = [];
let errors = 0;
let non2xx = 0;
const statusCounts = {};
const endAt = Date.now() + DURATION * 1000;

async function virtualUser() {
    while (Date.now() < endAt) {
        const t0 = performance.now();
        try {
            const res = await fetch(`${BASE}${PATH}`, { headers, redirect: 'manual' });
            await res.arrayBuffer();
            latencies.push(performance.now() - t0);
            statusCounts[res.status] = (statusCounts[res.status] ?? 0) + 1;
            if (res.status < 200 || res.status >= 300) non2xx++;
        } catch {
            errors++;
        }
    }
}

const startedAt = performance.now();
await Promise.all(Array.from({ length: CONNECTIONS }, virtualUser));
const elapsedSeconds = (performance.now() - startedAt) / 1000;

latencies.sort((a, b) => a - b);
const round = (n) => Math.round(n * 10) / 10;
const summary = {
    when: new Date().toISOString(),
    environment: { base: BASE, node: process.version, cpu: os.cpus()[0]?.model, cores: os.cpus().length, memGB: Math.round(os.totalmem() / 1e9) },
    workload: { path: PATH, virtualUsers: CONNECTIONS, durationSeconds: DURATION, totalRowsInDatabase: totalRows, user: EMAIL },
    results: {
        completedRequests: latencies.length,
        requestsPerSecond: round(latencies.length / elapsedSeconds),
        latencyMs: {
            avg: round(latencies.reduce((a, b) => a + b, 0) / (latencies.length || 1)),
            p50: round(percentile(latencies, 50)),
            p90: round(percentile(latencies, 90)),
            p95: round(percentile(latencies, 95)),
            p99: round(percentile(latencies, 99)),
            max: round(latencies.at(-1) ?? NaN),
        },
        networkErrors: errors,
        non2xx,
        statusCounts,
    },
    target: { p95Ms: TARGET_P95_MS },
};
summary.verdict = latencies.length > 0 && errors === 0 && non2xx === 0 && summary.results.latencyMs.p95 <= TARGET_P95_MS ? 'PASS' : 'FAIL';

mkdirSync('perf-results', { recursive: true });
writeFileSync('perf-results/load-test.json', JSON.stringify(summary, null, 2));
console.log(JSON.stringify(summary, null, 2));
process.exit(summary.verdict === 'PASS' ? 0 : 1);
