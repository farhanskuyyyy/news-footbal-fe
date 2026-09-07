<?php

return [

    'title' => 'Admin Dashboard - KREASIBALL',
    'kicker' => 'Panel Admin',
    'heading' => 'Kelola Scraper',
    'subheading' => 'Sportmonks & Berita — trigger, pantau, hentikan job.',

    'jobs' => [
        'heading' => 'Job Berjalan',
        'auto_refresh' => '(auto-refresh 5s)',
        'empty' => 'Tidak ada job yang berjalan.',
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
        'heading' => 'Berita',
        'description' => 'Tarik berita terbaru dari sumber (NewsAPI via backend).',
        'refresh' => 'Refresh Berita',
    ],

    'football' => [
        'heading' => 'Scrape Football (Pilih Liga & Musim)',
        'description' => 'Kosongkan musim untuk pakai musim aktif liga. Kosongkan liga untuk semua liga aktif.',
        'league' => 'Liga',
        'all_leagues' => '— Semua liga aktif —',
        'season' => 'Musim',
        'current_season' => '— Musim aktif —',
        'season_current_suffix' => ' (aktif)',
    ],

    'leagues' => [
        'heading' => 'Manajemen Liga',
        'description' => 'Aktifkan/nonaktifkan liga untuk di-scrape. Hanya liga :strong yang diproses saat scrape football (tanpa filter liga).',
        'description_strong' => 'Aktif',
        'th' => [
            'league' => 'Liga',
            'sportmonks_active' => 'Sportmonks Active',
            'seasons' => 'Musim',
            'scrape_status' => 'Status Scrape',
            'action' => 'Aksi',
        ],
        'yes' => 'Ya',
        'no' => 'Tidak',
        'enabled' => 'Aktif',
        'disabled' => 'Nonaktif',
        'enable' => 'Aktifkan',
        'disable' => 'Nonaktifkan',
        'empty' => 'Belum ada liga. Jalankan scrape :job dulu.',
    ],

    'sync' => [
        'heading' => 'Status Sinkronisasi',
        'th' => [
            'table' => 'Tabel',
            'last_sync' => 'Terakhir Sync',
            'records' => 'Records',
            'status' => 'Status',
        ],
        'empty' => 'Belum ada data sinkronisasi.',
    ],

    'flash' => [
        'league_enabled' => 'Liga diaktifkan.',
        'league_disabled' => 'Liga dinonaktifkan.',
        'league_failed' => 'Gagal mengubah status liga.',
        'football_running' => 'Job football masih berjalan — hentikan dulu sebelum menjalankan ulang.',
        'football_started' => 'Scrape football dijalankan (background).',
        'football_failed' => 'Gagal menjalankan scrape football.',
        'job_running' => "Job ':job' masih berjalan — hentikan dulu sebelum menjalankan ulang.",
        'job_started' => "Job ':job' dijalankan (background).",
        'job_failed' => "Gagal menjalankan job ':job'.",
        'fixture_invalid' => 'Fixture ID tidak valid.',
        'fixture_scraped' => 'Fixture :id berhasil di-scrape.',
        'fixture_failed' => 'Gagal scrape fixture :id.',
        'job_stopping' => "Menghentikan job ':job'.",
        'news_refreshed' => 'Berita berhasil di-refresh.',
        'news_failed' => 'Gagal refresh berita.',
    ],

];
