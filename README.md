# FreshCart — Multi-Vendor E-Commerce Platform

A multi-vendor marketplace API built with **Laravel 12** and **PHP 8.2**. Vendors manage their own catalogs while customers browse, search, and check out across all stores — with fast full-text search, nested categories, coupons, product variants, and reviews.

## Features

- **Multi-vendor marketplace** — each vendor owns its products; customers shop across all vendors in a single cart and checkout
- **Product catalog** — products with variants (`ProductVariant`), customer reviews, and a nested category tree
- **Full-text search** — powered by **Laravel Scout + Meilisearch** for fast, typo-tolerant product search
- **Nested categories** — hierarchical category tree using an adjacency-list model
- **Cart & checkout** — cart, cart items, orders, order items, and saved shipping addresses
- **Coupons & discounts** — coupon codes with per-user redemption tracking
- **Reviews & ratings** — customers can review products they purchased
- **Token-based API** — stateless REST API secured with **Laravel Sanctum**
- **Email logging** — outbound notification/email tracking via an `EmailLog` model

## Tech Stack

| Area | Technology |
|------|------------|
| Backend | Laravel 12, PHP 8.2 |
| Database | MySQL |
| API Auth | Laravel Sanctum |
| Search | Laravel Scout + Meilisearch |
| Categories | staudenmeir/laravel-adjacency-list |
| Dev / Docker | Laravel Sail |
| Testing | PHPUnit |
| Code Style | Laravel Pint |

## Documentation

Feature notes live in the [`docs/`](docs) folder:

- [`docs/search.md`](docs/search.md) — how product search is indexed and queried with Scout + Meilisearch
- [`docs/coupons.md`](docs/coupons.md) — coupon rules and redemption flow

## Getting Started

```bash
# 1. Clone and install dependencies
git clone https://github.com/MostafaMosaad3/FreshCart.git
cd FreshCart
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate
# configure DB and MEILISEARCH_* variables in .env

# 3. Database
php artisan migrate --seed

# 4. Index searchable models into Meilisearch
php artisan scout:import "App\Models\Product"

# 5. Run
php artisan serve
```

### Using Laravel Sail (Docker)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
```

## Testing

```bash
php artisan test
```

## License

Released under the [MIT License](LICENSE).
