# Backend

Laravel 13 on PHP 8.4 or later, with PostgreSQL. This page describes what exists today. The full request workflow is built: submitting, listing with search, filters and sorting, viewing, commenting, taking, offering and answering offers, status changes with emails to the requester, the management dashboard, and the 5-year retention purge.

## Authentication

Laravel Fortify handles the form submissions, and only registration is enabled in `config/fortify.php`.

| Route | Handled by |
|---|---|
| `GET /login`, `GET /register` | `routes/auth.php`, which renders the `auth/login` and `auth/register` pages |
| `POST /login`, `POST /logout`, `POST /register` | Fortify |

New users are created by `app/Actions/Fortify/CreateNewUser.php` and always get the `requestor` role, the database default. Staff and management accounts exist only in the seeders so far. After login or registration, users are sent to the `home` path in `config/fortify.php`, which is `/requests`. `/` redirects there too.

Logins are limited to 5 attempts per minute for each email address and IP address (`app/Providers/FortifyServiceProvider.php`). Running the end-to-end tests twice within a minute hits this limit.

Password reset, email verification and two-factor login are switched off because the frontend has no pages for them.

## Settings

`routes/settings.php` and the controllers in `app/Http/Controllers/Settings/` serve the profile, password and appearance pages under `/settings`.

## Requests

All request routes are in `routes/web.php` and require a signed-in user.

| Route | Handled by | Purpose |
|---|---|---|
| `GET /requests` | `ServiceRequestController@index` | List with search, filters, sorting and 50 rows per page |
| `GET /requests/create` | `ServiceRequestController@create` | Submission form |
| `POST /requests` | `ServiceRequestController@store` | Saves a request; validated by `app/Http/Requests/StoreServiceRequest.php` |
| `GET /requests/{id}` | `ServiceRequestController@show` | Detail with status history, comments and the actions the user may take |
| `POST /requests/{id}/comments` | `RequestCommentController@store` | Adds a comment |
| `POST /requests/{id}/take` | `RequestAssignmentController@take` | Staff assign an open, unassigned request to themselves |
| `POST /requests/{id}/offer` | `RequestAssignmentController@offer` | Staff or management offer an open request to a staff member |
| `POST /assignments/{id}/respond` | `RequestAssignmentController@respond` | The person offered a request accepts or declines it |
| `POST /requests/{id}/status` | `RequestStatusController@store` | The assigned staff member moves the request to its next status |
| `GET /dashboard` | `DashboardController` | Management statistics; other roles get 403 |

The models are in `app/Models/`, one per table. The tables do not follow Laravel's naming defaults, so each model sets its own table name, timestamps and foreign keys.

### Who can see a request

| Role | Sees |
|---|---|
| `requestor` | Only requests they submitted |
| `staff` | Unassigned requests and requests assigned to them |
| `management` | Every request |

The rule is written twice and the two must stay in step: `ServiceRequest::scopeVisibleTo()` filters the list, and `ServiceRequestPolicy::view()` guards a single request. Anyone who may view a request may comment on it (`ServiceRequestPolicy::comment()`).

### Who can do what

