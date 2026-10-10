# Test evidence register: frontend (Xander)

Status is Pass, Fail, Blocked or **Not yet executed**. Do not change a status until you have a real run to point to.

| Suite | Tool | Cases | Status | Evidence |
|---|---|---|---|---|
| Frontend component and unit | Vitest | 27 | Pass (executed 2026-10-07) | `pnpm test` output; add CI run link |
| End-to-end | Playwright | 3 | Not yet executed | `playwright-report/` (needs the full app running) |

## Unit and component

| ID | Test basis | Technique | Input or precondition | Expected result | Traceability |
|---|---|---|---|---|---|
| TC-U-04 | FR-001 | Valid partition | Complete valid form | No errors | FR-001 |
| TC-U-05 | FR-003 | Equivalence | Mixed empty and filled filters | Only filled values kept | FR-003 |
| TC-U-06 | FR-005 | Decision | Each target status | Only `resolved` needs a note | FR-005 |
| TC-U-07 | FR-002 | Component | Each of 5 statuses, overdue on and off | Label text, data-status, Overdue badge | FR-002 |
| TC-U-08 | FR-001 | Negative | Empty form, click submit | 4 errors, `onSubmit` not called | FR-001 |
| TC-U-09 | FR-001 | Component | Valid form with padded text | Trimmed values, numeric category | FR-001 |
| TC-U-10 | FR-001 | Component | Type 5 characters | "5 / 2000 characters" | FR-001 |
| TC-U-11 | FR-001 | Component | `serverErrors` prop | Alert shown | FR-001 |
| TC-U-12 | FR-003 | Component | Status, category and "assigned to me" | `onApply` with exactly those | FR-003 |

## Black-box (client side)

| ID | Basis | Technique | Input | Expected | Traceability |
|---|---|---|---|---|---|
| TC-BB-09 | FR-001 description 10..2000 | BVA | Lengths 9, 10, 2000, 2001 | Reject, accept, accept, reject | FR-001 |
| TC-BB-10 | FR-001 location and area | Equivalence | Empty, 255 and 256 characters | Reject, accept, reject | FR-001 |

## End-to-end (needs the full app)

| ID | Journey | Expected | Traceability |
|---|---|---|---|
| TC-E2E-01 | Requestor submits; staff searches, takes, starts, resolves; requestor sees the result and history | Final state Resolved with note | FR-001..FR-005 |
| TC-E2E-02 | Guest and requestor try protected pages (negative) | Redirect to login; dashboard link hidden; /dashboard 403 | FR-008 |
| TC-E2E-03 | Management dashboard with category filter | Totals shown; filtered total <= total | FR-007 |
