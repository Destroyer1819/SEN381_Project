# Frontend

The frontend is React 19 with TypeScript, rendered through Inertia 3. Laravel decides which page to show and passes it data as props; there is no separate JSON API.

## Layout of `resources/js`

| Path | Contents |
|---|---|
| `app.tsx` | Entry point. Loads the page component Laravel names. |
| `pages/` | One file per screen. `Inertia::render('requests/index')` loads `pages/requests/index.tsx`. |
| `layouts/` | `app-layout` (sidebar shell), `auth-layout` (login and register), `settings/layout`. |
| `pages/errors/` | `403.tsx` and `404.tsx`, for `Inertia::render('errors/403')` and `errors/404`. |
| `components/` | App components: `request-form`, `filter-bar`, `sort-control`, `status-badge`, `pagination`, `avg-resolution-card`, `error-state`, `http-error-overlay`, sidebar and navigation. |
| `components/ui/` | Generic shadcn/ui building blocks. |
| `lib/validation.ts` | Client-side rules for the request form. |
| `lib/workflow.ts` | Status labels, action labels, date formatting. |
| `lib/sort.ts` | Sort choices for the request list, reading and writing the `sort` and `direction` query parameters. |
| `lib/duration.ts` | Turns hours into text such as `2 d 4 h` for the dashboard. |
| `lib/errors.ts` | Wording for the 403 and 404 screens. |
| `types/` | `index.ts` (shared props, `User`) and `civic.ts` (request data shapes). |

`resources/views/errors/` holds plain Blade pages for 403 and 404. Laravel uses them automatically when `APP_DEBUG=false`. They need no build files, so they still work if the Vite build is missing.

Component and unit tests sit next to the code as `*.test.ts(x)`. End-to-end journeys are in `e2e/`.

## Data every page receives

Shared from `app/Http/Middleware/HandleInertiaRequests.php`:

| Prop | Shape | Used for |
|---|---|---|
| `auth.user` | `{ id, name, email, role, ... }` or `null` | Sidebar and role-based display. `role` is `requestor`, `staff` or `management`. |
| `flash.success` | string or `null` | Green banner at the top of the page. Set with `->with('success', '...')` on a redirect. |
| `name` | string | App name. |

Validation errors arrive through Inertia's standard `errors` prop, keyed by field name.

## What each page expects from the backend

All of these require a signed-in user, and every route below is built on the backend (see [backend.md](backend.md) for the rules behind them).

### `GET /requests` renders `requests/index`

| Prop | Shape |
|---|---|
| `requests` | Paginator of request summaries: `{ data, total, current_page, last_page, prev_page_url, next_page_url }` |
| `filters` | The filters currently applied, echoed back so the form keeps its values |
| `categories` | `[{ id, name }]` |
| `statuses` | `['open', 'assigned', 'in_progress', 'resolved', 'closed']` |

Each request summary:

| Field | Type | Notes |
|---|---|---|
| `id` | number | Used in links to `/requests/{id}` |
| `reference` | string | Displayed reference, `CC-` followed by digits, such as `CC-2026000336` |
| `status` | one of the five statuses | |
| `category` | string or null | The category name, not its id |
| `location`, `area`, `description` | string | |
| `requestor`, `assignee` | string or null | Names, not ids |
| `overdue` | boolean | Shows an "Overdue" badge. True when the request is not resolved or closed and was submitted more than 7 days ago. |
| `created_at`, `updated_at` | ISO date string | |

Query parameters the filter form sends (all optional; empty ones are omitted):

| Parameter | Meaning |
|---|---|
| `q` | Free-text search, ignoring case, over location and description. A whole reference (`CC-2026000336` or `2026000336`) also matches. |
| `status` | One status value |
| `category_id` | A category id |
| `from`, `to` | Dates as `YYYY-MM-DD` |
| `mine` | Present when "Assigned to me" is ticked. Only offered to staff and management. |
| `active` | Present when "Active only" is ticked: hides resolved and closed requests (FR-021) |
| `overdue` | Present when "Overdue only" is ticked: only overdue requests (FR-023). The dashboard's Overdue tile links here with its own filters. |
| `sort` | `created_at`, `updated_at`, `reference`, `status` or `category`. Left out for the default order. |
| `direction` | `asc` or `desc`. Always sent together with `sort`. |

