# PitchTicker color and interaction revision

## Standings client rendering, 10 October

League selection on the home standings card now fetches JSON from the new read-only Laravel route `/home/standings?league_id=...` and renders only the card using Alpine. Initial HTML still contains standings for first load and no-JS access. The new route validates the selected enabled league, fetches only its season/standings, returns explicit JSON errors and leaves Go API/schema unchanged. Existing homepage/no-JS GET selection remains supported.

Client state includes a request-bound static skeleton, aria-busy/status, empty and failure messages, and retry. New selections abort the previous request; a controller identity check discards stale results. A 12-second timeout exits loading on a stalled connection. Team names use x-text, and existing logos/fallbacks and server-generated portal links are retained.

Verification: build/Pint/diff checks PASS; 36 tests / 208 assertions PASS using HTTP fakes and isolated database. New endpoint tests cover selected-only fetching (three upstream reads, no homepage news/fixtures), zero points, link parameters, invalid and unavailable leagues, absent seasons and upstream errors. Actual local browser selection showed six club rows for Premier League, unchanged document sentinel and URL, zero navigation requests and no page errors. Preview browser checks exercised held-request skeleton, HTTP 503 error and successful retry, rapid selection with old request finishing last, empty results and both themes at 320/390/768/1440px without overflow. Native no-JS submit still works.

Delivery Gate PASS: prior per-rule records apply; R-17/R-38 retain real data and labelled isolated fixtures, R-19/R-27 use request-bound loading and explicit states, R-24/R-26/R-32 preserve native labelled selection and functional retry, R-33 uses direct patches, R-34/R-35 include browser/build/tests. Visual palette, typography and ENERGY 2 / RHYTHM 2 / MOTION 1 remain unchanged. No deployment; changes remain local and uncommitted.

User direction: landing and CMS need distinct canvas, card header, card body and hover colors, plus clear cursors and interaction feedback. Existing green identity and ENERGY 2 / RHYTHM 2 / MOTION 1 retained. No new assets or dependencies.

| Role | Light | Dark | Purpose |
|---|---|---|---|
| Canvas | #F1F0E8 | #101714 | Ground plane behind content |
| Card body | #FFFDF8 | #1C2923 | Reading surface |
| Card header | #E2EBDD | #293C31 | Separate titles and controls from data |
| Hover | #D1E4D0 | #304B3A | Highlight the row or action under the pointer |
| Header hover | #CBDCC5 | #35503E | Feedback on collapsible league summaries |
| Sidebar/navigation | #E7EBDD | #15271E | Separate persistent navigation from content |

Landing calendar, match-group summaries, standings and news headings use the header token. CMS running/sync/scraper panels and table headings use the same hierarchy. Search toolbars use the header fill with body-colored inputs. Existing table and list rows highlight on hover, and the parent border strengthens when its action is hovered. News headline links underline on hover. Non-clickable containers retain their ordinary cursor; links, enabled buttons/selects and checkbox controls use pointer, disabled controls use not-allowed. Color transitions last 150ms and honor reduced-motion settings. No scale, glow or looping effects.

## Verification

- Production build PASS; PHPUnit PASS, 33 tests / 198 assertions; Pint and diff whitespace checks PASS.
- Playwright: nine landing/CMS pages at five widths, 320/390/768/1024/1440px, both themes, 90 combinations. HTTP 200, no document overflow or page errors, and all visible links/enabled buttons/selects report pointer cursors.
- Disabled season selector reports not-allowed. Actual computed header/body colors differ in both themes.
- Computed text contrast: nine pages in both themes and normal/hover states, 36 variants. Initial audit found dark live/error text at 4.22:1 on hover. Dark danger token corrected to #FFA399, and both affected live/sync hover cases rechecked at 4.99:1. Other inspected variants passed AA.
- Screenshots inspected: workspace `.playwright-mcp/color-home-dark.png` and `.playwright-mcp/color-admin-light.png`.
- Existing request-bound skeleton, validation, selected-league behavior and native dialogs retained. No production deployment or native screen-reader test performed.

## Delivery Gate

Per-rule R-01 through R-38 and C-1 through C-5 records remain in [portal-cards.md](portal-cards.md) and [cms-verification.md](cms-verification.md), with this revision superseding their palette evidence. Rechecked PASS: R-01/R-20/R-29 use the existing identity; R-03/R-34/R-35 use the 90-combination check; R-19 retains MOTION 1 with reduced-motion support; R-25 includes normal/hover contrast correction; R-26/R-32 provide accurate cursor and focus feedback; R-31/R-37 tie each color layer to its hierarchy purpose; R-33 uses direct apply_patch edits. Remaining rules have no new affected content or behavior. UI checklist PASS for brand palette, purposeful states, accessible controls, real content, responsive themes and restrained effects.

Changes remain local and uncommitted.

## League selector and club marks, 10 October

User requested league icons, better dropdown arrow spacing and club icons in the standings card. Both home league selectors now share a native, labelled select component with the current league's API logo on the left. Native options and keyboard behavior are retained; the logo updates on selection. All select arrows have a consistent theme-aware chevron inset 14px from the right, with 44px right padding. League selectors also have 44px left padding for the 20px mark. Standings rows show the real 20px club logo beside the name, with the existing shield fallback when no image exists or loading fails.

Build, Pint and diff checks PASS. PHPUnit: 33 tests / 198 assertions PASS. Playwright verified 44px computed select padding, selected Premier League logo matching its option's API URL, standings switching to league 8, six club images, and no overflow at 320/390/768/1440px in both themes. Empty and failed league-image fallbacks and missing club-image fallback were checked on the isolated preview. Screenshot: workspace `.playwright-mcp/league-club-icons.png`.

Delivery Gate PASS: prior per-rule evidence remains applicable; R-23 uses existing API logos explicitly requested by the user, R-25 retains the tested palette, R-26/R-32 retain native select behavior, R-27 covers missing/failed logos, R-31 records padding and mark purpose above, R-34/R-35 include browser/build checks, R-38 retains real API images and visibly labelled preview data. No new dependencies, provider jobs, API/schema changes or deployment.
