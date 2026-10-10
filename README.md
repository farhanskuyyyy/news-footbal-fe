# PitchTicker — Football & News

Frontend Laravel/Blade untuk jadwal, live score, portal liga, profil tim/pemain, transfer, dan berita dari backend Go. CMS scraper dan upload gambar tetap memakai integrasi existing.

## Arsitektur

- **Backend (Go News API):** Berjalan di port `8082` — `GET /health`, `GET /news`, `GET /news/{id}`.
- **Frontend (Laravel 13):** Blade + Tailwind 4/Vite 8, Alpine 3, dan Instrument Sans yang dibundle lokal.
- **RabbitMQ Message Broker:** Mengirim metadata upload gambar (`image_upload_queue`) dari Laravel ke Go Consumer backend.
- **Grafana Monitoring:** Berjalan via Docker Compose di port `3001`.

## Halaman

| Route | Method | Deskripsi |
|---|---|---|
| `/` | `GET` | Dashboard pertandingan, liga, berita, klasemen, dan top skor |
| `/football/live` | `GET` | Pertandingan live dengan refresh manual |
| `/football/matches` | `GET` | Jadwal menurut tanggal, AJAX/history dengan fallback GET |
| `/football/` | `GET` | Liga, musim, klasemen, fixtures, tim, top skor, transfer, bracket |
| `/football/fixtures/{id}` | `GET` | Detail pertandingan dan prepare/retry bila belum tersedia |
| `/football/teams/{id}`, `/football/players/{id}` | `GET` | Profil tim dan pemain |
| `/football/search`, `/football/compare`, `/football/transfers` | `GET` | Pencarian, perbandingan, transfer |
| `/berita` | `GET` | Daftar berita dengan pagination |
| `/berita/{id}` | `GET` | Detail berita + link ke sumber asli |
| `/berita/refresh` | `POST` | Trigger backend fetch ulang dari NewsAPI & clear cache FE |
| `/login`, `/admin` | `GET` | Login dan CMS scraper (admin membutuhkan auth) |
| `/upload` | `GET` | Halaman form upload gambar |
| `/upload` | `POST` | Upload file gambar ke storage local & publish event ke RabbitMQ |

## Run Lokal & Docker Services

### 1. Jalankan RabbitMQ & Grafana via Docker Compose

```bash
docker-compose up -d
```

- **RabbitMQ Manager UI:** [http://localhost:15672](http://localhost:15672) (User: `guest`, Pass: `guest`)
- **Grafana Dashboard:** [http://localhost:3001](http://localhost:3001) (User: `admin`, Pass: `admin`)

### 2. Jalankan Laravel FE

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link  # symlink storage public
npm ci --ignore-scripts
npm run build
make run                  # atau: php artisan serve
```

Buka [http://localhost:8000/upload](http://localhost:8000/upload) untuk mencoba fitur upload gambar. Setiap kali gambar di-upload, Laravel akan mengirimkan event payload JSON ke queue `image_upload_queue` di RabbitMQ.

## Tampilan PitchTicker dan preview fixture

Warna dan typography berada di `resources/css/app.css`; light adalah default dan dark disimpan melalui `pitchticker-theme`. Halaman publik memakai `layouts.app`; CMS memakai `layouts.admin` dengan sidebar tersendiri. Logo liga/tim dan isi berita tetap berasal dari backend; tidak ada data pertandingan produksi yang dibuat di frontend.

Untuk preview lokal tanpa backend live, setelah build:

```sh
rtk proxy php -S 127.0.0.1:8099 -t public tests/preview.php
```

Router ini hanya berjalan di PHP development server melalui loopback dan memblokir request HTTP yang tidak difake. Halaman publik memakai database in-memory; CMS memakai SQLite sementara `pitchticker-cms-preview.sqlite` di direktori temporary OS agar perubahan akun fixture bertahan antar-request. Database aplikasi tidak dipakai oleh preview. `/admin` di preview memakai user fixture untuk pemeriksaan visual; mekanisme ini tidak masuk image produksi. Semua isi preview diberi label test fixture. Gunakan `?preview_state=empty`, `error`, `long`, atau `running` untuk state tambahan; `/football/fixtures/999` menguji prepare gagal/retry.

Asset produksi dibuat dalam Node stage Docker lalu disalin ke image PHP; CI juga membuild asset sebelum PHPUnit. Build dapat mengunduh font dari Bunny, tetapi runtime browser tidak membutuhkan CDN CSS/JS/font. Detail hasil pemeriksaan ada di [rebranding-verification.md](../rebranding-verification.md).

## Konfigurasi

CMS tersedia setelah login pada `/admin`, dengan menu Ringkasan, Pengguna, Berita, Liga, Scraper, dan Status sinkronisasi. Semua route CMS membutuhkan autentikasi. Model akses existing dipertahankan: semua akun terdaftar memiliki akses penuh ke CMS, belum ada pembagian role.

Manajemen pengguna menggunakan tabel `users` existing: tambah, cari, edit nama/email/password, dan hapus akun lain dengan konfirmasi. Password baru minimal 12 karakter; password kosong pada edit mempertahankan password lama. Akun yang sedang login tidak dapat menghapus dirinya sendiri.

Berita menggunakan API existing untuk daftar, detail, pencarian/pagination dan refresh sumber. Edit/hapus berita tidak tersedia karena API upstream tidak menyediakan operasi tersebut. Liga dan scraper memakai endpoint Go existing; tidak ada migration/schema atau perubahan backend Go. Status job/sync diperbarui tiap 5 detik selama tab terlihat, dan snapshot sebelumnya tetap ditampilkan saat polling gagal.

Bukti verifikasi CMS dan Delivery Gate: [cms-verification.md](anti-slop/cms-verification.md).

| Env | Default | Keterangan |
|---|---|---|
| `NEWS_API_URL` | `http://localhost:8082` | Base URL backend Go |
| `NEWS_CACHE_TTL` | `300` | TTL cache response (detik) |
| `RABBITMQ_HOST` | `127.0.0.1` | Host RabbitMQ (`rabbitmq` jika di dalam Docker) |
| `RABBITMQ_PORT` | `5672` | Port AMQP RabbitMQ |
| `RABBITMQ_USER` | `guest` | Username RabbitMQ |
| `RABBITMQ_PASSWORD` | `guest` | Password RabbitMQ |
| `RABBITMQ_QUEUE` | `image_upload_queue` | Nama queue event upload gambar |

## Test & Lint

```bash
make test   # PHPUnit dengan environment terisolasi dan HTTP fake
make lint   # Pint check
make fix    # Pint fix
```