The sort control sends `sort` and `direction` only when the choice is not the default. Newest first is the default and sends nothing. The server sorts the whole result before paging, with `status` in workflow order (open, assigned, in progress, resolved, closed) and ties going to the newest id, so the page shows the rows in the order they arrive. An unknown `sort` or `direction` comes back as a validation error.

The page title and columns change for requestors. The server decides which requests are listed: requestors get only their own, staff get unassigned requests and those assigned to them, and management gets all of them. The list is newest first unless `sort` and `direction` say otherwise, 50 per page. An invalid `status` or date comes back as a validation error.

### `GET /requests/create` renders `requests/create`

| Prop | Shape |
|---|---|
| `categories` | `[{ id, name }]`, the categories a requester may choose |

### `POST /requests`

Body: `{ category_id: number, location: string, area: string, description: string }`, already trimmed.

The client applies these rules first, and the server enforces the same ones:

| Field | Rule |
|---|---|
| `category_id` | Required, and must be an active category |
| `location`, `area` | Required, at most 255 characters |
| `description` | Required, 10 to 2000 characters |

Some categories need more on the server only: a Security concern needs a description of at least 30 characters and Lost property at least 20. The form's counter only knows the general minimum of 10, so a shorter description in those categories passes the browser check and comes back as a server error under `description`, with a message that says what the category needs.

On failure the server returns validation errors keyed `category_id`, `location`, `area`, `description`. On success it redirects to the new request's page with the flash message "Request submitted. Your reference is CC-…", which the end-to-end test looks for.

### `GET /requests/{id}` renders `requests/show`

| Prop | Shape |
|---|---|
| `request` | A request summary plus `version` (number) and `resolved_at` (ISO string or null) |
| `history` | `[{ id, old_status, new_status, by, at }]`. `old_status` is null for the first entry; `by` is a name; `at` is an ISO string. |
| `comments` | `[{ id, text, is_resolution, by, role, at }]` |
| `assignments` | `[{ id, state, to, by, at, responded_at }]`, oldest first. `state` is `self_assigned`, `offered`, `accepted` or `declined`; `to` and `by` are names. Shown in the Assignments panel. Always empty for requestors. |
| `actions` | What the current user may do; see below |

The server returns 403 for a request the user may not see and 404 for one that does not exist. `history` and `comments` are oldest first.

`actions`, all calculated by the server for the current user:

| Field | Type | Effect on the page |
|---|---|---|
| `transitions` | string[] | Statuses the user may move this request to. Only the assigned staff member gets any. Empty hides the status form. |
| `canTake` | boolean | Shows "Take this request". True only for staff, on an open request with nobody assigned and no offer waiting. |
| `canOffer` | boolean | Shows the "Offer to" form. True for staff and management on an open request with nobody assigned and no offer waiting. |
| `staff` | `[{ id, name }]` | Options for the offer form, least busy first. Leaves out the current user and anyone who already declined this request. Empty hides the form. |
| `pendingOfferId` | number or null | Set only for the person an offer is waiting on; shows Accept and Decline for that assignment |
| `pendingOfferTo` | string or null | For other staff and management while an offer is waiting: the name it is waiting on, shown as "Offered to …, waiting for a reply". Null for the offeree and for requestors. |

Refusals from Take, Offer, Accept or Decline and stale status changes come back under the `request` error key. The page shows that error once, at the top of the side panel.

### Actions on a request

