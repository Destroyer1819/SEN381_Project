# Register entries: frontend (Xander)

Add these to the team's existing registers. Edit anything that does not match what the team decided.

## Technical debt (frontend)

| Item | Why deferred | Consequence | Next action |
|---|---|---|---|
| No notification UI (FR-006) | Time; the history timeline covers visibility | Requestors must open the site to see changes | Add in-app notification list |
| Date filters send plain dates | Backend defines the timezone rule | A request near midnight can land on the neighbouring day | Agree timezone handling with the backend |
| No password reset, email verification or account deletion pages | Out of M3 scope | Forgotten passwords need an admin | Add reset flow |
| Pages checked by tests and build, not yet by eye in a browser | Time | Layout problems may remain | Click through all three roles and keep screenshots |

## Risk (frontend)

| Risk | Likelihood and impact | Control now | Residual |
|---|---|---|---|
| Page names or props drift from the backend | Medium / Medium | Prop types in `resources/js/types/civic.ts`, route list in README | Medium until E2E runs |
| Role links shown to the wrong user | Low / Medium | Role-based menu; server still enforces access (TC-E2E-02) | Low |

## AI Usage Register entry (required by the brief)

| Date | Tool | What it was used for | How it was verified | Changes made by the team |
|---|---|---|---|---|
| 2026-10-07 to 2026-10-10 | Claude (Anthropic) | Generated the first version of the React pages, components, Vitest and Playwright tests and frontend CI | Vitest, ESLint, Prettier and build run; E2E to be run | Record here what was changed, rejected or corrected after reading the code |

Every team member must be able to explain the code they present. Read it, change what you disagree with, and record real corrections here.
