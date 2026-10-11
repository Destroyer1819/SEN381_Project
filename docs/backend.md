# Backend

Laravel 12 on PHP 8.4 or later, with PostgreSQL. This page describes what exists today; the request workflow itself has not been built yet.

## Authentication

Laravel Fortify handles the form submissions, and only registration is enabled in `config/fortify.php`.

| Route | Handled by |
|---|---|
| `GET /login`, `GET /register` | `routes/auth.php`, which renders the `auth/login` and `auth/register` pages |
| `POST /login`, `POST /logout`, `POST /register` | Fortify |

New users are created by `app/Actions/Fortify/CreateNewUser.php`. After login, users are sent to the `home` path in `config/fortify.php`.

Password reset, email verification and two-factor login are switched off because the frontend has no pages for them.

## Settings

`routes/settings.php` and the controllers in `app/Http/Controllers/Settings/` serve the profile, password and appearance pages under `/settings`.

## Shared page data

`app/Http/Middleware/HandleInertiaRequests.php` sends `auth.user`, `flash.success` and `name` to every page. See [frontend.md](frontend.md) for what each page expects beyond that.

## Database

The schema is defined by the migrations in `database/migrations/`:

| Table | Purpose |
|---|---|
| `users` | Accounts, with a `role` of `requestor`, `staff` or `management` |
| `categories` | Controlled list of request categories |
| `service_requests` | The request itself, with `status` and a `version` column for stale-write checks |
| `request_status_transitions` | Legal from/to status pairs, inserted by the migration |
| `request_status_history` | Append-only record of status changes |
| `request_assignments` | Offers, acceptances and self-assignments |
| `request_comments` | Comments and resolution notes |
| `sessions`, `cache`, `jobs`, `failed_jobs` | Laravel's own tables |

On PostgreSQL the migrations also add CHECK constraints for status values, roles and date ordering. SQLite skips them.

The PostgreSQL connection sets its session time zone to `APP_TIMEZONE`, so database defaults such as `CURRENT_TIMESTAMP` agree with the timestamps Laravel writes.

## Tests

`php artisan test` runs the Pest tests in `tests/` against in-memory SQLite. They cover login, registration and the settings pages.
