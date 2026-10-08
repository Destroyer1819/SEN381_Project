# RTM rows to merge into the existing RTM

Do not create a new RTM. Add these columns/rows to the team's existing one.
The FR numbers below follow the M1 working plan; check them against the team's real RTM
(for example the CR-009 change that affects FR-010) before merging.

| Req | Acceptance criterion | Implementation | Tests | Execution evidence | Defect / status |
|---|---|---|---|---|---|
| FR-001 Submit request | Required fields filled creates request with unique ID, status open | `StoreServiceRequest`, `RequestController@store`, `RequestWorkflow::create`, `RequestForm.jsx` | TC-API-01, TC-BB-01..03, TC-BB-09..10, TC-U-04, TC-U-08..11, TC-E2E-01 | Vitest passed 2026-10-07; PHPUnit/E2E: add run link | Open until PHP/E2E run |
| FR-002 Own requests and status | Requestor sees only own requests; status current | `RequestController@index/show`, `StatusBadge.jsx` | TC-API-03, TC-API-04, TC-U-07, TC-E2E-01 | add | Open |
| FR-003 Search/filter | Single filter returns matches; combined returns intersection | `RequestController@index`, `FilterBar.jsx` | TC-BB-06..08, TC-API-14, TC-U-05, TC-U-12 | add | Open |
| FR-004 Assign | Assignment updates owner and is in history | `RequestWorkflow::selfAssign/offer/respondToOffer`, `Show.jsx` | TC-API-08, TC-API-09, TC-API-13, TC-E2E-01 | add | Open |
| FR-005 Controlled workflow | Invalid transitions rejected with message | `RequestWorkflow::changeStatus`, `request_status_transition` table | TC-BB-04, TC-BB-05, TC-API-10..12, TC-U-06, TC-E2E-01; DB replay (passed) | DB replay passed; add rest | Open |
| FR-006 Notifications | Notification within 1 minute | Not built. History timeline only | none | n/a | Deferred, see technical debt |
| FR-007 Dashboard | Counts match data; filter by category/date | `DashboardController`, `Dashboard.jsx`, `config/civicconnect.php` | TC-API-15, TC-API-16, TC-U-01..03, TC-E2E-03 | add | Open |
| FR-008 Role-based access | Requestor cannot reach staff/management views | `EnsureRole`, `RequestWorkflow::canView/canManage` | TC-API-02, 04..07, 12, TC-E2E-02 | add | Open |
