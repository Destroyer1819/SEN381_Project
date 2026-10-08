# GitHub controls, release candidate, staging and rollback

## Repository settings (Settings, Branches, add rule for `main`)

* Require a pull request before merging, with **2 approvals**, and dismiss stale approvals on new commits.
* Require status checks to pass: select **Quality gate (required check for merging to main)**.
* Do not allow bypassing the rules; no direct pushes to `main`.
* Work on branches such as `feature/frontend-request-list`, `fix/status-validation`. Link each PR to an issue.
* Reviewers should leave comments about what they actually checked; approval alone earns little review credit.
* Never commit `.env`. CI uses `.env.example` plus environment variables.

## Release candidate

When the team agrees the scope is frozen and CI is green on `main`:

```bash
git tag -a m3-rc1 -m "M3 release candidate"
git push origin m3-rc1
git rev-parse m3-rc1          # put this hash in PED v3.0
```

Demonstrate exactly this tag. Anything merged after the deadline is not part of M3.

## Simulated staging (acceptable for M3)

A second environment on the same machine with its own database, its own `.env`, and a production-style build.

```bash
createdb civicconnect_staging
git clone <repo-url> civicconnect-staging && cd civicconnect-staging
git checkout m3-rc1
composer install --no-dev --optimize-autoloader
pnpm install --frozen-lockfile && pnpm build
cp .env.example .env && php artisan key:generate
# .env: APP_ENV=staging, APP_DEBUG=false, APP_URL=http://127.0.0.1:8080,
#       DB_DATABASE=civicconnect_staging, SEED_PASSWORD=<different from dev>
php artisan migrate --force --seed
php artisan config:cache && php artisan route:cache
php artisan serve --port=8080
E2E_BASE_URL=http://127.0.0.1:8080 E2E_PASSWORD=<staging password> pnpm e2e
```

Show during the demo: the tag/commit that is running, `APP_ENV=staging` and `APP_DEBUG=false`, that secrets come from `.env`
and not the repo, and the E2E journey passing against port 8080.

Differences from production to state honestly: single machine, `php artisan serve` instead of a real web server,
file sessions, mail goes to the log, no TLS, seeded demo data.

## Rollback

1. Before deploying, back up: `pg_dump civicconnect_staging > backup-before-<tag>.sql`.
2. To roll back: `git checkout <previous-tag>`, `composer install --no-dev`, `pnpm install --frozen-lockfile && pnpm build`, `php artisan config:cache`.
3. If a migration changed the schema, restore the backup: drop and recreate the database, then `psql civicconnect_staging < backup-before-<tag>.sql`.
4. Data concern: requests submitted after the backup are lost on restore, so export them first if they matter.
