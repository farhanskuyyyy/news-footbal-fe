# PitchTicker introduction

10 October 2026. User requested a substantive landing page at `/` for a global audience and automatic football-related animation. Their latest instruction removes the returning-visitor redirect: `/` always displays the introduction.

## Design Read and implementation

ENERGY 3 / RHYTHM 3 / MOTION 2. Established Instrument Sans and cream/green/amber palette retained. UI UX Pro Max's required design-system generator was run with sports/editorial/animation keywords; oversized editorial type and focused navigation were applied, while its alternate blue/orange palette and font suggestions were rejected to preserve PitchTicker's established identity.

The introduction leads with the score-to-context promise, a labelled passing illustration, concrete descriptions of existing match/league/player/news functionality, actual enabled competitions, and a match-centre destination. The illustration is native SVG with a single ball following a 16-second passing sequence. It is explicitly labelled as an illustration, not live data. Automatic repeated movement is explicitly requested by the user; pause/play controls and reduced-motion behavior limit distraction. Short entrance motion runs once. No new raster assets, libraries, dependencies, fabricated statistics or testimonials.

- `/`: introduction for every visitor.
- `/home`: dashboard, retaining its named `home` route, selectors and client-rendered standings.
- About PitchTicker links directly to `/`.
- Visit tracking middleware removed. No visit cookie is set; existing `pitchticker_seen` cookies are ignored.
- English and Indonesian copy provided. Existing default locale remains English. No API Go or database/schema change.

## Verification

- Build PASS for the landing implementation. PHPUnit rerun after removing the redirect PASS: 40 tests / 219 assertions using isolated database and HTTP fakes. Checks cover landing for new and returning browsers, absence of visit cookies, direct dashboard visits and upstream failure. Existing dashboard tests moved to `/home`.
- Pint and diff whitespace checks PASS.
- Playwright: six widths, 320/375/390/768/1024/1440px, light/dark, no horizontal overflow or page errors. Indonesian copy additionally checked at 320/390px.
- Automatic ball movement observed by different computed transforms; pause reports paused; resume reports running; reduced motion reports no animation. Four settled normal/hover theme contrast variants found no text below AA. Keyboard focus displays a 2px outline; gold focus overrides on green panels preserve visibility.
- Earlier browser checks covered the original redirect, now superseded by the user's instruction. Updated feature tests verify `/` renders the introduction even with an existing visit cookie and `/home` remains directly accessible.
- Click-through: product anchor, schedule, league portal, player search and news destinations; theme toggle; English/Indonesian; main CTA to `/home`; About returns to introduction.
- Actual local screenshots inspected: workspace `.playwright-mcp/landing-desktop-light.png` and `.playwright-mcp/landing-mobile-dark.png`.

## Delivery Gate

- R-01 PASS: existing brand palette retained instead of generated defaults.
- R-02 PASS: solid headline color, no text gradient.
- R-03 PASS: six widths and mobile translations checked; controls retain 44px targets.
- R-04 PASS: existing SVG icon vocabulary, no decorative emoji.
- R-05 PASS: editorial hero, linear product index, competition directory and compact closing action vary composition.
- R-06 PASS: Instrument Sans retained; readable body text and restrained hierarchy.
- R-07 PASS: no decorative background patterns; field markings explain the football illustration.
- R-08 PASS: arrows accompany actual navigation, not empty decoration.
- R-09 PASS: no pill badges above the headline.
- R-10 PASS: no blur/glass.
- R-11 PASS: 6px controls/directory and 10px illustration/closing panel; no pill containers.
- R-12 PASS: no shadows added.
- R-13 PASS: no glow added.
- R-14 PASS: competition cards represent actual available destinations; product information uses linear rows.
- R-15 PASS: CTA names its destination: Open the match centre.
- R-16 PASS: copy describes actual features, with no AI/marketing buzzwords.
- R-17 PASS: no invented metrics, scores, match events or customer counts.
- R-18 PASS: no testimonials or invented people.
- R-19 PASS: user explicitly requested automatic football motion; one passing sequence, pause/play and reduced-motion stop tested.
- R-20 PASS: PitchTicker mark, palette, score-to-context positioning and passing illustration are product-specific.
- R-21 PASS: light default and optional dark toggle retained.
- R-22 PASS: football passing illustration directly serves the user's requested football motion.
- R-23 PASS: user authorized football animation; original SVG uses no external artwork or fabricated logos. League marks come from API data.
- R-24 PASS: destinations and anchors exercised; no placeholder links.
- R-25 PASS: settled text contrast checked in both themes, including navigation hover.
- R-26 PASS: pause/play, theme, locale, feature links and CTAs exercised.
- R-27 PASS: unavailable competition listing retains an actionable explanation; no fake loading required for server-rendered introduction.
- R-28 PASS: no FAQ added.
- R-29 PASS: established green/cream with amber reserved for the ball and closing action.
- R-30 PASS: original editorial composition and native football illustration; no reference site copied.
- R-31 PASS: color, type, rhythm, illustration and motion purposes recorded above.
- R-32 PASS: native links/buttons, skip link, focus outlines and no-JS navigation retained.
- R-33 PASS: direct source edits via apply_patch.
- R-34 PASS: both themes and reduced motion tested.
- R-35 PASS: build/tests/local server/browser screenshots and interaction checks completed.
- R-36 PASS: no fabricated performance, security or compliance claims.
- R-37 PASS: user direction applied with recorded design rationale and ENERGY 3 / RHYTHM 3 / MOTION 2.
- R-38 PASS: real enabled competitions; passing sequence visibly labelled as illustration; browser test fixtures isolated.
- Liveliness PASS: large editorial title, a specific football passing motif, restrained amber and meaningful automatic motion.
- C-1 PASS: major decisions have product-specific purposes.
- C-2 PASS: visible controls have real behavior.
- C-3 PASS: sections explain existing product paths or lead to them, with no template filler.
- C-4 PASS: responsive/themes/no-JS/locale/reduced-motion/cookie paths checked.
- C-5 PASS: no fabricated evidence or disguised live-data illustration.

No production deployment or native screen-reader testing was performed. Changes remain local and uncommitted for review.
