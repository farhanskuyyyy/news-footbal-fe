# PitchTicker SEO

Canonical origin: https://pitchticker.kreasifarhan.id. Set `SEO_URL` if the production domain changes; keep `APP_URL` consistent for application links. Tracking parameters are excluded from canonical URLs. League, season, tab, match-date and news pagination parameters remain because they change the displayed content.

Public layouts render titles, descriptions, canonical URLs, Open Graph, Twitter cards and JSON-LD on the server. English and Indonesian metadata follow the existing session language. No hreflang is emitted: the application does not yet have separate language URLs. Search crawlers ordinarily see the default English version.

The landing includes WebSite and WebPage markup. Team/player pages describe actual SportsTeam/Person entities. Available fixture names and kickoff times produce SportsEvent markup; optional teams and venue come from existing data. No ticket offers or invented event details are emitted. This does not claim eligibility for Google's event rich results.

News detail pages are sourced summaries. WebPage markup refers to the original NewsArticle through `about`, using the source URL, actual author, publisher, image and publication date when available. PitchTicker is not labelled the original publisher. No Google News sitemap is generated for these summaries.

`/sitemap.xml` lists public entry pages, news pagination, enabled leagues and actual news IDs. It uses existing cached read-only API methods. It does not invent lastmod timestamps. If either upstream list fails, it responds 503 with Retry-After rather than returning an incomplete success. More than 50,000 URLs requires a sitemap index; the endpoint fails explicitly at that limit. Team/player/fixture detail discovery currently relies on crawlable portal links, because there is no exhaustive cached directory endpoint for these entities.

`/robots.txt` announces the sitemap and blocks API, health and standings-fragment crawling. Utility pages (login, CMS, upload, search and compare), AJAX responses and failures receive noindex headers; utility and fixture-loading views also use noindex metadata. Utility pages remain crawlable so bots can read the directive. Authentication still protects the CMS.

## After deployment

1. Verify the domain property in Google Search Console using its DNS TXT record, or use a URL-prefix property and put its HTML verification token in `GOOGLE_SITE_VERIFICATION`. Do not put the full meta tag in the variable.
2. If environment configuration changes, refresh Laravel's configuration cache through the existing deployment procedure.
3. Submit `https://pitchticker.kreasifarhan.id/sitemap.xml` in Search Console.
4. Inspect the landing, a league URL, a news detail and a fixture URL with URL Inspection. Verify canonical selection and indexing status after Google crawls them.
5. Check JSON-LD with Google's Rich Results Test. A valid schema type does not guarantee a rich result.
6. Monitor real Core Web Vitals, indexing and search queries. No PageSpeed score or Google indexing result has been measured by this local change.

## Content priorities

Start with specific competition, club and match queries that the site can answer using its available data. Broad terms such as “football live scores” have strong established competitors. Add original match previews, explanations of standings and sourced reporting if editorial capacity permits; syndicated summaries alone provide little reason to choose PitchTicker over the original publisher. Publish honest author and editorial-policy pages when those responsibilities have been defined. Do not fabricate writers, claim exclusive coverage, or mass-generate near-identical pages.

Keep internal league/team/player/match links useful and crawlable. When the domain or URL structure changes, add permanent redirects and update sitemap/canonical origins together. Review language-specific URLs before targeting separate Indonesian and English search results.

References: [Google SEO starter guide](https://developers.google.com/search/docs/fundamentals/seo-starter-guide), [Article structured data](https://developers.google.com/search/docs/appearance/structured-data/article), [Sitemaps](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap).
