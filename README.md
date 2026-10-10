# SEN381_Project
# CivicConnect — Milestone 1 Working Plan
*Engineering Foundation & Requirements Baseline (PED v1.0)*

This is a starting draft for every required M1 output. It's meant to be argued with, edited, and pasted into your PED — not copied blind. Where something is a placeholder, it's marked `[TEAM: fill in]`.

---

## 0. Before anything else — team + repo setup

- [ ] Assign a lead per section for *drafting*, but every person must be able to defend every artefact (individual defence, not team defence).
- [ ] Suggested split for 3 people:
  - **A:** Problem, stakeholders, scope
  - **B:** Requirements, acceptance criteria, RTM
  - **C:** Constraints, risk register, forward engineering, decision log
  - GitHub governance and AI Usage Register are shared — nobody "owns" the controls.
- [ ] Create the repo now. Protect `main`. Require PRs. Require 2 approvals from non-authors. Add a PR template. Start committing docs from day 1 — the brief explicitly penalizes bulk-uploaded evidence.
- [ ] Folder structure (from the brief, Appendix C):
  ```
  docs/PED/ requirements/ architecture/ decisions/ risk/ change/ quality/ security/ deployment/
  src/
  tests/
  .github/workflows/ pull_request_template.md
  ```

---

## 1. Problem & Business Need

Draft (CivicConnect-specific, not a rewrite of the scenario):

> [TEAM NAME]'s community organisation currently handles service requests (facility faults, IT support, maintenance, lost property, security concerns) across email, phone, WhatsApp, spreadsheets, and paper. This produces duplicated or lost requests, no visibility for requesters, no clear ownership for staff, and no reliable reporting for management. CivicConnect's business value is a single controlled record of a request's lifecycle: submit → categorise → assign → act → resolve → report — closing the accountability gap that currently exists.

Link each sentence above to a stakeholder need in section 2 — that link is literally what's assessed here (3 marks).

---

## 2. Stakeholder Analysis

| Stakeholder | Needs | Influence | Interest | Conflict / competing expectation |
|---|---|---|---|---|
| Requester (community member) | Submit easily, see status, get feedback | Low | High | Wants fast resolution + full visibility |
| Staff (request handler) | Manageable workload, clear ownership, low admin overhead | Medium | High | Wants fewer notifications/updates — conflicts with requester's visibility need |
| Management / oversight | Accountability, reporting, overdue tracking | High | High | Wants detailed reporting — conflicts with staff's "less admin" preference |
| System owner (your team / lecturer as client proxy) | Working, defensible, scoped system | High | High | Wants breadth of features vs. team's schedule/cost limits |
| [Add: IT/security-conscious stakeholder if request data is sensitive] | Data handled consistently, no leaks | Medium | Medium | May conflict with speed of MVP delivery |

**The conflict worth writing up in full:** requester visibility vs. staff admin burden. E.g. real-time status updates for requesters could mean staff must update status frequently — if that's not designed for low friction, staff will stop updating it, which breaks the whole point of the system. That's a genuine trade-off you can defend in the interview.

---

## 3. Scope Baseline

**In scope (M1 commitment):**
- Request submission with category, description, location/area
- Controlled category list (not free text)
- Status tracking visible to requester (submitted → assigned → in progress → resolved → closed)
- Staff: view/search/filter/sort requests, assign/accept, update status, add comments/resolution notes
- Management: dashboard view by status/category/overdue, basic activity reporting
- Role-based access (Requester / Staff / Management)

**Out of scope (M1):**
- WhatsApp/SMS/telephone integration
- Native mobile app
- Payment processing
- Multi-language support
- Predictive/AI-driven prioritisation

**Deferred / future scope:**
- SMS or push notifications (email/in-app only for now)
- Public API for third-party integration
- Advanced analytics/BI-style reporting

**Defend one exclusion (required evidence):**
> We are excluding SMS/WhatsApp notification integration from the baseline. It introduces a paid third-party API dependency, rate limits, and delivery-reliability risk that conflicts with our cost constraint (Section 4: Master Brief prefers free/low-cost services). Email + in-app status visibility satisfies the underlying stakeholder need (know what's happening to my request) without that dependency. We will revisit this if staff/requester feedback during later milestones shows email is insufficient — this is a deliberate deferment, not an oversight.

---

## 4. Requirements & Acceptance Criteria

Use `FR-0xx` / `NFR-0xx`, a source, a priority (Must/Should/Could), and a *testable* acceptance criterion — "the system should be fast" is not acceptable; "returns results within 2 seconds for 95% of searches" is.

### Functional (sample — expand to your actual scope)

| ID | Requirement | Source | Priority | Acceptance Criteria |
|---|---|---|---|---|
| FR-001 | Requester can submit a service request with category, description, and location | Requester need | Must | Given required fields are filled, submitting creates a request with a unique ID and status "Submitted" |
| FR-002 | Requester can view the status and history of their own requests | Requester need | Must | Requester sees only their own requests; status reflects the latest staff action within 5 seconds of update |
| FR-003 | Staff can search/filter requests by status, category, and date | Staff need | Must | Filtering by any single field returns only matching requests; combinable filters return the intersection |
| FR-004 | Staff can assign a request to themselves or another authorised staff member | Staff need | Must | Assignment updates request owner and is visible in request history/audit trail |
| FR-005 | Staff can transition request status through a controlled workflow (Submitted → Assigned → In Progress → Resolved → Closed) | Staff need | Must | Invalid transitions (e.g. Closed → Assigned) are rejected with an error message |
| FR-006 | Requester receives a notification when request status changes | Requester need | Should | An email/in-app notification is generated within 1 minute of a status change event |
| FR-007 | Management can view a dashboard of open, overdue, resolved, and closed requests | Management need | Must | Dashboard counts match underlying request data at time of query, filterable by category/date range |
| FR-008 | System enforces role-based access (Requester/Staff/Management) | Security/governance | Must | A Requester account cannot access staff-only or management-only views/endpoints (verified by attempted access test) |

`[TEAM: add remaining FRs to reach full coverage of Section 3 capabilities in the Master Brief — aim for ~15-20 total, each traceable to a stakeholder need]`

### Non-functional (sample)

| ID | Requirement | Priority | Acceptance Criteria |
|---|---|---|---|
... (150 lines left)
