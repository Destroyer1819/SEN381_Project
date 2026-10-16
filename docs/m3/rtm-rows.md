# RTM rows: frontend (Xander)

Add these to the team's existing RTM. Do not create a new one. FR numbers follow the M1 working plan, so check them
against the real RTM before merging. Backend and database columns are filled in by Jared and Michael.

| Req | Frontend implementation | Frontend tests | Execution evidence | Status |
|---|---|---|---|---|
| FR-001 Submit request | `request-form.tsx`, `lib/validation.ts`, `pages/requests/create.tsx` | TC-U-04, TC-U-08..11, TC-BB-09, TC-BB-10, TC-E2E-01 | Vitest passed 2026-10-07; E2E: add run link | Open until E2E run |
| FR-002 Own requests and status | `pages/requests/index.tsx`, `pages/requests/show.tsx`, `status-badge.tsx` | TC-U-07, TC-E2E-01 | Vitest passed; E2E: add | Open |
| FR-003 Search and filter | `filter-bar.tsx`, `pagination.tsx`, `pages/requests/index.tsx` | TC-U-05, TC-U-12 | Vitest passed | Open |
| FR-004 Assign | Assignment actions in `pages/requests/show.tsx` | TC-E2E-01 | add | Open |
| FR-005 Controlled workflow | `lib/workflow.ts`, status form in `pages/requests/show.tsx` | TC-U-06, TC-E2E-01 | Vitest passed; E2E: add | Open |
| FR-006 Notifications | Not built | none | n/a | Deferred |
| FR-007 Dashboard | `pages/dashboard.tsx` | TC-E2E-03 | add | Open |
| FR-008 Role-based access | Role-based menu in `app-sidebar.tsx`, login and register pages | TC-E2E-02 | add | Open |