| Request | Body | Notes |
|---|---|---|
| `POST /requests/{id}/status` | `{ status, version, note }` | `version` is the value the page loaded, for the stale-write check. `note` is required when moving to `resolved` and is saved as a resolution comment. Errors are keyed `status` (a move that is not allowed, naming the allowed ones), `note`, and `request` ("This request changed since you opened it."). Sets the flash message "Status updated." |
| `POST /requests/{id}/take` | none | Staff self-assign; the request becomes `assigned`. Sets the flash message "You have taken this request." If someone else took it first, or an offer is waiting, redirects back with an error under `request`. |
| `POST /requests/{id}/offer` | `{ assigned_to }` | A staff user id from `actions.staff`. Errors keyed `assigned_to`, or `request` if the request was taken or offered first. Sets "Offered to …", or says the offer was accepted automatically when that person has used their 3 declines this month. |
| `POST /requests/{id}/comments` | `{ body }` | Required, at most 2000 characters. Errors keyed `body`. Anyone who may view the request may comment. Sets the flash message "Comment added." |
| `POST /assignments/{id}/respond` | `{ decision }` | `accept` or `decline`. A decline passes the offer to the next staff member, or back to the open pool. A 4th decline in a month is refused with an error under `request`. |

Each redirects back to the request page so it reloads with fresh data.

### `GET /dashboard` renders `dashboard`

Management only. Other roles get a 403, which the end-to-end test checks.

| Prop | Shape |
|---|---|
| `filters` | `{ category_id?, from?, to? }` echoed back; only the filters that were sent |
| `categories` | `[{ id, name }]`, the active categories, for the filter dropdown |
| `stats` | `{ total, by_status, overdue, overdue_days, by_category, avg_resolution_hours }` |

`stats.by_status` is an object of status to count. It always has all five statuses, in workflow order, with 0 for any that have no requests. `stats.by_category` is `[{ name, total }]`, highest first, listing only categories that have requests in the filtered period, so it can be an empty list. `stats.overdue_days` is the threshold shown in the "Overdue (> N days)" label, currently 7.

The filter form sends `category_id`, `from` and `to` as query parameters, and every figure respects all three. An invalid date comes back as a validation error.

`stats.avg_resolution_hours` is the mean number of hours from `created_at` to `resolved_at` for the requests submitted in the filtered period that have been resolved, or `null` when none of them have. The dashboard shows it as "Average resolution time" (for example `2 d 4 h`), and shows "No data" for `null`.

### 403 and 404 screens

Two ways the frontend shows them, so the backend does not need to change anything:

- **Full page load** (typing a URL, refreshing): Laravel renders `resources/views/errors/403.blade.php` or `404.blade.php`. This only happens with `APP_DEBUG=false`; with debug on, Laravel shows its own debug page.
- **Inside the app** (clicking a link that the server answers with a plain 403 or 404): `components/http-error-overlay.tsx` catches the response and shows the same screen over the current page, with a Close button.

If the backend later renders `Inertia::render('errors/403')` or `errors/404` from the exception handler, those pages exist and show the same wording inside the normal layout.

### Navigation the frontend assumes

- The sidebar logo and the first menu item link to `/requests`. `/` and the redirect after login or registration go there too (`routes/web.php` and `home` in `config/fortify.php`).
- The "Dashboard" link is shown only when `auth.user.role` is `management`.

## Where the frontend's names differ from the database

The backend converts between the two, so pages can rely on the left-hand column.

| Frontend uses | Database has | How the backend bridges it |
|---|---|---|
| `reference` as a string such as `CC-2026000336` | `reference_number` as a number | Adds the `CC-` prefix when sending |
| `overdue` flag and an `overdue_days` threshold | No overdue column | Calculated: not resolved or closed, and submitted more than 7 days ago |
| Comment `text` in responses, `body` on submit | Column `comment` | Renamed in both directions |

## What the end-to-end tests need

`e2e/` signs in as the seeded requestor, staff member and manager listed in `e2e/helpers.js`, all with the seeded test password. The journeys need a running site (`php artisan serve` and `pnpm run dev`) and a seeded database. They also expect categories named "IT support" and "Lost property", with "Lost property" having id 4, and existing requests for the dashboard totals.

Fortify allows 5 logins per minute for each address, so wait a minute between two full runs. Running `pnpm test` while `pnpm run dev` is running deletes `public/hot`, and the site then shows "Vite manifest not found" until `pnpm run dev` is restarted.
