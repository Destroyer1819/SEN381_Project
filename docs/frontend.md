# Frontend

The frontend is React 19 with TypeScript, rendered through Inertia 3. Laravel decides which page to show and passes it data as props; there is no separate JSON API.

## Layout of `resources/js`

| Path | Contents |
|---|---|
| `app.tsx` | Entry point. Loads the page component Laravel names. |
| `pages/` | One file per screen. `Inertia::render('requests/index')` loads `pages/requests/index.tsx`. |
| `layouts/` | `app-layout` (sidebar shell), `auth-layout` (login and register), `settings/layout`. |
| `components/` | App components: `request-form`, `filter-bar`, `status-badge`, `pagination`, sidebar and navigation. |
| `components/ui/` | Generic shadcn/ui building blocks. |
| `lib/validation.ts` | Client-side rules for the request form. |
| `lib/workflow.ts` | Status labels, action labels, date formatting. |
| `types/` | `index.ts` (shared props, `User`) and `civic.ts` (request data shapes). |

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

The pages below exist, but their routes do not yet. All of them require a signed-in user.

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
| `reference` | string | Displayed reference. The end-to-end test expects the form `CC-` followed by digits. |
| `status` | one of the five statuses | |
| `category` | string or null | The category name, not its id |
| `location`, `area`, `description` | string | |
| `requestor`, `assignee` | string or null | Names, not ids |
| `overdue` | boolean | Shows an "Overdue" badge |
| `created_at`, `updated_at` | ISO date string | |

Query parameters the filter form sends (all optional; empty ones are omitted):

| Parameter | Meaning |
|---|---|
| `q` | Free-text search over reference, location or text |
| `status` | One status value |
| `category_id` | A category id |
| `from`, `to` | Dates as `YYYY-MM-DD` |
| `mine` | Present when "Assigned to me" is ticked. Only offered to staff and management. |

The page title and columns change for requestors, who are expected to see only their own requests.

### `GET /requests/create` renders `requests/create`

| Prop | Shape |
|---|---|
| `categories` | `[{ id, name }]`, the categories a requester may choose |

### `POST /requests`

Body: `{ category_id: number, location: string, area: string, description: string }`, already trimmed.

The client applies these rules first, and the server is expected to enforce the same ones:

| Field | Rule |
|---|---|
| `category_id` | Required |
| `location`, `area` | Required, at most 255 characters |
| `description` | Required, 10 to 2000 characters |

On failure, return validation errors keyed `category_id`, `location`, `area`, `description`. On success, the end-to-end test expects a redirect to the new request's page with a flash message containing "Request submitted".

### `GET /requests/{id}` renders `requests/show`

| Prop | Shape |
|---|---|
| `request` | A request summary plus `version` (number) and `resolved_at` (ISO string or null) |
| `history` | `[{ id, old_status, new_status, by, at }]`. `old_status` is null for the first entry; `by` is a name; `at` is an ISO string. |
| `comments` | `[{ id, text, is_resolution, by, role, at }]` |
| `actions` | What the current user may do; see below |

`actions`:

| Field | Type | Effect on the page |
|---|---|---|
| `transitions` | string[] | Statuses the user may move this request to. Empty hides the status form. |
| `canTake` | boolean | Shows "Take this request" |
| `canOffer` | boolean | Shows the "Offer to" form |
| `staff` | `[{ id, name }]` | Options for the offer form. Empty hides it. |
| `pendingOfferId` | number or null | When set, shows Accept and Decline for that assignment |

### Actions on a request

| Request | Body | Notes |
|---|---|---|
| `POST /requests/{id}/status` | `{ status, version, note }` | `version` is the value the page loaded, for the stale-write check. `note` is filled in only when moving to `resolved`. Errors are shown for the keys `status`, `note` and `request` (use `request` for conflicts such as a stale version). |
| `POST /requests/{id}/take` | none | Staff self-assign |
| `POST /requests/{id}/offer` | `{ assigned_to }` | A staff user id. Errors keyed `assigned_to`. |
| `POST /requests/{id}/comments` | `{ body }` | Errors keyed `body` |
| `POST /assignments/{id}/respond` | `{ decision }` | `accept` or `decline` |

Each should redirect back to the request page so it reloads with fresh data.

### `GET /dashboard` renders `dashboard`

Management only. The end-to-end test expects a 403 for other roles.

| Prop | Shape |
|---|---|
| `filters` | `{ category_id?, from?, to? }` echoed back |
| `categories` | `[{ id, name }]` |
| `stats` | `{ total, by_status, overdue, overdue_days, by_category }` |

`stats.by_status` is an object of status to count, `stats.by_category` is `[{ name, total }]`, and `stats.overdue_days` is the threshold shown in the "Overdue (> N days)" label. The filter form sends `category_id`, `from` and `to` as query parameters.

### Navigation the frontend assumes

- The sidebar logo and the first menu item link to `/requests`. `/` and the after-login redirect currently go to `/settings/profile` and should move to `/requests` once it exists (`routes/web.php` and `home` in `config/fortify.php`).
- The "Dashboard" link is shown only when `auth.user.role` is `management`.

## Differences from the current database schema

| Frontend expects | Schema has |
|---|---|
| `reference` as a string such as `CC-123` | `reference_number` as a number |
| `overdue` flag and an `overdue_days` threshold | Nothing defines when a request is overdue |
| Comment `text` in responses, `body` on submit | Column `comment` |

## What the end-to-end tests need

`e2e/` signs in as three seeded users, one per role, with the addresses listed in `e2e/helpers.js`. It also expects categories named "IT support" and "Lost property", with "Lost property" having id 4, and at least one existing request for the dashboard totals.
