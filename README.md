Be advised this is the current document in a .md format however the Proto Team recommends to still read through the pdf or docx

**SEN381 - CIVICCONNECT PROJECT** 

**Project Engineering Document (PED)** Milestone 2 - Architecture, Technology & Initial Design Baseline 

**Version 2.0** 

**Team Name / Number:** Proto 

**Team Members:** 

Jared Swanepoel - 601621 Michael Cheyne - 602441 

Xander Oosthuyzen - 601256 

**Module:** Software Engineering 381 (SEN381) 

**Governing document** : SEN381 CivicConnect Master Project Brief 

**Submission Date:** 30/09/2026 

# Table of Contents 

|Document Control ......................................................................................................................................................................................................................................... 6|
|---|
|Change record ........................................................................................................................................................................................................................................... 8|
|Baseline Approval .................................................................................................................................................................................................................................... 11|
|Baseline Approval (Milestone 2) ............................................................................................................................................................................................................. 11|
|1. Problem Statement & Business Need ..................................................................................................................................................................................................... 13|
|1.1 Problem Statement ........................................................................................................................................................................................................................... 13|
|1.2 Business Need ................................................................................................................................................................................................................................... 13|
|1.3 Intended Business Value ................................................................................................................................................................................................................... 13|
|2. Stakeholder Analysis ............................................................................................................................................................................................................................... 13|
|3. Scope Baseline ......................................................................................................................................................................................................................................... 14|
|3.1 In Scope ............................................................................................................................................................................................................................................. 14|
|3.2 Out of Scope ...................................................................................................................................................................................................................................... 15|
|3.3 Deferred / Future Scope.................................................................................................................................................................................................................... 15|
|3.4 Defended Exclusion / Deferment ...................................................................................................................................................................................................... 15|
|3.5 Scope baseline reviews and changes ................................................................................................................................................................................................ 15|
|4. Requirements & Acceptance Criteria ...................................................................................................................................................................................................... 16|
|4.1 Functional Requirements .................................................................................................................................................................................................................. 16|
|4.2 Non-Functional Requirements .......................................................................................................................................................................................................... 18|
|4.3 Architecturally Significant Requirements and Quality Drivers ......................................................................................................................................................... 20|
|5. Constraints ............................................................................................................................................................................................................................................... 22|
|5.1 Constraint Interaction / Trade-off .................................................................................................................................................................................................... 23|
|5.2 Assumptions and Dependencies ....................................................................................................................................................................................................... 24|
|6. Requirements Traceability Matrix (RTM) ................................................................................................................................................................................................ 25|
|6.1 Matrix filled by Milestone 1 .............................................................................................................................................................................................................. 25|
|6.2 Matrix filled by Milestone 2 .............................................................................................................................................................................................................. 28|
|7. Risk Register............................................................................................................................................................................................................................................. 33<br>7.1 Index .................................................................................................................................................................................................................................................. 33|



|8. Forward Engineering Considerations ...................................................................................................................................................................................................... 47|
|---|
|9. Engineering Decision Log ........................................................................................................................................................................................................................ 50|
|9.1 Index .................................................................................................................................................................................................................................................. 50|
|10. GitHub & Team Governance ................................................................................................................................................................................................................. 64|
|10.1 Repository ....................................................................................................................................................................................................................................... 64|
|10.2 Team Working Agreement .............................................................................................................................................................................................................. 64|
|10.3 Repository structure and continuous integration .......................................................................................................................................................................... 65|
|11. AI Usage Register ................................................................................................................................................................................................................................... 65|
|12. Baseline Readiness Checklist ................................................................................................................................................................................................................. 68|
|Milestone 1 Readiness Checklist ............................................................................................................................................................................................................. 68|
|Milestone 2 Readiness Checklist ............................................................................................................................................................................................................. 68|
|13. Solution Design Baseline ....................................................................................................................................................................................................................... 69|
|13.1 Architecture ..................................................................................................................................................................................................................................... 69|
|13.1.1 Logical components ..................................................................................................................................................................................................................... 69|
|13.1.2 Considered alternatives ............................................................................................................................................................................................................... 70|
|13.1.3 Justification of selected architecture ........................................................................................................................................................................................... 71|
|13.1.4 Physical deployment .................................................................................................................................................................................................................... 71|
|13.1.5 Architectural diagrams ................................................................................................................................................................................................................. 72|
|13.2 Data and persistence baseline ............................................................................................................................................................................................................ 73|
|Intro ......................................................................................................................................................................................................................................................... 73|
|13.2.1 Core data entities ......................................................................................................................................................................................................................... 73|
|13.2.2 ERD ............................................................................................................................................................................................................................................... 74|
|13.2.3 Data Ownership and Lifecycle...................................................................................................................................................................................................... 75|
|13.2.4 Persistence model choice ............................................................................................................................................................................................................ 76|
|13.3 Tech Stack Analysis .............................................................................................................................................................................................................................. 77|
|13.3.1 Common web bundles ................................................................................................................................................................................................................. 77|
|13.3.2 Candidates not recommended .................................................................................................................................................................................................... 78|
|13.3.3 Weight matrix ............................................................................................................................................................................................................................... 78|
|13.3.4 Weight matrix for database ......................................................................................................................................................................................................... 79|



|13.3.5 Results of the matrices ................................................................................................................................................................................................................. 80|
|---|
|13.3.6 Selected versions and compatibility ............................................................................................................................................................................................ 80|
|13.4 Design Decisions .................................................................................................................................................................................................................................. 81|
|13.4.1 Design Problem 1: Status-Change Side Effects ............................................................................................................................................................................ 81|
|Problem ............................................................................................................................................................................................................................................... 81|
|Assignment 2 Alternatives .................................................................................................................................................................................................................. 82|
|Final Decision (adapts the Assignment 2 recommendation).............................................................................................................................................................. 82|
|Trade-off .............................................................................................................................................................................................................................................. 82|
|Class / Flow Diagram ........................................................................................................................................................................................................................... 82|
|Planned implementation (Milestone 3) .............................................................................................................................................................................................. 83|
|13.4.2 Design Problem 2: Category-Specific Request Handling ............................................................................................................................................................. 84|
|Problem ............................................................................................................................................................................................................................................... 84|
|Assignment 2 Alternatives .................................................................................................................................................................................................................. 84|
|Final Decision ....................................................................................................................................................................................................................................... 84|
|Trade-off .............................................................................................................................................................................................................................................. 84|
|Class Diagram ...................................................................................................................................................................................................................................... 84|
|Code Link ...............................................................................................................................................................................................**Error! Bookmark not defined.**|
|13.5 UI & Information Architecture ............................................................................................................................................................................................................ 86|
|13.5.1 Role Sitemap ................................................................................................................................................................................................................................ 86|
|13.5.2 Wireframes ................................................................................................................................................................................................................................... 86|
|13.5.3 Usability and Accessibility Rationale............................................................................................................................................................................................ 87|
|13.6 Interfaces and Integration ................................................................................................................................................................................................................... 89|
|13.6.1 Interface inventory ....................................................................................................................................................................................................................... 89|
|13.6.2 Page contracts .............................................................................................................................................................................................................................. 89|
|13.6.3 Failure behaviour ......................................................................................................................................................................................................................... 91|
|13.6.4 Internal event contract ................................................................................................................................................................................................................ 91|
|13.6.5 External interface: mail provider ................................................................................................................................................................................................. 91|
|13.6.6 Change and versioning ................................................................................................................................................................................................................. 92|
|13.6.7 Interactions not yet established .................................................................................................................................................................................................. 92|



|13.7 Deployment & Environments .............................................................................................................................................................................................................. 92|
|---|
|13.7.1 Environment Structure ................................................................................................................................................................................................................. 92|
|13.7.2 Configuration and Secrets ............................................................................................................................................................................................................ 92|
|13.7.3 Queue worker and scheduler....................................................................................................................................................................................................... 93|
|13.7.4 Free-Tier Limits ............................................................................................................................................................................................................................. 93|
|13.7.5 Backup and Recovery ................................................................................................................................................................................................................... 93|
|13.8 Open and Deferred Decisions ............................................................................................................................................................................................................. 93|
|References ................................................................................................................................................................................................................................................... 96|



## **Document Control** 

|**Version**|**Date**|**Author(s)**|**Summary of Change**|**Reviewed By**|
|---|---|---|---|---|
|0.1|07/09/2026|Xander|Initial Template|Michael,<br>Jared|
|0.2|07/09/2026|Xander|Sections 5, 7, 8, 9, 10 filled out|Michael|
|0.3|09/09/2026|Michael|Sections 4, 6 added||
|0.4|09/09/2026|Xander (AI-<br>assisted)|AI Usage Register entries added for Sections 5/7/8/9/10 AI assistance; Risk Register owners assigned;<br>Team Working Agreement drafted; FR-001 wording aligned between Section 4 and the RTM|Michael|
|0.5|09/09/2026|Jared|Sections 1, 2, 3 added to the main assignment|Michael|
|1.0|09/09/2026|Michael,<br>Jared,<br>Xander|Initial PED version 1.0 for Milestone 1 signed off|Michael,<br>Jared, Xander|
|1.2|22/09/2026|Michael|Spelling corrections. Section 2: lecturer added as stakeholder. Section 3: expanded the WhatsApp<br>exclusion, added the reasoning for excluding multi-language support and for the 5-year retention.<br>Section 5: rationale now cited. Section 6: restored a missing functional requirement|Jared, Xander|
|1.3|23/09/2026|Michael|Section 4: added 4.3 Architecturally Significant Requirements; Section 5: added constraint IDs and<br>CON-007 technology environment; Section 6: split the RTM into 6.1 (M1) and 6.2 (M2)|Jared, Xander|
|1.4|24/09/2026|Jared|Section 2: added the ASR influence column; Section 3: scope reviewed for v2.0; change record started|Michael,<br>Xander|
|1.5|25/09/2026|Michael|Section 13.1 architecture and 13.2 data and persistence baseline drafted|Jared, Xander|
|1.6|25/09/2026|Xander|Sections 13.4 design decisions, 13.5 UI and information architecture, 13.7 deployment and<br>environments drafted|Michael,<br>Jared|
|1.7|26/09/2026|Jared|Section 13.3 technology stack analysis and weighted matrices added|Michael,<br>Xander|
|1.8|26/09/2026|Xander|Section 7 risk register restructured per risk, R-008 to R-016 added; Section 8 Milestone 2 status added<br>and FEC 7 and 8 raised; Section 9 decision log restructured per decision, D-006 to D-015 added;<br>Section 10 governance updated|Michael,<br>Jared|



|**Version**|**Date**|**Author(s)**|**Summary of Change**|**Reviewed By**|
|---|---|---|---|---|
|1.9|26/09/2026|Jared|Change record completed (CR-001 to CR-016); Section 11 AI Usage Register updated for Milestone 2;<br>Section 12 Milestone 2 readiness checklist added; References extended|Michael,<br>Xander|
|1.9.1|27/09/2026|Jared|Team member names corrected; bundle ID B-002 corrected; checklist symbols removed; Section 10.3<br>repository structure added; NFR-009 and ASR-004 review wording changed; D-001 category list<br>changed; 13.2.1 requirement links updated|Michael,<br>Xander|
|1.10|27/09/2026|Jared|Consistency review applied: CR-017 recorded and SPA wording replaced in 13.1, 13.5 and 13.7; CR-<br>009, CR-012 and CR-013 applied; CR-018 and CR-019 raised; NFR-009, ASR-004 and D-001 restored to<br>the baseline wording; 13.2.4 completed; 5.2 assumptions and 13.3.6 versions added; references<br>corrected|Michael,<br>Xander|
|1.11|27/09/2026|Jared|Consistency review applied (part 2): 13.5 and 13.6 updated for Inertia; all six figures redrawn (Figures<br>13.1 to 13.6); Section 13 heading restored; references corrected in Source Manager|Michael,<br>Xander|
|2.0|30/09/2026|Jared|Grammer fixes such as section 13.7.2 was missing a full stop; Change document name to SEN381<br>Proto PED Milestone 2 V2.0|Michael,<br>Xander|



**Change record** 

|**CR**|**Date**|**Raised**<br>**by**|**Item**|**Change**|**Reason**|**Impact**|**Decision**|
|---|---|---|---|---|---|---|---|
|CR-<br>001|22/09/2026|Michael|FR-010|List the access levels explicitly|Requirement was not testable while<br>the levels were undecided|Requirements, RTM|Accepted|
|CR-<br>002|22/09/2026|Michael|FR-011|Elaborate on which access<br>levels may see what|Same reason as CR-001|Requirements, RTM|Accepted|
|CR-<br>003|23/09/2026|Michael|Section 6 RTM|Split the matrix into 6.1 (M1)<br>and 6.2 (M2)|Readability, and to show M2 evidence<br>separately from the M1 baseline|Traceability only|Accepted|
|CR-<br>004|23/09/2026|Michael|Section 5|Added constraint IDs and<br>CON-007 technology<br>environment|Constraints could not be referenced;<br>Master Brief section 25 makes the<br>team responsible for checking<br>technology availability on the BC<br>Desktop|Constraints, ASRs, D-<br>007|Accepted|
|CR-<br>005|23/09/2026|Jared|Section 2|Added the ASR influence<br>column|Links stakeholders to the drivers they<br>cause, as Milestone 2 section 5.2<br>requires|Stakeholder analysis,<br>section 4.3|Accepted|
|CR-<br>006|23/09/2026|Michael|Section 4|Added 4.3 Architecturally<br>Significant Requirements and<br>Quality Drivers|Milestone 2 requires ASRs to be<br>identified and linked to decisions|Requirements,<br>architecture, RTM|Accepted|
|CR-<br>007|26/09/2026|Xander|Section 7|Restructured the register one<br>risk per table and raised R-<br>008 to R-016|Register must be reviewed at every<br>milestone; new risks arise from the M2<br>decisions|Risk, deployment,<br>testing|Accepted|
|CR-<br>008|26/09/2026|Xander|Section 9|Restructured the log one<br>decision per table, closed D-<br>004 and D-005, added D-006<br>to D-015|Decisions made in M2 had no records,<br>and the log was unreadable as a single<br>table|Architecture, data,<br>technology, design,<br>deployment|Accepted|
|CR-<br>009|26/09/2026|Jared|FR-010, FR-<br>011|Access levels reduced to<br>three: Requester, Staff,<br>Management. Administrative<br>functions are handled by<br>Management for the MVP;a|The data model, policies and UI in<br>Section 13 are built on three roles, and<br>a fourth undefined role cannot be<br>tested|Requirements, data<br>model (users.role), D-<br>012, RTM, UI|Accepted|



|**CR**|**Date**|**Raised**<br>**by**|**Item**|**Change**|**Reason**|**Impact**|**Decision**|
|---|---|---|---|---|---|---|---|
|||||separate Admin role moves to<br>deferred scope||||
|CR-<br>010|26/09/2026|Xander|FR-017,<br>Section 8 FEC<br>2|Adopt the status set open,<br>assigned, in_progress,<br>resolved, closed, with<br>transitions seeded in a lookup<br>table|FR-017 requires pre-defined options,<br>and D-008 cannot enforce transitions<br>without a fixed set|Requirements, data<br>model, UI, D-015|Accepted|
|CR-<br>011|26/09/2026|Jared|Section 3.1,<br>FR-004, FR-<br>007|Confirm that email<br>notification of a status<br>change is in scope. Push<br>notifications remain deferred|Section 3.4 already names email as the<br>affordable replacement for WhatsApp,<br>and D-009 and D-011 depend on it|Scope, design,<br>deployment (queue<br>worker), R-009|Accepted|
|CR-<br>012|26/09/2026|Michael|FR-008, FR-<br>016, FR-020|One retention wording:<br>records are purged 5 years<br>after the last update, not 5<br>years after creation|Section 4 and the RTM contradicted<br>each other. The full history must be<br>kept until the whole record expires.|Requirements, data<br>model, retention job|Accepted|
|CR-<br>013|26/09/2026|Michael|NFR-001|Reword to bcrypt password<br>hashing with a cost factor of<br>12|“12 rounds of encryption” is not<br>testable and confuses hashing with<br>encryption|Requirement wording,<br>M3 test evidence|Accepted|
|CR-<br>014|26/09/2026|Xander|Section 8|Added Milestone 2 status and<br>resolution columns, and<br>raised FEC 7 and FEC 8|Forward considerations must show<br>progress, and deferring construction<br>created a new one|Forward<br>considerations, risk|Accepted|
|CR-<br>015|26/09/2026|Jared|Section 12|Added the Milestone 2<br>readiness checklist beside the<br>Milestone 1 list|The M1 checklist does not cover the<br>M2 baseline|Checklist only|Accepted|
|CR-<br>016|26/09/2026|Michael|Section 6.2|Extended the M2 matrix with<br>implementation evidence,<br>verification evidence and<br>status columns|Milestone 2 section 4.2 requires these<br>columns, with a controlled status<br>where evidence does not yet exist|Traceability, M3<br>planning|Accepted|
|CR-<br>017|26/09/2026|Jared|D-006, D-007,<br>Sections 13.1,<br>13.5,13.6,|Presentation layer changed<br>from a separate React SPA<br>over a Laravel JSON API to|The section 13.3.3 matrix ranked B-009<br>(Laravel + Inertia) highest. One<br>deployable with server-side session|Architecture,<br>deployment topology,<br>authentication(D-012),|Accepted|



|**CR**|**Date**|**Raised**<br>**by**|**Item**|**Change**|**Reason**|**Impact**|**Decision**|
|---|---|---|---|---|---|---|---|
||||13.7, R-010, R-<br>016|React and TypeScript<br>rendered through Inertia.|authentication removes CORS and<br>token handling, and no requirement<br>needs an independent client or<br>independent scaling (Assignment 2,<br>Task 3).|interfaces, UI structure;<br>R-010 reduced; R-016<br>raised.||
|CR-<br>018|27/09/2026|Jared|FR-005, FR-<br>013, FR-021,<br>FR-022, NFR-<br>007, NFR-008|Wording changed so each<br>requirement is testable and<br>matches the design.|FR-005’s title and criteria disagreed;<br>FR-013 said filters remove relevant<br>entries; FR-021 described deletion<br>although resolution is a status change;<br>FR-022 was not testable; the 95%<br>targets were not supported by the<br>cited source.|Requirements, RTM<br>6.1, M3 test design.|Accepted|
|CR-<br>019|27/09/2026|Jared|Section 4.1<br>note, ASR-<br>005, CR-012<br>reason,<br>references|Basis for the 5-year retention<br>changed from RICA to a<br>stakeholder decision<br>balanced against POPIA<br>section 14.|RICA section 30 applies to<br>telecommunication service providers,<br>which CivicConnect is not.|Requirement wording,<br>ASR-005, reference list.|Accepted|



### **Baseline Approval** 

|Project|CivicConnect|
|---|---|
|Baseline Type|Engineering Baseline (PED)|
|Version|1.0|
|Date|09/09/2026|
|Scope reviewed (Y/N)|Y|
|Requirements/traceability checked (Y/N)|Y|
|Repository/governance controls checked (Y/N)|Y|
|Outcome (ACCEPTED / CONDITIONALLY<br>ACCEPTED / REVISION REQUIRED)|Conditionally accepted|



### **Baseline Approval (Milestone 2)** 

|Project|CivicConnect|
|---|---|
|Baseline Type|Engineering Baseline (PED)|
|Version|2.0|
|Date|30/09/2026|
|Scope reviewed (Y/N)|N/A|



|Requirements/traceability checked (Y/N)|N/A|
|---|---|
|Repository/governance controls checked (Y/N)|N/A|
|Outcome (ACCEPTED / CONDITIONALLY|N/A|
|ACCEPTED / REVISION REQUIRED)||



## **1. Problem Statement & Business Need** 

### **1.1 Problem Statement** 

A community-focused organisation CivicConnect currently manages their services requests such as facility faults, IT support, maintenance, lost property and security concerns across email, phone, WhatsApp, spreadsheets, and paper. This produces duplicated or lost requests, no visibility for requesters, no reliable reporting for management and no clear ownership for staff. 

### **1.2 Business Need** 

CivicConnect needs a controlled digital platform that provides a reliable, traceable and usable way to submit, manage, monitor and report on service requests. 

### **1.3 Intended Business Value** 

CivicConnect business value is a single controlled record of a request’s lifecycle. Submit to categorise to assign to act to resolve then report which closes the accountability gap that their current infrastructure has. 

## **2. Stakeholder Analysis** 

