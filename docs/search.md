# Product Search — Scout + Meilisearch

Full-text product search for FreshCart, powered by [Laravel Scout](https://laravel.com/docs/scout)
over a [Meilisearch](https://www.meilisearch.com/) engine.

## Why a search engine (not `LIKE` / FULLTEXT)

`LIKE '%honey%'` disables the index (leading `%` → full table scan), has no relevance
ranking, no typo tolerance, and no facets. MySQL `FULLTEXT` is better but still weak on
typo tolerance, Arabic tokenization, synonyms, and faceted counts. FreshCart is a
marketplace where user intent is fuzzy, so we run a real engine. Scout is the abstraction
(`Product::search(...)`); Meilisearch is the implementation — swap engines with one config
change, no model edits.

## Engine

- **Meilisearch (self-hosted).** Locally, run the `meilisearch.exe` binary listening on
  `http://127.0.0.1:7700` (Docker/Compose is deferred to Week 14).
- Master key lives in `.env` (`MEILI_MASTER_KEY`), wired into Scout via
  `MEILISEARCH_HOST` / `MEILISEARCH_KEY`.
- Health check: `curl http://127.0.0.1:7700/health` → `{"status":"available"}`.

## Relevant `.env`

```
SCOUT_DRIVER=meilisearch
SCOUT_QUEUE=true
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY="${MEILI_MASTER_KEY}"
```

## Queue

Indexing runs on the `scout` queue (`SCOUT_QUEUE=true`). A worker **must** be running or
indexing jobs pile up in the `jobs` table:

```bash
php artisan queue:work --queue=scout,default
```

Trade-off: a model save returns instantly, and the index catches up ~100ms later (the
worker processes the Meilisearch call in the background). Never index synchronously in
production.

## Index shape

`Product::toSearchableArray()` defines what each document looks like — a *derivative*,
denormalized structure, not a copy of the table:

- `category_names` / `vendor_name` are flattened (no JOINs at search time).
- `in_stock` is computed from `stock > 0`.
- `created_at` is stored as a Unix timestamp (sorts correctly).
- `makeSearchableUsing()` eager-loads `categories` + `vendor` per batch → no N+1 on bulk reindex.
- `searchableAs()` env-prefixes the index (`products_local`, `products_production`) → no local/prod collision.
- `shouldBeSearchable()` keeps drafts/inactive products out of the index.

## Attribute roles

Defined in `app/Console/Commands/ConfigureMeilisearchCommand.php` (`search:configure`):

- **searchable** (in priority order): `name`, `description`, `category_names`, `vendor_name`, `tags`
- **filterable**: `vendor_id`, `category_ids`, `price`, `rating_avg`, `in_stock`
- **sortable**: `price`, `rating_avg`, `created_at`

Re-run `php artisan search:configure` whenever these roles change.

## Endpoints

- `GET /api/search/products` — full-text search + filters + sort + pagination.
  Query params (see `ProductSearchRequest`): `q`, `category_ids[]`, `vendor_id`,
  `price_min`, `price_max`, `rating_min`, `in_stock`, `sort`
  (`price_asc|price_desc|rating_desc|newest`), `page`. Defaults to in-stock only.
- `GET /api/search/products/facets` — facet counts per `category_names` + `vendor_name`
  via a `raw()` query (`facetDistribution`). The frontend calls both in parallel.

## Operations

- **Apply index settings:** `php artisan search:configure`
- **Reindex everything:** `php artisan scout:import "App\Models\Product"`
  (then verify `numberOfDocuments` matches `Product::active()->count()`).
- **After a mapping change** (new field, or changing a field's role): re-run
  `search:configure` **and** a full `scout:import`. Single-row updates auto-sync; mapping
  changes do not.
- **In production:** run `search:configure` during deploy and watch for failed indexing jobs.

## Testing

Tests force the in-memory `collection` driver (`SCOUT_DRIVER=collection` in `phpunit.xml`,
or `config(['scout.driver' => 'collection'])` in `setUp()`) — no live Meilisearch needed.
The collection driver does substring matching only: no typo tolerance, ranking, or facets.
Behavior that needs the real engine (typo tolerance, relevance) belongs in a CI-only
integration test grouped `meilisearch`.

## Consistency

MySQL is the source of truth; Meilisearch is a derived copy. A scheduled reconciliation
job (compare document counts, re-import on drift) is added in Week 8 — not today.
