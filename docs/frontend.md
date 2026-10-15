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

All of these require a signed-in user. Every page exists; the backend routes are at different stages:

| Route | Backend |
|---|---|
| `GET /requests` | Built |
| `GET /requests/create`, `POST /requests` | Built |
| `GET /requests/{id}` | Built; in `actions`, only `canTake` is live so far |
| `POST /requests/{id}/comments` | Built |
| `POST /requests/{id}/take` | Built |
| `POST /requests/{id}/offer`, `/status` and `POST /assignments/{id}/respond` | Not built |
| `GET /dashboard` | Built |

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
| `sort` | `created_at`, `updated_at`, `reference`, `status` or `category`. Left out for the default order. |
| `direction` | `asc` or `desc`. Always sent together with `sort`. |

The sort control sends `sort` and `direction` only when the choice is not the default. Newest first is the default and sends nothing. The server must apply the sort to the whole result before paging, ignore unknown values, and fall back to newest first. `status` should follow the workflow order (open, assigned, in progress, resolved, closed), not the alphabet. The page also puts the rows it received into the chosen order, so the visible page is always consistent. Until the server applies the sort, that only reorders the current page of 50.

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

On failure the server returns validation errors keyed `category_id`, `location`, `area`, `description`. On success it redirects to the new request's page with the flash message "Request submitted. Your reference is CC-…", which the end-to-end test looks for.

### `GET /requests/{id}` renders `requests/show`

| Prop | Shape |
|---|---|
| `request` | A request summary plus `version` (number) and `resolved_at` (ISO string or null) |
| `history` | `[{ id, old_status, new_status, by, at }]`. `old_status` is null for the first entry; `by` is a name; `at` is an ISO string. |
| `comments` | `[{ id, text, is_resolution, by, role, at }]` |
| `actions` | What the current user may do; see below |

The server returns 403 for a request the user may not see and 404 for one that does not exist. `history` and `comments` are oldest first.

`actions` (`canTake` is calculated; until the other routes are built, the server sends empty `transitions` and `staff`, `false` for `canOffer` and `null` for `pendingOfferId`):

| Field | Type | Effect on the page |
|---|---|---|
| `transitions` | string[] | Statuses the user may move this request to. Empty hides the status form. |
| `canTake` | boolean | Shows "Take this request". True only for staff, on an open request with nobody assigned. |
| `canOffer` | boolean | Shows the "Offer to" form |
| `staff` | `[{ id, name }]` | Options for the offer form. Empty hides it. |
| `pendingOfferId` | number or null | When set, shows Accept and Decline for that assignment |

### Actions on a request

| Request | Body | Notes |
|---|---|---|
| `POST /requests/{id}/status` | `{ status, version, note }` | `version` is the value the page loaded, for the stale-write check. `note` is filled in only when moving to `resolved`. Errors are shown for the keys `status`, `note` and `request` (use `request` for conflicts such as a stale version). |
| `POST /requests/{id}/take` | none | Built. Staff self-assign; the request becomes `assigned`. Sets the flash message "You have taken this request." If someone else took it first, redirects back with an error under the `request` key. The page currently shows that key only inside the status form, which is hidden here, so the message is not displayed yet. |
| `POST /requests/{id}/offer` | `{ assigned_to }` | A staff user id. Errors keyed `assigned_to`. |
| `POST /requests/{id}/comments` | `{ body }` | Built. Required, at most 2000 characters. Errors keyed `body`. Anyone who may view the request may comment. Sets the flash message "Comment added." |
| `POST /assignments/{id}/respond` | `{ decision }` | `accept` or `decline` |

Each redirects back to the request page so it reloads with fresh data. The comments and take routes exist so far.

### `GET /dashboard` renders `dashboard`

Management only. Other roles get a 403, which the end-to-end test checks.

| Prop | Shape |
|---|---|
| `filters` | `{ category_id?, from?, to? }` echoed back; only the filters that were sent |
| `categories` | `[{ id, name }]`, the active categories, for the filter dropdown |
| `stats` | `{ total, by_status, overdue, overdue_days, by_category, avg_resolution_hours }` |

`stats.by_status` is an object of status to count. It always has all five statuses, in workflow order, with 0 for any that have no requests. `stats.by_category` is `[{ name, total }]`, highest first, listing only categories that have requests in the filtered period, so it can be an empty list. `stats.overdue_days` is the threshold shown in the "Overdue (> N days)" label, currently 7.

The filter form sends `category_id`, `from` and `to` as query parameters, and every figure respects all three. An invalid date comes back as a validation error.

`stats.avg_resolution_hours` is the mean number of hours from `created_at` to `resolved_at` for requests resolved in the filtered period, or `null` when none were resolved. The dashboard shows it as "Average resolution time" (for example `2 d 4 h`), and shows "No data" for `null` or when the key is missing, which is what it shows until the server sends it.

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

`e2e/` signs in as three users, one per role, with the addresses listed in `e2e/helpers.js`. The seeders do not create those addresses (they create `@example.com` users), so the journeys cannot log in until one side is changed to match the other. The first journey also needs the status route, which is not built; the take step it uses now exists.

The other expectations are met by the seeders: categories named "IT support" and "Lost property", with "Lost property" having id 4, and existing requests for the dashboard totals.
