#!/usr/bin/env bash
#
# Первичная установка «Архива БНО» на чистый сервер Ubuntu 22.04 / 24.04.
#
#   curl -fsSL https://raw.githubusercontent.com/qniman/BNO/master/deploy/install.sh \
#     | sudo bash -s -- --domain archive.example.ru --email you@example.ru --admin you@example.ru
#
# Параметры:
#   --domain   домен сайта (без него сайт откроется по IP сервера, без HTTPS)
#   --email    почта для сертификата Let's Encrypt (нужна вместе с --domain)
#   --admin    почта администратора; пароль будет сгенерирован и выведен в конце
#   --repo     git-репозиторий (по умолчанию https://github.com/qniman/BNO.git)
#   --branch   ветка (по умолчанию master)
#   --demo     залить демо-страницы
#
# Скрипт можно запускать повторно: уже сделанные шаги он пропускает.

set -euo pipefail

DOMAIN=""
EMAIL=""
ADMIN=""
REPO="https://github.com/qniman/BNO.git"
BRANCH="master"
DEMO=0
APP_DIR="/var/www/bno"
PHP="8.4"

while [[ $# -gt 0 ]]; do
    case "$1" in
        --domain) DOMAIN="$2"; shift 2 ;;
        --email) EMAIL="$2"; shift 2 ;;
        --admin) ADMIN="$2"; shift 2 ;;
        --repo) REPO="$2"; shift 2 ;;
        --branch) BRANCH="$2"; shift 2 ;;
        --demo) DEMO=1; shift ;;
        *) echo "Неизвестный параметр: $1" >&2; exit 1 ;;
    esac
done

step() { printf '\n\033[1;32m==> %s\033[0m\n' "$*"; }

if [[ $EUID -ne 0 ]]; then
    echo "Запустите от root (sudo)." >&2
    exit 1
fi

export DEBIAN_FRONTEND=noninteractive

step "Системные пакеты"
apt-get update -q
apt-get install -yq software-properties-common ca-certificates curl git unzip nginx sqlite3 ufw

if ! command -v "php${PHP}" >/dev/null; then
    step "PHP ${PHP}"
    add-apt-repository -y ppa:ondrej/php
    apt-get update -q
fi
apt-get install -yq "php${PHP}-fpm" "php${PHP}-cli" "php${PHP}-sqlite3" "php${PHP}-mbstring" "php${PHP}-xml" \
    "php${PHP}-curl" "php${PHP}-zip" "php${PHP}-bcmath" "php${PHP}-intl" "php${PHP}-gd"

if ! command -v composer >/dev/null; then
    step "Composer"
    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
    rm /tmp/composer-setup.php
fi

if ! command -v node >/dev/null || [[ "$(node -v | cut -d. -f1 | tr -d v)" -lt 20 ]]; then
    step "Node.js 22"
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
    apt-get install -yq nodejs
fi

step "Код сайта → ${APP_DIR}"
if [[ ! -d "${APP_DIR}/.git" ]]; then
    git clone --branch "${BRANCH}" "${REPO}" "${APP_DIR}"
else
    git -C "${APP_DIR}" fetch origin "${BRANCH}"
    git -C "${APP_DIR}" reset --hard "origin/${BRANCH}"
fi
git config --global --add safe.directory "${APP_DIR}"
cd "${APP_DIR}"

if [[ ! -f .env ]]; then
    step "Файл настроек .env"
    cp .env.example .env
    URL="http://${DOMAIN:-$(curl -fsS https://api.ipify.org || hostname -I | awk '{print $1}')}"
    [[ -n "${DOMAIN}" && -n "${EMAIL}" ]] && URL="https://${DOMAIN}"
    sed -i \
        -e "s|^APP_ENV=.*|APP_ENV=production|" \
        -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
        -e "s|^APP_URL=.*|APP_URL=${URL}|" \
        -e "s|^LOG_LEVEL=.*|LOG_LEVEL=warning|" \
        .env
    touch database/database.sqlite
fi

step "Зависимости и сборка"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction --quiet
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force
npm ci --no-audit --no-fund --loglevel=error
npm run build

step "База данных"
php artisan migrate --force
# Сидер создаёт стартовые типы страниц; демо-страницы — только с --demo.
BNO_DEMO=$([[ ${DEMO} -eq 1 ]] && echo 1 || echo "") php artisan db:seed --force
php artisan storage:link --force >/dev/null
php artisan optimize

chown -R www-data:www-data "${APP_DIR}"
chmod -R ug+rwX storage bootstrap/cache database

step "Nginx"
SERVER_NAME="${DOMAIN:-_}"
cat > /etc/nginx/sites-available/bno <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${SERVER_NAME};
    root ${APP_DIR}/public;
    index index.php;
    charset utf-8;
    client_max_body_size 12m;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ ^/(build|storage)/ {
        expires 30d;
        access_log off;
        try_files \$uri =404;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ ^/index\.php(/|\$) {
        fastcgi_pass unix:/run/php/php${PHP}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX
ln -sf /etc/nginx/sites-available/bno /etc/nginx/sites-enabled/bno
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl restart "php${PHP}-fpm"

step "Файрвол"
ufw allow OpenSSH >/dev/null
ufw allow 'Nginx Full' >/dev/null
ufw --force enable >/dev/null

if [[ -n "${DOMAIN}" && -n "${EMAIL}" ]]; then
    step "HTTPS-сертификат"
    apt-get install -yq certbot python3-certbot-nginx
    certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos -m "${EMAIL}" --redirect
fi

if [[ -n "${ADMIN}" ]]; then
    step "Администратор"
    php artisan bno:admin "${ADMIN}"
fi

step "Готово"
grep '^APP_URL=' .env | sed 's/APP_URL=/Сайт: /'
echo "Админка: /admin"
[[ -z "${ADMIN}" ]] && echo "Создать администратора: cd ${APP_DIR} && sudo -u www-data php artisan bno:admin почта@пример.ру"
echo "Обновление сайта из git: sudo bash ${APP_DIR}/deploy/deploy.sh"
