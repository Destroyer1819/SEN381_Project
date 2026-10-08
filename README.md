# CivicConnect

Community service request system for SEN381 (team Proto). Laravel 12, Inertia, React (TypeScript), Tailwind 4, shadcn/ui and PostgreSQL.

The database design is the team's own: `database/sql/civicconnect_schema.sql` is loaded by a single migration.

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

Demo accounts use the password in `SEED_PASSWORD` (default `password`):
`management@civicconnect.test`, `staff1@civicconnect.test`, `requestor1@civicconnect.test`.

## Checks

| Check | Command |
|---|---|
| PHP unit and API tests | `composer test` |
| Frontend tests | `pnpm test` |
| Lint, format, types | `pnpm lint:check`, `pnpm format:check`, `pnpm types`, `composer lint:check` |
| End-to-end | `pnpm exec playwright install chromium`, then `pnpm e2e` (app running and seeded) |
| Load test | `pnpm perf` |

## Milestone 3 evidence

See `docs/m3/`: run guide, test evidence register, RTM rows, register additions, GitHub and staging notes.
CI is in `.github/workflows/ci.yml` and fails the quality gate if any job fails.
