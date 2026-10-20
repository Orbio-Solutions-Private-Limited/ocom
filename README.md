# Ocom

E-commerce platform by [Orbio Solutions](https://www.orbiosolutions.com/), built on Laravel and Vue.js.

## Requirements

- PHP 8.4+
- MySQL 8.0, MariaDB 10.11, or PostgreSQL 16
- Composer
- Node.js (for building Admin, Shop, and Installer assets)

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan bagisto:install
```

Set `APP_NAME=Ocom` and your database credentials in `.env` before installing.

## Development

```bash
php artisan serve
php artisan optimize:clear
```

Build storefront and admin assets:

```bash
cd packages/Webkul/Admin && npm install && npm run build
cd packages/Webkul/Shop && npm install && npm run build
```

## Docker

Use `docker-compose.yml` in the project root for a local stack with Laravel Sail.

## Testing

```bash
vendor/bin/pest --parallel
vendor/bin/pint --test
php artisan bagisto:translations:check
```

## License

This project is open source under the [MIT License](LICENSE).

## Contact

[https://www.orbiosolutions.com/](https://www.orbiosolutions.com/)
