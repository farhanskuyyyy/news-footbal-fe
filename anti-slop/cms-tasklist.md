# PitchTicker CMS

Design read: an editorial operations workspace for PitchTicker administrators, using the established green/neutral palette and Instrument Sans. ENERGY 2 / RHYTHM 2 / MOTION 1. Antislop applies during implementation, as already chosen in this session.

The user requested a sidebar instead of the public navigation, with users, news, leagues and scraper menus. Existing brand and assets are retained. No new visual assets or schema/API Go changes are needed.

## Design decisions

- A persistent desktop sidebar separates publishing operations from the public match browsing routes.
- Native mobile dialog navigation provides keyboard focus containment and Escape dismissal without an additional plugin.
- Green marks the selected destination and primary action; status colors only describe real outcomes.
- Instrument Sans keeps the public brand recognizable and readable in dense lists.
- Lists and tables are the main composition; section spacing separates monitoring from actions without decorative metric cards.
- Each page focuses on one task: monitor jobs, maintain accounts, inspect news, select leagues, or run scrapers.
- Existing account permissions are preserved: every authenticated user has CMS access. Self-deletion is blocked.
- News is synchronized upstream content. Available actions are inspect, search and refresh; no unsupported edit/delete controls.

## Tasks

- [x] Separate CMS shell and responsive sidebar, retain theme/language/logout/site access.
- [x] Split overview, users, news, leagues, scraper and sync routes.
- [x] Implement account CRUD with validation, password hashing and self-deletion protection.
- [x] Preserve scraper, season skeleton, league toggle, polling and refresh feedback.
- [x] Add isolated behavior tests and run PHP/Pint/Vite checks.
- [x] Exercise pages/actions/states in Playwright on mobile/desktop and both themes.
- [x] Record antislop Delivery Gate with concrete evidence and remaining limits.

Evidence: [cms-verification.md](cms-verification.md). Local implementation is ready for review at http://127.0.0.1:8000/admin. No CMS commit or production deployment was performed in this task.
