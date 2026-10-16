// Performance check for the request list (NFR-004, OD-07).
//
// NFR-004: at most 1 second per 100 returned entries. The list returns 50 rows per page,
// so the target for one page is 500 ms. Internet latency is excluded by the NFR, so this
// runs against a local or staging instance.
//
// What it does: signs in once as a management user (who sees every request, the most rows),
// warms up, then requests the list in four realistic shapes, each REPEAT times, with CONCURRENCY
// requests in flight at once. It records response time and status for every request and writes
// a JSON result file to perf-results/.
//
// Run (both servers running, database seeded):
//   pnpm perf
// Options (environment variables):
//   PERF_BASE_URL     default http://127.0.0.1:8000
//   PERF_EMAIL        default grace.naidoo@example.com (seeded management account)
//   PERF_PASSWORD     default the seeded test password
//   PERF_REPEAT       requests per scenario, default 40
//   PERF_CONCURRENCY  requests in flight at once, default 5

import { mkdirSync, writeFileSync } from 'node:fs';
import { performance } from 'node:perf_hooks';
import { fileURLToPath } from 'node:url';

const BASE = process.env.PERF_BASE_URL ?? 'http://127.0.0.1:8000';
const EMAIL = process.env.PERF_EMAIL ?? 'grace.naidoo@example.com';
const PASSWORD = process.env.PERF_PASSWORD ?? process.env.SEED_PASSWORD ?? 'password';
const REPEAT = Number(process.env.PERF_REPEAT ?? 40);
const CONCURRENCY = Number(process.env.PERF_CONCURRENCY ?? 5);
const PAGE_SIZE = 50;
const TARGET_MS = (1000 / 100) * PAGE_SIZE; // NFR-004 scaled to one page of 50 rows

const SCENARIOS = [
    { name: 'first page, no filters', path: '/requests' },
    { name: 'text search', path: '/requests?q=water' },
    { name: 'status + category filter', path: '/requests?status=open&category_id=1' },
    { name: 'deep page (page 5)', path: '/requests?page=5' },
];

const cookies = new Map();

function storeCookies(response) {
    for (const line of response.headers.getSetCookie()) {
        const [pair] = line.split(';');
        const [name, ...rest] = pair.split('=');
        cookies.set(name.trim(), rest.join('='));
    }
}

const cookieHeader = () => [...cookies].map(([k, v]) => `${k}=${v}`).join('; ');

async function signIn() {
    storeCookies(await fetch(`${BASE}/login`, { redirect: 'manual' }));
    const xsrf = decodeURIComponent(cookies.get('XSRF-TOKEN') ?? '');

    const response = await fetch(`${BASE}/login`, {
        method: 'POST',
        redirect: 'manual',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': xsrf, Cookie: cookieHeader() },
        body: JSON.stringify({ email: EMAIL, password: PASSWORD }),
    });
    storeCookies(response);

    if (response.status >= 400) {
        throw new Error(`Sign-in failed with HTTP ${response.status}. Check the account, password and that the database is seeded.`);
    }
}

async function timedGet(path) {
    const start = performance.now();
    try {
        const response = await fetch(`${BASE}${path}`, { headers: { Cookie: cookieHeader(), Accept: 'text/html' }, redirect: 'manual' });
        const body = await response.text();
        const ms = performance.now() - start;
        // A redirect to /login means the session was lost, which counts as a failure
        const ok = response.status === 200 && body.includes('data-page');
        return { ms, status: response.status, ok };
    } catch (error) {
        return { ms: performance.now() - start, status: 0, ok: false, error: String(error) };
    }
}

async function runScenario({ name, path }) {
    const results = [];
    let next = 0;

    async function worker() {
        while (next < REPEAT) {
            next++;
            results.push(await timedGet(path));
        }
    }

    const wallStart = performance.now();
    await Promise.all(Array.from({ length: CONCURRENCY }, worker));
    const wallMs = performance.now() - wallStart;

    const times = results.map((r) => r.ms).sort((a, b) => a - b);
    const pct = (p) => times[Math.min(times.length - 1, Math.ceil((p / 100) * times.length) - 1)];
    const failures = results.filter((r) => !r.ok);

    return {
        scenario: name,
        path,
        requests: results.length,
        failures: failures.length,
        failure_statuses: [...new Set(failures.map((f) => f.status))],
        p50_ms: Math.round(pct(50)),
        p95_ms: Math.round(pct(95)),
        max_ms: Math.round(times[times.length - 1]),
        throughput_per_s: Number(((results.length / wallMs) * 1000).toFixed(1)),
        meets_target: failures.length === 0 && pct(95) <= TARGET_MS,
    };
}

await signIn();
for (const s of SCENARIOS) await timedGet(s.path); // warm-up: route, view and query caches

const summary = [];
for (const scenario of SCENARIOS) {
    summary.push(await runScenario(scenario));
}

const run = {
    run_at: new Date().toISOString(),
    base_url: BASE,
    account: EMAIL,
    repeat_per_scenario: REPEAT,
    concurrency: CONCURRENCY,
    target: `p95 <= ${TARGET_MS} ms per page of ${PAGE_SIZE} rows (NFR-004: 1 s per 100 entries)`,
    results: summary,
};

console.table(
    summary.map((r) => ({
        scenario: r.scenario,
        requests: r.requests,
        failures: r.failures,
        p50_ms: r.p50_ms,
        p95_ms: r.p95_ms,
        max_ms: r.max_ms,
        per_s: r.throughput_per_s,
        meets_target: r.meets_target,
    })),
);
console.log(`Target: ${run.target}`);

const dir = new URL('../../perf-results/', import.meta.url);
mkdirSync(dir, { recursive: true });
const file = new URL(`request-list-${run.run_at.replace(/[:.]/g, '-')}.json`, dir);
writeFileSync(file, JSON.stringify(run, null, 2));
console.log(`Saved ${fileURLToPath(file)}`);

process.exitCode = summary.every((r) => r.meets_target) ? 0 : 1;
