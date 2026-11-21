#!/bin/sh
set -eu

echo "[perkoci] Menyiapkan direktori penyimpanan..."

mkdir -p \
    bootstrap/cache \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
    echo "[perkoci] ERROR: APP_KEY belum diisi." >&2
    echo "[perkoci] Jalankan 'php artisan key:generate --show' lalu simpan hasilnya" >&2
    echo "[perkoci] sebagai environment variable APP_KEY di server/Coolify." >&2
    exit 1
fi

if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Tunggu database siap (penting saat docker compose / Coolify baru start).
if [ "${DB_CONNECTION:-}" = "mysql" ]; then
    echo "[perkoci] Menunggu database ${DB_HOST:-127.0.0.1}:${DB_PORT:-3306}..."
    percobaan=0
    until php -r '$host = getenv("DB_HOST") ?: "127.0.0.1"; $port = getenv("DB_PORT") ?: "3306"; $db = getenv("DB_DATABASE") ?: ""; $user = getenv("DB_USERNAME") ?: ""; $pass = getenv("DB_PASSWORD") ?: ""; try { new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [PDO::ATTR_TIMEOUT => 2]); } catch (Throwable $e) { exit(1); }' 2>/dev/null; do
        percobaan=$((percobaan + 1))
        if [ "$percobaan" -ge 30 ]; then
            echo "[perkoci] Database belum merespons setelah 60 detik, lanjut start Apache." >&2
            break
        fi
        sleep 2
    done
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "[perkoci] Menjalankan migrasi database..."
    php artisan migrate --force
fi

if [ "${RUN_SEED:-false}" = "true" ]; then
    echo "[perkoci] Menjalankan seeder database (idempotent)..."
    php artisan db:seed --force
fi

if [ "${RUN_OPTIMIZE:-true}" = "true" ]; then
    echo "[perkoci] Membuat cache konfigurasi, route, dan view..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Cache dibuat oleh root; kembalikan kepemilikan agar Apache dapat menulis.
chown -R www-data:www-data storage bootstrap/cache

echo "[perkoci] Menjalankan: $*"
exec "$@"
