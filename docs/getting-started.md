# Getting started

CivicConnect is a Laravel 13 application with a React 19 and TypeScript frontend, connected through Inertia 3, on PostgreSQL.

## Prerequisites

- PHP 8.4 or later with the `pdo_pgsql` extension (check with `php -m`)
- Composer
- Node.js (LTS) and pnpm
- PostgreSQL

## First-time setup

Run these from the repository root.

1. Install the PHP and frontend packages:

    ```bash
    composer install
    pnpm install
    ```

2. Create your environment file and app key:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

3. `.env` is already set up for a local PostgreSQL database called `civicconnect_dev` with the user `postgres`. Fill in your password on the `DB_PASSWORD=` line, and change the other `DB_` lines if your setup differs.

4. Create the empty database (in pgAdmin, or with `createdb -U postgres civicconnect_dev`), then build the schema and seed it:

    ```bash
    php artisan migrate --seed
    ```

    This creates the 7 categories, 54 test users and 310 test requests. Set `SEED_REQUESTS` in `.env` to change the number of generated requests.

    Three of the seeded logins, one per role. They share the test password noted at the top of `database/seeders/DatabaseSeeder.php`:

    | Address | Role |
    |---|---|
    | `thandi.nkosi@example.com` | Requestor |
    | `lerato.mokoena@example.com` | Staff |
    | `grace.naidoo@example.com` | Management |

## After pulling new changes

Other people's changes can add packages or migrations. If something stops working after a pull, run:

```bash
composer install
pnpm install
php artisan migrate
```

## Running the site

Use two terminals and leave both running (a third for `php artisan queue:work` if you want emails sent straight away):

```bash
php artisan serve
```

```bash
pnpm run dev
```

Open http://127.0.0.1:8000, which takes you to the login page and then to `/requests`. Vite prints its own address on port 5173, but that only serves assets.

## Emails and background jobs

Status changes email the requester through a queued job, so emails wait in the `jobs` table until a worker sends them. With `MAIL_MAILER=log` (the default in `.env.example`) they are written to `storage/logs/laravel.log`, not sent. To send what is waiting:

```bash
php artisan queue:work --stop-when-empty
```

Leave `php artisan queue:work` running in a third terminal to send them as they happen.

The 5-year retention purge runs from Laravel's scheduler at 02:00. Locally it only runs while `php artisan schedule:work` is running. To see what it would delete, without deleting anything:

```bash
php artisan model:prune --pretend
```

## Checks

| Command | What it runs |
|---|---|
| `php artisan test` | PHP tests, on in-memory SQLite |
| `vendor/bin/pint --test` | PHP code style |
| `pnpm test` | Frontend unit and component tests (Vitest) |
| `pnpm run types` | TypeScript type-check |
| `pnpm run lint:check` | ESLint |
| `pnpm run format:check` | Prettier, over `resources/` |
| `composer audit`, `pnpm audit --audit-level high` | Known security advisories in installed packages |
| `pnpm run e2e` | Playwright journeys; needs the running site and a seeded database |
| `pnpm perf` | Load test of the request list against NFR-004; needs the running site, saves results in `perf-results/` |

Everything except the last two also runs automatically on GitHub for every pull request, from `.github/workflows/tests.yml`.

Two things to know when running them:

- `pnpm test` deletes `public/hot` when it finishes. If `pnpm run dev` is running at the time, the site then shows "Vite manifest not found". Restart `pnpm run dev` to fix it.
- Logins are limited to 5 per minute per address, so wait a minute between two full `pnpm run e2e` runs.

## Database changes

The schema lives in `database/migrations/`. Change it by adding a new migration, never by editing one that teammates have already run. The CHECK constraints only apply on PostgreSQL; SQLite skips them, so the PHP tests do not exercise them.

`php artisan migrate:fresh --seed` drops every table and rebuilds from the migrations. Only run it against your local database: if `.env` points at the Neon staging database, it wipes that instead. The seeder refuses to run when `APP_ENV=production` (which is also what Laravel assumes when there is no `.env` at all), but by then the tables have already been dropped.
