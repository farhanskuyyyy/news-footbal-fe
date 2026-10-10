# PitchTicker CMS verification

Latest palette and interaction revision: [color-interactions.md](color-interactions.md), including shared header/body/hover colors, cursors and settled hover contrast checks. This supersedes earlier palette descriptions below.

9 October 2026. Implemented with [antislop](/Users/farhanarfianto/.agents/skills/antislop/SKILL.md), plus UI, human and mobile guidance. Direction and reasons: [cms-tasklist.md](cms-tasklist.md). ENERGY 2 / RHYTHM 2 / MOTION 1.

## Border revision

User requested the same clear card boundaries on admin pages. CMS section panels now have a full 1px border, 10px corners, surface fill and 20px padding. Standalone tables have complete outer borders; tables inside an already framed section retain internal row separators without a second outer frame. Search forms, user forms, account deletion sections, news articles and unavailable-content panels share the card style. CMS divider colors now match the public portal in light and dark. This supersedes the earlier flat-table styling evidence below; page behavior and routes remain unchanged.

- Build, Pint and diff whitespace checks PASS.
- PHPUnit PASS: 33 tests, 198 assertions using isolated services/database.
- Playwright PASS: nine CMS pages at 320/390/768/1024/1440px in both themes, 90 combinations. All returned HTTP 200, no document overflow, no page errors, and checked standalone card/panel elements have an outer border.
- Desktop scraper screenshot inspected: workspace `.playwright-mcp/cms-borders-desktop.png`.

Delivery Gate recheck: the R-01 through R-38 and C-1 through C-5 evidence below remains applicable, with R-11/R-14/R-31 updated to the explicitly requested framed content groups, consistent 10px radius and padding. R-03/R-34/R-35 use the new 90-combination browser check. Established ENERGY 2 / RHYTHM 2 / MOTION 1 and existing accessible controls remain unchanged. PASS for this border revision; no production deployment or screen-reader testing performed. Changes remain local and uncommitted.

## Result

CMS has its own sidebar layout and real destinations for overview, users, news, leagues, scrapers and sync status. Public Home/Live navigation remains on public pages. Account CRUD uses the existing users table and access model; all authenticated accounts are administrators. News supports reading/searching/pagination and refresh, with no unsupported edit/delete controls. No migration or Go API changes.

## Checks

- `rtk php artisan test`: PASS, 31 tests and 178 assertions, SQLite in-memory and HTTP fakes. Covers auth protection, account CRUD, hashed/password-preserving updates, duplicates/invalid fields, self-deletion, source failure, invalid operations and backend parameter forwarding.
- `rtk proxy vendor/bin/pint --test`: PASS.
- `rtk npm run build`: PASS, five bundled modules and production CSS/fonts.
- `rtk proxy git diff --check`: PASS.
- Playwright: eight pages at 320/375/390/768/1024/1280/1440px, light and dark, 112 combinations: HTTP 200, no horizontal document overflow, no page errors.
- Computed text contrast: seven pages in both themes, 14 combinations, no AA failures after theme transitions settle. Control/focus tokens retain the established contrast values.
- Real local app at port 8000: authenticated admin users page rendered, existing account visible, sidebar visible, no public Live navbar link, no page errors. No account mutation was performed against the application database.
- Browser mutation checks use the labelled preview at port 8099 and its separate temporary SQLite database, not the application PostgreSQL database. Scraper/refresh/toggle requests are HTTP fakes; no provider jobs were launched for these tests.

## Recorded click-through

| Control | Observed result |
|---|---|
| Each desktop sidebar destination | Navigates to overview/users/news/leagues/scrapers/sync |
| Mobile Menu | Opens native dialog |
| Close menu, Escape, backdrop click | Closes dialog; Escape returns focus to opener |
| Tab across drawer controls | 24 consecutive Tab presses remain inside dialog |
| Theme on desktop and mobile | Changes theme; selected theme survives reload |
| ID and EN | Switch locale and return to CMS page |
| View website | Opens public home with public navigation |
| Log out | Redirects to login |
| Add user | Opens account form; valid POST creates fixture account |
| Edit user + Save | Renames fixture account; blank password preserved by regression test |
| Duplicate email + mismatched password | Two fields show aria-invalid and associated error text |
| Cancel account form | Returns to account list |
| Delete account | Opens native confirmation dialog |
| Cancel/Escape delete dialog | Keeps account and closes dialog |
| Confirm delete | Removes only the temporary browser-test account |
| Self-deletion | No delete control on own edit; direct DELETE rejected by regression test |
| Search/Clear news | Shows no-results message, then restores articles |
| News headline/read/back | Opens article detail and returns to list |
| News page 2 | Navigates with page=2 |
| Refresh news | Fake source refresh succeeds and visible feedback appears |
| Disable league | Existing POST route succeeds and visible feedback appears |
| Run core/leagues/football/fixture-details/player-statistics | Each posts its actual job and shows start feedback |
| League/season selector | Fetches seasons and permits scoped football submit |
| Pending season request | Shows skeleton until response arrives |
| Failed season request | Shows error, disables stale season selection and scoped submit |
| Fixture scrape | Posts fixture 100 and shows success feedback |
| Stop running football | Posts existing stop route and shows stopping feedback |
| Polling failure | Shows error and keeps the previous running job visible |
| No JavaScript | Mobile navigation and initial running-job snapshot remain usable |

