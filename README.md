# CivicConnect

Community service request system for SEN381 Software Engineering at Belgium Campus ITversity (2026). Built by team Proto.

CivicConnect replaces the mix of email, phone calls, WhatsApp, spreadsheets and paper that a community organisation uses to handle service requests (facility faults, IT support, maintenance, lost property, security concerns). It keeps one traceable record of every request: submit, assign, work on it, resolve, close.

**Stack:** Laravel 12, Inertia, React 19 (TypeScript), Tailwind 4, shadcn/ui and PostgreSQL.

## Team

| Member | GitHub |
|---|---|
| Xander Oosthuyzen | Destroyer1819 |
| Michael Cheyne | |
| Jared Swanepool | |

## What it does

**Requestors**
- Submit a request with a category from a controlled list, a location, an area and a description
- See only their own requests, with the current status and a full history timeline

**Staff**
- Search, filter and sort requests (status, category, date range, assigned to me)
- Take a request, or offer it to another staff member who can accept or decline
- Move a request through the status workflow and add comments and resolution notes

**Management**
- Dashboard of open, overdue, resolved and closed requests, filterable by category and date
- Staff abilities, plus offering requests to staff

Access is role based (requestor, staff, management) and enforced on the server.

### Status workflow

`open` to `assigned` to `in_progress` to `resolved` to `closed`

The allowed moves are stored as data in the `request_status_transition` table. They are enforced by `RequestWorkflow` (with a clear error message) and again by a composite foreign key in the database. Resolving a request needs a resolution note. Each request carries a `version`, so two staff members cannot silently overwrite each other.

A request that is not resolved or closed after 3 days is flagged overdue. Change the limit with `OVERDUE_DAYS` in `.env`.

## Quick start

Needs PHP 8.2+, Composer, Node 20+, pnpm and PostgreSQL.

```bash
createdb civicconnect
createdb civicconnect_test
cp .env.example .env      # set DB_USERNAME / DB_PASSWORD if needed
composer install
php artisan key:generate
php artisan migrate --seed
pnpm install
pnpm build
php artisan serve         # http://127.0.0.1:8000
```

For frontend development, run `pnpm dev` alongside `php artisan serve`.

### Demo accounts

The password is `SEED_PASSWORD` from `.env` (default `password`).

| Role | Email |
|---|---|
| Management | management@civicconnect.test |
| Staff | staff1@civicconnect.test, staff2@civicconnect.test |
| Requestor | requestor1@civicconnect.test, requestor2@civicconnect.test |

The seeder also creates the five categories and a few sample requests across different statuses, including one overdue request.

## Checks

| Check | Command |
|---|---|
| PHP unit and API tests | `composer test` (needs the `civicconnect_test` database) |
| Frontend tests | `pnpm test` |
| Lint, format, types | `pnpm lint:check`, `pnpm format:check`, `pnpm types`, `composer lint:check` |
| Dependency security | `composer audit`, `pnpm audit --audit-level=high` |
| End-to-end | `pnpm exec playwright install chromium`, then `pnpm e2e` (app running and seeded) |
| Load test | `LOAD_ROWS=5000 php artisan db:seed --class=LoadTestSeeder`, then `pnpm perf` |

CI is in `.github/workflows/ci.yml` and fails the quality gate if any job fails.

## Project layout

```
app/
  Http/Controllers/   Request, dashboard, auth and settings controllers
  Http/Middleware/    EnsureRole (role-based access)
  Http/Requests/      Form validation (StoreServiceRequest)
  Models/             ServiceRequest, Category, RequestAssignment,
                      RequestComment, RequestStatusHistory, User
  Services/           RequestWorkflow (all request business rules)
config/civicconnect.php   Statuses, roles, overdue limit, page size
database/
  sql/civicconnect_schema.sql   The team's schema, loaded by one migration
  seeders/                      Demo data and load-test data
resources/js/
  pages/              Dashboard, requests (list, create, show), auth, settings
  components/         Filter bar, request form, status badge, shadcn/ui
  lib/                Client-side validation and workflow helpers
routes/               web, auth, settings
tests/                Unit, Feature (API) and perf (load test)
e2e/                  Playwright request lifecycle journeys
docs/m3/              Milestone 3 evidence and notes
```

The database design is the team's own. `database/sql/civicconnect_schema.sql` is loaded by a single migration, so schema changes go into that SQL file through a pull request.

## Documentation

Milestone 3 documents are in `docs/m3/`:

- `README.md`: run and verify guide
- `test-evidence-register.md`: test cases, techniques and results
- `rtm-rows.md`: requirement traceability rows
- `register-additions.md`: ADRs, technical debt and risk entries
- `github-and-staging.md`: repository controls, release candidate, staging and rollback

## Known limitations

- Notifications (FR-006) are not built. Requestors follow progress through the request history timeline.
- Overdue uses one global rule, not a per-category target.
- No password reset, email verification or request attachments.
- Staff and management accounts are created by the seeder only.

See the technical debt register in `docs/m3/register-additions.md` for the full list and next actions.

## Engineering controls

- `main` is protected. All changes go through pull requests with 2 approvals from people other than the author.
- The CI quality gate must pass before merging.
- Never commit `.env` or any secret. Use `.env.example` and environment variables.
- Work on branches such as `feature/<name>` or `fix/<name>` and link each PR to an issue.
- Material AI use is recorded in the AI Usage Register and verified before merging.
