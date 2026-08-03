# Deployment — VPS Ubuntu (Docker + Compose)

Pola deploy sama dengan backend: push ke `main` → GitHub Actions SSH ke VPS → `git pull` → `docker compose up -d --build`.

Routing production (nginx di host):

```
https://domain/      → FE  (container, 127.0.0.1:3000)
https://domain/api/  → BE  (Go API, 127.0.0.1:8082, prefix /api di-strip)
```

FE memanggil BE **server-side** (PHP → Go), lewat host gateway Docker, bukan lewat nginx.

## 1. Setup awal di VPS (sekali saja)

### Clone via deploy key

```bash
# Di VPS — generate deploy key khusus repo ini
ssh-keygen -t ed25519 -f ~/.ssh/laravel-fe_deploy -N "" -C "deploy laravel-fe"
cat ~/.ssh/laravel-fe_deploy.pub
```

Tambahkan public key tadi di GitHub: **repo → Settings → Deploy keys → Add deploy key** (read-only cukup).

```bash
# Config SSH biar git pakai key itu untuk repo ini
cat >> ~/.ssh/config <<'EOF'
Host github.com-laravel-fe
    HostName github.com
    User git
    IdentityFile ~/.ssh/laravel-fe_deploy
EOF

git clone github.com-laravel-fe:USERNAME/laravel-fe.git ~/laravel-fe
cd ~/laravel-fe
```

> Ganti `USERNAME/laravel-fe` sesuai repo kamu.

### Env & compose override

```bash
cd ~/laravel-fe
cp .env.example .env
# generate APP_KEY tanpa PHP di host:
docker run --rm -v $(pwd):/app -w /app serversideup/php:8.3-cli php artisan key:generate

# edit .env: APP_ENV=production, APP_DEBUG=false, APP_URL=https://domainmu

cp docker-compose.override.yml.example docker-compose.override.yml
```

`docker-compose.override.yml` menambahkan `host.docker.internal` → gateway host, dan set `NEWS_API_URL=http://host.docker.internal:8082` supaya container FE bisa manggil Go API yang bind di `127.0.0.1:8082` host. (Dari dalam container, `127.0.0.1` = container itu sendiri, makanya tidak bisa langsung.)

### Start container

```bash
docker compose up -d --build
curl -s http://127.0.0.1:3000/ | head   # harus keluar HTML
```

Container bind ke `127.0.0.1:3000` saja (lihat `docker-compose.yml`) — tidak terekspos publik.

## 2. Update config nginx host

Config lama (semua request → 8082) diubah jadi dua location block:

```nginx
server {
    # ... listen/ssl/server_name yang sudah ada ...

    # BE: /api/... → Go API, prefix /api di-strip oleh trailing slash di proxy_pass
    location /api/ {
        proxy_pass http://127.0.0.1:8082/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # FE: semua sisanya → Laravel
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
sudo nginx -t && sudo systemctl reload nginx
```

Cek: `https://domain/` tampil daftar berita, `https://domain/api/health` balas `{"status":"ok"}`.

## 3. GitHub Actions secrets

Di **repo → Settings → Secrets and variables → Actions**, tambahkan:

| Secret | Isi |
|---|---|
| `SERVER_HOST` | IP / hostname VPS |
| `SERVER_USER` | User SSH di VPS (mis. `ubuntu`) |
| `SSH_PRIVATE_KEY` | Private key SSH yang bisa login ke VPS (bukan deploy key repo) |

> `SSH_PRIVATE_KEY` = key untuk **login SSH ke server** (public key-nya ada di `~/.ssh/authorized_keys` VPS). Deploy key di langkah 1 = key untuk **git pull dari GitHub**. Dua hal berbeda.

## 4. Flow CI/CD

Workflow: `.github/workflows/ci.yml`

- **push/PR ke `main` & `dev`** → job `test` (composer install, Pint, PHPUnit) lalu job `docker` (build image, sanity check).
- **push ke `main` saja** → job `deploy`: SSH ke VPS (`appleboy/ssh-action`), lalu:

```bash
cd ~/laravel-fe
git pull origin main
docker compose up -d --build
```

## Troubleshooting

- **Halaman "Berita sedang tidak tersedia" (503):** FE tidak bisa reach Go API. Cek dari VPS: `curl http://127.0.0.1:8082/health`, lalu cek dari container: `docker compose exec app curl -s http://host.docker.internal:8082/health`.
- **Perubahan berita tidak muncul:** cache FE TTL 300 detik. Paksa refresh: `docker compose exec app php artisan cache:clear`.
- **Log:** `docker compose logs -f app`.
