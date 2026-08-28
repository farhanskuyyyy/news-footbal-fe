# Laravel FE — Portal Berita & Upload Service

Frontend Laravel (Blade murni) yang menampilkan berita dari backend Go (Echo) serta fitur upload gambar dengan event dispatcher ke RabbitMQ.

## Arsitektur

- **Backend (Go News API):** Berjalan di port `8082` — `GET /health`, `GET /news`, `GET /news/{id}`.
- **Frontend (Laravel 13):** Blade + Tailwind (CDN).
- **RabbitMQ Message Broker:** Mengirim metadata upload gambar (`image_upload_queue`) dari Laravel ke Go Consumer backend.
- **Grafana Monitoring:** Berjalan via Docker Compose di port `3001`.

## Halaman

| Route | Method | Deskripsi |
|---|---|---|
| `/` | `GET` | Daftar berita (title, source, tanggal, deskripsi terpotong) + pagination |
| `/berita/{id}` | `GET` | Detail berita + link ke sumber asli |
| `/refresh` | `POST` | Trigger backend fetch ulang dari NewsAPI & clear cache FE |
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
make run                  # atau: php artisan serve
```

Buka [http://localhost:8000/upload](http://localhost:8000/upload) untuk mencoba fitur upload gambar. Setiap kali gambar di-upload, Laravel akan mengirimkan event payload JSON ke queue `image_upload_queue` di RabbitMQ.

## Konfigurasi

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
make test   # PHPUnit test suite (14 passing tests)
make lint   # Pint check
make fix    # Pint fix
```