Original article URLs are data-driven HTTP/HTTPS links with noopener/noreferrer; external publisher reachability was inspected in code, not asserted from fixture URLs. Backend integration parameter tests cover league toggle and scoped scraping. Expected console network errors occur only in deliberately intercepted 503 cases; normal route renders had no JavaScript page errors.

## Antislop Delivery Gate

One line per rule, scoped to the new CMS and its shared controls.

- R-01 PASS: existing green/neutral tokens indicate active destination and primary action; no gradients added.
- R-02 PASS: CMS-authored translations and views contain no em dash.
- R-03 PASS: 112 viewport/theme renders have no document overflow; mobile rows expose actions without horizontal table scrolling.
- R-04 PASS: sidebar uses text labels and the existing approved monogram, no decorative icon collection.
- R-05 PASS: monitoring, account forms, news and scraper rows follow their different tasks, without metric-card filler.
- R-06 PASS: Instrument Sans retains the PitchTicker brand and readable dense-list typography, reason recorded in tasklist.
- R-07 PASS: no decorative background pattern.
- R-08 PASS: no decorative button arrows.
- R-09 PASS: no capsule marketing badges; actual status is plain text.
- R-10 PASS: no glass/backdrop blur.
- R-11 PASS: 4px navigation and 6px control/dialog radii follow existing tokens; tables are flat.
- R-12 PASS: no component shadows added.
- R-13 PASS: no glow added; focus uses a 2px solid outline.
- R-14 PASS: no repeated feature-card grid; rows contain actual data/actions.
- R-15 PASS: actions name their operation: Add user, Save changes, Refresh News, Disable, Run, Stop.
- R-16 PASS: CMS text describes account/content/scraper work, without marketing buzzwords.
- R-17 PASS: displayed users, articles, leagues and sync counts come from database/API or explicitly labelled test fixtures; missing values say Not available.
- R-18 PASS: no testimonials, invented avatars or fictional customer claims.
- R-19 PASS: MOTION 1, no looping/decorative animation; skeleton is static and request-bound.
- R-20 PASS: PitchTicker monogram, green selection and editorial content/operations grouping carry the existing identity; content hierarchy is task-specific.
- R-21 PASS: light default and working optional dark controls, tested on desktop/mobile.
- R-22 PASS: no illustrations created.
- R-23 PASS: sidebar structure was explicitly requested; existing approved brand assets retained, no new visual assets fabricated.
- R-24 PASS: every sidebar destination rendered and was clicked successfully.
- R-25 PASS: 14 settled theme/page contrast audits found no text below AA; inherited focus/control contrast maintained.
- R-26 PASS: recorded click-through above covers the new controls; unsupported news mutations are absent.
- R-27 PASS: empty/error list states, native POST validation/feedback and real season-request skeleton tested; polling retains stale data on failure.
- R-28 PASS: no FAQ added.
- R-29 PASS: existing green/neutral palette; red reserved for failure/destructive actions.
- R-30 PASS: no external dashboard cloned, no new library/template installed.
- R-31 PASS: color/layout/type/spacing/container/navigation reasons recorded in cms-tasklist.md.
- R-32 PASS: native dialogs, explicit Tab wrapping, Escape, opener focus return and visible 2px outline tested.
- R-33 PASS: UI changes written directly through apply_patch; no source-rewriting helper scripts.
- R-34 PASS: light and dark included in render, contrast and control checks.
- R-35 PASS: build and local server run, normal page errors checked, click-through recorded above.
- R-36 PASS: no performance/security/compliance claims or invented statistics.
- R-37 PASS: established PitchTicker direction and requested sidebar structure applied; Design Read recorded before implementation.
- R-38 PASS: production uses database/API data; preview fixtures visibly labelled and isolated.
- Liveliness PASS: ENERGY 2 / RHYTHM 2 / MOTION 1; page task is focal, green is deliberate, PitchTicker motif retained, spacing separates actual tasks.
- C-1 PASS: major visual choices have written purpose in tasklist.
- C-2 PASS: new actions exercised; no dead sidebar links or unsupported news mutations.
- C-3 PASS: each CMS page corresponds to actual administration work, no template filler.
- C-4 PASS: mobile/desktop, themes, loading/error/empty, keyboard and no-JS navigation checked.
- C-5 PASS: no fabricated claims or unlabelled production-looking fixture data.

## Screenshots and limits

- [Users, desktop light](cms-screenshots/users-light-1440.png)
- [Leagues, mobile dark](cms-screenshots/leagues-dark-390.png)
- [Navigation, mobile dark](cms-screenshots/navigation-dark-390.png)

Native screen-reader testing, production deployment and real provider scraper execution were not performed. There is no role hierarchy in this existing application; adding role-based permissions is a separate feature. The CMS implementation remains local and uncommitted for review.
