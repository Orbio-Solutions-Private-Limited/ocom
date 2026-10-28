#!/usr/bin/env bash
set -euo pipefail

APP_DIR=/opt/orbio/ocom
DEPLOY_DIR="${APP_DIR}/deploy"

cd "${APP_DIR}"

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views/livewire/islands \
    storage/logs \
    bootstrap/cache
touch storage/logs/laravel.log

sudo chown -R ubuntu:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 2775 {} +
sudo find storage bootstrap/cache -type f -exec chmod 664 {} +

composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-req=php

python3 - <<'PY'
from pathlib import Path

path = Path('vendor/bagistoplus/visual-debut/resources/views/sections/customer-order-details.blade.php')
text = path.read_text()
text = text.replace(
    "@foreach ($item->additional['attributes'] as $attribute)",
    '@foreach ($item->displayAttributes() as $attribute)',
)
text = text.replace(
    '$item->getTypeInstance()->getOrderedItem($item)->sku',
    '$item->getTypeInstance()->getOrderedItem($item)?->sku ?? $item->sku',
)
path.write_text(text)
PY

php artisan migrate --force --no-interaction
php artisan storage:link --force --no-interaction
php artisan vendor:publish --tag=visual-assets --force --no-interaction
php artisan optimize --no-interaction
php artisan view:clear --no-interaction

sudo cp "${DEPLOY_DIR}/php/99-ocom.ini" /etc/php/8.5/fpm/conf.d/99-ocom.ini
sudo cp "${DEPLOY_DIR}/php/99-ocom.ini" /etc/php/8.5/cli/conf.d/99-ocom.ini

if sudo test -f /etc/letsencrypt/live/pluto.orbiosolutions.com/fullchain.pem; then
    sudo cp "${DEPLOY_DIR}/nginx/pluto.orbiosolutions.com.conf" /etc/nginx/sites-available/pluto
    sudo ln -sfn /etc/nginx/sites-available/pluto /etc/nginx/sites-enabled/pluto
    sudo nginx -t
    sudo systemctl reload nginx
fi

sudo cp "${DEPLOY_DIR}/systemd/ocom-queue.service" /etc/systemd/system/ocom-queue.service
sudo cp "${DEPLOY_DIR}/systemd/ocom-scheduler.service" /etc/systemd/system/ocom-scheduler.service
sudo cp "${DEPLOY_DIR}/systemd/ocom-scheduler.timer" /etc/systemd/system/ocom-scheduler.timer

sudo chown -R ubuntu:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 2775 {} +
sudo find storage bootstrap/cache -type f -exec chmod 664 {} +

sudo systemctl daemon-reload
sudo systemctl enable --now ocom-queue.service ocom-scheduler.timer
sudo systemctl reload php8.5-fpm
sudo systemctl restart ocom-queue.service
