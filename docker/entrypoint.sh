#!/bin/sh
# Container start: prepares secrets, caches the configuration, migrates (and seeds) the database.
set -e
cd /var/www/html

# Secrets: taken from the environment, otherwise generated once and kept in the storage volume.
mkdir -p storage/app/keys
if [ -z "$APP_KEY" ]; then
    [ -f storage/app/keys/app_key ] || php -r 'echo "base64:".base64_encode(random_bytes(32));' > storage/app/keys/app_key
    export APP_KEY="$(cat storage/app/keys/app_key)"
fi
if [ -z "$JWT_SECRET" ]; then
    [ -f storage/app/keys/jwt_secret ] || php -r 'echo bin2hex(random_bytes(32));' > storage/app/keys/jwt_secret
    export JWT_SECRET="$(cat storage/app/keys/jwt_secret)"
fi
chmod 600 storage/app/keys/* 2>/dev/null || true

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# DatabaseSeeder skips seeding when users already exist.
if [ "${SEED_DATABASE:-true}" = "true" ]; then
    php artisan db:seed --force
fi

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
