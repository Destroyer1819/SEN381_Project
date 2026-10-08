# Entries to add to the team registers

Add these to the existing registers (do not create new ones). Edit anything that does not match what the team decided.

## ADR / change records (1 to 3 is enough)

**ADR-M3-01: Schema is applied from the team's SQL, not rebuilt with Laravel's schema builder**
Original decision (M2): PostgreSQL schema designed by the database owner. Implementation: one migration loads
`database/sql/civicconnect_schema.sql` unchanged. Reason: keeps the CHECK constraints, composite foreign key on
status transitions and indexes exactly as designed, and avoids a second, drifting definition of the same schema.
Consequence: schema changes go into the SQL file via PR; Laravel's `Schema::` helpers are not used.

**ADR-M3-02: Status workflow is data, enforced by the database and the service**
The allowed moves live in `request_status_transition` and are enforced twice: `RequestWorkflow` rejects bad moves
with a message, and the composite foreign key on `request_status_history` makes the database refuse them too.
Consequence: changing the workflow means changing reference data, not code; the UI reads available moves from the server.

**ADR-M3-03: Optimistic locking with `service_request.version`**
Two staff acting on the same request must not silently overwrite each other. Every status change sends the version it
saw and updates `WHERE version = ?`; zero rows updated means "changed by someone else". Alternative rejected: pessimistic row
locks (more complex, hold locks across user think-time).

**Confirm against M2 before writing this up:** the PED notes show the team moved from an SPA + API to Inertia. If that
change already has an ADR (D-006/D-007), reference it here rather than writing a new one.

## Technical debt register

| Item | Why deferred | Consequence | Next action |
|---|---|---|---|
| FR-006 notifications not built (Should priority) | Time; status timeline covers the visibility need | Requestors must open the site to see changes | Email via queued mail notification |
| "Overdue" uses one global 3-day rule | Schema has no due-date or priority column | Urgent categories not treated differently | Add per-category target to `categories` |
| Date filters compare UTC dates | App and DB run in UTC | A request near midnight in SAST can fall on the neighbouring day | Convert filter dates to the user's timezone |
| No password reset or email verification | Out of M3 scope | Forgotten passwords need an admin | Add reset flow |
| Staff/management accounts created by seeder only | No admin UI | Onboarding needs a developer | Admin user screen |
| Load-test data has no history rows | Bulk insert for speed | Detail page timing not covered by the load test | Seed history for a sample |
| No request attachments or photos | Out of scope | Less detail for staff | Later milestone |

## Risk register additions (check IDs against the existing register)

| Risk | Likelihood / impact | Control now | Residual |
|---|---|---|---|
| Concurrent edits overwrite each other | Medium / Medium | Version check (TC-API-11) | Low |
| Role bypass by guessing URLs | Medium / High | Role middleware + per-request checks (TC-API-04..06, TC-E2E-02) | Low |
| Privilege escalation through registration | Low / High | Role never read from input (TC-API-07) | Low |
| Secrets committed to the repo | Medium / High | `.env` ignored, Gitleaks job in CI | Low |
| Demo password left on staging | Medium / High | `SEED_PASSWORD` env value, different on staging | Medium until staging is set up |
| Performance at larger scale untested | Medium / Medium | One load test at about 5,000 rows | Medium, stated in release summary |

## AI Usage Register entry (required by the brief)

| Date | Tool | What it was used for | How it was verified | Changes made by the team |
|---|---|---|---|---|
| 2026-10-07 | Claude (Anthropic) | Generated the first version of the Laravel/Inertia/React code, PHPUnit/Vitest/Playwright tests, CI workflow, load-test script and these draft documents | Frontend tests, lint and build run; schema replayed on PostgreSQL; PHP tests and E2E run by the team (add result) | Record here what the team changed, rejected or corrected after reading the code |

Every team member must be able to explain the code they present. Read it, change what you disagree with, and record real corrections here.
