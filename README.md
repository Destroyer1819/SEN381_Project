# CivicConnect

Laravel 12 + Inertia + React (TypeScript) + PostgreSQL, on the Laravel React starter kit.

## Who owns what (Milestone 3)

| Area | Owner | Where |
|---|---|---|
| Frontend (React pages, components, tests) | Xander | `resources`, `e2e`, `package.json`, Vite, ESLint and TypeScript configs |
| Backend (routes, controllers, services, PHP tests) | Jared | `app`, `routes`, `config`, `tests` |
| Database (schema, migrations, seeders) | Michael | `database` |

`app`, `bootstrap`, `config`, `routes`, `database` and `storage` are the unmodified starter kit. Jared and Michael build on them.

## What the backend needs to provide

URLs and Inertia page names the pages use:

| Method and URL | Inertia page or action |
|---|---|
| GET /requests | `requests/index` |
| GET /requests/create | `requests/create` |
| POST /requests | create a request |
| GET /requests/{id} | `requests/show` |
| POST /requests/{id}/status | change status |
| POST /requests/{id}/take | staff self-assign |
| POST /requests/{id}/offer | offer to a staff member |
| POST /requests/{id}/comments | add a comment |
| POST /assignments/{id}/respond | accept or decline an offer |
| GET /dashboard | `dashboard` (management only) |

Prop shapes are in `resources/js/types/civic.ts`. Login and register redirect to `/requests`, and `/` should redirect there.

The frontend removed the kit pages for password reset, email verification, password confirmation, the welcome page and account deletion. Trim the matching kit routes and controllers. `users.role` (requestor, staff or management) must reach the frontend as `auth.user.role`.

## Frontend commands

```bash
pnpm install
pnpm dev
pnpm lint:check && pnpm format:check
pnpm test          # 27 component and unit tests
pnpm build
pnpm e2e           # needs the full app running and seeded
```
