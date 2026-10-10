# PitchTicker public cards

Latest palette and interaction revision: [color-interactions.md](color-interactions.md), including updated color tokens, cursor/hover feedback and contrast verification. This supersedes the earlier color values below.

9 October 2026. User direction: clearly bordered cards throughout landing and public portal, with a composition distinct from FotMob. ENERGY 2 / RHYTHM 2 / MOTION 1; existing green/neutral palette and Instrument Sans retained.

## Design Read and completed work

- [x] Read installed UI UX Pro Max at `/Users/farhanarfianto/Projects/belajar/farhanskuyyyy.github.io/.agent/skills/ui-ux-pro-max/SKILL.md` and run its required `search.py --design-system -p PitchTicker -f markdown` generation.
- [x] Supplement generation with `--domain style 'Data-Dense Dashboard'` and `--stack html-tailwind 'responsive cards borders keyboard focus'`.
- [x] Apply compact information grouping, visible theme borders, row hover feedback and keyboard focus. Generated marketing journey/before-after layouts, alternate fonts/palettes and glow were rejected because they do not serve football score scanning or the established brand.
- [x] Replace the three-column landing with a full-width league directory above a match board and compact news column. On mobile the directory collapses and content stacks.
- [x] Frame calendar, filters, league match groups and news with 1px borders, surface backgrounds and 10px corners. Match teams remain vertically stacked, distinct from the reference's opposing horizontal teams.
- [x] Strengthen public-only divider colors to #C8D1C9 light and #405147 dark; existing bordered portal cards receive surface fill while inset canvas panels retain their fill. CMS colors/layout are unaffected by these scoped changes.
- [x] Keep headline text first and thumbnail second in news rows, preserving scan order and reducing image dominance.
- [x] Correct original-source button text contrast and remove the stray purple border from the portal rounds summary.
- [x] Build, test, browser-check and inspect screenshots. Local review at http://127.0.0.1:8000/; no deployment in this task.

## Verification

- `rtk proxy npm run build`: PASS.
- `rtk proxy php artisan test`: PASS, 31 tests / 178 assertions, isolated SQLite and HTTP fakes.
- `rtk proxy vendor/bin/pint --test` and `rtk proxy git diff --check`: PASS.
- Playwright: 16 public pages/tab variants at 320/375/768/1024/1440px in light/dark, 160 combinations without document overflow. The first 320px home request overlapped Vite's asset replacement and returned 500; both themes returned 200 after the build finished. Other requests returned 200.
- No page errors in the initial 96 combinations. Actual app screenshots inspected at desktop dark, mobile dark and portal desktop light.
- Click-through: text filter to no results and reset, Live checkbox, league collapse/expand and fixture navigation all worked.
- Empty, upstream-error and long-team-name preview states render at 320px without overflow; upstream error exposes alerts.
- Six page variants in both themes received computed text contrast checks. One existing source-button failure was fixed; its final colors are white on #14532D in both themes. Keyboard Tab exposes a 2px focus outline.
- JavaScript-disabled preview retains match links and hides the unavailable client filter toolbar.
- Screenshots: workspace `.playwright-mcp/pitchticker-cards-desktop.png`, `pitchticker-cards-mobile.png`, `pitchticker-portal-light.png`.

## Delivery Gate