|**Stakeholder**|**Role /**<br>**Description**|**Needs / Expectations**|**Influence**|**Interest**|**Potential Conflicts**|**ASR influence**|
|---|---|---|---|---|---|---|
|Requester|Community<br>member<br>submitting<br>service requests|Visibility of request,<br>Progress of request,<br>Easy interface,<br>Feedback|Low|High|Wants the request to be handled fast<br>and wants full visibility|ASR-003, as the requester is harmed<br>when requests are lost.|
|Staff|Actions and<br>resolves<br>requests|Clear ownership,<br>interaction with requests|Medium|High|Admin overhead.<br>Wants fewer notifications<br>Could cause problems with requesters<br>full visibility|<br>ASR-001 and ASR-002, as the query<br>response time and authorisation<br>affect the staff.|
|Management|Oversight and<br>reporting|Accountability,<br>Reports,<br>Overdue tracking|High|High|Wants detailed reporting which could<br>conflict with staff's "less admin"<br>preference|ASR-001 and ASR-002, as<br>management requires access to all<br>records. The Overdue tracking is also<br>demanding on performance|



|**Stakeholder**|**Role /**<br>**Description**|**Needs / Expectations**|**Influence**|**Interest**|**Potential Conflicts**|**ASR influence**|
|---|---|---|---|---|---|---|
|System<br>owner|Scoping the<br>system|Scope and setting<br>boundaries,<br>Defendable system<br>choices|High|High|Wants breadth of features while team<br>has schedule and cost constraints|ASR-004, ASR-005, and ASR-006. The<br>owner set the cost constraint, and is<br>thus responsible for ASR-006, but<br>retention conflicts with ASR-005 and<br>a three-person team limits ASR-004|
|Security<br>team|Data handling|How the data is handled,<br>Where the data is stored,<br>How the data is stored,|Medium|High|May impact speed to make sure data<br>is completely secured|ASR-002 and ASR-005, as the data<br>requires authorisation, and raises a<br>cross-cutting concern.|
|IT team|Service being<br>requested|Alerted of request,<br>Interaction with request,|Medium|Medium|Wants to be thorough with the<br>request and requester wants to have<br>it handled fast|ASR-002, as the IT support is a<br>potentially sensitive category.<br>Routing and restricting a request is<br>thus governed by the IT team.|
|Lecturer|Oversees the<br>life cycle of the<br>application’s<br>creation|As the client substitute,<br>expects a working<br>solution that satisfies the<br>business need.<br>As Assessor, expects<br>traceable evidence to be<br>continuously produced<br>as the project<br>progresses.|High|High|As the substitute for the client, they<br>are the only source of clarification.<br>However, information can be<br>withheld to test the skills,<br>understanding and judgement of the<br>developers.<br>As assessor they need access to the<br>evidence. Time spent on evidence<br>competes with time to create the<br>application.|ASR-004. Maintainability is highly<br>required, as the habits and<br>continuously produced evidence for<br>reviewability is highly required by<br>the Assessor.|



## **3. Scope Baseline** 

### **3.1 In Scope** 

- Requests must be detailed such as category, location, area and short description 

- Category must be selectable 

- Staff be able to view, search, filter and sort requests and assign, accept, add comments, and update status 

- Management must have a dashboard to view requests by status, category, overdue and basic activity reporting 

- Role based access 

- email notification of a status change 

### **3.2 Out of Scope** 

- Native mobile app 

- Multi-language support 

- WhatsApp, SMS and or telephone integration 

### **3.3 Deferred / Future Scope** 

- Push notifications 

- Power BI style reporting to view advance analytics 

- Separate Administrator role. 

### **3.4 Defended Exclusion / Deferment** 

WhatsApp integration is excluded because it depends on a paid third-party API, which conflicts with the cost constraint (CON-003) and adds a reliability dependency. Status notifications are sent outside the customer’s 24-hour service window, so they must use utility template messages, which Meta charges per message (Meta, 2026). SMS and telephone integration are excluded for the same cost reason. Email notification of status changes, which is in scope (Section 3.1), meets the same need at no cost. Multi-language support is excluded because most translation services charge per word (Translated, 2025) **.** 

### **3.5 Scope baseline reviews and changes** 

The scope baseline was reviewed for PED v2.0. No capabilities were added or removed. One clarification was made under change control: CR-011 confirms that email notification of a status change is in scope, which Section 3.4 already anticipated when it named email as the affordable replacement for WhatsApp notification. Push notifications and Power BI style analytics remain in deferred scope. CR-009 reduced the access levels to three roles and moved a separate Administrator role to deferred scope (Section 3.3). Administrative functions are carried by Management for the MVP. 

## **4. Requirements & Acceptance Criteria** 

### **4.1 Functional Requirements** 

All mentions of data being kept for 5 years is in accordance with the RICA compliance laws (VerifyNow, 2025). This is balanced against the POPIA act, section 14, where personal information should not be kept longer than necessary (POPIA, 2019). 

|**ID**|**Requirement**|**Source /**<br>**Stakeholder**|**Priority**|**Acceptance Criteria**|
|---|---|---|---|---|
|FR-001|Submission: being able to<br>accept a new submission|Requester|High|The form used to submit must be saved into a database.|
|FR-002|Submission: return of<br>requested data|Requester|High|The requested data must be able to reach the requester.|
|FR-003|Submission: Validation|Requester|Medium|Form submission must not be allowed if the form does not adhere to submission<br>rules|
|FR-004|Submission: feedback|Requester|Medium|Upon submission attempt, the user must receive feedback. If it’s an error, the<br>problem must be stated. If successful, the user must be notified|
|FR-005|Sort requests|Requester|Low|A requester can sort their requests by date submitted, date last changed or<br>alphabetical order (changed by CR-018)|
|FR-006|View Status: Accuracy|Requester|Medium|The status displayed must display the correct status.|
|FR-007|View Status: Delay|Requester|Medium|From the moment the status is updated on the database, the viewer must be able<br>to see the update if they were to refresh on their end|
|FR-008|View History: Time data is<br>stored|Requester|Low|A request and its related history, assignment and comment records are retained<br>for 5 years after the last update, then purged automatically (changed by CR-012,<br>v2.0)|



|**ID**|**Requirement**|**Source /**<br>**Stakeholder**|**Priority**|**Acceptance Criteria**|
|---|---|---|---|---|
|FR-009|View History: Amount|Requester|Medium|The Requester will see the last 50 requests per page. The Requester can go to<br>other pages to see older requests.|
|FR-010|Authentication|All users (Staff,<br>management and<br>requesters)|High|Users are assigned exactly one of three roles: Requester, Staff or Management. A<br>user may only reach data permitted to that role (changed by CR-009, v2.0)|
|FR-011|Authorised requests by<br>staff|Staff|Medium|A Requester sees only their own requests. Staff see unassigned requests and<br>requests assigned to them, not those assigned to other staff. Management sees all<br>requests. Verified by a test per role and endpoint (changed by CR-009, v2.0)|
|FR-012|Views provide relevant<br>information|Staff|Low|The views should provide enough information in order to make relevant decisions.<br>Examples being: category, status, time submitted, who is assigned to the request.|
|FR-013|Search, filter and sort|Staff/Management|Low|Search returns every matching request the user is authorised to see; a filter<br>removes non-matching requests; sort orders results by the chosen column<br>(alphabetical, last updated, etc.). No combination of search, filter and sort causes<br>an error or shows unauthorised data (changed by CR-018)|
|FR-014|View full details|Staff|Medium|Tying in with the previous requirement, the view must be expandable to access<br>more information on the request. A change history should also be accessible.|
|FR-015|Assign/Accept responsibility|Staff|High|Staff should be able to assign requests to themselves. They should also be able to<br>offer requests to others, and accept requests offered to them.|
|FR-016|Request accountability|Staff|Low|Every assignment, offer and acceptance is recorded with the staff member and<br>timestamp and retained for 5 years after the request’s last update, then purged<br>automatically (changed by CR-012, v2.0).|
|FR-017|Request update validation|Staff|Low|A status may only change to one of the seeded values open, assigned, in progress,<br>resolved or closed, and only along a transition listed in the transition table<br>(changed by CR-010, v2.0)|



|**ID**|**Requirement**|**Source /**<br>**Stakeholder**|**Priority**|**Acceptance Criteria**|
|---|---|---|---|---|
|FR-018|authorisation of request<br>update|Staff|Medium|Only authorised staff should be allowed to make updates. For example, staff should<br>not be allowed to change the status of a request not assigned to them.|
|FR-019|Controlled transitions|Staff|Low|Every status change records the staff member and timestamp; the history is<br>retained for 5 years after the request’s last update (changed by CR-012, v2.0).|
|FR-020|Add comments/resolution<br>information|Staff|Medium|Staff can add comments and resolution notes to a request; they are retained for 5<br>years after the request’s last update (changed by CR-012, v2.0).|
|FR-021|Resolve Requests|Staff|High|When a request has been fulfilled, the assigned staff member can move it to<br>Resolved. It leaves their active list but stays in the system with its full history<br>(changed by CR-018)|
|FR-022|View all information|Management|Medium|Management can view every request with its full status history, assignments and<br>comments (changed by CR-018)|
|FR-023|Identify overdue|Management|Low|Requests will have to be resolved within a set amount of time. Early estimates sets<br>it at within 7 days.|
|FR-024|Access information for<br>performance analysis|Management|Low|Management should have access to the additional information given by the staff<br>members in order to review the resolved requests. Basic analysis features, such as<br>average time to resolution could also be added.|



### **4.2 Non-Functional Requirements** 

The following NFR’s are created making use of the ISO/IEC 25010 (ISO, 2023) standards for software product quality as reference. 

|**ID**|**Requirement**|**Category**|**Source**|**Priority**|**Acceptance Criteria**|
|---|---|---|---|---|---|
|NFR-<br>001|Credential Storage|Security|Master Brief|High|Passwords are stored only as bcrypt hashes with a cost factor of 12; plain-text<br>passwords are never stored or logged (changed by CR-013)|
|NFR-<br>002|Authorisation Enforced|Security|Master Brief,<br>Requester|High|Users can only access data that they are allowed to access. For instance, a<br>requester can’t access the credentials of other requesters|
|NFR-<br>003|Status Changes with user<br>and timestamp|Security|Master Brief,<br>Management|Medium|If a status change is made, an accurate timestamp must be made and the last<br>user to change the status must be recorded.|
|NFR-<br>004|Result return time|Performanc<br>e|Staff|Medium|For each 100 entries returned, there will be a maximum of 1 second wait time<br>regardless of filters applied (Nielsen, 1993). This does not include Internet lag<br>as we are not responsible for the internet connection.|
|NFR-<br>005|Availability|Reliability|Management|Medium|The application is available 99% of the time during operating hours, defined as<br>08:00-18:00, Monday to Friday (Tovarys, 2022).|
|NFR-<br>006|Data recovery|Reliability|Master Brief|Low|In case of data loss or overwriting, data must be able to be recovered/ rolled<br>back within 48 hours|
|NFR-<br>007|Ease of use|Interaction<br>capability|Requester|Low|At least 80% of test users submit a request without help on their first attempt<br>(the average task-completion rate across studies is 78%). All functions are at<br>most 3 clicks from the main screen (changed by CR-018) (Jeff, 2011).|
|NFR-<br>008|Validation failures<br>produce actionable<br>messages.|Interaction<br>capability|Requester|Low|In a usability walkthrough, every validation message names the field<br>concerned and states how to correct it (changed by CR-018) (Jeff, 2011).|
|NFR-<br>009|Code follows<br>documented standards|Maintainabil<br>ity|Team|Medium|Every pull request merged to main has approvals from the two team members<br>who did not author it, verified from the GitHub history. Code follows the<br>standards in Section 10.2.|



### **4.3 Architecturally Significant Requirements and Quality Drivers** 

|**ID**|**Requirement**|**Quality**<br>**attribute**|**Significance**|**Measurable response**|**Decision**<br>**reference**|
|---|---|---|---|---|---|
|ASR-<br>001|NFR-004, FR-<br>013|Performance|The speed at which a response must be given is bound, with<br>the “worst-case” query having a limit of 1 second response<br>time per 100 records. This means that optimisation will be<br>required, such as indexing. It also conflicts with the<br>constraint of a free-tier service, as it caps connections and<br>computational capacity.|For every 100 entries,<br>regardless of filter<br>combination, may not add<br>more than 1 second to the<br>search.|D-006<br>architecture, D-<br>007 stack, D-013<br>deployment|
|ASR-<br>002|FR-010, FR-<br>011, FR-013,<br>FR-017, FR-<br>018|Security|Different roles will require different views on the same data.<br>Searches and filters will also cause the security to be a<br>dedicated layer instead of a set of guards on the code,<br>meaning that it will highly affect the querying system.|No query made may return<br>records outside the<br>authorisation level of the<br>specific user, regardless of the<br>combination of filters applied.|D-002 audit<br>history, D-012<br>authentication<br>and RBAC, D-015<br>transitions|
|ASR-<br>003|FR-001, FR-<br>002, NFR-005,<br>NFR-006|Reliability/<br>availability|A main issue CivicConnect is supposed to solve is requests<br>being lost. Requests must be submitted, or a report must be<br>given. Requests that has been stored must be recoverable<br>when lost.|No submissions may be simply<br>lost. Submission failures must<br>be indicated, successful<br>submissions must be<br>acknowledged. Furthermore,<br>overwritten data must be<br>recoverable within 48 hours.<br>99% availability during<br>operating hours is also<br>required.|D-008<br>transactional<br>write, D-011<br>notification<br>integration, D-<br>013 deployment|
|ASR-<br>004|NFR-009,<br>Forward<br>Engineering<br>Consideration<br>(FEC)|Maintainability|A three-person team on a fixed schedule with fixed<br>milestones will have trouble with any re-work that may<br>need to be done, and the FEC notes that retrofitting will be<br>difficult and expensive to do.|Core workflow logic stays in<br>service classes, and every<br>change to main is approved by<br>the two non-author team<br>members before merging.|D-006<br>architecture, D-<br>009 side-effect<br>split, D-010<br>category strategy|



|**ID**|**Requirement**|**Quality**<br>**attribute**|**Significance**|**Measurable response**|**Decision**<br>**reference**|
|---|---|---|---|---|---|
|ASR-<br>005|FR-008, FR-<br>016, FR-019,<br>FR-020|scalability|The requirement of data needing to be stored for at least 5<br>years conflicts with the free-tier services that are required in<br>the constraints.  It also conflicts with the RICA laws, as data<br>must be stored for at least 5 years. Furthermore, the POPIA<br>act also requires the data to be deleted once the retention<br>period ends.|Data cannot be lost or<br>overwritten before the<br>minimum 5 years due to limited<br>storage.|D-008<br>persistence, D-<br>013 deployment,<br>CR-012 retention|
|ASR-<br>006|FR-008, FR-<br>016, FR-019,<br>FR-020, NFR-<br>006|Cost|CivicConnect needs to make use of free-tier software and<br>services. This will mean that the 5-year retention rules will<br>need to be able to handle the accumulated data over the 5<br>years without deleting anything.|Estimation and calculation of a<br>5-year data volume, and<br>comparing it to the capacity<br>provided by the free-tier<br>services. Automated purges<br>after 5 years.|D-007 stack, D-<br>013 deployment,<br>R-002 and R-013<br>free-tier limits|



## **5. Constraints** 

|**ID**|**Category**|**Constraint**|**Engineering Implication**|
|---|---|---|---|
|CON-001|Scope|Exactly 3 registered students must deliver<br>a controlled MVP covering requester, staff<br>and management capabilities within the<br>SEN381 timeline; once approved,<br>committed scope must be baselined and<br>controlled.|Every additional feature beyond the baseline (e.g.<br>SMS notifications, map-based tagging) creates<br>obligations to specify, secure, test and maintain it.<br>Non-essential capabilities must go to Section 3.3<br>(Deferred/Future Scope) rather than be absorbed<br>silently, to protect schedule and quality.|
|CON-002|Schedule|The project must progress through four<br>formal milestones (M1 - M4) within the<br>SEN381 delivery period, each building on<br>the previous approved baseline.|Requirements and early decisions made under this<br>timeline must be evidence-based rather than rushed.<br>Research must be timeboxed (as in Assignment 1) and<br>baselined items may only be re-decided through a<br>formal change record (Master Brief section 14), not<br>informally.|
|CON-003|Cost / Resources|The team should prefer free or low-cost<br>services where practical but must identify<br>their limitations and the likely operational<br>cost beyond the educational context.|Free-tier hosting/database services typically cap<br>storage, connection counts, request quotas or<br>uptime, for example, Supabase’s free tier provides a<br>500 MB database and pauses projects after one week<br>of inactivity (supabase, 2026).These limits must be<br>documented during M2 technology research so the<br>data model and expected request volume are sized<br>realistically rather than assumed unlimited.|
|CON-004|Quality|Quality attributes (e.g. traceability of<br>request status, data accuracy, usability for<br>non-technical requesters) must be defined<br>now and later supported by measurable<br>test/acceptance evidence.|NFRs must be written in testable terms at M1 (Section<br>4.2) - e.g. a measurable response-time or data-<br>integrity condition - rather than as vague aspirations,<br>so M3 testing has something concrete to verify<br>against.|



|**ID**|**Category**|**Constraint**|**Engineering Implication**|
|---|---|---|---|
|CON-005|Security|Security is a lifecycle-wide responsibility.<br>CivicConnect handles categories such as<br>security concerns and IT support requests<br>that may contain sensitive personal or<br>safety-related information.|Access control (RBAC) and an audit trail must be<br>treated as foundational requirements at M1 (see<br>Forward Engineering Consideration #1 and #6, Section<br>8), not features added just before deployment -<br>deferring this risks a costly late redesign of the data<br>model and authentication approach.|
|CON-006|Team Capability|Only three registered students must<br>analyse, design, build, secure, test, deploy<br>and individually defend the entire system<br>(Master Brief section 8).|The team must weigh unfamiliar-but-powerful<br>technology against realistic learning-curve risk within<br>the schedule constraint above - this interaction is<br>expanded in Section 5.1 and tracked as Risk R-001<br>(Section 7).|
|CON-007|Technology<br>environment|Tools must run on the BC Desktop or team<br>laptops so the lecturer can assess the<br>project in the institutional environment.<br>Master Brief section 25 states the<br>institution cannot guarantee that a<br>chosen technology is available, so the<br>team is responsible for checking this (R-<br>014)|This helps the lecturer to assess the project on their<br>end and to see how each part integrates with each<br>other (the master brief also lists this as a requirement<br>section 25)|



### **5.1 Constraint Interaction / Trade-off** 

Cost/Resources interacts directly with Security. The preference for free-tier hosting and database services (driven by the Cost constraint) is likely to mean weaker default network isolation, connection encryption or backup guarantees than a paid tier would provide. If the team selects a free-tier service purely because it is easiest to set up, without evaluating its security posture, the cost saving could increase security risk exposure for the sensitive request categories described in the Business Need (Section 1). This trade-off must therefore be evaluated explicitly in the M2 weighted technology decision matrix (Master Brief section 18.1) - security ecosystem is one of the required comparison criteria, rather than defaulting to whichever free service is fastest to configure under schedule pressure. 

Schedule also interacts with Quality: because M1 - M4 are fixed and non-negotiable, a team that loses time to an unplanned issue (e.g. Risk R-001, technology learning curve) may be tempted to informally cut test coverage to protect the delivery date. The Master Project Brief (section 18) requires that any such trade-off be recorded and approved rather than silently absorbed, which is why Section 9 (Engineering Decision Log) and Section 7 (Risk Register) exist as controlled places to make that trade-off visible instead of hidden. 

Milestone 2 outcome: the cost and security trade-off was resolved in D-007 and D-013 rather than left open. The selected stack is free and open source, and the deployment topology was deliberately reduced to one web process, one queue worker and one managed PostgreSQL instance so that the platform fits a free tier without weakening the security posture. Security ecosystem carried 12 of the 100 points in the section 13.3 weighted matrix, so the trade-off was scored rather than assumed. The remaining exposure is the hosting provider itself, which is tracked as R-002 and R-013 and closes with D-013 

### **5.2 Assumptions and Dependencies** 

|**ID**|**Assumption / dependency**|**Relied on by**|**If it proves false**|**Risk / decision**|**Owner**|**Status**|
|---|---|---|---|---|---|---|
|AS-001|The chosen free-tier host can run a<br>persistent queue worker and Laravel’s<br>scheduler (or a cron entry).|D-011, D-013, FR-<br>008|Notifications are queued but<br>never sent; the retention purge<br>never runs.|R-009, OD-01|Michael|Open - verify when OD-<br>01 closes|
|AS-002|PHP 8.3+, Composer, Node.js and<br>PostgreSQL (or Docker) can be installed<br>and run on the BC Desktop or team<br>laptops.|CON-007, D-007|The project cannot be built or<br>assessed in the institutional<br>environment.|R-014|Jared|Open - test at the start<br>of M3|
|AS-003|Five years of request data fits within<br>the free-tier database storage cap.|ASR-005, ASR-<br>006, FR-008|Storage fills before the retention<br>period ends.|R-002, R-013|Michael|Open - volume<br>estimate required|
|AS-004|A free email provider allows enough<br>daily sends for status notifications.|D-011, FR-007|Notifications fail or need a paid<br>plan.|OD-02, R-009|Jared|Open|
|AS-005|Every requester has an email address<br>and a web browser.|Scope 3.1-3.2,<br>FR-007|Some requesters cannot receive<br>notifications or use the platform.|R-007|Xander|Assumed|
|AS-006|Three roles (Requester, Staff,<br>Management) are enough for the MVP.|CR-009, D-012|Department sub-roles would<br>change the policy layer and data<br>model.|OD-03, R-003|Jared|Assumed|
|AS-007|Laravel 13, Inertia and the React starter<br>kit stay supported for the project’s<br>lifetime.|D-007, 13.3.6|A forced upgrade or migration<br>during construction.|R-001|Xander|Assumed - Laravel 13<br>security fixes run to Q1<br>2028|



**6. Requirements Traceability Matrix (RTM)** 

### **6.1 Matrix filled by Milestone 1** 

|**Req ID**|**Stakehold**<br>**er /Source**|**Requirement Summary**|**Acceptance Criteria**|**Priority**|
|---|---|---|---|---|
|FR-001|Requester|Submission: being able to accept a<br>new submission|The form used to submit must be saved into a database.|High|
|FR-002|Requester|Requested data is received|The requested data must be able to reach the requester from the database.|High|
|FR-003|Requester|Input validation|Upon submission attempt, the user must receive feedback. If it’s an error, the problem<br>must be stated. If successful, the user must be notified|Medium|
|FR-004|Requester|Requester will receive feedback on<br>submission|Upon submission attempt, the user must receive feedback. If it’s an error, the problem<br>must be stated. If successful, the user must be notified|Medium|
|FR-005|Requester|Requests can be categorised and<br>sorted|On selection, requests must be categorise according to date submitted, date changed,<br>or alphabetical order (Superseded by CR-018)|Low|
|FR-006|Requester|Accuracy of status|The status displayed must display the correct status.|Medium|
|FR-007|Requester|The status, upon update, must be<br>visible to the requester|From the moment the status is updated on the database, the viewer must be able to see<br>the update if they were to refresh on their end|<br>Medium|
|FR-008|Requester|The data must be stored for a set<br>amount of time|The data must be stored for at least 5 years after the last update before it is<br>automatically deleted (Superseded by CR-012)|Low|
|FR-009|Requester|The requester must be able to view<br>more than one request at a time|The Requester will see the last 50 requests per page. The Requester can go to other<br>pages to see older requests.|Medium|
|FR-010|All users of<br>the system|User Authentication|Users can only access data that corresponds with their access levels/roles. These levels<br>are still to be decided (Superseded by CR-009)|High|



|**Req ID**|**Stakehold**<br>**er /Source**|**Requirement Summary**|**Acceptance Criteria**|**Priority**|
|---|---|---|---|---|
|FR-011|Staff|Authorised requests by staff|When the rules/authority levels are set, test to see that all users can only see what they<br>are authorised to see (Superseded by CR-009)|Medium|
|FR-012|Staff|Views provide limited but relevant<br>information for quick overview|The views should provide enough information in order to make relevant decisions.<br>Examples being: category, status, time submitted, who is assigned to the request.|Low|
|FR-013|Staff/<br>Managem<br>ent|Search, filter and sort features|Search shows all valid data entries, filter removes all relevant data entries, and sort<br>correctly sorts the data according to choice (alphabetical, last updated, etc.).<br>Combinations of sorting, filtering and searching should not cause errors or show<br>unauthorised data. (Superseded by CR-018)|Low|
|FR-014|Staff|View full details on demand|Tying in with the previous requirement, the view must be expandable to access more<br>information on the request. A change history should also be accessible.|Medium|
|FR-015|Staff|Assign/Accept responsibility of a<br>request|Staff should be able to assign requests to themselves. They should also be able to offer<br>requests to others, and accept requests offered to them.|High|
|FR-016|Staff|Accountability of handling request|Assignments should be recorded and stored in the database for review. These<br>recordings should stay for only 5 years before deletion. (Superseded by CR-012)|Low|
|FR-017|Staff|Update validation of Requests|Status updates can only be changed to pre-defined options. (Superseded by CR-010)|Low|
|FR-018|Staff /<br>Managem<br>ent|authorisation of request update|Only authorised staff should be allowed to make updates. For example, staff should not<br>be allowed to change the status of a request not assigned to them.|Medium|
|FR-019|Staff|Controlled transitions|If updates are made, the staff member and timestamp must be recorded, and stored for<br>5 years. (Superseded by CR-012)|<br>Low|
|FR-020|Staff|Add comments/resolution<br>information|Staff should be able to add additional information to requests, and this should also be<br>saved for 5 years in the database. (Superseded by CR-012)|Medium|



|**Req ID**|**Stakehold**<br>**er /Source**|**Requirement Summary**|**Acceptance Criteria**|**Priority**|
|---|---|---|---|---|
|FR-021|Staff|Resolve Requests|When a request has been fulfilled, staff should be able to remove the request from their<br>assigned requests (Superseded by CR-018)|<br>High|
|FR-022|Managem<br>ent|View additional information|Management will likely need full access to all information in the database. (Superseded<br>by CR-018)|Medium|
|FR-023|Managem<br>ent|Identify overdue requests|Requests will have to be resolved within a set amount of time. Early estimates sets it at<br>within 7 days.|Low|
|FR-024|Managem<br>ent|Access information for performance<br>analysis|Management should have access to the additional information given by the staff<br>members in order to review the resolved requests. Basic analysis features, such as<br>average time to resolution could also be added.|Low|



### **6.2 Matrix filled by Milestone 2** 

|**Req**<br>**ID**|**ASR**|**Module /**<br>**component**|**Data and**<br>**persistence impact**|**Design /**<br>**interface**<br>**decision**|**Technolog**<br>**y and**<br>**decision**<br>**reference**|**Implementation**<br>**evidence**|**Verification**<br>**evidence**|**Status**|
|---|---|---|---|---|---|---|---|---|
|FR-<br>001|ASR-003|Submission|Inserts a row in<br>service_requests;<br>commits before the<br>response is returned|Controller<br>validates, then<br>delegates to<br>the<br>submission<br>service|D-006, D-<br>007, D-010|Planned M3:<br>RequestSubmissionServic<br>e|Planned M3:<br>submission feature<br>test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>002|ASR-003|RequestQuery|Reads<br>service_requests<br>only|Reads go<br>through the<br>data-access<br>layer, not ad<br>hoc queries|D-006, D-<br>007|Planned M3: requester<br>list page|Planned M3:<br>feature test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>003|ASR-003|Submission|Constraints<br>enforced by schema<br>and validation rules|Validation<br>contract in the<br>form request,<br>merged with<br>category rules|D-007, D-<br>010|Planned M3:<br>StoreServiceRequest|Planned M3:<br>validation test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>004|ASR-003|Submission|None; no write of its<br>own|Response<br>always returns<br>a confirmation<br>or a specific<br>field error|D-007, CR-<br>011|Planned M3: submission<br>response handling|Planned M3:<br>feature test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>005|ASR-001|RequestQuery<br>, Category<br>handling|Sorting applied to<br>indexed columns|Sorting<br>applied in the<br>query, not in<br>the client|D-001, D-<br>010|Planned M3: query<br>scopes|Planned M3:<br>sorting test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>006|ASR-002|StatusAudit|Current status must<br>agree with the<br>history|One source of<br>truth for<br>status|D-002, D-<br>008, D-015|Planned M3:<br>RequestStatusService|Planned M3:<br>consistency test|Approved,<br>not yet<br>implemente<br>d|



|**Req**<br>**ID**|**ASR**|**Module /**<br>**component**|**Data and**<br>**persistence impact**|**Design /**<br>**interface**<br>**decision**|**Technolog**<br>**y and**<br>**decision**<br>**reference**|**Implementation**<br>**evidence**|**Verification**<br>**evidence**|**Status**|
|---|---|---|---|---|---|---|---|---|
|FR-<br>007|ASR-003|StatusAudit,<br>Notification|New status visible<br>on the next read|Notification<br>consumes the<br>event; it is not<br>a step inside<br>the update|D-009, D-<br>011, CR-<br>011|Planned M3:<br>RequestStatusChanged<br>event|Planned M3:<br>listener test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>008|ASR-005,<br>ASR-006|Retention|Records purged 5<br>years after last<br>update|Scheduled<br>purge<br>command|CR-012, D-<br>013|Planned M3: purge<br>command|Planned M3:<br>retention test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>009|ASR-001,<br>ASR-002|RequestQuery|Indexed pagination,<br>50 rows per page|Pagination<br>mandatory on<br>every list view|D-007|Planned M3: paginated<br>queries|Planned M3:<br>pagination test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>010|ASR-002|Authenticatio<br>n|users.role holds one<br>of three roles|Session<br>authentication<br>; policies<br>applied at the<br>data layer|D-012, CR-<br>009|Planned M3:<br>authentication<br>scaffolding|Planned M3: login<br>and role tests|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>011|ASR-002|Authorisation|Role determines<br>which rows are<br>visible|Enforced in<br>the data layer,<br>not per screen|D-012, CR-<br>009|Planned M3:<br>ServiceRequestPolicy|Planned M3: policy<br>test per role|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>012|ASR-001|RequestQuery|Reads only the fields<br>the summary needs|Summary view<br>separate from<br>the detail read|D-006|Planned M3: list page<br>props|Planned M3:<br>feature test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>013|ASR-001,<br>ASR-002|RequestQuery<br>,<br>Authorisation|Filters and sorts<br>scoped by<br>authorisation|Sortable<br>columns<br>whitelisted;<br>authorisation|D-012|Planned M3: query<br>builder|Planned M3:<br>combination and<br>authorisation tests|Approved,<br>not yet<br>implemente<br>d|



|**Req**<br>**ID**|**ASR**|**Module /**<br>**component**|**Data and**<br>**persistence impact**|**Design /**<br>**interface**<br>**decision**|**Technolog**<br>**y and**<br>**decision**<br>**reference**|**Implementation**<br>**evidence**|**Verification**<br>**evidence**|**Status**|
|---|---|---|---|---|---|---|---|---|
|||||applied in the<br>query|||||
|FR-<br>014|ASR-002|StatusAudit|Detail read includes<br>the change history|Detail page<br>receives<br>history with<br>the request|D-002|Planned M3: detail page<br>props|Planned M3:<br>feature test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>015|ASR-004|Assignment|Writes<br>request_assignment<br>s rows|Offer and<br>accept<br>workflow<br>rather than<br>direct<br>reassignment|D-006|Planned M3:<br>AssignmentService|Planned M3:<br>assignment test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>016|ASR-002,<br>ASR-005|Assignment,<br>StatusAudit|Assignment history<br>retained for 5 years<br>after last update|Assignment<br>changes<br>recorded the<br>same way<br>status changes<br>are|D-002, CR-<br>012|Planned M3: assignment<br>history|Planned M3:<br>retention and audit<br>test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>017|ASR-002|StatusAudit|Status limited to the<br>seeded<br>enumeration|Transition<br>validated in<br>the service<br>and backed by<br>a database<br>constraint|D-015, CR-<br>010|Planned M3: transition<br>table and enum|Planned M3: illegal<br>transition test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>018|ASR-002|Authorisation,<br>StatusAudit|Authorisation<br>checked before the<br>write commits|Policy check<br>on the service<br>call path|D-012|Planned M3: policy in<br>transition path|Planned M3:<br>unauthorised<br>update test|Approved,<br>not yet<br>implemente<br>d|



|**Req**<br>**ID**|**ASR**|**Module /**<br>**component**|**Data and**<br>**persistence impact**|**Design /**<br>**interface**<br>**decision**|**Technolog**<br>**y and**<br>**decision**<br>**reference**|**Implementation**<br>**evidence**|**Verification**<br>**evidence**|**Status**|
|---|---|---|---|---|---|---|---|---|
|FR-<br>019|ASR-002,<br>ASR-005|StatusAudit|Append-only history<br>with actor and<br>timestamp|Audit row<br>written inside<br>the same<br>transaction as<br>the status<br>change|D-008, D-<br>009|Planned M3:<br>RequestStatusService<br>transition|Planned M3:<br>StatusTransitionTes<br>t|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>020|ASR-005|StatusAudit|request_comments<br>retained 5 years<br>after last update|Adding a<br>comment is an<br>audited action|D-002, CR-<br>012|Planned M3: comment<br>creation|Planned M3:<br>feature test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>021|ASR-002|Assignment,<br>StatusAudit|Resolution is a<br>status change, not a<br>deletion|Resolve is a<br>transition; the<br>request stays<br>in the system|D-015|Planned M3: resolve<br>action|Planned M3:<br>transition test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>022|ASR-002|Authorisation,<br>Reporting|Reads the same<br>tables at<br>management scope|Same query<br>path, wider<br>authorisation<br>scope|D-012|Planned M3:<br>management scope|Planned M3: scope<br>test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>023|ASR-001|Reporting|Overdue derived<br>from timestamps,<br>not stored|Overdue<br>computed on<br>read so the<br>threshold can<br>change|D-003|Planned M3: reporting<br>query|Planned M3:<br>overdue test|Approved,<br>not yet<br>implemente<br>d|
|FR-<br>024|ASR-001|Reporting|Aggregates over the<br>status history|On-demand<br>report rather<br>than a live<br>dashboard|D-003|Planned M3: summary<br>report|Planned M3:<br>reporting test|Approved,<br>not yet<br>implemente<br>d|
|NFR<br>-001|ASR-002|Authenticatio<br>n|Password hashes<br>only, never plain<br>text|bcrypt with<br>cost factor 12|CR-013, D-<br>012|Planned M3: hashing<br>configuration|Planned M3: hash<br>verification test|Approved,<br>not yet|



|**Req**<br>**ID**|**ASR**|**Module /**<br>**component**|**Data and**<br>**persistence impact**|**Design /**<br>**interface**<br>**decision**|**Technolog**<br>**y and**<br>**decision**<br>**reference**|**Implementation**<br>**evidence**|**Verification**<br>**evidence**|**Status**|
|---|---|---|---|---|---|---|---|---|
|||||||||implemente<br>d|
|NFR<br>-002|ASR-002|Authorisation,<br>RequestQuery|Authorisation<br>applied on every<br>read|Cross-cutting<br>policy layer<br>rather than<br>per-screen<br>guards|D-012|Planned M3: policy layer|Planned M3: policy<br>test matrix|Approved,<br>not yet<br>implemente<br>d|
|NFR<br>-003|ASR-002|StatusAudit|Actor and<br>timestamp recorded<br>per change|Recorded<br>inside the<br>transaction|D-008|Planned M3: history<br>writes|Planned M3: audit<br>field test|Approved,<br>not yet<br>implemente<br>d|
|NFR<br>-004|ASR-001|RequestQuery|Indexes sized<br>against a 5-year<br>data volume|Pagination<br>plus indexes<br>on status, due<br>date and<br>assignee|D-007|Planned M3: migrations<br>with indexes|Planned M3: timed<br>query check|Approved,<br>not yet<br>implemente<br>d|
|NFR<br>-005|ASR-003|Deployment|Availability depends<br>on the chosen host|Single instance<br>accepted as a<br>point of failure<br>at MVP|D-013|Planned M3: deployment<br>configuration|Planned M4:<br>availability<br>observation|Approved,<br>not yet<br>implemente<br>d|
|NFR<br>-006|ASR-003,<br>ASR-005|RequestQuery<br>, Deployment|Backups consume<br>the storage<br>allowance|Host backup<br>or a scheduled<br>database<br>dump|D-013|Planned M3: backup<br>configuration|Planned M3:<br>restore rehearsal|Approved,<br>not yet<br>implemente<br>d|
|NFR<br>-007|Not<br>architecturall<br>y significant|UI|None|Every screen<br>within two<br>clicks of the<br>landing view|D-006|Planned M3: navigation<br>structure|Planned M3: click-<br>path walkthrough|Approved,<br>not yet<br>implemente<br>d|



|**Req**<br>**ID**|**ASR**|**Module /**<br>**component**|**Data and**<br>**persistence impact**|**Design /**<br>**interface**<br>**decision**|**Technolog**<br>**y and**<br>**decision**<br>**reference**|**Implementation**<br>**evidence**|**Verification**<br>**evidence**|**Status**|
|---|---|---|---|---|---|---|---|---|
|NFR<br>-008|ASR-004|Submission,<br>UI|None|Field-level<br>validation<br>messages<br>returned with<br>the response|D-007|Planned M3: error<br>handling|Planned M3:<br>validation message<br>test|Approved,<br>not yet<br>implemente<br>d|
|NFR<br>-009|ASR-004|Repository<br>governance|None|Two-reviewer<br>approval<br>before merge|Section 10,<br>D-014|In progress: pull request<br>history|Planned M3:<br>review evidence in<br>PRs|In<br>developmen<br>t|



## **7. Risk Register** 

### **7.1 Index** 

|**ID**|**Risk**|**Prob.**|**Impact**|**Priority**|**Owner**|**Status**|
|---|---|---|---|---|---|---|
|R-001|Team lacks production experience with the selected stack|Med|High|High|Xander|Mitigating|
|R-002|Free-tier hosting and database limits exceeded or<br>discovered late|Med|Med|Med|Michael|Mitigating|
|R-003|Inadequate role-based access control exposes request<br>information|Low|High|High|Jared|Mitigating|
|R-004|Uncontrolled scope creep|Med|Med|Med|Xander|Open|
|R-005|Two-reviewer approval creates a review bottleneck|Med|Med|Med|Michael|Open|
|R-006|AI-assisted work accepted without sufficient verification|Med|High|High|Jared|Open|



|**ID**|**Risk**|**Prob.**|**Impact**|**Priority**|**Owner**|**Status**|
|---|---|---|---|---|---|---|
|R-007|Duplicate, incomplete or miscategorised requests|Med|Med|Med|Xander|Mitigating|
|R-008|Concurrent status edits lose an update|Med|High|High|Xander|Mitigating|
|R-009|No background worker on the chosen free-tier host|Med|Med|Med|Michael|Open|
|R-010|Session and CSRF misconfiguration|Low|Med|Med|Jared|Mitigating|
|R-011|Status changed outside the service layer, bypassing the<br>audit trail|Low|High|High|Xander|Mitigating|
|R-012|Whole team learning PHP at the same time|Med|Med|Med|Michael|Mitigating|
|R-013|Free-tier database pauses or fills during assessment|Med|Med|Med|Michael|Open|
|R-014|Toolchain unavailable on the BC Desktop|Med|Med|Med|Jared|Open|
|R-015|Stack unvalidated because construction was deferred|Med|High|High|Xander|Open|
|R-016|Inertia coupling limits future non-browser clients|Low|Low|Low|Jared|Accepted|



#### **R-001: Team lacks production experience with the selected stack** 

|Field|Record|
|---|---|
|**Description**|Team lacks prior production experience with the technology stack eventually selected in M2, causing slower-than-planned<br>implementation in M3.|
|**Cause**|Limited prior exposure beyond coursework-level use of the relevant framework(s).|
|**Probability**|Med|
|**Impact**|High|



|Field|Record|
|---|---|
|**Priority**|High|
|**Mitigation**|Select the M2 stack using the required weighted decision matrix, explicitly weighting team capability/learning curve; build a<br>small proof-of-concept before committing.|
|**Contingency**|Fall back to the more familiar of the two shortlisted M2 stacks if velocity targets are missed by the M3 checkpoint.|
|**Owner**|Xander|
|**Status**|Mitigating|
|**Milestone 2 review**|Stack selected on evidence in D-007 using the section 13.3 matrix, with team capability and framework learning weighted 20 of<br>100 combined. Named fallback is now bundle B-003, the same Laravel backend with Blade views, which replaces only the<br>presentation layer. The proof-of-concept part of the mitigation is not yet done and is carried as R-015.|



#### **R-002: Free-tier hosting and database limits exceeded or discovered late** 

|Field|Record|
|---|---|
|**Description**|Free-tier hosting/database limits (storage, connections, request quota) are exceeded or discovered too late, constraining<br>deployment or demo reliability.|
|**Cause**|Cost constraint (Section 5) drives selection of free-tier services without upfront investigation of their limits.|
|**Probability**|Med|
|**Impact**|Med|
|**Priority**|Med|
|**Mitigation**|Document free-tier limits for shortlisted platforms during M2 research; size the data model and expected request volume<br>against those limits before committing.|



|Field|Record|
|---|---|
|**Contingency**|Identify a low-cost upgrade path in advance so the team can react quickly if limits are approached before M4.|
|**Owner**|Michael|
|**Status**|Mitigating|
|**Milestone 2 review**|D-013 fixes the topology at one web process, one queue worker and one managed PostgreSQL instance, which is the smallest<br>footprint that satisfies the design. Section 13.7.4 records the limits that interact with the 5-year retention rule. The specific<br>provider is still open, so this risk stays open until D-013 is closed.|



#### **R-003: Inadequate role-based access control exposes request information** 

|Field|Record|
|---|---|
|**Description**|Inadequate role-based access control exposes request information|
|**Cause**|RBAC is treated as an implementation detail rather than a first-class requirement at baseline.|
|**Probability**|Low|
|**Impact**|High|
|**Priority**|High|
|**Mitigation**|Define RBAC roles and access rules as explicit NFRs and acceptance criteria at M1 (Section 4.2); include access-control test<br>cases in M3 security evidence.|
|**Contingency**|Conduct a focused access-control review before staging deployment; restrict production data exposure until verified.|
|**Owner**|Jared|
|**Status**|Mitigating|



|Field|Record|
|---|---|
|**Milestone 2 review**|D-012 places authorisation in ServiceRequestPolicy at the data-access layer rather than per screen, so the same rule applies to|
||every query path. The policy test set is specified but not yet written, so the risk remains open until M3 evidence exists. The|
||fourth ‘Admin’ level was resolved by CR-009: three roles for the MVP, with a separate Administrator role deferred.|



#### **R-004: Uncontrolled scope creep** 

|Field|Record|
|---|---|
|**Description**|Uncontrolled scope creep (for example adding SMS notifications or a mobile app beyond the approved baseline) consumes<br>time needed for core-capability quality.|
|**Cause**|Desire to appear comprehensive or impressive at milestone presentations.|
|**Probability**|Med|
|**Impact**|Med|
|**Priority**|Med|
|**Mitigation**|Enforce the scope baseline (Section 3); any proposed addition must pass through the Change Request/Impact Analysis<br>template (Master Brief Appendix E) before implementation.|
|**Contingency**|Revert to baseline scope and formally defer the addition to future scope if schedule slippage is detected at a checkpoint.|
|**Owner**|Xander|
|**Status**|Open|
|**Milestone 2 review**|The control worked as intended: the only scope movement in M2, confirming that status-change email is in scope, went<br>through the change record rather than being absorbed silently. No features were added beyond the M1 baseline.|



#### **R-005: Two-reviewer approval creates a review bottleneck** 

|Field|Record|
|---|---|
|**Description**|With only three team members, the mandatory two-reviewer PR approval effectively requires unanimous team review of<br>every substantive change, creating a review bottleneck or an incentive to rubber-stamp under time pressure.|
|**Cause**|Small team size relative to the two-approvals-from-non-authors governance control (Master Brief section 9).|
|**Probability**|Med|
|**Impact**|Med|
|**Priority**|Med|
|**Mitigation**|Agree review-turnaround expectations in the Team Working Agreement (Section 10.2); stagger substantive PRs rather than<br>batching them near deadlines. Reviewers respond to a review request within 24 hours on weekdays. If a reviewer is<br>unavailable for longer, the author records the delay on the pull request rather than merging without the second approval.|
|**Contingency**|If a bottleneck occurs, prioritise reviewing PRs that block others’ work and record the delay rather than silently skipping<br>meaningful review.|
|**Owner**|Michael|
|**Status**|Open|
|**Milestone 2 review**|Still open. The risk grows in M3 when code review replaces document review, because all three members will be reviewing<br>PHP none of them has written before. Record actual PR turnaround times from M3 so the exposure can be measured rather<br>than estimated.|



#### **R-006: AI-assisted work accepted without sufficient verification** 

|Field|Record|
|---|---|
|**Description**|AI-generated code, tests or requirement wording is accepted into the controlled baseline without sufficient independent<br>verification, introducing a defect, incorrect assumption or security gap.|



|Field|Record|
|---|---|
|**Cause**|Schedule pressure encourages faster acceptance of AI output; verification takes time.|
|**Probability**|Med|
|**Impact**|High|
|**Priority**|High|
|**Mitigation**|Apply the AI Usage Register (Section 11) to every material AI contribution; the responsible student must test, cite or otherwise<br>verify the specific claim before it enters the baseline.|
|**Contingency**|Treat any unverifiable AI-assisted claim as rejected by default rather than including it to save time.|
|**Owner**|Jared|
|**Status**|Open|
|**Milestone 2 review**|Register maintained through M2. The exposure changes in M3: verifying a factual claim in a document is cheaper than<br>verifying generated PHP in a language none of the team has used, so AI-assisted code must be read line by line and covered by<br>a test before merge.|



#### **R-007: Duplicate, incomplete or miscategorised requests** 

|Field|Record|
|---|---|
|**Description**|Requesters submit duplicate, incomplete or miscategorised service requests, reducing staff’s ability to prioritise and increasing<br>the perceived unreliability of the platform.|
|**Cause**|This is one of the core problems described in the CivicConnect business need; unconstrained submission would reproduce it.|
|**Probability**|Med|
|**Impact**|Med|



|Field|Record|
|---|---|
|**Priority**|Med|
|**Mitigation**|Design mandatory fields and a controlled category list into the functional requirements (Section 4.1) rather than leaving<br>submission unconstrained.|
|**Contingency**|Allow staff to merge/relabel duplicate or miscategorised requests as a controlled, audited action.|
|**Owner**|Xander|
|**Status**|Mitigating|
|**Milestone 2 review**|Mitigation strengthened: category handling now goes through a dedicated CategoryHandler class per category, resolved from<br>a CategoryRegistry keyed on the category code (D-010, Section 9). A request reclassified out of ‘Other’ is routed through its<br>own handler rather than a conditional branch, so a reclassification cannot silently default to generic handling.|



#### **R-008: Concurrent status edits lose an update** 

|Field|Record|
|---|---|
|**Description**|Two staff members open the same request and update its status at nearly the same time; the second write silently overwrites<br>the first, so an action is lost and the audit trail records a state nobody intended.|
|**Cause**|Requests are visible to multiple staff members, and without a concurrency control the last write wins.|
|**Probability**|Med|
|**Impact**|High|
|**Priority**|High|
|**Mitigation**|D-008 adds an optimistic version column checked inside the transaction; a stale version raises StaleRequestException rather<br>than overwriting. The user is returned to the request with its current state and asked to retry.|



|Field|Record|
|---|---|
|**Contingency**|If conflicts prove frequent in M3 testing, narrow the editable window by refreshing request state when the detail screen is<br>opened, before considering heavier locking.|
|**Owner**|Xander|
|**Status**|Mitigating|
|**Milestone 2 review**|Raised at M2 from Assignment 2 Task 2 research. Design-level mitigation is in place; verification is a Milestone 3 feature test<br>asserting that a stale version is rejected.|



#### **R-009: No background worker on the chosen free-tier host** 

|Field|Record|
|---|---|
|**Description**|The selected free-tier host does not support a persistent background worker process, so queued status notifications are<br>accepted by the application but never delivered.|
|**Cause**|D-011 makes notification delivery a queued listener, which requires a worker process that many free tiers do not provide.|
|**Probability**|Med|
|**Impact**|Med|
|**Priority**|Med|
|**Mitigation**|Make worker support a selection criterion when D-013 closes; confirm it before committing to a provider.|
|**Contingency**|Run the queue from a scheduled task using queue:work with a stop-when-empty flag, accepting delayed delivery, or send the<br>notification synchronously after commit as a temporary measure.|
|**Owner**|Michael|
|**Status**|Open|



|Field|Record|
|---|---|
|**Milestone 2 review**|Raised at M2 as a direct consequence of D-011 and D-013. Remains open until the hosting provider is chosen in section 13.8.|



#### **R-010: Session and CSRF misconfiguration** 

|Field|Record|
|---|---|
|**Description**|Session cookie or CSRF settings are wrong for a deployed environment, so users cannot log in, or protection against cross-site<br>request forgery is weakened.|
|**Cause**|Session lifetime, cookie domain, secure flag and CSRF token handling differ between local development and a hosted<br>environment.|
|**Probability**|Low|
|**Impact**|Med|
|**Priority**|Med|
|**Mitigation**|Keep all environment-specific values in environment variables (section 13.7.2); add a feature test covering login and an<br>authenticated Inertia request so a broken configuration fails in CI rather than in the demo.|
|**Contingency**|Roll back to the last known-good environment configuration and verify against the CI test before redeploying.|
|**Owner**|Jared|
|**Status**|Mitigating|
|**Milestone 2 review**|Revised at M2. The original form of this risk covered CORS and cross-origin token handling for a separate SPA. D-006 and D-012<br>removed that boundary entirely, so the remaining exposure is ordinary session and CSRF configuration, which is smaller.<br>Downgraded from High to Medium priority.|



**R-011: Status changed outside the service layer, bypassing the audit trail** 

|Field|Record|
|---|---|
|**Description**|A controller, seeder, console command or future feature updates a request’s status directly through the model, skipping<br>RequestStatusService, so no history row is written and the audit trail has a gap that cannot be reconstructed.|
|**Cause**|Nothing in the framework prevents a direct model update; the rule that only the service may change status is a convention.|
|**Probability**|Low|
|**Impact**|High|
|**Priority**|High|
|**Mitigation**|State the rule explicitly in the code standards, enforce it in review, and add a Milestone 3 test asserting that the count of<br>history rows equals the count of status transitions for a request.|
|**Contingency**|If a gap is found, record it as a defect, backfill what can be reconstructed from application logs, and state plainly in the PED<br>what cannot be recovered.|
|**Owner**|Xander|
|**Status**|Mitigating|
|**Milestone 2 review**|Raised at M2 from D-002, D-008 and Forward Engineering Consideration #3. Audit gaps cannot be backfilled after the fact,<br>which is why the probability is low but the impact is high.|



#### **R-012: Whole team learning PHP at the same time** 

|Field|Record|
|---|---|
|**Description**|All three members are learning PHP and Laravel simultaneously, with no member able to act as an internal reference, so early<br>mistakes are less likely to be caught in review and rework is more likely.|
|**Cause**|D-007 selects a stack in a language none of the team has used, accepted deliberately as a trade-off.|



|Field|Record|
|---|---|
|**Probability**|Med|
|**Impact**|Med|
|**Priority**|Med|
|**Mitigation**|Pair on the first vertical slice rather than splitting unfamiliar work three ways; follow the official Laravel documentation and<br>the official starter kit structure rather than mixed tutorials; keep the first PRs small so review is meaningful.|
|**Contingency**|Invoke the D-007 fallback to bundle B-003 if the first slice takes materially longer than planned.|
|**Owner**|Michael|
|**Status**|Mitigating|
|**Milestone 2 review**|Raised at M2 as a distinct exposure from R-001: R-001 is about stack experience generally, this is about having no internal<br>expert to review against.|



#### **R-013: Free-tier database pauses or fills during assessment** 

|Field|Record|
|---|---|
|**Description**|A free-tier PostgreSQL instance pauses after inactivity or approaches its storage cap, making the demonstration unreliable or<br>blocking writes during assessment.|
|**Cause**|Free tiers commonly suspend idle databases and cap storage, which interacts with the 5-year retention requirement in FR-008.|
|**Probability**|Med|
|**Impact**|Med|
|**Priority**|Med|



|Field|Record|
|---|---|
|**Mitigation**|Record the chosen provider’s actual limits when D-013 closes; seed a small realistic dataset rather than bulk test data; warm<br>the connection before presenting.|
|**Contingency**|Keep a local Docker environment ready as a demonstration fallback and state plainly that it is not the hosted environment.|
|**Owner**|Michael|
|**Status**|Open|
|**Milestone 2 review**|Raised at M2, related to R-002 but specific to demonstration reliability rather than to sizing.|



#### **R-014: Toolchain unavailable on the BC Desktop** 

|Field|Record|
|---|---|
|**Description**|PHP, Composer, Node or PostgreSQL cannot be installed or run on the BC Desktop platform, so the project cannot be built or<br>assessed in the institutional environment.|
|**Cause**|CON-007 and Master Brief section 25: the institution does not guarantee that a chosen technology is available or supported on<br>its platform.|
|**Probability**|Med|
|**Impact**|Med|
|**Priority**|Med|
|**Mitigation**|Install and run the full toolchain on a BC Desktop machine and record the result in section 13.3 before construction begins.|
|**Contingency**|Develop on team laptops and document the limitation, or invoke the D-007 fallback if the difference blocks assessment.|
|**Owner**|Jared|



|Field|Record|
|---|---|
|**Status**|Open|
|**Milestone 2 review**|Raised at M2. Until the installation is verified, the dev-environment row of the section 13.3 matrix is an estimate rather than<br>evidence. This is a few hours of work and should be closed early in M3.|



#### **R-015: Stack unvalidated because construction was deferred** 

|Field|Record|
|---|---|
|**Description**|With no proof of concept built during M2, the assumptions behind D-007, D-008 and D-013 are untested, so a problem with<br>the stack, the transaction design or the hosting topology would surface only in M3 under delivery pressure.|
|**Cause**|D-014 defers construction to M3, concentrating learning and delivery in the same window.|
|**Probability**|Med|
|**Impact**|High|
|**Priority**|High|
|**Mitigation**|Timebox a spike at the start of M3: create the Laravel project, connect it to PostgreSQL, run one migration and render one<br>Inertia page, before committing to the full build order.|
|**Contingency**|If the spike reveals a blocking problem, invoke the D-007 fallback to B-003 and record the change through the change-control<br>process.|
|**Owner**|Xander|
|**Status**|Open|
|**Milestone 2 review**|Raised at M2 alongside D-014. This is the direct cost of deferring construction and should be stated plainly in the defence<br>rather than left implicit.|



#### **R-016: Inertia coupling limits future non-browser clients** 

|Field|Record|
|---|---|
|**Description**|Because D-006 exchanges page props rather than a published API, a future mobile client or third-party integration would<br>require a JSON API to be added before it could consume CivicConnect data.|
|**Cause**|Inertia couples the presentation layer to the application, which is the trade-off accepted in D-006 and D-007.|
|**Probability**|Low|
|**Impact**|Low|
|**Priority**|Low|
|**Mitigation**|Keep business rules in services rather than controllers so that adding routes/api.php later is additive rather than a rewrite.|
|**Contingency**|Add a JSON API alongside Inertia over the same services if a second client is ever required.|
|**Owner**|Jared|
|**Status**|Accepted|
|**Milestone 2 review**|Accepted at M2. No stakeholder requirement in the approved scope calls for a non-browser client, and the scope baseline<br>excludes a native mobile app.|



## **8. Forward Engineering Considerations** 

|**Concern**|**Why It Matters Now**|**Decision/Activity It**<br>**Influences**|**Information Still**<br>**Missing**|**Risk of Ignoring It**|**Milestone 2 status**|**Resolved by / next step**|
|---|---|---|---|---|---|---|
|1<br>Authenticatio<br>n & role-<br>based access<br>control<br>(Requester /<br>Staff /<br>Management<br>)|The three-role model<br>is already implied by<br>the minimum<br>business capabilities<br>(Master Brief section<br>3) and shapes which<br>requirements,<br>screens and data<br>fields exist at all.|M2<br>authentication/archi<br>tecture design, M2<br>data model, M3<br>security testing, M4<br>security evidence.|Whether staff<br>need finer sub-<br>roles (e.g. by<br>department), and<br>whether<br>management<br>needs read-only<br>or configurable<br>access.|Retrofitting access<br>control after<br>construction typically<br>forces rework of the<br>data model, API<br>contracts and UI, with a<br>higher chance of an<br>overlooked<br>authorisation gap.|Resolved for the three-<br>role model; department<br>sub-roles carried forward|<br>D-012 and CR-009.<br>Sub-roles<br>reconsidered only if a<br>stakeholder need<br>appears|
|2<br>Controlled<br>request<br>category &<br>status<br>taxonomy|Several FRs<br>(submission, filtering,<br>reporting) and the<br>quality/NFR<br>constraint depend on<br>a fixed, agreed<br>vocabulary rather<br>than free text.|M1 requirement<br>wording, M2 data<br>model, M4<br>management<br>reporting.|The final list of<br>categories/status<br>values, and<br>whether it must<br>be extensible later<br>without a code<br>change.|<br>Free-text categorisation<br>reproduces the exact<br>fragmentation problem<br>CivicConnect is meant to<br>solve; late taxonomy<br>changes ripple through<br>requirements, data and<br>reports.|<br> <br>Resolved|CR-010 fixes the<br>status set; D-001 and<br>D-010 fix the category<br>set and handling|
|3<br>Request<br>audit trail /<br>status-<br>change<br>accountabilit<br>y|The business need<br>explicitly names<br>'weak accountability<br>for changes to<br>request status' as a<br>current problem; the<br>data model must<br>capture who changed<br>what and when from<br>the outset.|M2 data/persistence<br>design, M3<br>traceability<br>evidence, M4<br>stakeholder<br>validation of<br>accountability.|Retention period<br>for audit history<br>and whether staff<br>need full history<br>or only current<br>state.|Adding an audit trail<br>after core tables are<br>built typically requires a<br>migration touching<br>every write path; audit<br>gaps found late cannot<br>be backfilled.|<br>Designed, not yet<br>implemented|D-008 and D-009.<br>Retention of audit<br>history settled by CR-<br>012; enforcement<br>verified in M3 (R-011)|
|4<br>Deployment<br>platform &<br>environment<br>strategy<br>(dev/test/sta|The cost constraint<br>(free/low-cost<br>preference) and the<br>later need for<br>staging-to-|M2 technology<br>decision matrix, M3<br>staging deployment,|Concrete free-tier<br>limits, and<br>whether the<br>eventual stack's<br>hosting options|Discovering a platform<br>incompatibility at<br>M3/M4 could force an<br>emergency migration<br>under deadline pressure|<br>Partly resolved: topology<br>fixed, provider deferred|D-013. Provider<br>selection closes in<br>Section 13.8 before|



|**Concern**|**Why It Matters Now**|**Decision/Activity It**<br>**Influences**|**Information Still**<br>**Missing**|**Risk of Ignoring It**|**Milestone 2 status**|**Resolved by / next step**|
|---|---|---|---|---|---|---|
|ging/producti<br>on)|production parity<br>(Master Brief section<br>17) both depend on<br>understanding<br>platform options and<br>limits early.|M4 production<br>release.|support proper<br>environment<br>separation.|with no time to test<br>properly.||the M3 staging<br>deployment|
|5<br>Testability of<br>core<br>workflows<br>(submission<br>→<br>assignment<br>→<br>resolution)|If requirements and<br>design don't<br>anticipate automated<br>testing (Master Brief<br>section 15), M3's<br>automated test<br>evidence will be<br>retrofitted and<br>weaker.|M1 acceptance-<br>criteria wording, M2<br>design decisions<br>(e.g. avoiding tight<br>coupling), M3 test<br>strategy.|Which workflows<br>are highest-risk<br>and therefore<br>deserve the<br>earliest test<br>coverage.|Untestable design<br>decisions made in M2<br>(e.g. logic embedded<br>directly in UI code) are<br>expensive to unpick<br>once construction is<br>underway.|In progress|D-006 keeps business<br>rules in services<br>rather than the UI,<br>which is what makes<br>them testable; test set<br>defined in Section 6.2,<br>written in M3|
|6<br>Sensitive-<br>data<br>handling for<br>security/IT-<br>support<br>request<br>categories|Some categories<br>(security concerns, IT<br>support) may contain<br>sensitive<br>information, which<br>interacts directly<br>with RBAC (Concern<br>1) and drives specific<br>NFRs.|M1 NFRs (Section<br>4.2), M2<br>architecture (e.g.<br>encryption at rest/in<br>transit), M3 security<br>review.|Whether any<br>category needs<br>restricted visibility<br>even from general<br>staff, not only<br>from requesters.|Sensitive information<br>could be exposed to<br>unauthorised staff,<br>leaving a residual<br>security risk the team<br>cannot honestly claim to<br>understand at M4<br>(Master Brief section<br>23).|<br>Partly resolved|D-012 applies<br>authorisation at the<br>data layer; encryption<br>at rest depends on<br>the provider chosen in<br>D-013|
|7<br>Application<br>evidence and<br>the start of<br>construction|D-014 defers<br>construction to<br>Milestone 3, so the<br>baseline is not yet<br>backed by code and<br>the end-to-end trace<br>stops at the<br>technology decision|M3 build order,<br>Section 6.2 status<br>values, criterion F<br>evidence|How long the first<br>vertical slice takes<br>with a stack new<br>to the team|Learning and delivery<br>are compressed into the<br>same window, and an<br>unworkable assumption<br>would surface late (R-<br>015)|<br>Open - construction<br>deferred to Milestone 3<br>(D-014)|R-015 spike at the<br>start of M3, then the<br>first vertical slice<br>covering FR-001, FR-<br>017 and FR-019|



|**Concern**|**Why It Matters Now**|**Decision/Activity It**<br>**Influences**|**Information Still**<br>**Missing**|**Risk of Ignoring It**|**Milestone 2 status**|**Resolved by / next step**|
|---|---|---|---|---|---|---|
|8<br>Staging and<br>production<br>parity|Master Brief section<br>17 expects staging to<br>resemble production,<br>and the queue<br>worker required by<br>D-011 must exist in<br>both|D-013 provider<br>selection, M3<br>staging deployment,<br>M4 release|Whether the<br>chosen free tier<br>supports a<br>persistent worker<br>and environment<br>separation|A platform that cannot<br>run a worker would<br>break notification<br>delivery only after<br>deployment (R-009)|Open - hosting provider<br>not yet chosen|OD-01 and OD-02;<br>confirm worker and<br>scheduler support<br>before the M3 staging<br>deployment (R-009,<br>AS-001)|



## **9. Engineering Decision Log** 

### **9.1 Index** 

|**ID**|**Decision**|**Status**|
|---|---|---|
|D-001|Controlled request category vocabulary|Baselined M1; Later Consequence updated at M2|
|D-002|Append-only status history|Baselined M1; Later Consequence updated at M2|
|D-003|On-demand management reporting at MVP|Baselined M1; Later Consequence updated at M2|
|D-004 (Deferred, now closed)|Technology-stack selection|Closed at M2; resolved by D-007|
|D-005 (Deferred, now closed)|Deployment and hosting platform selection|Closed at M2; superseded by D-013|
|D-006|Architecture style|New at M2; option C (SPA) superseded by option B (Inertia) through CR-<br>017|
|D-007|Technology stack selection|New at M2; closes D-004|
|D-008|Status change and audit write in a single<br>transaction|New at M2|
|D-009|Splitting status-change side effects|New at M2; adapts the Assignment 2 recommendation|
|D-010|Category-specific handling|New at M2|



|**ID**|**Decision**|**Status**|
|---|---|---|
|D-011|Status-to-notification integration mechanism|New at M2|
|D-012|Authentication and role-based access control|New at M2|
|D-013|Deployment direction and environment strategy|New at M2; supersedes D-005|
|D-014|Construction deferred to Milestone 3|New at M2|
|D-015|Status taxonomy and controlled transitions|New at M2; closes Forward Engineering Consideration #2|



#### **D-001: Controlled request category vocabulary** 

|Field|Record|
|---|---|
|**Status**|Baselined M1; Later Consequence updated at M2|
|**Context**|CivicConnect requires a shared vocabulary for request types to enable consistent submission, staff triage and management<br>reporting.|
|**Constraints**|Must stay simple for non-technical requesters; must cover the categories named in the business need (facility faults, damaged<br>equipment, security concerns, IT support, maintenance, lost property), plus ‘Other’.|
|**Alternatives**|(a) Free-text field; (b) Fixed controlled list; (c) Fixed list with an ‘Other’ option requiring staff reclassification.|
|**Decision**|Adopt (c): a fixed controlled list with a staff-reclassifiable ‘Other’ category.|
|**Rationale**|Balances requester ease-of-use with the data-quality and reporting needs in the business need, without an over-rigid list that<br>blocks legitimate edge cases.|
|**Trade-offs**|Slightly more implementation complexity than free text; requires a staff workflow for reclassifying ‘Other’ items.|
|**Risks**|If the list is poorly chosen, most requests may default to ‘Other’, undermining the benefit (linked to Risk R-007).|
|**Evidence**|Master Brief section 2 (fragmentation problem); Stakeholder Analysis, Section 2.|
|**Later consequence**|M2: realised as the categories table (section 13.2.1) and the per-category handlers of D-010. Reclassification of ‘Other’ is an<br>audited staff action.|



#### **D-002: Append-only status history** 

|Field|Record|
|---|---|
|**Status**|Baselined M1; Later Consequence updated at M2|
|**Context**|Every service request must support staff and management accountability for status changes.|
|**Constraints**|It must remain technology-agnostic at M1. It must not assume a specific database engine.|
|**Alternatives**|(a) Store only current status. (b) Store current status plus a full append-only status-change history.|
|**Decision**|Adopt (b): every status transition is recorded as an immutable history entry (who, what, when).|
|**Rationale**|Directly addresses the ‘weak accountability’ problem in the business need and is required groundwork for the M3/M4<br>traceability and quality-evidence standards.|
|**Trade-offs**|Slightly larger data model and additional write operations per status change.|
|**Risks**|If not enforced consistently across every code path that changes status, gaps could appear in the audit trail (linked to Forward<br>Engineering Consideration #3).|
|**Evidence**|Master Brief section 2 problem statement, and section 11 traceability standard.|
|**Later consequence**|M2: realised as request_status_history (section 13.2.1), written inside the D-008 transaction. The enforcement rule is set by D-<br>009.|



#### **D-003: On-demand management reporting at MVP** 

|Field|Record|
|---|---|
|**Status**|Baselined M1; Later Consequence updated at M2|
|**Context**|Whether management reporting requires a real-time dashboard or a periodic/on-demand view at MVP stage.|
|**Constraints**|Must fit within the schedule constraint (Section 5) and the three-person team capability constraint.|
|**Alternatives**|(a) Real-time dashboard; (b) Periodic/on-demand report view.|
|**Decision**|Adopt (b) for MVP scope; defer (a) to future scope.|



|Field|Record|
|---|---|
|**Rationale**|Reduces architectural complexity and risk within the schedule constraint while still satisfying the minimum management<br>capability of identifying open/overdue/resolved requests (Master Brief section 3).|
|**Trade-offs**|Less immediate visibility for management than a live dashboard would offer.|
|**Risks**|If stakeholders expected real-time visibility, this could become a change request later (Master Brief Appendix E).|
|**Evidence**|Scope Baseline, Section 3.3.|
|**Later consequence**|M2: realised as an on-demand reporting read path (Reporting component, section 13.1.1). No live dashboard has been<br>introduced.|



#### **D-004 (Deferred, now closed): Technology-stack selection** 

|Field|Record|
|---|---|
|**Status**|Closed at M2; resolved by D-007|
|**Context**|Technology-stack selection for CivicConnect.|
|**Constraints**|Not applicable at the time of the M1 entry.|
|**Alternatives**|Not applicable at the time of the M1 entry. M2: the nine bundles compared in section 13.3.1.|
|**Decision**|DEFERRED to Milestone 2. M2: resolved by D-007.|
|**Rationale**|M1 explicitly excludes final technology-stack selection (Milestone 1 Brief section 5). A defensible choice requires the weighted<br>decision matrix and evidence gathering scoped to M2 (Master Brief section 18.1), not yet performed at M1.|
|**Trade-offs**|Not applicable at the time of the M1 entry.|
|**Risks**|Delaying keeps options open but leaves less M2 time for the required comparison (linked to Risk R-001). Research must begin<br>immediately after M1 submission.|
|**Evidence**|Not applicable at the time of the M1 entry. M2: section 13.3 matrices and D-007.|
|**Later consequence**|Superseded by D-007 on the date recorded in Document Control.|



#### **D-005 (Deferred, now closed): Deployment and hosting platform selection** 

|Field|Record|
|---|---|
|**Status**|Closed at M2; superseded by D-013|
|**Context**|Deployment/hosting platform selection.|
|**Constraints**|Not applicable at the time of the M1 entry.|
|**Alternatives**|Not applicable at the time of the M1 entry. M2: the options compared in D-013.|
|**Decision**|DEFERRED to Milestone 2, pending D-004. M2: superseded by D-013, which fixes the deployment topology and defers only the<br>provider.|
|**Rationale**|Hosting compatibility depends on the technology stack chosen in D-004. Deciding hosting first would risk prematurely<br>constraining that choice.|
|**Trade-offs**|Not applicable at the time of the M1 entry.|
|**Risks**|Free-tier limits (Risk R-002) must still be scoped generally now (Forward Engineering Consideration #4) even though the<br>specific platform is deferred.|
|**Evidence**|Not applicable at the time of the M1 entry. M2: section 13.7 and D-013.|
|**Later consequence**|Provider selection remains open and is tracked in section 13.8.|



#### **D-006: Architecture style** 

|Field|Record|
|---|---|
|**Status**|New at M2; revised to the Inertia presentation layer|
|**Context**|CivicConnect needs an architecture that enforces auditable status changes, and that applies authorisation consistently across<br>queries. All this must be done within the free-tier service constraint.|
|**Constraints**|The cost constraint, as the services must be free. It needs browser-based access and must be buildable and maintainable by a<br>3-person team.|



|Field|Record|
|---|---|
|**Alternatives**|A server-rendered application without APIs (option A), a layered modular monolith with an Inertia-rendered React<br>presentation layer (option B), a layered modular monolith with a separate React SPA over a Laravel API (option C), or<br>distributed services (option D).|
|**Decision**|Option B, a layered modular monolith application using React and TypeScript rendered through Inertia over Laravel. There is<br>no internal network boundary and no separate JSON API at this stage.|
|**Rationale**|Detailed justification and reasoning is in section 13.1.3. In summary: option D turns commits into distributed transactions and<br>would exceed the free-tier constraint; option C satisfies the requirements but adds a network boundary, CORS handling, a<br>second deployable and duplicated validation for a client the project does not need to serve independently, which is the cost<br>Assignment 2 Task 3 warned against; option A is the simplest but leaves the team’s React and TypeScript skills unused. Option<br>B keeps one deployable and server-side authorisation while still using React for presentation.|
|**Trade-offs**|The front-end and back-end deploy together and cannot be released independently. Page props are an internal contract, not a<br>reusable API, so a future mobile or third-party client would require a JSON API to be added alongside Inertia.|
|**Risks**|R-001 and R-005, the boundaries may erode under the pressure of the project’s schedule. Presentation logic could leak into<br>controllers if the component responsibilities in section 13.1.1 are not enforced in review.|
|**Evidence**|Section 8 considerations 3 and 5, D-002, FR-013, FR-019, section 5’s cost constraint, and the business needs in section 1.1.<br>Assignment 2 Task 3, sections 4.2 and 4.3.|
|**Later consequence**|Influences the repository structure, component names, deployment and the layering, the D-012 authentication approach, and<br>the interface documentation in section 13.6. Because the business logic sits in services, adding a JSON API later is additive<br>rather than a rewrite.|
|**Superseded version**|20/09/2026: option C, a React SPA over a Laravel JSON API, was the first selection. It was replaced by option B through CR-017<br>after the section 13.3 matrix ranked B-009 highest and the SPA’s network boundary, CORS handling and second deployable<br>were found to add cost with no requirement that needed them. The SPA analysis is kept as option C in section 13.1.2.|



#### **D-007: Technology stack selection** 

|Field|Record|
|---|---|
|**Status**|New at M2; closes D-004|



|Field|Record|
|---|---|
|**Context**|The technology stack had to be selected on evidence before construction begins. Section 13.3 scored nine common web<br>bundles against ten weighted criteria drawn from Master Brief section 18.1.|
|**Constraints**|Free or low-cost services (CON-003); a three-person team with PHP new to all members and React/TypeScript known to two of<br>the three (CON-006); tools must run on the BC Desktop or team laptops (CON-007); the fixed milestone schedule (CON-002).|
|**Alternatives**|Nine bundles, B-001 to B-009 (section 13.3.1). MERN was excluded before scoring because MongoDB is not relational. The<br>PERN variant was scored as B-001. Leading scores: B-009 Laravel with Inertia 85%, B-003 Laravel with Blade 83%, B-005 Django<br>82%, B-006 ASP.NET Core with Razor 80%, B-004 Laravel API with a separate SPA 74%.|
|**Decision**|Adopt bundle B-009: Laravel (PHP) with Inertia serving React and TypeScript components, over PostgreSQL. This is the highest-<br>scoring bundle in the section 13.3.3 matrix and is consistent with the architecture selected in D-006.|
|**Rationale**|B-009 scores highest on the criteria carrying the most weight. It deploys as a single unit, which gives the team a working<br>deployment quickly, and CivicConnect has no requirement to scale the presentation layer independently of the application. It<br>keeps session-based security and framework validation, and it uses the React and TypeScript that two members already work<br>in while the team takes on Laravel. PostgreSQL wins its own sub-matrix at 96% on CHECK constraints, composite foreign keys<br>and transactional DDL, which the D-008 and D-015 integrity design depends on. Tiebreakers between the four candidates<br>within five percentage points:<br>(1) reuse of the React and TypeScript skills two members already have<br>(2) a single deployable with session authentication<br>(3) an officially supported Laravel starter kit for React, Inertia and TypeScript<br>(4) PostgreSQL integrity support.<br>B-009 is the only candidate that meets all four|
|**Trade-offs**|PHP is new to all three members, so the framework learning curve is real and is the weakest dimension of every Laravel<br>bundle. Inertia couples the front-end and back-end: page props are not a reusable API contract.|
|**Risks**|R-001 learning curve; R-014 toolchain availability on the BC Desktop, not yet verified; R-015 no proof of concept before M3.|
|**Evidence**|Section 13.3.3 weighted matrix; section 13.3.4 database sub-matrix; section 13.3.5 result and sensitivity check; Assignment 2<br>Task 3; the laravel/react-starter-kit repository (Laravel, React, Inertia, TypeScript) as evidence that this combination is an<br>officially supported path.|
|**Later consequence**|If velocity targets are missed at the M3 checkpoint, the fallback recorded against R-001 is B-003, the same Laravel backend<br>with Blade views, which replaces only the presentation layer. Target versions and compatibility assumptions are recorded in<br>section 13.3.6 and confirmed against composer.lock and package.json when the project is created.|



#### **D-008: Status change and audit write in a single transaction** 

|Field|Record|
|---|---|
|**Status**|New at M2|
|**Context**|D-002 (Milestone 1) committed CivicConnect to recording every status change as an immutable history entry, but left the<br>transactional and concurrency detail open. That detail has to be settled before the persistence layer is built.|
|**Constraints**|Free-tier relational database (Section 5). FR-018 (only the assigned staff member may update a request). NFR-003 (user and<br>timestamp on every status change). Three-person team and academic schedule.|
|**Alternatives**|(a) Single relational transaction, application-managed, with optimistic concurrency via a version column and a service-layer<br>validation check backed by a database constraint. (b) Outbox/event-sourced write, where the status change and a separate<br>outbox event are written atomically and a worker process later handles downstream effects.|
|**Decision**|Adopt (a). RequestStatusService::transition() updates service_requests.status guarded by an optimistic version check (a stale<br>version raises StaleRequestException, which the controller turns into a redirect back with an error message) and inserts the<br>matching request_status_history row, both inside one DB::transaction call.|
|**Rationale**|Option (a) delivers exactly the atomicity D-002 requires without introducing a worker process, message queue or eventual<br>consistency the team does not yet have a justified need for. It maps onto Laravel’s DB::transaction(). Laravel has no built-in<br>optimistic locking, so the version check is written explicitly: the update includes WHERE id = ? AND version = ?, increments<br>version, and if no row is affected RequestStatusService throws StaleRequestException.|
|**Trade-offs**|The transaction briefly holds a database connection open. Calling code has to catch a version conflict and retry or surface it to<br>the user rather than assuming every write succeeds.|
|**Risks**|The database CHECK constraint on status and the database transition table (request_status_transitions) have to be kept in<br>sync by hand until an automated consistency check exists (Forward Engineering Consideration #3). Concurrent staff edits are<br>tracked as R-008.|
|**Evidence**|Assignment 2, Task 2, Sections 3.2 to 3.4.|
|**Later consequence**|[to be updated once Milestone 3 implementation and test evidence is available]|



#### **D-009: Splitting status-change side effects** 

|Field|Record|
|---|---|
|**Status**|New at M2; adapts the Assignment 2 recommendation|
|**Context**|FR-016 and FR-019 require an audit entry recording who changed a request’s status and when. FR-007 requires the requester<br>to see the update reflected on their side. Forward Engineering Consideration #3 flags that the audit trail has to be designed in<br>from the outset rather than retrofitted.|
|**Constraints**|The audit write must stay inside the D-008 transaction so it can never be silently skipped. A notification failure must not roll<br>back the status change. Laravel’s event/listener mechanism changes what building this reaction costs compared with the<br>Assignment 2 research.|
|**Alternatives**|(a) A general-purpose Observer applied directly at the ServiceRequest entity, as Assignment 2 Task 1 recommended, with the<br>audit write as one of the registered observers. (b) Split the two reactions: audit write synchronous inside the D-008<br>transaction, every other reaction a queued listener on a RequestStatusChanged event dispatched only after commit. (c) A<br>general-purpose domain-event/pub-sub dispatcher.|
|**Decision**|Adopt (b). The audit write is written directly inside RequestStatusService::transition() as part of D-008.<br>NotifyRequesterOfStatusChange is a queued listener (implements ShouldQueue) on the RequestStatusChanged event,<br>dispatched once the transaction commits. RequestStatusChanged implements ShouldDispatchAfterCommit, so Laravel<br>dispatches it only after the D-008 transaction commits; if the transaction rolls back, the event is never dispatched|
|**Rationale**|Assignment 2 Task 1 recommended a uniform Observer over a full dispatcher because a dispatcher looked like more<br>infrastructure than a single entity raising events needed. That reasoning changes once Laravel is the confirmed stack: the<br>event/listener mechanism already exists. What Assignment 2 Task 1 did not consider is Assignment 2 Task 2’s constraint that<br>the audit write must be inside the same transaction as the status update, so a uniform after-commit Observer would not fit.<br>Splitting the reactions keeps D-008’s atomicity guarantee intact.|
|**Trade-offs**|The status-change reaction logic is no longer handled by one uniform mechanism, which is one more thing to explain in the<br>defence. A misconfigured listener still silently skips its reaction.|
|**Risks**|If a future reaction is added as a queued listener without checking whether it also needs to be inside the transaction, it could<br>reintroduce a consistency gap.|
|**Evidence**|Assignment 2, Task 1, Section 2.1 (Design Problem 1). Assignment 2, Task 2, Sections 3.2 to 3.4.|
|**Later consequence**|[to be updated once Milestone 3 implementation and test evidence is available]|



**D-010: Category-specific handling** 

|Field|Record|
|---|---|
|**Status**|New at M2|
|**Context**|D-001 fixed the category vocabulary itself, but not how category-specific validation and routing behaviour is implemented.<br>Writing that behaviour as one long conditional means every new category requires editing and retesting the same method.|
|**Constraints**|Adding a category should not require editing already-tested code. CivicConnect’s categories differ in behaviour, not only in the<br>fields they require.|
|**Alternatives**|(a) Strategy pattern, one handler class per category behind a shared interface. (b) Factory Method producing category-specific<br>request objects. (c) A configuration-only lookup of required fields.|
|**Decision**|Adopt (a), the Strategy pattern, as Assignment 2 recommended. Each category implements a shared CategoryHandler interface<br>(rules(), defaultAssignee()). CategoryRegistry, bound in AppServiceProvider, resolves the handler for a given category code,<br>and an unrecognised code falls back to OtherHandler. StoreServiceRequest merges the resolved handler’s rules into its own<br>FormRequest validation.|
|**Rationale**|The configuration-only approach was set aside because the categories differ in behaviour, not only in required fields.Factory<br>Method was set aside because the categories differ in behaviour, not in data shape; a subclass per category would force a class<br>hierarchy onto the persisted request and complicate the data model (Assignment 2, Task 1). With Strategy, adding a category<br>means one new handler class, one seeded category row and one line in the CategoryRegistry mapping; existing handlers and<br>StoreServiceRequest are not edited.|
|**Trade-offs**|Requires a small registry/lookup mechanism. If the category list stays very small with simple, near-identical rules, this is more<br>structure than the problem needs.|
|**Risks**|If the handler set drifts from the seeded category list, a request could silently resolve to OtherHandler (linked to Risk R-007).|
|**Evidence**|Assignment 2, Task 1, Design Problem 2. Section 13.4.2.|
|**Later consequence**|[to be updated once Milestone 3 implementation and test evidence is available]|



#### **D-011: Status-to-notification integration mechanism** 

|Field|Record|
|---|---|
|**Status**|New at M2|
|**Context**|The status-update component and the notification component must exchange information without a notification failure<br>affecting the status change (Assignment 2 Task 3).|



|Field|Record|
|---|---|
|**Constraints**|A notification failure may not roll back a status change. No network boundary without justification. Free-tier hosting with no<br>Redis instance available.|
|**Alternatives**|(a) Synchronous in-process call. (b) Asynchronous in-process event handled by a queued listener. (c) A separate notification<br>service called over HTTP/REST.|
|**Decision**|Adopt (b), as Assignment 2 recommended: a queued listener on the RequestStatusChanged event, using the database queue<br>driver so the queue lives in PostgreSQL.|
|**Rationale**|Option (a) lets a mail-provider failure roll back the transaction, the exact failure Task 3 identified. Option (c) adds a second<br>deployable, a security boundary and a versioned contract for benefits, such as independent scaling and third-party consumers,<br>that this project does not need. Option (b) satisfies both constraints inside the single deployable chosen in D-006.|
|**Trade-offs**|Requires a queue worker process in every deployed environment. Delivery is eventual rather than immediate.|
|**Risks**|R-009: a free-tier host without background-worker support would accept notifications that are never sent.|
|**Evidence**|Assignment 2, Task 3, Sections 4.2 and 4.3. Section 13.7.3.|
|**Later consequence**|Makes a persistent worker process a hosting requirement, which constrains D-013. The email provider is recorded in section<br>13.7 once a free-tier provider is confirmed.|



#### **D-012: Authentication and role-based access control** 

|Field|Record|
|---|---|
|**Status**|New at M2|
|**Context**|FR-010, FR-011 and FR-018 require role-based access. NFR-002 requires authorisation on every read. Forward Engineering<br>Consideration #1 warns that retrofitting access control forces rework of the data model and UI.|
|**Constraints**|Sensitive request categories (Forward Engineering Consideration #6). Credentials hashed at cost factor 12 (NFR-001). A single<br>deployable with server-side rendering through Inertia (D-006).|
|**Alternatives**|(a) Laravel’s session-based authentication with CSRF protection and framework policies. (b) API tokens held in browser<br>storage. (c) An external identity provider.|



|Field|Record|
|---|---|
|**Decision**|Adopt (a): session cookie authentication with CSRF protection, and authorisation enforced by ServiceRequestPolicy at the<br>data-access layer rather than per screen. Roles are stored on users.role.|
|**Rationale**|Because D-006 keeps one deployable and Inertia requests are ordinary authenticated requests, no cross-origin token exchange<br>is needed, which removes the CORS and stateful-domain configuration a separate SPA would have required. Framework<br>policies apply the same rule to every query path, which is what NFR-002 and ASR-002 require. An external provider adds cost<br>and an external dependency for no stated requirement.|
|**Trade-offs**|Session authentication assumes browser clients. A future non-browser client would need token authentication added<br>alongside.|
|**Risks**|The role model must match the data model, CR-009 fixed it at three roles. Policy coverage must be verified by test rather than<br>assumed.|
|**Evidence**|FR-010, FR-011, FR-018, NFR-001, NFR-002. Section 13.5.1.|
|**Later consequence**|Determines the users.role column, the policy test set for M3, and the route-level role guards, which remain a usability<br>convenience only.|



#### **D-013: Deployment direction and environment strategy** 

|Field|Record|
|---|---|
|**Status**|New at M2; supersedes D-005|
|**Context**|A deployment direction is needed so the architecture and technology choices can be checked for plausibility, but provider<br>selection depends on evidence the team does not yet have.|
|**Constraints**|Free or low-cost services (CON-003). One web process, one queue worker and one PostgreSQL instance (ASR-006).<br>Environment separation (Master Brief section 17). A persistent worker is required by D-011.|
|**Alternatives**|(a) Commit to a named provider now. (b) Fix the topology and environment strategy and defer the provider. (c) Defer the<br>whole question to M3.|
|**Decision**|Adopt (b). The topology is one Laravel deployable serving Inertia pages and compiled assets, one queue worker sharing that<br>codebase, and a managed PostgreSQL instance. Development runs on Docker Compose and test runs in CI against a<br>PostgreSQL service container. The specific provider is deferred.|



|Field|Record|
|---|---|
|**Rationale**|Because D-006 produces a single deployable, no separate static host is required, which widens the range of usable free tiers<br>and shortens the deployment path. Fixing the topology is enough to size free-tier limits and validate the architecture, whereas<br>naming a provider before verifying worker support and backups would be an unevidenced claim.|
|**Trade-offs**|Environment-specific configuration and secrets handling must be designed before the provider is known.|
|**Risks**|R-009 worker support on free tiers. R-013 idle pausing and storage caps. NFR-006 recovery depends on the provider’s backup<br>facility.|
|**Evidence**|Sections 13.7.1 to 13.7.5. Master Brief section 17. Free-tier limits recorded in section 13.7.4.|
|**Later consequence**|Provider selection remains open in section 13.8 and must be closed before the M3 staging deployment.|



#### **D-014: Construction deferred to Milestone 3** 

|Field|Record|
|---|---|
|**Status**|New at M2|
|**Context**|Milestone 2 is a design baseline for this team, and construction is planned to begin in Milestone 3.|
|**Constraints**|The fixed M2 window (CON-002). PHP is new to all three members (CON-006). Milestone 2 brief section 7 expects<br>development to have begun.|
|**Alternatives**|(a) Build a thin vertical slice during M2. (b) A throwaway prototype only. (c) Defer construction to M3 with the repository, CI<br>and backlog prepared during M2.|
|**Decision**|Adopt (c): the design baseline, backlog and repository governance (protected main and two-reviewer approval) are prepared<br>during M2. The repository folder structure, README, pull request template and CI workflow are created at the start of M3<br>together with the Laravel project, and application code begins in M3.|
|**Rationale**|Concentrating construction in M3 keeps the M2 evidence coherent and avoids partly-built code that contradicts the baseline.<br>The team accepts that the evidence for the development criterion is correspondingly limited.|
|**Trade-offs**|Implementation and verification columns in the RTM read ‘Planned - M3’, and the end-to-end trace is complete only as far as<br>the technology decision.|
|**Risks**|R-015: without a proof of concept, the stack and toolchain risks (R-001, R-014) remain unvalidated until M3.|



|Field|Record|
|---|---|
|**Evidence**|Milestone 2 brief sections 5.7, 7 and 12. RTM section 6.2 status values. Forward Engineering Consideration #7.|
|**Later consequence**|Sets the M3 build order: the first deliverable is a vertical slice covering FR-001, FR-017 and FR-019.|



**D-015: Status taxonomy and controlled transitions** 

|Field|Record|
|---|---|
|**Status**|New at M2; closes Forward Engineering Consideration #2|
|**Context**|Forward Engineering Consideration #2 left the status vocabulary open, but FR-006, FR-017, FR-019 and the D-008 transaction<br>all depend on a fixed set of states and legal transitions.|
|**Constraints**|Status updates limited to pre-defined options (FR-017). Transitions must be auditable (D-002) and enforceable in the database<br>(D-008).|
|**Alternatives**|(a) Free-form status text. (b) A fixed enumeration checked in application code only. (c) A fixed enumeration plus a seeded<br>transition table referenced by the history rows.|
|**Decision**|Adopt (c): the statuses are open, assigned, in_progress, resolved and closed, with legal transitions seeded in<br>request_status_transitions and referenced by a composite foreign key from request_status_history.|
|**Rationale**|The service layer can return a clear message for an illegal transition while the database refuses it as a backstop, which is the<br>layered validation Assignment 2 Task 2 recommended. A code-only enumeration leaves the database able to store states the<br>business rules forbid.|
|**Trade-offs**|Adding or renaming a status requires a migration and a seeded row rather than a code change alone.|
|**Risks**|If the CHECK constraint and the transition table drift apart, a legal transition could be rejected or an illegal one accepted<br>(Forward Engineering Consideration #3).|
|**Evidence**|Assignment 2, Task 2, Section 3.2. Forward Engineering Consideration #2. Change record CR-010.|
|**Later consequence**|Fixes the status options offered in the UI, the transition test set for M3, and the definitions of open and overdue used in<br>reporting.|



**10. GitHub & Team Governance** 

### **10.1 Repository** 

|**Control**|**Status / Evidence Location**|
|---|---|
|Repository URL|https://github.com/Destroyer1819/SEN381_Project|
|Main branch protected|Yes|
|PR required for substantive<br>changes|Yes|
|Minimum two-reviewer<br>approval configured|Yes|
|Self-approval disabled|Yes|
|Secrets and credentials<br>policy|.gitignore excludes environment files; no credentials are committed; environment values are documented in .env.example; CI<br>secrets will be held in GitHub Actions secrets once the CI workflow exists.|
|Issue board / backlog in use|https://github.com/users/Destroyer1819/projects/2|
|Pull request template|planned|
|Continuous integration<br>workflow|planned|
|Required status checks on<br>main|planned|



### **10.2 Team Working Agreement** 

**Roles:** All three members contribute across requirements, engineering artefacts and repository work. No member is confined to a single artefact type. Current section ownership: Jared - Sections 1, 2 and 3 (Problem/Business Need, Stakeholder Analysis, Scope Baseline), Michael - Sections 4 & 6 (Requirements, RTM), Xander - Sections 5, 7, 8, 9, and 10 (Constraints, Risk Register, Forward Engineering Considerations, Decision Log, GitHub Governance). Ownership may shift for M2 to M4 as the work changes. 

**Communication:** The team will check in at least twice a week (in person or via chat) and immediately before each milestone deadline. Blockers are raised as soon as identified rather than at the next scheduled check-in. 

**Commit & PR conventions:** Commits use short, meaningful messages describing the change (e.g. "Add NFR-004 performance requirement"). Substantive changes to controlled artefacts (PED sections, registers, code) go through a Pull Request. Direct pushes to main are not used for substantive change, per the Master Project Brief section 9. 

**Review:** Every PR requires review from the two team members who did not author it before merging. Reviewers check alignment with requirements/acceptance criteria, consistency with other PED sections, and flag anything that looks rubber-stamped rather than genuinely reviewed. 

**Conflict resolution:** Disagreements on content or approach are raised in the team chat first. If unresolved within 24 hours, the team discusses it together (in person or on a call) rather than letting it stall a PR. The lecturer is consulted only if the team genuinely cannot reach agreement. 

**AI use:** Any material AI-assisted contribution must be logged in the AI Usage Register (Section 11) by the student who used it, including what was verified, changed or rejected, before it is merged into a controlled artefact. 

### **10.3 Repository structure and continuous integration** 

The repository is organised so that documentation, decisions and code sit beside each other and a single pull request can carry a change to all three. 

|**Path**|**Contents**|
|---|---|
|docs/PED/|The Project Engineering Document|
|docs/decisions/|One file per decision record, D-001 onward|
|docs/requirements/, risk/, change/|Requirement notes, risk working papers, change request forms|
|docs/architecture/, deployment/|Diagrams and environment notes|
|app/, database/, resources/js/|Laravel application code, migrations and React components (from Milestone 3)|
|tests/|Feature and unit tests (from Milestone 3)|
|.github/workflows/|Continuous integration workflow|
|.github/pull_request_template.md|Pull request template|



This structure will be created at the start of Milestone 3 together with the Laravel project (D-014). 

## **11. AI Usage Register** 

|**Date**|**Student**|**Tool**|**Engineering Task**|**AI Contribution**|**Verification**|**Decision**|**Issues Found**|
|---|---|---|---|---|---|---|---|
|08/09/<br>2026|Michael|Claude|Finding sources|Provided a list of sources|Read the sources|Accepted|None|
|08/09/<br>2026|Michael|Claude|Understanding<br>the project|Provided explanations|See if the explanation<br>makes sense, and<br>check with other<br>online sources|Partially<br>accepted|Not all of the explanations<br>were relevant to the project|
|07/09/2<br>026|Xander|Claude|Drafting M1 PED<br>template structure<br>(all section<br>headings, tables,<br>guidance notes)|Generated the section<br>structure, table layouts and<br>instructional notes<br>matching the M1 brief's<br>required outputs|<br>Checked every table's<br>required fields against<br>the Milestone 1 Brief<br>section 3 and Master<br>Brief sections 9-13 field-<br>by-field|Accepted<br>(structure<br>only, no<br>project<br>content yet)|None. structure only, no factual<br>claims to verify|
|08/09/2<br>026|Xander|Claude|Drafting Sections 5<br>(Constraints), 7<br>(Risk Register), 8<br>(Forward<br>Engineering<br>Considerations)<br>and 9 (Decision<br>Log) content|Drafted CivicConnect-<br>specific constraint<br>implications, 7 project risks,<br>6 forward-engineering<br>concerns and 5 decision-log<br>entries|<br> <br>Cross-checked every<br>claim against the<br>CivicConnect Master<br>Project Brief and<br>Milestone 1 Brief<br>sections cited inline (e.g.<br>section 2, 3, 9, 18.1);<br>reviewed with Michael<br>before merging|<br>Partially<br>accepted|Owner names were placeholders<br>([Name]) and needed real<br>assignment; some risk/decision<br>wording needed personalising<br>against the team's actual scope<br>once agreed|
|09/09/2<br>026|Xander|Claude|Reviewing merged<br>team document<br>(Michael's Sections<br>4 & 6) for<br>consistency and<br>completing AI<br>Usage Register /<br>Risk Register<br>owners / Team<br>Working<br>Agreement|<br>Identified that AI Usage<br>Register did not yet log<br>Xander's own AI-assisted<br>sections; drafted Section<br>10.2 Team Working<br>Agreement content;<br>proposed a draft Risk<br>Register owner distribution;<br>aligned FR-001 wording<br>between Section 4 and the<br>RTM|<br>Reviewed against the<br>Master Project Brief's<br>Responsible AI standard<br>(section 10) requiring<br>every material AI<br>contribution to be<br>logged and verified;<br>owner assignments and<br>working-agreement<br>content flagged to team<br>as draft, pending<br>confirmation|<br>Accepted,<br>pending<br>team<br>confirmation<br>of owner<br>assignments|<br>None found, flagged as<br>draft/pending team sign-off<br>rather than a factual error|



|**Date**|**Student**|**Tool**|**Engineering Task**|**AI Contribution**|**Verification**|**Decision**|**Issues Found**|
|---|---|---|---|---|---|---|---|
|4/09/<br>2026|Michael|Claude|Linking of multiple<br>sections, such as<br>the ASR’s to the<br>influence of the<br>ASR’s on the<br>stakeholders.|Found potential links and<br>provided explanations|Read through the<br>explanations, and<br>verified if the links<br>found are valid|Partially<br>accepted|Some potential links included<br>links with little in common to<br>what they are linked with.|
|24/09/2<br>026|Jared|Claude|Structuring the<br>Milestone 2 work:<br>what evolves in the<br>PED and where<br>each M2<br>deliverable belongs|<br> <br>Produced a section-by-<br>section plan mapping M2<br>brief and Master Brief<br>requirements to PED<br>sections|Checked each mapping<br>against the Milestone 2<br>brief sections 4, 5 and<br>12 and the Master Brief<br>section 20.2 list|Accepted<br>with<br>changes|The first version proposed new<br>sections for everything; the<br>team required the M1 baseline<br>to be extended instead|
|25/09/2<br>026|Jared|Claude|Technology<br>selection research<br>and the weighted<br>matrix structure|Listed common web<br>bundles, proposed criteria<br>and weights traced to<br>constraints and ASRs, and<br>produced illustrative scores|<br>Team scored every cell<br>itself and rejected the<br>initial weighting; free-<br>tier and licensing claims<br>checked against the<br>providers’ own pricing<br>pages|Partially<br>accepted|Early scoring treated capability<br>as one criterion and had to be<br>split into language familiarity<br>and framework learning; a lock-<br>in score was inconsistent and<br>was corrected|
|26/09/2<br>026|Xander|Claude|Drafting decision<br>records D-007 and<br>D-010 to D-015,<br>and restructuring<br>Section 9|Drafted the missing<br>decision records in the<br>Master Brief section 13<br>field structure and<br>reformatted the log one<br>decision per table|Each record checked<br>against the Assignment<br>2 task it cites and<br>against the design in<br>Section 13; D-006 and<br>D-007 rewritten by the<br>team after the Inertia<br>decision|Accepted<br>with<br>changes|The first draft recorded the SPA<br>architecture, which contradicted<br>the matrix result and was<br>corrected|
|26/09/2<br>026|Xander|Claude|Raising risks R-008<br>to R-016 and<br>reviewing R-001 to<br>R-007|Proposed new risks traced<br>to the M2 decisions, and<br>Milestone 2 review notes<br>for the existing risks|Team confirmed or<br>changed every<br>probability, impact and<br>owner; R-010 reworded<br>because Inertia<br>removed the CORS<br>exposure|Accepted<br>with<br>changes|Suggested owners and ratings<br>were placeholders and were<br>reassigned by the team|



|**Date**|**Student**|**Tool**|**Engineering Task**|**AI Contribution**|**Verification**|**Decision**|**Issues Found**|
|---|---|---|---|---|---|---|---|
|26/09/2<br>026|Jared|Claude|Consistency review<br>of the merged<br>document|<br>Identified contradictions<br>and missing cross-<br>references between<br>Sections 6, 8, 9, 11, 12 and<br>13|Every reported issue<br>checked in the<br>document before acting<br>on it|Partially<br>accepted|Some reported items were<br>formatting artefacts of the<br>conversion rather than real<br>defects|



## **12. Baseline Readiness Checklist** 

### **Milestone 1 Readiness Checklist** 

- PED v1.0 is complete, coherent, versioned and reviewed 

- Scope and requirements are baselined rather than merely drafted 

- RTM, Risk Register, Decision Log, Forward Engineering Considerations and AI Usage Register are current 

- GitHub governance required by the Master Project Brief is demonstrable 

- Team Working Agreement is agreed and controlled 

- Baseline sign-off/gate evidence is ready 

- All students can independently locate and explain the project foundation and its Software Engineering significance 

- All students are prepared to present professionally and defend the team's evidence individually 

### **Milestone 2 Readiness Checklist** 

- PED v2.0 visibly continues M1 and retains traceable history - Met: document control, change record CR-001 to CR-019. 

- RTM has evolved with architecture/data/design/interface/technology/implementation/verification/status evidence - Met: Section 6.2 (implementation and verification Planned M3 under D-014). 

- Risk Register, assumptions/dependencies and Forward Engineering Considerations reflect new evidence - Met: Sections 7, 5.2 and 8. 

- ASRs/quality drivers are project-specific and linked to architecture decisions - Met: Section 4.3. 

- Architecture selection and diagrams are defensible and proportionate - Met: Section 13.1, D-006, CR-017. 

- Data/persistence decisions are documented and linked to correctness/quality needs - Met: Section 13.2, D-008, D-015. 

- Technology stack is justified with evidence rather than preference - Met: Section 13.3, D-007. 

- At least two genuine design problems have final pattern/approach decisions informed by A2 research - Met: Section 13.4, D-009, D-010. 

- A2 research is referenced as supporting evidence, not copied - Met: D-008 to D-011. 

- Initial interface/integration decisions are documented where applicable - Met: Section 13.6. 

- Architecture, Technology & Initial Design Baseline is identifiable and controlled - Met once signed: Baseline Approval (Milestone 2). 

- Meaningful development has begun against the baseline - Deferred to Milestone 3 by D-014. 

- Application/technical documentation matches the repository/application - Deferred with D-014. 

- At least one requirement traces into implementation and verification evidence - Trace complete to the technology decision (D-007); implementation and verification planned in M3 (D-014). 

- GitHub history demonstrates progressive controlled work and peer review - Met for documentation: pull requests with two approvals. 

- Relevant A2 SCM/CI recommendations are adopted progressively - Partly met: protected main and two-reviewer approval adopted; PR template and CI at the start of M3. 

- AI use is recorded and verified - Met: Section 11. 

- Every team member can defend the shared evidence - confirmed 

## **13. Solution Design Baseline** 

## **13.1 Architecture** 

### **13.1.1 Logical components** 

|**Component**|**Responsibility**|
|---|---|
|Submission|Accepting, validating, and providing persistence to the new requests that are being made.|
|StatusAudit|Status changes, updates and appending the records of who made a change, what change was made,<br>and when was the change made.|
|Assignment|Responsible for the ownership of a request, be it assigning a request, offering a request to another<br>staff member, or accepting an offered request.|
|RequestQuery|Reading and then returning of data. Includes search functions, sorting, filtering, listing and pagination<br>of data.|
|Authentication|Verifies who the user is (session login) and which of the three roles they hold (D-012).|



|**Component**|**Responsibility**|
|---|---|
|Category handling|Category specific validation and routing rules.|
|Notification|Delivering notifications about the status changes to the requesters.|
|Retention|Enforcement of the 5-year retention before permanent deletion rules.|
|Authorisation|ServiceRequestPolicy: decides which requests each role may see and change, applied on every query<br>path.|
|Reporting|On-demand management reports: counts by status and category, the overdue list and average time to<br>resolution.|



### **13.1.2 Considered alternatives** 

|**Option**|**Description**|**ASRs supported**|**Risks / weaknesses**|**Decision**|
|---|---|---|---|---|
|A. Server-<br>rendered<br>monolith (Blade)|Pages are rendered by Laravel Blade<br>views and the browser submits<br>ordinary forms. No client-side<br>application.|ASR-001, ASR-002 (authorisation<br>stays on the server), ASR-006<br>(single deployable).|Leaves the team’s React and TypeScript<br>experience unused; less interactive<br>filtering and status updates.|Rejected - kept as fallback B-<br>003 (R-001)|
|B. Layered<br>modular<br>monolith with<br>Inertia-rendered<br>React|Laravel controllers return Inertia<br>responses and React + TypeScript<br>components render the pages. One<br>deployable, session authentication,<br>no JSON API.|ASR-002 (policies on every query<br>path), ASR-003 (D-008<br>transaction in one process),<br>ASR-004 (business rules in<br>services), ASR-006 (one web<br>process, one worker, one<br>database).|Front-end and back-end are coupled and<br>released together; page props are not a<br>reusable API (R-016).|Selected (CR-017)|
|C. Layered<br>modular<br>monolith with a<br>separate React|A React single-page application calls<br>a Laravel API using token or cookie-<br>based authentication.|ASR-002, ASR-003, ASR-004.|Adds a network boundary, CORS and<br>token handling, a second deployable and<br>duplicated validation for a client the<br>project does not need to serve<br>independently (Assignment 2, Task 3).|First selected; superseded by<br>CR-017|



|**Option**|**Description**|**ASRs supported**|**Risks / weaknesses**|**Decision**|
|---|---|---|---|---|
|SPA over a<br>Laravel JSON API|||||
|D. Distributed<br>services|Separate deployables for requests,<br>notifications, reporting and identity.|None better supported than<br>option B.|The status change and audit write<br>become a distributed transaction (ASR-<br>002, ASR-003); several deployables<br>exceed free-tier limits (ASR-006).|Rejected|



### **13.1.3 Justification of selected architecture** 

CivicConnect adopts option B: a layered modular monolith in which Laravel controllers return Inertia responses and React with TypeScript renders the pages. Option C, a separate React single-page application over a Laravel JSON API, was the first selection and was superseded during Milestone 2 by CR-017. 

Accountability is the first reason. Weak accountability for status changes is a core problem in the business need. D-002 makes the status history append-only, and D-008 writes the status change and its history row in one database transaction. That guarantee depends on both writes happening in one process against one database; option D would turn it into a distributed transaction. 

Access control is the second reason. FR-013 lets staff combine search, filter and sort freely, so there is no single screen at which permissions can be checked. In option B every page visit is an ordinary authenticated request to Laravel, and ServiceRequestPolicy applies the same rule on every query path (D-012, ASR-002). Option C would need the same rules exposed through an API, with authentication carried across a network boundary using CORS and token handling. 

Integrity is the third reason. A stale write is rejected by the optimistic version check in D-008 instead of silently overwriting another staff member’s change (R008). 

Cost rules out option D and weighs against option C. The cost constraint (CON-003) favours the fewest deployables, and option B runs as one web process, one queue worker and one PostgreSQL instance (D-013). A more distributed system is not automatically more advanced, and no requirement justifies the extra complexity. 

The trade-off accepted with option B is coupling: the React pages and the Laravel application are released together, and page props are an internal contract rather than a reusable API. No requirement in the approved scope needs a second client, so this is accepted and tracked as R-016. 

### **13.1.4 Physical deployment** 

The components in 13.1.1 are logical boundaries in the source tree, not separate processes. CivicConnect runs as three physical units: 

Laravel web process: runs all the logical components in 13.1.1, handles every page visit and returns Inertia responses together with the React and TypeScript assets compiled by Vite. 

Queue worker: runs php artisan queue:work from the same codebase and executes the queued NotifyRequesterOfStatusChange listener (D-011). Laravel’s scheduler, which runs the retention purge (FR-008), also runs from this codebase (13.7.3). PostgreSQL database: stores all application data and the job queue (database queue driver). It is a single point of failure, accepted at MVP under the cost constraint and mitigated by backups (13.7.5). 

**13.1.5 Architectural diagrams** 

shows the logical components inside the single Laravel deployable, the queue worker and scheduler that share its codebase, and the external mail provider. 



<!-- Start of picture text -->
Figure 13.1: CivicConnect logical architecture<br>‘One Laravel deployable serving Inertia pages » one queue worker -one PostgreSQL instance (D-006, D-013)<br>Browser<br>React + TypeScript pages (Inertia client)<br>HTTPS. session ome Inertia page visits<br>| Laravel web process (one deployable) }<br>{ ; | Scheduler (same codebase) ;<br>{ Routes, middleware and controllers ' ‘ '<br>t controllers return Inertia responses (page component+ props) } f !<br>{ ; ; Retention ;<br>q 1 daily purge of records 5 years 1<br>! Authentication Authorisation - ServiceRequestPolicy ] f after last update (FR-008) q<br>' session login and the user's role (D-012) applied on every query path (D-012, ASR-002) ; ‘ q<br>{ ‘Submission Category ausAual Assignment RequestQuery Reporting same tnodels<br>' create and validate handling (0-010) ooo offer accept search, fter, sort ‘on demand (0-003)<br>{ (0-008, 0-009) i<br>{<br>{' porn tert reer tren tenses<br>{ ] | Queue worker (same codebase) ;<br>t Data access ~ Eloquent models - ' !<br>H ' ' Notification '<br>{ ‘Modules are folders in one codebase, not separate services (13.1.4). ; { NotifyRequesterOfStatusChange 1<br>'' transactionStatusAudit commits queues RequestStatusChanged (0-009); the ob isa rowonlyntheafter jobs its !; tof queued listener (0-011) !;<br>' table, picked up by the queue worker (D-011). } ' '<br>t 1 ee en<br>‘SQL: data and queued jobs<br>worker reads the jobs table<br>PostgreSQL<br>application tables - jobs table (database queue driver)<br>Mail provider<br>external service<br><!-- End of picture text -->

**13.2 Data and persistence baseline** 

### **Intro** 

This section is the design-level explanation, and identifies the core data entities CivicConnect needs at this stage, sets out the initial data model, and commits the persistence approach that keeps a status change and its audit entry consistent. The full decision record is D-008 in Section 9. 

### **13.2.1 Core data entities** 

|**Table**|**Purpose**|**Traces back to**|
|---|---|---|
|users|Account data for every user: name, email, password hash and one of three roles.|FR-010, NFR-001, NFR-002|
|categories|Seeded, controlled list of request categories, including ‘Other’.|FR-005, D-001, D-010|
|service_requests|The root entity: requester, category, location, area, description, current status, assignee<br>and version.|FR-001, FR-006, FR-023, D-008|
|request_status_transition<br>s|Seeded list of legal from-status / to-status pairs.|FR-017, D-015|
|request_status_history|Append-only record of every status change: request, previous and new status, who and<br>when.|FR-014, FR-019, NFR-003, D-<br>002|
|request_assignments|Offers, acceptances and self-assignments, with who and when.|FR-015, FR-016|
|request_comments|Staff comments and resolution notes, with author and time.|FR-014, FR-020|
|jobs, failed_jobs|Laravel database queue tables for queued notifications.|D-011|



**13.2.2 ERD** 

Figure 13.2 shows the initial schema. Table ownership and lifecycle are in 13.2.3 



<!-- Start of picture text -->
Figure 13.2: CivicConnect entity relationship diagram (initial schema, v2.0)<br>PK primary key - FK foreign key -UQ unique -CHK check constraint - crow’s foot = many<br>changed by<br>users service_requests categories<br>Kid PK id PK id<br>name requester id J reference_number code<br>ug email assigned to Ny veFR requester_id~ users S category_id ve name<br>password berypt hash FK categoryid categories is_active<br>cHK role requesterfstafimanagement location<br>created_at area<br>updated_at description<br>statuses (0-015)<br>CHK status S<br>FR assigned_to users, nullable pemnee tien stsiany<br>version int, default 0 (0-008) request id id<br>i FK request id sence requests<br>request o created_at 4 "<br>updated_at = ae<br>FK — from_status,<br>resolved_at nullable FK to status 1 compositetransitionsFK &[A<br>thor id<br>authori FK — changed_by users<br>changed_at append-only<br>request id ¥<br>assigned to, from_status, status<br>Z\ offered_by<br>request_commentsZ\ request_assignmentsZ\ request_status transitions<br>PK id PK id PK from status<br>Fe request irae |r requestid sari joaas PK tostatus seeded (0-015)<br>FK —author_id users FR assigned_to users<br>body FK offered _by users<br>is_resolution boolean CHK state offeredjaccepted|decined|selt<br>|_Sentegcreated_at at created_atA jobs, failedjobs<br>responded_at Laravel database queue tables (0-011)<br>Every status change writes service_requests.status/version and one request_status_history row in a single transaction (D-008).<br><!-- End of picture text -->

### **13.2.3 Data Ownership and Lifecycle** 

|**Table**|**Owning component**|**Written by**|
|---|---|---|
|users|Authentication|Account creation (OD-10) and profile updates.|
|categories|Category handling|Seeded by migration; changed only through a migration and a change<br>record.|
|service_requests|Submission (create); StatusAudit (status);<br>Assignment (assignee)|Created by the submission service. Status changed only by<br>RequestStatusService (D-008, R-011). Category changed only by an<br>audited staff reclassification.|
|request_status_transitions|StatusAudit|Seeded by migration (D-015).|
|request_status_history|StatusAudit|Inserted inside the D-008 transaction; never updated; purged 5 years<br>after the request’s last update.|
|request_assignments|Assignment|Written on offer, accept, decline and self-assignment.|
|request_comments|StatusAudit|Written when staff add a comment or resolution note.|
|jobs, failed_jobs|Notification|Written by the framework when the listener is queued or fails.|



### **13.2.4 Persistence model choice** 

The database that will be used is a relational model in PostgreSQL. The evidence for this decision is as follows: 

|**Factor**|**Evidence**|**Implication**|
|---|---|---|
|Structure|Users, categories, requests, assignments,<br>comments and status history are separate entities<br>linked by foreign keys, with one-to-many<br>relationships from each request (13.2.1).|A relational model represents these<br>relationships directly. A document store<br>would duplicate user and category data<br>inside every request.|
|Integrity|Status values and legal transitions must be<br>enforced (FR-017, D-015), and every history row<br>must point to a real request and user (FR-019).|PostgreSQL CHECK constraints, foreign<br>keys and a composite foreign key to<br>request_status_transitions act as a<br>backstop behind service-layer validation.|
|Consistency|A status change and its audit row must succeed or<br>fail together (D-002, D-008), and concurrent edits<br>must not overwrite each other (R-008).|ACID transactions (DB::transaction) plus a<br>version column for optimistic<br>concurrency. No eventual consistency is<br>needed at MVP.|
|Access patterns|Staff and management search, filter, sort and<br>paginate lists (FR-009, FR-013); overdue is derived<br>from timestamps (FR-023).|Indexes on status, category_id,<br>assigned_to and updated_at; pagination<br>at 50 rows per page (NFR-004, ASR-001).|
|Sensitivity|Security and IT-support requests may contain<br>personal or safety-related information (CON-005,<br>FEC 6).|Authorisation is applied at the data-access<br>layer (D-012); passwords are hashed<br>(NFR-001); encryption at rest depends on<br>the provider (OD-05).|
|Growth|Records are kept 5 years after the last update (FR-<br>008) on a free tier with a storage cap (CON-003,<br>ASR-006).|Rows are small text records. A 5-year<br>volume estimate is checked against the<br>provider’s cap before OD-01 closes (AS-<br>003), and the purge job removes expired<br>rows.|



### **13.2.5 SPOF, scalability and bottlenecks** 

The single PostgreSQL instance is a single point of failure. This is accepted at MVP under the cost constraint (CON-003) and mitigated by the provider's backup and recovery facilities (13.7.5). 

Horizontal scaling is not required at this stage. CivicConnect serves a single organisation, load grows gradually rather than in spikes, the architecture is a single deployable (D-006), and additional instances would exceed the free-tier constraint. NFR-004 is a response-time target and is met through indexing and pagination (13.2.4), not additional servers. If growth erodes that target, vertical scaling is the first response. Horizontal scaling becomes necessary only if load exceeds what one server can handle, or if the NFR-005 availability target requires removing the single point of failure. 

Three bottlenecks are expected. The first is the database connection limit: free-tier PostgreSQL caps concurrent connections (CON-003), and every page request, the queue worker and each D-008 transaction hold one, so transactions are kept short. The second is reporting load: FR-024 aggregates over request_status_history, the fastest-growing table, on the same instance that serves day-to-day work; on-demand reporting (D-003) and indexes limit this. The third is the retention purge (FR-008): deleting a large batch of expired rows at once can lock tables, so the purge runs off-peak in batches. 

## **13.3 Tech Stack Analysis** 

### **13.3.1 Common web bundles** 

|**Bundle**<br>**ID**|**Bundle**|**Frontend**|**Backend**|**Database**|**Source**|
|---|---|---|---|---|---|
|B-001|MERN / PERN|React (JS/TS)|Node + Express|MongoDB (MERN) / PostgreSQL (PERN)|(Express, 2026)|
|B-002|Next.js full-stack|React via Next.js|Next.js route<br>handlers / server<br>actions (Node),<br>usually Prisma|PostgreSQL|(Next.js, 2026)|
|B-003|LAMP / LEMP|Server-rendered PHP views (Blade) + light<br>JS|PHP on Apache or<br>Nginx, typically<br>Laravel|<br>MySQL / MariaDB (PostgreSQL also<br>supported)|(Laravel, 2026a)|
|B-004|Laravel API + SPA|React or Vue SPA (TypeScript)|Laravel JSON API<br>(PHP)|PostgreSQL or MySQL|(Laravel, 2026b)|



|**Bundle**<br>**ID**|**Bundle**|**Frontend**|**Backend**|**Database**|**Source**|
|---|---|---|---|---|---|
|B-005|Django|Django templates, or React with Django<br>REST Framework|Django (Python)|PostgreSQL|(Django Software<br>Foundation, 2026)|
|B-006|ASP.NET Core|Razor Pages, Blazor, or React|ASP.NET Core (C#<br>+ Entity<br>Framework|)<br>SQL Server (PostgreSQL supported)|(Microsoft, 2026)|
|B-007|Spring Boot|Angular or React|Spring Boot (Java<br>+ JPA/Hibernate|)<br>PostgreSQL or MySQL|(Spring, 2026)|
|B-008|Rails|ERB views with Hotwire/Turbo, or React|Ruby on Rails|PostgreSQL|(Ruby on Rails, 2026)|
|B-009|Laravel + Inertia|React + TypeScript via Inertia|Laravel (PHP)|PostgreSQL|(Inertia.js, 2026) (Laravel,<br>2026c)|



The bundles were selected from the most widely used web frameworks among developers (Statista, 2026) 

PostgreSQL capabilities are taken from the PostgreSQL Global Development Group (2026). 

### **13.3.2 Candidates not recommended** 

MERN was excluded before scoring because MongoDB is not a relational database, and the data design (D-008, D-015) depends on transactions, foreign keys and CHECK constraints. Its relational variant, PERN (PostgreSQL), was kept and scored as B-001 

### **13.3.3 Weight matrix** 

Scale: 1 = poor fit or high risk, 2 = weak, 3 = adequate with effort, 4 = good, 5 = strong, with evidence. Weights total 100. Maximum weighted score 500. 

|**Criteria**|**Weighting**|||||**Options**|||||
|---|---|---|---|---|---|---|---|---|---|---|
|||B-001|B-002|B-003|B-004|B-005|B-006|B-007|B-008|B-009|
|Requirement & ASR fit|15|4|3|5|5|5|5|5|5|5|
|Team capability|8|3|3|2|2|4|4|4|2|2|



|**Criteria**<br>**W**|**eighting**|||||**Options**|||||
|---|---|---|---|---|---|---|---|---|---|---|
|Framework & surface learning|12|2|3|3|2|3|3|1|2|3|
|Dev environment availability|10|5|5|4|4|4|5|5|3|4|
|Security ecosystem|12|3|3|5|4|5|5|5|5|5|
|Maintainability|10|4|3|4|4|4|4|4|4|4|
|Testing & tooling|10|4|3|5|4|4|4|4|5|5|
|Deployment & ops|10|4|2|5|3|4|3|3|4|5|
|Cost|5|4|4|5|4|5|3|3|5|5|
|Lock-in|4|4|2|3|5|3|3|5|3|4|
|Fallback consequence|4|4|2|3|4|3|3|2|3|4|
|Weighted total (/500)||366|307|416|369|412|402|382|384|424|
|Percentage (%)||73|61|83|74|82|80|76|77|85|



### **13.3.4 Weight matrix for database** 

|**Criteria**|**Weight**|**PostgreSQL**|**MySQL / MariaDB**|**SQL Server**|
|---|---|---|---|---|
|Integrity support: CHECK constraints,<br>composite foreign keys, transactional DDL|30|5|3|4|
|Team capability|20|5|4|2|
|Managed free-tier availability|20|4|4|2|
|Migrations and tooling support in the<br>chosen stack|15|5|4|4|
|Cost and lock-in|15|5|5|3|



|**Criteria**|**Weight**|**PostgreSQL**|**MySQL / MariaDB**|**SQL Server**|
|---|---|---|---|---|
|Weighted total (max 500)||480|385|305|
|Percentage||96|77|61|



### **13.3.5 Results of the matrices** 

B-009 (85%), B-003 (83%), B-005 (82%) and B-006 (80%) fall within five percentage points of one another. The matrix therefore narrows the field to four defensible candidates rather than producing a single winner and the final selection is recorded in D-007 together with the stated tiebreakers. 

Sensitivity check: halving the two capability criteria to ten points combined and reallocating those ten points to requirement and ASR fit gives B-009 90%, B-003 88%, B-005 86% and B-006 84%. The order of the leading four is unchanged, so the result is not an artefact of the weight placed on learning curve. A second check moved the weight the other way. Raising the two capability criteria to 30 points combined (team capability 12, framework learning 18) and reducing requirement and ASR fit to 5 gives B-009 80.0%, B-005 79.2%, B-003 78.4% and B-006 77.2%. B-009 remains first, but its lead falls to 0.8 points, so the ranking is sensitive to how heavily the learning curve is weighted. This is why the tiebreakers in D-007 matter. 

### **13.3.6 Selected versions and compatibility** 

Target versions for the selected stack. Each is confirmed against composer.lock and package.json when the project is created. Any change after the baseline goes through the change record. 

|**Component**|**Target version**|**Reason / compatibility**|
|---|---|---|
|PHP|8.3 or later|Laravel 13 supports PHP 8.3 to 8.5. Availability on the BC<br>Desktop still to be verified (R-014, AS-002).|
|Laravel framework|13.x|Current major release (March 2026): bug fixes until Q3 2027<br>and security fixes until Q1 2028, which covers the project.<br>Laravel 12 bug fixes ended in August 2026.|
|Inertia (inertiajs/inertia-laravel,<br>@inertiajs/react)|3.x (confirmed from<br>composer.lock at the start<br>of M3)|Inertia v3 was released in March 2026. v2 bug fixes ended on<br>26 September 2026 (security fixes until 26 March 2027), so v3<br>is preferred for a new project.|



|**Component**|**Target version**|**Reason / compatibility**|
|---|---|---|
|React|19.x (confirmed from<br>package.json at the start of<br>M3)|Rendered through the Inertia React adapter.|
|TypeScript|5.x (confirmed from<br>package.json at the start of<br>M3)|Used by the official React starter kit.|
|Node.js|20 LTS or later (confirmed<br>against Vite’s minimum at<br>the start of M3)|Needed for npm and the Vite build.|
|PostgreSQL|To be matched to the<br>provider’s version when<br>OD-01 closes|The local Docker image must match the provider’s version<br>(environment parity, Master Brief section 17).|
|Composer|2.x|PHP dependency manager.|



## **13.4 Design Decisions** 

The two Assignment 2 design problems as final, defended Milestone 2 decisions. The full ADR entries are D-009 and D-010 in Section 9. This subsection is the design-level explanation with diagrams and code links. 

**13.4.1 Design Problem 1: Status-Change Side Effects** 

Problem 

A status change on a request is not a single-field update. FR-016 and FR-019 require an audit entry recording who made the change and when, and FR-007 requires the requester to see the update reflected on their side. Writing all of this directly inside the method that changes the status collects several unrelated reasons to change and becomes harder to extend, for example if an SLA-overdue escalation reaction is added at a later milestone. 

Assignment 2 Alternatives 

Assignment 2 Task 1 compared a direct sequential-call approach against the Observer pattern and a full domain-event/pub-sub dispatcher, and recommended a uniform Observer applied at the ServiceRequest entity. A full dispatcher was set aside there as more infrastructure than a single eventraising entity needed. 

Final Decision (adapts the Assignment 2 recommendation) 

Once Laravel was confirmed as the stack, its built-in event/listener mechanism removed the infrastructure cost that ruled out a dispatcher in the Assignment 2 research, but Assignment 2 Task 2's transaction requirement meant a uniform Observer would not work: an after-commit reaction cannot also be the audit write. The reactions were split: the audit history write happens synchronously inside RequestStatusService::transition(), inside the same database transaction as the status update (D-008). Every other reaction, currently the requester notification, is a queued listener (NotifyRequesterOfStatusChange) on the RequestStatusChanged event, dispatched only once that transaction has committed. RequestStatusChanged implements ShouldDispatchAfterCommit, so Laravel dispatches it only after the D-008 transaction commits; if the transaction rolls back, the event is never dispatched (Laravel, 2026c). 

### Trade-off 

The status-change reaction logic is no longer one uniform mechanism. A misconfigured listener still silently skips its reaction, the same risk a plain Observer would carry. 

Class / Flow Diagram 



<!-- Start of picture text -->
Figure 13.3: Status-change write path (D-008, D-009, D-011)<br>‘Synchronous inside one transaction - asynchronous only after commit<br>| Synchronous - one database transaction (D-008) }<br>‘ GESTS, __--p| crows updated — SualeRequesttxcepton | |<br>\ t eae een version redirect back with an ertor (R-008) ;<br>\ RequestStatusController RequestStatusService '<br>H PATCH IrequestsidVstatus sAransitiond) !<br>' t i INSERT request status history a ‘Any failure ~ rollback '<br>t H ((rom_status, to_status, changed_by) ‘event is never dispatched ;<br>‘ : legal trom- to pairis rejected by the service and by the composite FK to request status, transitions (D-015) '<br>| Asynchronous ~ oniy after the transaction commits (0-009, D-011) '<br>H{' ‘eventRequestStatusChanged -ShouldDispatchatterCommit databasejobs Gunie tableOver worker Conqueued tenereeAcres(ShouldQueve) ‘emailMail tothe provider requester '';<br><!-- End of picture text -->

_Figure: synchronous audit write inside the D-008 transaction, asynchronous notification only after commit._ 

### Planned implementation (Milestone 3) 

These files do not exist yet. Construction begins in Milestone 3 (D-014). Paths will be replaced with repository links when they are created. 

|**Service**|app/Services/RequestStatusService.php - transition()|
|---|---|
|**Event**|app/Events/RequestStatusChanged.php|
|**Listener**|app/Listeners/NotifyRequesterOfStatusChange.php (implements ShouldQueue)|
|**Notification**|app/Notifications/RequestStatusChangedMail.php|
|**Test**|tests/Feature/StatusTransitionTest.php|



### **13.4.2 Design Problem 2: Category-Specific Request Handling** 

Problem 

Decision D-001 fixed the category vocabulary itself, but not how category-specific validation and routing behaviour is implemented. Writing that behaviour as one long conditional inside the request-handling code means every new category requires editing and retesting that same method. Assignment 2 Alternatives 

Assignment 2 Task 1 compared the Strategy pattern against Factory Method and a configuration-only lookup. The configuration-only approach was set aside because CivicConnect's categories differ in behaviour, not only in the fields they require. Factory Method was set aside because the categories differ in behaviour, not in data shape; a subclass per category would force a class hierarchy onto the persisted request and complicate the data model (Assignment 2, Task 1). 

### Final Decision 

The Strategy pattern is adopted as recommended. Each category implements a shared CategoryHandler interface (rules(), defaultAssignee()). CategoryRegistry, bound in AppServiceProvider, resolves the handler for a given category code. An unrecognised code falls back to OtherHandler. StoreServiceRequest merges the resolved handler's rules into its own FormRequest validation. 

Trade-off 

Requires a small registry/lookup mechanism. If the category list stays very small with simple, near-identical rules, this is more structure than the problem needs. 

Class Diagram 



<!-- Start of picture text -->
Figure 13.4: Category-specific handling - Strategy pattern (D-010)<br>Sey boundCategoryRegistry inappsenoeProter categontander«interface»<br>rules; merges handler rules category coe + resolve(code): CategoryHandler returns + rules: array<br>unknown code — OtherHandler + defaultAssignee(): 7User<br>A\<br>implements (realisation)<br>FacilityFaultHandler DamagedEquipmentHandleri ItSupportHandler ‘SecurityConcernHandler LostPropertyHandler Oth e rHandlerS<br>‘Adding a category = one new handler class + one seeded categories row + one line in the registry mapping<br>Existing handlers and StoreServiceRequest are not edited (Open/Closed ~ Assignment 2, Task 1).<br>Categories differ in behaviour (validation, default assignee), not in data shape, so no per-category subclass of the request is persisted<br><!-- End of picture text -->

_Figure: CategoryRegistry resolves a CategoryHandler per category code; OtherHandler is the fallback._ 

### Planned implementation (Milestone 3) 

These files do not exist yet. Construction begins in Milestone 3 (D-014). Paths will be replaced with repository links when they are created. 

|**Interface**|app/Domain/Categories/CategoryHandler.php|
|---|---|
|**Registry**|app/Domain/Categories/CategoryRegistry.php|
|**Handlers**|app/Domain/Categories/Handlers/ (FacilityFault, DamagedEquipment, ItSupport,<br>SecurityConcern, Maintenance, LostProperty, Other)|
|**Binding**|app/Providers/AppServiceProvider.php|
|**Test**|tests/Feature/CategoryHandlingTest.php|



## **13.5 UI & Information Architecture** 

The role-based site map, low-fidelity wireframes for the six screens in scope for Milestone 2, and the usability and accessibility rationale behind them (NFR-007, NFR-008). 

### **13.5.1 Role Sitemap** 

Navigation is organised by role rather than by feature. Auth/Login is the single entry point for all three roles. 

Which pages a logged-in user can reach is read from users.role, not chosen at login. Pages are React components in resources/js/Pages, rendered through Inertia. Laravel route middleware limits each route to the roles that use it, as a navigation convenience; the actual access control is ServiceRequestPolicy on the server (ASR-002). 

Every page is reachable from the role landing page in at most two clicks, satisfying the three-click rule in NFR-007. 



<!-- Start of picture text -->
Figure 13.5: Role sitemap - Inertia pages in resources/js/Pages<br>sale enty‘utLogin part a es<br>[ = | 2steek SosManagementiDashboardensin none<br>one wc tot<br>mega hny sone ae coment ‘una oregano<br>“ArosFoe mientoo al tsarte 8h oe tick ramby ef ks ang nagar: page SaceqesPoyeer page at mst2chs away (NFR)<br>onte sve ste ac aces cone (ASR)<br><!-- End of picture text -->

_Figure: role sitemap - Inertia pages in resources/js/Pages._ 

Every screen is reachable from LoginPage in at most two clicks, satisfying the three-click rule in NFR-007. Route-level role guards are a usability convenience only; the actual access control is enforced server-side by ServiceRequestPolicy (ASR-002). 

### **13.5.2 Wireframes** 

Low-fidelity: they fix what information and controls each screen needs, not final visual design. 



<!-- Start of picture text -->
Figure 13.6: Low-fidelity wireframes<br>- , ‘open vera 7a) esohed al<br><!-- End of picture text -->

_Figure: low-fidelity wireframes - Requests/New, Requests/Index (requester and staff views), Requests/Show, Management/Dashboard and Auth/Login._ 

### **13.5.3 Usability and Accessibility Rationale** 

|**Requirement**|**How the screens satisfy it**|
|---|---|
|NFR-007 (3 clicks from main screen)|Every page is at most 2 clicks from the role landing page reached after Auth/Login. Requests/New and Requests/Index<br>are 1 click from the requester landing page; Requests/Show is 1 click from Requests/Index.|
|NFR-008 (validation failures produce<br>actionable messages)|When validation fails, Laravel redirects back to Requests/New with an error next to each field and the submitted<br>values preserved. If the version is stale, Requests/Show reloads with the message “This request changed since you<br>opened it. Please review and try again.”|
|FR-004 (submission feedback)|Submitting Requests/New always ends in either a confirmation on the created request or field-level errors; there is no<br>silent failure state.|



**Requirement How the screens satisfy it** 

FR-012 (views provide relevant information) Requests/Index shows the category, status, time submitted and assignee in each row. 

Formal accessibility testing is not scoped to Milestone 2; these wireframes fix the information architecture the accessibility work at Milestone 3 will build on. 

## **13.6 Interfaces and Integration** 

Because D-006 selects an Inertia presentation layer, CivicConnect exposes no public API at Milestone 2. The interfaces described below are the boundaries that do exist, documented at the level currently established. Where an interaction is not yet established it is recorded as a forward engineering consideration rather than specified speculatively 

### **13.6.1 Interface inventory** 

|**Boundary**|**Type**|**Crosses a network boundary**|**Decision**<br>|
|---|---|---|---|
||||**reference**|
|1 Browser to application|Inertia page visit over HTTPS, authenticated by session cookie|Yes, client to server|D-006, D-012|
|2 Web process to queue<br>worker|Job row written to the database queue and picked up by the<br>worker|No, same codebase and same<br>database|D-011|
|3 Application to PostgreSQL|Database connection from the web process and the worker|Yes, to the managed database<br>instance|D-007, D-008|
|4 Application to mail provider|SMTP or provider API call made by the queued listener|Yes, external service|D-011, D-013|



The only internal boundary the team introduced by choice is number 2, and it exists because D-011 requires a notification failure to be isolated from the statuschange transaction. Boundaries 1, 3 and 4 are unavoidable for a browser-based application with a managed database and email delivery. 

### **13.6.2 Page contracts** 

Inertia page visits replace the endpoint table an API-based design would have. Each route names the page component it renders, the props it sends, the input it accepts and how it responds. These props are an internal contract between a controller and its page component; they are not a published API. 

|**Route**|**Method**|**Page component**|**Props sent to the**<br>**page**|**Input accepted**|**Responses**|**Requirements**|
|---|---|---|---|---|---|---|
|/login|POST|Auth/Login|none|email, password|Redirect to the role landing<br>page; validation errors<br>returned to the form|FR-010|
|/logout|POST|-|none|none|Redirect to the login page|FR-010|
|/requests/create|GET|Requests/New|Active categories,<br>required fields for<br>the selected<br>category|none|Page|FR-005, D-010|



|**Route**|**Method**|**Page component**|**Props sent to the**<br>**page**|**Input accepted**|**Responses**|**Requirements**|
|---|---|---|---|---|---|---|
|/requests|POST|-|-|category,<br>location, area,<br>description,<br>category-specific<br>fields|Redirect to the created<br>request with a<br>confirmation; field-level<br>errors on failure|FR-001, FR-003,<br>FR-004|
|/requests|GET|Requests/Index|Paginated requests<br>scoped to the user’s<br>role, available filters,<br>current sort|search, status,<br>category, sort,<br>page|Page|FR-002, FR-009,<br>FR-012, FR-013|
|/requests/{id}|GET|Requests/Show|Request, status<br>history, comments,<br>assignment state,<br>allowed next<br>statuses, current<br>version|none|Page; 403 when the policy<br>denies access|FR-006, FR-014|
|/requests/{id}/status|PATCH|-|-|to_status, version|Redirect back with updated<br>props; if the version is<br>stale, redirect back with an<br>error message; 403 when<br>the user is not the assignee|FR-017, FR-018,<br>FR-019|
|/requests/{id}/assignments|POST|-|-|assignee_id, or<br>self-assignment|Redirect with updated<br>props; 403 when not<br>authorised|FR-015, FR-016|
|/assignments/{id}|PATCH|-|-|accept or decline|Redirect with updated<br>props|FR-015|
|/requests/{id}/comments|POST|-|-|body,<br>is_resolution|Redirect with updated<br>props|FR-020|
|/reports/summary|GET|Management/Dashboard|Counts by status and<br>category, overdue<br>list, average time to<br>resolution|date range|Page; 403 for any non-<br>management role|FR-022, FR-023,<br>FR-024|



### **13.6.3 Failure behaviour** 

|**Condition**|**Response**|**Requirement**|
|---|---|---|
|Input fails validation|Field-level messages returned to the originating form, with the submitted values preserved|FR-003, FR-004, NFR-<br>008|
|Policy denies the<br>action|403 with a generic message; the attempt is logged|FR-011, FR-018, NFR-<br>002|
|Version supplied is<br>stale|Redirect back to Requests/Show with the message “This request changed since you opened it” and the<br>current state reloaded|R-008, D-008|
|Legal transition not<br>found|Validation failure naming the allowed next statuses|FR-017, D-015|
|Unexpected server<br>error|Generic error page with no internal detail exposed|NFR-008|



### **13.6.4 Internal event contract** 

RequestStatusChanged is the one internal payload that behaves like a contract, because a listener consumes it rather than sharing the caller’s memory of the request. 

|**Field**|**Purpose**|
|---|---|
|request_id and reference number|Identifies which request the notification concerns|
|from_status, to_status|Describes the transition that occurred|
|actor_id|The staff member who made the change, for the message and for traceability|
|occurred_at|The timestamp recorded in the history row|
|requester contact address|Where the notification is delivered|



The event deliberately does not carry the full request entity, which follows the Assignment 2 Task 3 analysis of what information an interaction actually needs. Listeners are queued and retried on failure with a backoff; a permanently failed job is recorded in the failed jobs table and does not affect the committed status change. 

### **13.6.5 External interface: mail provider** 

The queued listener is the only component that calls an external service. Credentials and the provider endpoint are supplied as environment variables and are never committed (section 13.7.2). The provider itself is deferred under D-013, and the free-tier send limits that constrain it are recorded in section 13.7.4. A delivery failure is isolated from the status change by D-011 and is visible in the failed jobs table rather than silently lost. 

### **13.6.6 Change and versioning** 

Page props are internal: a controller and its page component change together in a single pull request, so no interface versioning scheme is required at this stage. If a JSON API is added later for a non-browser client, it becomes a published contract and requires versioning, deprecation handling and its own authentication decision. That possibility is recorded as an open decision in section 13.8 and as a trade-off in D-006 and R-016. 

### **13.6.7 Interactions not yet established** 

The following interactions are recorded here rather than specified, because no requirement currently establishes them: third-party or partner integration; an export interface for reporting beyond the on-demand view in D-003; and any inbound channel from email, SMS or WhatsApp, which section 3.2 places out of scope. Each would require its own interface decision before implementation. 

## **13.7 Deployment & Environments** 

The environment strategy for the selected stack (Laravel with Inertia serving React and TypeScript, and PostgreSQL) and the deployment risks it interacts with. The hosting provider remains open (D-013, OD-01); this section describes how the environments are structured around whichever provider is chosen. 

### **13.7.1 Environment Structure** 

|**Environment**|**Composition**|**Purpose**|
|---|---|---|
|Development|Docker Compose running PostgreSQL; Laravel served by php artisan serve (or a container); the<br>Vite dev server for React assets; a local queue worker (php artisan queue:work).|Local development and manual testing<br>with the same database engine and<br>queue driver as production.|
|Test (CI)|GitHub Actions workflow (ci.yml) with a PostgreSQL service container: composer install, npm ci,<br>npm run build, php artisan migrate and php artisan test.|Every pull request is built and tested<br>against real PostgreSQL before merge.|
|Staging /<br>Production|One Laravel deployable serving Inertia pages and the compiled assets, one queue worker<br>process, the scheduler, and a managed PostgreSQL instance on a free tier. Staging and<br>production use separate databases. Provider deferred (D-013, OD-01).|Demonstration, then real deployment.|



### **13.7.2 Configuration and Secrets** 

All environment-specific values are read from environment variables and never hard-coded: APP_KEY, APP_ENV, APP_URL, DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, MAIL_MAILER and the mail provider credentials, SESSION_DRIVER, SESSION_SECURE_COOKIE and QUEUE_CONNECTION=database. 

The repository ships one .env.example with no real values. Real secrets for CI are stored in GitHub Actions secrets and are never committed. 

Because Inertia requests come from the same origin as the application, no CORS or Sanctum stateful-domain configuration is needed. What must be set correctly per environment is the session cookie (domain, secure flag and lifetime) and APP_URL; a misconfiguration here is Risk R-010. 

### **13.7.3 Queue worker and scheduler** 

D-011 makes NotifyRequesterOfStatusChange a queued listener, so a queue worker process (php artisan queue:work) must run continuously in every environment beyond local development. The database queue driver is used, so no additional infrastructure such as Redis is needed. 

The retention purge (FR-008) runs from Laravel’s scheduler, so the host must also support either a cron entry running php artisan schedule:run every minute or a persistent php artisan schedule:work process. 

Both are covered by Risk R-009 and assumption AS-001: a host without background processes would accept notifications that are never sent. Fallback: run queue:work --stop-when-empty from the scheduler, accepting delayed delivery. 

### **13.7.4 Free-Tier Limits** 

Two limits interact directly with this work and are tracked as Risk R-013: a free-tier PostgreSQL instance that pauses when idle would make the demo unreliable, and the storage cap (R-002) interacts with the 5-year retention period on service_requests, request_status_history and request_comments. Mitigation: record the chosen host's actual limits once D-013 is finalised, seed a small, realistic dataset for the demo, and warm up the database connection before presenting. 

### **13.7.5 Backup and Recovery** 

NFR-006 requires data to be recoverable within 48 hours. Free tiers do not always include automatic backups (Supabase’s free plan, for example, does not) (supabase, 2026), so the baseline is a scheduled pg_dump of the database, run daily and stored outside the database host, with a restore rehearsed in Milestone 3. If the chosen provider includes daily backups or point-in-time recovery, that is used as an additional layer. A single PostgreSQL instance is accepted as a single point of failure at MVP under the cost constraint. 

## **13.8 Open and Deferred Decisions** 

Milestone 2 section 6 requires open decisions and deferred concerns to be identified separately from the approved baseline. None of the items below blocks the Architecture, Technology and Initial Design Baseline. Each is open because the evidence needed to decide it does not yet exist, and each names that evidence, an owner and the point at which it must be closed. 

|**ID**|**Open decision**|**Why it is still open**|**Evidence still needed**|**Owner**|**Decide by**|**Related**<br>**records**|
|---|---|---|---|---|---|---|
|OD-<br>01|Hosting provider|D-013 fixes the topology but not<br>the provider|Background-worker support, backup and<br>recovery facility, idle-pause behaviour,<br>storage cap and pricing beyond the free<br>tier|Michael|Before Milestone 3<br>staging deployment|D-013, R-002,<br>R-009, R-013,<br>FEC 4|
|OD-<br>02|Email delivery<br>provider|Depends on the host and on<br>free-tier send limits|Daily and monthly send allowances,<br>sender-domain verification requirements,<br>log retention|Jared|With OD-01|D-011, R-009,<br>section 13.7.4|
|OD-<br>03|Staff sub-roles by<br>department|No stakeholder requirement for<br>department-scoped visibility<br>has appeared|Whether staff need to see only their<br>department’s requests|Jared|Milestone 3, if a<br>need appears|FEC 1, D-012,<br>CR-009|
|OD-<br>04|Depth of audit<br>history exposed to<br>staff|Retention is decided; how much<br>history each role sees is not|Management input on whether staff need<br>full history or only the current state|Xander|Milestone 3|FEC 3, CR-<br>012, FR-014|
|OD-<br>05|Encryption at rest<br>for sensitive<br>categories|Depends on what the chosen<br>provider offers and at what cost|Provider encryption options, key handling,<br>cost impact|Xander|With OD-01|FEC 6, D-012,<br>ASR-002|
|OD-<br>06|Static analysis as a<br>blocking quality<br>gate|Adopted as a warning first,<br>following the Assignment 2 Task<br>4 recommendation|One clean run against real application code|Michael|Milestone 3|Section 10.3,<br>NFR-009|
|OD-<br>07|Verification method<br>for NFR-004|The target is measurable but<br>the measurement method is<br>not chosen|A seeded data volume representative of<br>five years and a repeatable timing<br>approach|Michael|Milestone 3 test<br>strategy|NFR-004,<br>ASR-001|
|OD-|Accessibility testing|Section 13.5.3 fixes the|Which accessibility checks are feasible|Xander|Milestone 3|NFR-007,|
|08|scope|information architecture only|within the Milestone 3 window|||NFR-008|
|OD-<br>09|Whether a JSON API<br>is added alongside<br>Inertia|No second client exists and<br>none is in scope|A stakeholder need for a non-browser<br>client|Jared|Milestone 3|D-006, R-016|



|**ID**|**Open decision**|**Why it is still open**|**Evidence still needed**|**Owner**|**Decide by**|**Related**<br>**records**|
|---|---|---|---|---|---|---|
|OD-<br>10|Account creation|No requirement defines how<br>requesters and staff get<br>accounts|Whether requesters self-register (starter-<br>kit registration) and whether staff and<br>management accounts are created by<br>Management|Jared|Before the Milestone<br>3 authentication slice|FR-010, D-<br>012, 13.2.3|



Deliberately deferred rather than open: 

construction itself is deferred to Milestone 3 under D-014, with the consequences recorded in R-015 and Forward Engineering Consideration 7. That is a decision with a date, not an unresolved question, which is why it appears in Section 9 rather than in this table. 

## **References** 

Django Software Foundation, 2026. _Django at a glance._ [Online] Available at: https://docs.djangoproject.com/en/stable/intro/overview/ [Accessed 26 September 2026]. 

Express, 2026. _Express - Node.js web application framework._ [Online] Available at: https://expressjs.com [Accessed 26 September 2026]. 

Inertia.js, 2026. _How it works._ [Online] Available at: https://inertiajs.com/how-it-works [Accessed 26 September 2026]. 

ISO, 2023. _ISO/IEC 25010:2023 Systems and software engineering - Systems and sofware Quality Requirements and Evaluation (SQuaRE) - Product Quality model,_ International Organisation for Standardization: Geneva. 

Jeff, S., 2011. _What Is A Good Task-Completion Rate?._ [Online] Available at: https://measuringu.com/task-completion/ [Accessed 13 September 2026]. 

Laravel, 2026a. _Blade Templates._ [Online] Available at: https://laravel.com/docs/blade [Accessed 26 September 2026a]. 

Laravel, 2026b. _Laravel Sanctum._ [Online] Available at: https://laravel.com/docs/sanctum [Accessed 26 September 2026b]. Laravel, 2026c. _Starter Kits._ [Online] Available at: https://laravel.com/docs/starter-kits [Accessed 26 September 2026c]. 

Meta, 2026. _Pricing on the WhatsApp Business Platform._ [Online] Available at: https://developers.facebook.com/documentation/business-messaging/whatsapp/pricing [Accessed 13 September 2026]. 

Microsoft, 2026. _Overview of ASP.NET Core._ [Online] Available at: https://learn.microsoft.com/en-us/aspnet/core/introduction-to-aspnet-core [Accessed 26 September 2026]. 

Next.js, 2026. _Documentation._ [Online] Available at: https://nextjs.org/docs [Accessed 26 September 2026]. 

Nielsen, J., 1993. _Response Times: The 3 Important Limits._ [Online] Available at: https://www.nngroup.com/articles/response-times-3-important-limits/ [Accessed 13 September 2026]. POPIA, 2019. _Section 14 Retention and restriction of records._ [Online] Available at: https://popia.co.za/section-14-retention-and-restriction-of-records/ [Accessed 27 September 2026]. PostgreSQL Global Development Group, 2026. _About PostgreSQL._ [Online] Available at: https://www.postgresql.org/about/ [Accessed 26 September 2026]. Ruby on Rails, 2026. _Getting Started with Rails._ [Online] Available at: https://guides.rubyonrails.org/getting_started.html [Accessed 26 September 2026]. 

Spring, 2026. _Spring Boot._ [Online] Available at: https://spring.io/projects/spring-boot [Accessed 26 September 2026]. 

Statista, 2026. _Most used web frameworks among developers worldwide as of 2025._ [Online] Available at: https://www.statista.com/statistics/1124699/worldwide-developer-survey-most-used-frameworks-web/ [Accessed 26 September 2026]. 

supabase, 2026. _https://supabase.com/pricing._ [Online] Available at: https://supabase.com/pricing [Accessed 13 September 2026]. 

Tovarys, J., 2022. _Availability Table._ [Online] Available at: https://betterstack.com/community/guides/incident-management/availability-table/ [Accessed 13 September 2026]. 

Translated, 2025. _Software Localization Cost: Application Translation Pricing & Budget Guide._ [Online] Available at: https://translated.com/resources/software-localization-cost-application-translation-pricing-budget-guide [Accessed 13 September 2026]. 

VerifyNow, 2025. _https://www.verifynow.co.za/how-to-comply-with-rica-in-south-africa-your-comprehensive-guide._ [Online] Available at: https://www.verifynow.co.za/how-to-comply-with-rica-in-south-africa-your-comprehensive-guide [Accessed 13 September 2026]. 

