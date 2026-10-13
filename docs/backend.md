# Backend

Laravel 13 on PHP 8.4 or later, with PostgreSQL. This page describes what exists today. Listing, viewing, submitting and commenting on requests are built; assignment, status changes and the dashboard are not.

## Authentication

Laravel Fortify handles the form submissions, and only registration is enabled in `config/fortify.php`.

| Route | Handled by |
|---|---|
| `GET /login`, `GET /register` | `routes/auth.php`, which renders the `auth/login` and `auth/register` pages |
| `POST /login`, `POST /logout`, `POST /register` | Fortify |

New users are created by `app/Actions/Fortify/CreateNewUser.php` and always get the `requestor` role, the database default. After login or registration, users are sent to the `home` path in `config/fortify.php`, which is `/requests`. `/` redirects there too.

Password reset, email verification and two-factor login are switched off because the frontend has no pages for them.

## Settings

`routes/settings.php` and the controllers in `app/Http/Controllers/Settings/` serve the profile, password and appearance pages under `/settings`.

## Requests

All request routes are in `routes/web.php` and require a signed-in user.

| Route | Handled by | Purpose |
|---|---|---|
| `GET /requests` | `ServiceRequestController@index` | List with search, filters and 50 rows per page |
| `GET /requests/create` | `ServiceRequestController@create` | Submission form |
| `POST /requests` | `ServiceRequestController@store` | Saves a request; validated by `app/Http/Requests/StoreServiceRequest.php` |
| `GET /requests/{id}` | `ServiceRequestController@show` | Detail with status history and comments |
| `POST /requests/{id}/comments` | `RequestCommentController@store` | Adds a comment |

The models are in `app/Models/`, one per table. The tables do not follow Laravel's naming defaults, so each model sets its own table name, timestamps and foreign keys.

### Who can see a request

| Role | Sees |
|---|---|
| `requestor` | Only requests they submitted |
| `staff` | Unassigned requests and requests assigned to them |
| `management` | Every request |

The rule is written twice and the two must stay in step: `ServiceRequest::scopeVisibleTo()` filters the list, and `ServiceRequestPolicy::view()` guards a single request. Anyone who may view a request may comment on it (`ServiceRequestPolicy::comment()`).

### Rules applied when saving

- A new request gets the next reference number for the current year, in the form year followed by a six-digit counter (`2026000336`). Pages show it as `CC-2026000336`.
- A new request and its first status history row are written in one database transaction.
- `status`, `assigned_to` and `version` are not mass assignable, so form input cannot set them.
- A request is overdue when it is not `resolved` or `closed` and was submitted more than 7 days ago.
- Adding a comment updates the request's `updated_at`. Resolution notes are not written through the comments route.

### Not built yet

- Assignment: `POST /requests/{id}/take`, `POST /requests/{id}/offer`, `POST /assignments/{id}/respond`
- Status changes: `POST /requests/{id}/status`
- `GET /dashboard`
- Sorting the list, and email notification of status changes

Until assignment and status changes exist, the detail page is sent an `actions` object that allows nothing.

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

`php artisan test` runs the Pest tests in `tests/` against in-memory SQLite. They cover login, registration and the settings pages. There are no tests for the request routes yet.

`.github/workflows/tests.yml` runs the PHP tests, the TypeScript type-check, ESLint and the frontend tests on every pull request and on pushes to `main` and `Development`.
