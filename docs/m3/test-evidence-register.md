# Test evidence register

Fields follow M3 section 11. "Status" is Pass / Fail / Blocked / **Not yet executed**.
Do not change a status until you have a real run to point to in the Evidence column.

Run summary so far

| Suite | Tool | Cases | Status | Evidence |
|---|---|---|---|---|
| Frontend component/unit | Vitest | 27 | Pass (executed 2026-10-07, build workspace) | `pnpm test` output; add CI run link |
| Database constraints | psql replay | 10 checks | Pass (executed 2026-10-07) | see README section 3 |
| PHP unit + API/integration | PHPUnit | 42 | Not yet executed | CI run / `phpunit-junit.xml` |
| End-to-end | Playwright | 3 | Not yet executed | `playwright-report/` |
| Load | Node script | 1 | Not yet executed | `perf-results/load-test.json` |

## Unit / component

| ID | Test basis | Why selected | Technique | Input / precondition | Expected result | Traceability |
|---|---|---|---|---|---|---|
| TC-U-01 | FR-007 overdue rule (3 days) | Wrong overdue flag misleads management | Boundary value | Open request 2d23h59m old vs 3d1m old | false, then true | FR-007 |
| TC-U-02 | FR-007 | Finished work must never be overdue | Equivalence partition | Resolved and closed requests 9 months old | Not overdue; in_progress is | FR-007 |
| TC-U-03 | FR-007, config | Limit must be configuration | Configuration | `overdue_days=10` | 5 days old is not overdue, 11 is | FR-007 |
| TC-U-04 | FR-001 | Valid form must pass client validation | Valid partition | Complete valid form | No errors | FR-001 |
| TC-U-05 | FR-003 | Clean filter URLs | Equivalence | Mixed empty/filled filters | Only filled values kept | FR-003 |
| TC-U-06 | FR-005 | Resolve needs a note; labels | Decision | Each target status | Only `resolved` needs note | FR-005 |
| TC-U-07 | FR-002 | Status readable without colour | Component | Each of 5 statuses, overdue on/off | Label text, data-status, Overdue badge | FR-002 |
| TC-U-08 | FR-001 | Empty submit blocked (negative) | Negative | Empty form, click submit | 4 errors, `onSubmit` not called | FR-001 |
| TC-U-09 | FR-001 | Clean values sent | Component | Valid form with padded text | Trimmed values, numeric category | FR-001 |
| TC-U-10 | FR-001 | Counter helps stay inside limits | Component | Type 5 characters | "5 / 2000 characters" | FR-001 |
| TC-U-11 | FR-001 | Server-only messages shown | Component | `serverErrors` prop | Alert shown | FR-001 |
| TC-U-12 | FR-003 | Combinable filters | Component | Status + category + "assigned to me" | `onApply` with exactly those | FR-003 |

## Black-box functional

| ID | Basis | Technique | Input | Expected | Traceability |
|---|---|---|---|---|---|
| TC-BB-01 | FR-001 description 10..2000 | BVA (server) | Lengths 9, 10, 2000, 2001 | Reject, accept, accept, reject | FR-001 |
| TC-BB-02 | FR-001 location max 255 | BVA (server) | Lengths 0, 1, 255, 256 | Reject, accept, accept, reject | FR-001 |
| TC-BB-03 | FR-001 category | Equivalence (server) | Null, non-existent, inactive | All rejected, nothing saved | FR-001 |
| TC-BB-04 | FR-005 workflow | Decision table | closed to assigned, closed to in_progress, open to resolved, open to in_progress, assigned to resolved, resolved to assigned | Each rejected, status unchanged | FR-005 |
| TC-BB-05 | FR-005 resolution note | Equivalence | No note, blank note, real note | Reject, reject, accept | FR-005 |
| TC-BB-06 | FR-003 single filter | Equivalence | status=open/assigned/closed | Only matching rows | FR-003 |
| TC-BB-07 | FR-003 combined filters | Decision table | category, status, both | Intersection | FR-003 |
| TC-BB-08 | FR-003 date range | BVA | Rows at 23:59:59 before start, start, end, next day | Inclusive both ends | FR-003 |
| TC-BB-09 | FR-001 (client) | BVA (client) | Same description lengths | Mirrors server | FR-001 |
| TC-BB-10 | FR-001 (client) | Equivalence (client) | Empty / 255 / 256 for location and area | Reject / accept / reject | FR-001 |

## API / integration (HTTP + real PostgreSQL)

| ID | Basis | Boundary verified | Expected | Traceability |
|---|---|---|---|---|
| TC-API-01 | FR-001 AC | Controller, service, DB | Row created: status open, version 0, ref 1001, history null to open | FR-001 |
| TC-API-02 | FR-008 | Auth middleware | Guest redirected to /login, nothing saved (negative) | FR-008 |
| TC-API-03 | FR-002 AC | Query scoping | Requestor sees only own requests | FR-002 |
| TC-API-04 | FR-002, FR-008 | Authorisation | Other requestor gets 403; owner and staff get 200 (negative) | FR-002, FR-008 |
| TC-API-05 | FR-008 | Role middleware | Dashboard 403 for requestor and staff, 200 for management (negative) | FR-008 |
| TC-API-06 | FR-008 | Role + service | Requestor cannot take or change status (negative) | FR-008 |
| TC-API-07 | FR-008 security | Registration | Posted `role=management` is ignored | FR-008 |
| TC-API-08 | FR-004 AC | Assignment | Request assigned, history and assignment row written | FR-004 |
| TC-API-09 | FR-004 | Concurrency guard | Second staff cannot take an assigned request (negative) | FR-004 |
| TC-API-10 | FR-005 | State transition | open to closed, 5 history rows in order | FR-005 |
| TC-API-11 | FR-005 | Optimistic locking | Stale version rejected, status unchanged | FR-005 |
| TC-API-12 | FR-005, FR-008 | Authorisation | Non-assignee staff 403; management allowed | FR-005 |
| TC-API-13 | FR-004 | Offer / accept / decline | Decline keeps open; accept assigns | FR-004 |
| TC-API-14 | FR-003 | Search | By text and by CC-reference | FR-003 |
| TC-API-15 | FR-007 AC | Aggregation | Counts match data | FR-007 |
| TC-API-16 | FR-007 | Overdue + filter | One overdue; category filter narrows | FR-007 |

## End-to-end

| ID | Journey | Expected | Traceability |
|---|---|---|---|
| TC-E2E-01 | Requestor submits; staff searches, takes, starts, resolves; requestor sees result and history | All screens work together; final state Resolved with note | FR-001..FR-005 |
| TC-E2E-02 | Guest and requestor try protected pages (negative) | Redirect to login; dashboard link hidden; /dashboard 403 | FR-008 |
| TC-E2E-03 | Management dashboard with category filter | Totals shown; filtered total <= total | FR-007 |

## Performance

| ID | Operation | Why it matters | Workload | Target |
|---|---|---|---|---|
| TC-PERF-01 | Staff list with filters (`/requests?status=open&category_id=1`) | Most-used read; staff work from it all day | 10 virtual users, 30 s, about 5,000 requests | p95 <= 2000 ms (confirm against the PED NFR), 0 errors |

Record the environment (laptop specs, PostgreSQL version, app mode) next to the result. What this does NOT prove:
production municipal scale, network latency, concurrent writes, or behaviour with other pages.