All in `app/Policies/`. A refusal returns a plain 403 and is logged (see [Refused actions](#refused-actions)).

| Action | Allowed when |
|---|---|
| Take | The user is `staff`, the request is `open`, nobody is assigned and no offer is waiting |
| Offer | The user is `staff` or `management`, the request is `open`, nobody is assigned and no offer is waiting |
| Accept or decline an offer | The user is the person the offer went to, and it is still waiting (`RequestAssignmentPolicy`) |
| Change status | The user is `staff` and is the one assigned to the request |

### Rules applied when saving

- A new request gets the next reference number for the current year, in the form year followed by a six-digit counter (`2026000336`). Pages show it as `CC-2026000336`.
- A new request and its first status history row are written in one database transaction.
- `status`, `assigned_to` and `version` are not mass assignable, so form input cannot set them. Only the workflow code changes them, with query-level updates.
- A request is overdue when it is not `resolved` or `closed` and was submitted more than 7 days ago. The rule lives on the model: `ServiceRequest::OVERDUE_DAYS`, `isOverdue()` for one request, and the `overdue()` query scope for counting. `ServiceRequest::STATUSES` is the single list of status values, and `ServiceRequest::STATUS_LABELS` holds their display names for emails.
- Adding a comment updates the request's `updated_at`.

### Category rules (D-010)

Each category has a handler in `app/Domain/Categories/Handlers/` that implements `CategoryHandler`. `CategoryRegistry`, bound in `app/Providers/AppServiceProvider.php`, maps the category code (101 to 107) to its handler and falls back to `OtherHandler` for an unknown code. `StoreServiceRequest` adds the chosen handler's rules and messages to its own.

| Category | Extra rule |
|---|---|
| Security concerns (105) | Description at least 30 characters |
| Lost property (104) | Description at least 20 characters |
| The other five | None; the base rules apply |

Adding a category means one new handler class, one line in `AppServiceProvider`, and the seeded category row. `defaultAssignee()` returns `null` in every handler: no category routes new requests to a particular person yet.

The category factory gives test categories codes from 200 upwards, so they never pick up a real category's rules by accident.

### Sorting the list

`GET /requests` accepts `sort` (`created_at`, `updated_at`, `reference`, `status` or `category`) and `direction` (`asc` or `desc`). Anything else fails validation, so user input never reaches the SQL. `ServiceRequestController::applySort()` orders the whole result before paging. Status follows the workflow order (open, assigned, in progress, resolved, closed), not the alphabet. Ties go to the newest id. Without `sort` the list is newest first.

Two more filters narrow the list: `active=1` hides `resolved` and `closed` requests, so finished work leaves a staff member's active list (FR-021), and `overdue=1` lists only overdue requests (FR-023). The dashboard's Overdue figure links to that list.

### Taking a request

`RequestAssignmentController@take`, in one transaction:

1. Locks the request row and refuses if an offer is waiting.
2. Sets the status to `assigned`, sets `assigned_to` to the user and adds 1 to `version`, but only if the request is still open and unassigned.
3. Writes a status history row, `open` to `assigned`, by the user.
4. Writes a `request_assignments` row with state `self_assigned`, where `offered_by` is the user.
5. Raises `RequestStatusChanged`, so the requester is emailed.

If two staff members click Take at the same time, only the first succeeds. The other is redirected back with an error under the `request` key and nothing is written for them.

### Offers

`app/Services/OfferService.php` holds the rule the team agreed:

- An offer waits for the staff member to accept or decline. The request stays open and unassigned meanwhile, and Take is blocked.
- Each staff member may decline 3 offers per calendar month (`OfferService::DECLINE_LIMIT`). The count resets on the 1st.
- An offer to someone with no declines left is accepted for them straight away, and a 4th decline is refused.
- A declined offer moves to the next staff member: the one with the fewest `assigned` or `in_progress` requests, then lowest id, skipping the person who made the offer and anyone who already declined this request.
- When nobody is left, the request goes back to the open pool and anyone can take it again.

Accepting, or an automatic accept, writes the same history row and email as Take. The service locks the request row, the offer row and the staff member's row, always in that order. Because of this, an offer and a Take on the same request can't both succeed, and two declines at the same moment can't take someone past the limit. SQLite ignores these locks, so the PHP tests do not prove them.

The request page also receives `assignments`: every self-assignment, offer, acceptance and decline, oldest first, with who it went to, who made it and when (FR-016, FR-022). Requestors get an empty list.

The request page receives `canOffer`, `staff` (the people who may be offered it, least busy first), `pendingOfferId` (only for the person the offer went to) and `pendingOfferTo` (the name the offer is waiting on, for other staff and management).

### Status changes

`app/Services/RequestStatusService.php`, used by `RequestStatusController`:

- `allowedNext($status)` reads the legal next statuses from `request_status_transitions`. The request page's `transitions` uses it too.
- `transition()` refuses a move that is not in the table, with a message naming the allowed ones. Then, in one transaction, it:
  - updates the request only if `version` still matches what the page loaded;
  - writes the history row;
  - saves the resolution note as a comment marked `is_resolution`;
  - raises `RequestStatusChanged`.
- A stale `version` throws `StaleRequestException`, which the controller turns into a redirect with "This request changed since you opened it."
- Moving to `resolved` needs a note and sets `resolved_at`. Re-opening (`resolved` to `in_progress`) clears `resolved_at`.

### Emails to the requester (D-009, D-011)

`RequestStatusChanged` (`app/Events/`) implements `ShouldDispatchAfterCommit`, so it is only sent once the transaction has committed, and a rolled-back change sends nothing. The queued listener `NotifyRequesterOfStatusChange` sends `RequestStatusChangedMail` to the requester's address. It tries 3 times, 1 and then 5 minutes apart, and a job that still fails is logged and kept in `failed_jobs`.

With `QUEUE_CONNECTION=sync`, which the PED plans for staging on Render (13.7.2), the email is sent during the user's request, after the status change has been saved. There is no retry then, so a mail failure is logged as `Status email could not be sent` and the user still sees the change succeed.

Emails need a queue worker: run `php artisan queue:work`, or `php artisan queue:work --stop-when-empty` to send what is waiting. Locally `MAIL_MAILER=log` writes them to `storage/logs/laravel.log`. When `MAIL_DEMO_TO` is set, every email goes to that one address instead (staging only). Links in emails use `APP_URL`.

### Dashboard

`GET /dashboard` is protected by the `view-dashboard` gate, defined in `app/Providers/AppServiceProvider.php`, which allows only the `management` role. It counts requests in the database with `GROUP BY` queries, so its cost does not grow with the number of rows sent to the page. The optional `category_id`, `from` and `to` filters apply to every figure, and they filter on the date a request was submitted.

`stats.avg_resolution_hours` is the mean time from `created_at` to `resolved_at`, worked out by the database, for the filtered requests that have been resolved. It is `null` when none have.

### Refused actions

`bootstrap/app.php` logs every policy or gate refusal as one `Access denied` warning line, with the user id, role, method, path, route name and IP address. The user still gets a plain 403.

### Retention purge (FR-008)

`ServiceRequest` uses Laravel's `Prunable`. A request last updated more than 5 years ago (`ServiceRequest::Retention_Years`) is deleted together with its history, assignments and comments, in one transaction. `routes/console.php` schedules `model:prune` daily at 02:00, which needs the scheduler to run (`php artisan schedule:work`, or a cron entry for `schedule:run`). `php artisan model:prune --pretend` shows what would be deleted.

### Still open

- Routing new requests by category (`defaultAssignee()`) needs a team decision.
- Deployment to Render (D-018) is not set up. Render's free tier has no background worker or cron, so the queue and the daily purge need a decision first.

## Shared page data

`app/Http/Middleware/HandleInertiaRequests.php` sends `auth.user`, `flash.success` and `name` to every page. See [frontend.md](frontend.md) for what each page expects beyond that.

## Database

The schema is defined by the migrations in `database/migrations/`:

| Table | Purpose |
|---|---|
| `users` | Accounts, with a `role` of `requestor`, `staff` or `management` |
| `categories` | Controlled list of request categories, with a numeric `code` |
| `service_requests` | The request itself, with `status` and a `version` column for stale-write checks |
| `request_status_transitions` | Legal from/to status pairs, inserted by the migration |
| `request_status_history` | Append-only record of status changes |
| `request_assignments` | Offers (`offered`, `accepted`, `declined`) and self-assignments |
| `request_comments` | Comments and resolution notes |
| `sessions`, `cache`, `jobs`, `failed_jobs` | Laravel's own tables |

On PostgreSQL the migrations also add CHECK constraints for status values, roles and date ordering. SQLite skips them.

The PostgreSQL connection sets its session time zone to `APP_TIMEZONE`, so database defaults such as `CURRENT_TIMESTAMP` agree with the timestamps Laravel writes.

## Tests

`php artisan test` runs the Pest tests in `tests/` against in-memory SQLite. They cover login, registration and the settings pages, plus the request features:

| File | Covers |
|---|---|
| `RequestVisibilityTest.php` | Which requests each role sees in the list and can open (FR-011) |
| `SubmitRequestTest.php` | Submitting, validation messages, guests, description length boundaries (TC-BB-01) |
| `CategoryHandlingTest.php` | Handler per category, the fallback, every seeded category has a handler, per-category limits |
| `CommentTest.php` | Who may comment, empty comments |
| `TakeRequestTest.php` | Who may take, the rows written, and the `canTake` flag |
| `StatusTransitionTest.php` | Legal and illegal moves, stale versions, who may change status, resolution notes, re-opening, one history row per change |
| `StatusNotificationTest.php` | The event after a status change or Take, nothing after a refused one, the queued listener and the email, mail failures on the sync queue and on a real queue |
| `OfferTest.php` | Offers, the decline limit and its monthly reset, passing a decline on, the open pool, who may offer and answer, page props, the assignment records |
| `RequestListFilterTest.php` | The Active only and Overdue only views, and that they keep each role to its own requests |
| `RequestSortTest.php` | Every sort option, sorting across pages, sorting with filters, rejected sort values |
| `DashboardTest.php` | Management-only access, every figure, average resolution time |
| `AccessLogTest.php` | Refused actions are logged once, allowed ones are not |
| `RetentionPruneTest.php` | The 5-year purge at its boundary, and that it is scheduled |

Test data comes from the factories in `database/factories/`. `ServiceRequestFactory` has an `assignedTo($staff)` state for requests that already have an assignee.

`.github/workflows/tests.yml` runs on every pull request and on pushes to `main` and `Development`:
- the dependency audits (`composer audit` and `pnpm audit --audit-level high`);
- Pint;
- the PHP tests;
- the TypeScript type-check;
- ESLint and Prettier;
- the frontend tests.

The Playwright journeys in `e2e/` and the load test in `tests/perf/` need a running site, so they are run by hand.
