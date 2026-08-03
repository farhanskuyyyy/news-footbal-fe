# Laravel FE — Portal Berita

Frontend Laravel (Blade murni) yang menampilkan berita dari backend Go (Echo) yang sudah ada. Project ini dibuat untuk RnD CI/CD.

## Arsitektur

- **Backend (sudah ada, tidak di repo ini):** Go News API di port `8082` — `GET /health`, `GET /news`, `GET /news/{id}`.
- **Frontend (repo ini):** Laravel 13, Blade + Tailwind (CDN), tanpa database. Data diambil server-side lewat `Http` facade dengan timeout, di-cache pakai cache Laravel (file driver, TTL 5 menit).

## Halaman

| Route | Deskripsi |
|---|---|
| `/` | Daftar berita (title, source, tanggal, deskripsi terpotong) + pagination |
| `/berita/{id}` | Detail berita + link ke sumber asli |
| `POST /refresh` | Tombol "Refresh Berita" — trigger backend fetch ulang dari NewsAPI, lalu clear cache FE |

Kalau API mati, halaman menampilkan pesan ramah dengan status 503 (bukan error 500).

## Run lokal

Prasyarat: PHP 8.3+, Composer. Backend Go jalan di `http://localhost:8082`.

```bash
composer install
cp .env.example .env
php artisan key:generate
make run   # atau: php artisan serve
```

Buka http://localhost:8000.

## Konfigurasi

| Env | Default | Keterangan |
|---|---|---|
| `NEWS_API_URL` | `http://localhost:8082` | Base URL backend Go |
| `NEWS_API_TIMEOUT` | `5` | Timeout HTTP client (detik) |
| `NEWS_CACHE_TTL` | `300` | TTL cache response (detik) |
| `NEWS_PER_PAGE` | `12` | Item per halaman |
| `NEWS_REFRESH_TIMEOUT` | `20` | Timeout trigger refresh (backend manggil NewsAPI, bisa lama) |
| `NEWS_REFRESH_TOKEN` | *(kosong)* | Harus sama dengan `REFRESH_TOKEN` di backend; kosong = tanpa auth |

## Test & lint

```bash
make test   # PHPUnit (Http::fake, tidak butuh backend jalan)
make lint   # Pint check
make fix    # Pint fix
```

## Docker

```bash
make up     # build + start, bind ke 127.0.0.1:3000
make down
```

## Deployment

Lihat [DEPLOY.md](DEPLOY.md) — setup VPS, deploy key, GitHub Actions secrets, dan config nginx.
