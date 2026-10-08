# CivicConnect: run and verify

Stack: Laravel + Inertia + React + PostgreSQL. The database design is the team's own
(`database/sql/civicconnect_schema.sql`, taken from `SEN381_database_initial_version.sql`).

## 1. Build the project (once)

Needs PHP 8.2+, Composer, Node 20+, pnpm and PostgreSQL running locally.

```bash
createdb civicconnect
createdb civicconnect_test
cp .env.example .env            # then set DB_USERNAME / DB_PASSWORD if yours are not postgres / empty
composer install
php artisan key:generate
php artisan migrate --seed
pnpm install
pnpm build
php artisan serve               # http://127.0.0.1:8000
composer lint                   # run once and commit, so the CI Pint check starts green
git add composer.lock && git commit -m "Add composer.lock"
```

Demo accounts (password is `SEED_PASSWORD` from `.env`, default `password`):

| Role | Email |
|---|---|
| Management | management@civicconnect.test |
| Staff | staff1@civicconnect.test, staff2@civicconnect.test |
| Requestor | requestor1@civicconnect.test, requestor2@civicconnect.test |

## 2. Run every kind of check

| What | Command | Needs |
|---|---|---|
| PHP unit + API/integration tests | `php artisan test` | `civicconnect_test` database |
| Frontend component/unit tests | `pnpm test` | nothing |
| Static analysis | `./vendor/bin/pint --test` and `pnpm lint:check` | nothing |
| Dependency security | `composer audit` and `pnpm audit --audit-level=high` | internet |
| End-to-end (3 journeys) | `pnpm exec playwright install chromium` then `pnpm e2e` | app running on :8000 and seeded |
| Load test | `LOAD_ROWS=5000 php artisan db:seed --class=LoadTestSeeder` then `pnpm perf` | app running |

Save the output of each run (terminal log, `phpunit-junit.xml`, `playwright-report/`,
`perf-results/load-test.json`) or link the CI run. The test evidence register
(`test-evidence-register.md`) has a column for exactly that.

## 3. What has and has not been run yet

Written and executed in the build workspace on 2026-10-07:

* Frontend: 27 Vitest cases passed, ESLint, Prettier and tsc clean, production build succeeds, `pnpm audit` shows 0 vulnerabilities.
* Database: the schema loads on PostgreSQL 16, the full request lifecycle was replayed in SQL, and 8 invalid
  inputs (bad transitions, duplicate reference, missing resolved time, bad status/role, duplicate email,
  wrong self-assignment) were all rejected by the database constraints.
* Load script: its login, CSRF, percentile and verdict logic was checked against a mock server.

Written but NOT yet run (the build workspace cannot install Composer packages, so no Laravel code has executed):

* All PHPUnit tests, the Playwright E2E tests, the real load test and the GitHub Actions workflow.

Treat the first run on your machine as the real test. Failures there are normal and are useful evidence:
record them in the defect register and fix them through a PR.