- R-01 PASS: established green/neutral identity; no generated palette substituted.
- R-02 PASS: no decorative headline gradient.
- R-03 PASS: five viewport widths checked; controls retain 44px targets.
- R-04 PASS: existing SVG icons; no decorative emoji.
- R-05 PASS: horizontal directory, stacked match rows and compact editorial rows serve different content.
- R-06 PASS: Instrument Sans retained; tabular score figures use the existing font.
- R-07 PASS: no background patterns added.
- R-08 PASS: no decorative arrows added.
- R-09 PASS: no marketing pill badges added.
- R-10 PASS: no glass or blur.
- R-11 PASS: existing 10px card radius retained to frame content, not create pill containers.
- R-12 PASS: no shadows added.
- R-13 PASS: no glow added.
- R-14 PASS: card boundaries group real leagues, dates, filters and news; explicitly requested by the user.
- R-15 PASS: existing functional action labels retained.
- R-16 PASS: no new marketing copy.
- R-17 PASS: displayed scores/counts come from the existing API flow.
- R-18 PASS: no testimonials or invented people.
- R-19 PASS: MOTION 1; no new animation or looping motion.
- R-20 PASS: PitchTicker monogram, green active states and score-first hierarchy retained.
- R-21 PASS: light default and optional dark retained.
- R-22 PASS: no illustrations added.
- R-23 PASS: existing authorized brand assets and API images retained; no assets generated.
- R-24 PASS: existing real destinations retained; fixture navigation exercised.
- R-25 PASS: contrast audit and source-button correction recorded above.
- R-26 PASS: filter, live, collapse and navigation interactions exercised.
- R-27 PASS: empty/error/long-content states checked; existing skeleton implementation retained.
- R-28 PASS: no FAQ added.
- R-29 PASS: public divider tokens follow the existing neutral/green palette.
- R-30 PASS: reference informed containment only; directory, match alignment and news composition differ.
- R-31 PASS: border, hierarchy, typography, spacing and image reasons recorded above.
- R-32 PASS: native details controls and existing visible keyboard focus retained; Tab checked.
- R-33 PASS: source edits made through apply_patch.
- R-34 PASS: both themes included in browser checks.
- R-35 PASS: production build, tests, actual local server screenshots and interactions checked.
- R-36 PASS: no invented performance or security claims.
- R-37 PASS: user's explicit reference/direction interpreted before implementation; dials recorded.
- R-38 PASS: live app uses existing data; isolated browser fixture preview visibly labels itself.
- Liveliness PASS: ENERGY 2 / RHYTHM 2 / MOTION 1, real team marks/news imagery, restrained active green and content-specific composition.
- C-1 PASS: visual decisions have concrete purposes above.
- C-2 PASS: controls remain functional and retain their routes.
- C-3 PASS: no filler sections added.
- C-4 PASS: responsive/themes/states/no-JS checks recorded; existing skeleton logic unchanged.
- C-5 PASS: no fabricated production data or unsupported claims.

Native screen-reader testing and production deployment were not performed. Changes remain local and uncommitted together with the earlier CMS work.

## Revision: compact league navigation and one standings card

User feedback replaced the horizontal league grid with a labelled native league selector and Open league action. It occupies one compact row on desktop and wraps on mobile. Standings now occupy one card above news, with a separate selector and an actual table for position/club/points. Only the selected enabled league's current-season standings are loaded; unavailable or invalid selections fall back safely, and absent season data keeps the selector and empty-state message. Topscorer lists are replaced with the existing portal link to keep the card short. No Go API or schema change.

- [x] Build, Pint and diff whitespace checks PASS.
- [x] PHPUnit PASS: 33 tests, 198 assertions. Added regression coverage for selected-only fetching, cache isolation, invalid/array parameters and missing seasons. HTTP fakes and isolated test services used.
- [x] Playwright: actual local Bundesliga to Champions League selection produced exactly one standings card for league 2. Five widths, 320/390/768/1024/1440px, both themes, no horizontal overflow.
- [x] Open league navigated to the selected Premier League portal. No-JS standings submit was visible and worked in the isolated preview.
- [x] Desktop screenshot `.playwright-mcp/pitchticker-league-picker.png` inspected.

Delivery Gate recheck: R-01 through R-38 and C-1 through C-5 remain PASS with the evidence above and the earlier per-rule record. R-05/R-14/R-30 now use a compact selector rather than a league grid. R-17/R-38 use only the selected league's real data. R-24/R-26 use the exercised league navigation and standings forms. R-27 includes missing-season fallback coverage. R-31/R-37 purpose: reduce navigation space and repetitive cards, keeping score scanning primary. R-32/R-34 native selects, visible focus and both themes retained. ENERGY 2 / RHYTHM 2 / MOTION 1 unchanged. Still local and uncommitted.
