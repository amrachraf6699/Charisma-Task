# Product Inventory Microservice

Laravel REST API for managing products and stock levels.

## Stack

- PHP 8.1+
- Laravel 10
- PostgreSQL
- Redis
- Docker Compose
- PHPUnit
- L5-Swagger

## Features

- Product CRUD API
- UUID product IDs
- Soft deletes
- Stock adjustment endpoint
- Low-stock listing
- Redis cache for product listing
- Cache TTL and invalidation on product writes
- Rate limiting for read/write API routes
- Low-stock event/listener alert
- Repository pattern
- Structured JSON responses and exception handling
- Swagger UI documentation
- Feature test coverage

## Docker Setup

Copy the environment file:

```bash
cp .env.example .env
```

Start the full stack:

```bash
docker compose up --build
```

The app will be available at:

```text
http://localhost:8000
```

The compose stack includes Laravel/PHP, PostgreSQL, and Redis. The app container installs dependencies, runs migrations, and starts the Laravel development server.

## Local Setup

Install dependencies:

```bash
composer install
```

Create `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Start the app:

```bash
php artisan serve
```

## API Endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/products` | List products with pagination |
| `GET` | `/api/products/{product}` | Get a single product |
| `POST` | `/api/products` | Create a product |
| `PUT` | `/api/products/{product}` | Update a product |
| `DELETE` | `/api/products/{product}` | Soft delete a product |
| `POST` | `/api/products/{product}/stock` | Adjust stock |
| `GET` | `/api/products/low-stock` | List products below threshold |

## Swagger Documentation

Generate Swagger docs:

```bash
php artisan l5-swagger:generate
```

Open Swagger UI:

```text
http://localhost:8000/api/documentation
```

## Postman

A Postman collection is included:

```text
postman_collection.json
```

## Testing

Run the test suite:

```bash
php artisan test
```

The tests use SQLite in-memory through `phpunit.xml`.

## Formatting

Check PSR-12 formatting:

```bash
./vendor/bin/pint --test
```

Format the codebase:

```bash
./vendor/bin/pint
```

## Response Format

Successful responses:

```json
{
  "success": true,
  "message": "Products fetched successfully.",
  "data": [],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 0
    }
  }
}
```

## Architecture Notes

This service is a Laravel 10 JSON API for product inventory and stock operations. Routes are defined in `routes/api.php` and handled by `ProductController`. Responses are normalized through the `ApiResponse` trait, and the exception handler converts validation failures, missing models, authorization failures, method errors, and rate-limit errors into structured API responses.

Products use UUID primary keys, soft deletes, a unique SKU, decimal pricing, stock quantity, low-stock threshold, and enum status. The `lowStock` model scope keeps the threshold query close to the model.

`ProductRepositoryInterface` defines product persistence operations, with `EloquentProductRepository` as the current implementation. The controller depends on the interface so HTTP concerns remain separate from data access.

`GET /api/products` is cached in Redis with a TTL controlled by `PRODUCT_LISTING_CACHE_TTL`. Product observer hooks flush the product cache tag on create, update, delete, restore, and force delete, keeping cached listings fresh after writes.

`ProductObserver` dispatches `ProductStockBecameLow` when stock is at or below the configured threshold. `SendLowStockAlert` currently logs the alert, leaving room for email, queues, or notification channels later.

Docker Compose runs Laravel/PHP, PostgreSQL, and Redis. PostgreSQL is the delivery database, while tests use SQLite in-memory for speed and isolation. L5-Swagger generates API documentation from annotations in `app/Swagger/ProductApiDocumentation.php`.

Error responses:

```json
{
  "success": false,
  "message": "Validation failed.",
  "data": {
    "sku": [
      "The sku field is required."
    ]
  }
}
```
