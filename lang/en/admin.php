<?php

return [

    'title' => 'Admin Dashboard - KREASIBALL',
    'kicker' => 'Admin Panel',
    'heading' => 'Manage Scrapers',
    'subheading' => 'Sportmonks & News — trigger, monitor, stop jobs.',

    'jobs' => [
        'heading' => 'Running Jobs',
        'auto_refresh' => '(auto-refresh 5s)',
        'empty' => 'No jobs are running.',
        'stop' => 'Stop',
    ],

    'scraper' => [
        'heading' => 'Sportmonks Scraper',
        'run' => 'Run',
        'force_ttl' => 'force (bypass TTL)',
        'force' => 'force',
        'fixture_id' => 'Fixture ID',
        'scrape_fixture' => 'Scrape Fixture',
    ],

    'news' => [
        'heading' => 'News',
        'description' => 'Pull the latest news from the source (NewsAPI via the backend).',
        'refresh' => 'Refresh News',
    ],

    'football' => [
        'heading' => 'Scrape Football (Pick League & Season)',
        'description' => 'Leave the season empty to use the league current season. Leave the league empty for all enabled leagues.',
        'league' => 'League',
        'all_leagues' => '— All enabled leagues —',
        'season' => 'Season',
        'current_season' => '— Current season —',
        'season_current_suffix' => ' (current)',
    ],

    'leagues' => [
        'heading' => 'League Management',
        'description' => 'Enable/disable leagues for scraping. Only :strong leagues are processed on a football scrape (with no league filter).',
        'description_strong' => 'Enabled',
        'th' => [
            'league' => 'League',
            'sportmonks_active' => 'Sportmonks Active',
            'seasons' => 'Seasons',
            'scrape_status' => 'Scrape Status',
            'action' => 'Action',
        ],
        'yes' => 'Yes',
        'no' => 'No',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'enable' => 'Enable',
        'disable' => 'Disable',
        'empty' => 'No leagues yet. Run the :job scrape first.',
    ],

    'sync' => [
        'heading' => 'Sync Status',
        'th' => [
            'table' => 'Table',
            'last_sync' => 'Last Sync',
            'records' => 'Records',
            'status' => 'Status',
        ],
        'empty' => 'No sync data yet.',
    ],

    'flash' => [
        'league_enabled' => 'League enabled.',
        'league_disabled' => 'League disabled.',
        'league_failed' => 'Failed to change the league status.',
        'football_running' => 'The football job is still running — stop it before starting a new run.',
        'football_started' => 'Football scrape started (background).',
        'football_failed' => 'Failed to start the football scrape.',
        'job_running' => "Job ':job' is still running — stop it before starting a new run.",
        'job_started' => "Job ':job' started (background).",
        'job_failed' => "Failed to start job ':job'.",
        'fixture_invalid' => 'Invalid fixture ID.',
        'fixture_scraped' => 'Fixture :id scraped successfully.',
        'fixture_failed' => 'Failed to scrape fixture :id.',
        'job_stopping' => "Stopping job ':job'.",
        'news_refreshed' => 'News refreshed successfully.',
        'news_failed' => 'Failed to refresh the news.',
    ],

];
