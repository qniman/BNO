#!/usr/bin/env bash
#
# Обновление сайта на сервере до последней версии из git.
# Запускается вручную (sudo bash /var/www/bno/deploy/deploy.sh)
# или автоматически из GitHub Actions после пуша в master.

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/bno}"
BRANCH="${BRANCH:-master}"
PHP="${PHP:-8.4}"

cd "${APP_DIR}"
export COMPOSER_ALLOW_SUPERUSER=1

echo "==> Получаю ${BRANCH}"
git fetch --quiet origin "${BRANCH}"
OLD=$(git rev-parse HEAD)
git reset --quiet --hard "origin/${BRANCH}"
NEW=$(git rev-parse HEAD)
echo "    ${OLD:0:7} → ${NEW:0:7}"

php artisan down --retry=15 >/dev/null || true
trap 'php artisan up >/dev/null' EXIT

echo "==> Зависимости"
composer install --no-dev --optimize-autoloader --no-interaction --quiet

# Фронтенд пересобираем, только если он менялся.
if [[ "${OLD}" == "${NEW}" ]] || git diff --quiet "${OLD}" "${NEW}" -- package.json package-lock.json resources/ vite.config.js 2>/dev/null; then
    [[ -f public/build/manifest.json ]] || { npm ci --no-audit --no-fund --loglevel=error; npm run build; }
else
    echo "==> Сборка фронтенда"
    npm ci --no-audit --no-fund --loglevel=error
    npm run build
fi

echo "==> Миграции и кеши"
php artisan migrate --force
php artisan optimize:clear >/dev/null
php artisan optimize >/dev/null

chown -R www-data:www-data storage bootstrap/cache database public/build
systemctl reload "php${PHP}-fpm"

echo "==> Готово: $(git log -1 --format='%h %s')"
