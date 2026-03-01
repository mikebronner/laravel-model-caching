# Model Caching for Laravel

[![Laravel Package](https://github.com/GeneaLabs/laravel-model-caching/workflows/Laravel%20Package/badge.svg?branch=master)](https://github.com/GeneaLabs/laravel-model-caching/actions?query=workflow%3A%22Laravel+Package%22)
[![Packagist](https://img.shields.io/packagist/dt/GeneaLabs/laravel-model-caching.svg)](https://packagist.org/packages/genealabs/laravel-model-caching)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/GeneaLabs/laravel-model-caching/master/LICENSE)

![Model Caching for Laravel masthead image](https://repository-images.githubusercontent.com/103836049/b0d89480-f1b1-11e9-8e13-a7055f008fe6)

## Summary
Automatic, self-invalidating Eloquent model and relationship caching. Add a
trait to your models and all query results are cached automatically — no manual
cache keys, no forgetting to invalidate. When a model is created, updated, or
deleted the relevant cache entries are flushed for you.

Typical performance improvements range from 100–900% reduction in database
queries on read-heavy pages.

**Use this package when** your application makes many repeated Eloquent queries
and you want a drop-in caching layer that stays in sync with your data without
any manual bookkeeping.

### What Gets Cached
- Model queries (`get`, `first`, `find`, `all`, `paginate`, `pluck`, `value`, `exists`)
- Aggregations (`count`, `sum`, `avg`, `min`, `max`)
- Eager-loaded relationships (via `with()`)

### What Does Not Get Cached
- Lazy-loaded relationships (see [#127](https://github.com/GeneaLabs/laravel-model-caching/issues/127))
- Queries using `select()` clauses (see [#238](https://github.com/GeneaLabs/laravel-model-caching/issues/238))
- Queries inside transactions (manual flush required, see [#305](https://github.com/GeneaLabs/laravel-model-caching/issues/305))
- `inRandomOrder()` queries (caching is automatically disabled)

### Cache Drivers
A taggable cache driver is required:
```diff
+ Redis (recommended)
+ Memcached
+ APC
+ Array (for testing)
```

Non-taggable drivers are not supported:
```diff
- File
- Database
- DynamoDB
```

### Requirements
- PHP 8.2+
- Laravel 11, 12, or 13

## Installation
```
composer require mikebronner/laravel-model-caching
```

The service provider is auto-discovered. No additional setup is required.

### Basic Usage
Add the `Cachable` trait to your models. The recommended approach is a base
model that all other models extend:

```php
<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use Cachable;
}
```

Alternatively, extend the included `CachedModel` directly:

```php
<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\CachedModel;

class Post extends CachedModel
{
    // ...
}
```

That's it — all Eloquent queries and eager-loaded relationships on these models
are now cached and automatically invalidated.

> **Note:** Avoid adding `Cachable` to the `User` model. It extends
> `Illuminate\Foundation\Auth\User`, and overriding that can break
> authentication. User data should generally be fresh anyway.

## Configuration
Publish the config file:
```sh
php artisan modelCache:publish --config
```

This creates `config/laravel-model-caching.php`:

```php
return [
    'cache-prefix'         => env('MODEL_CACHE_CACHE_PREFIX', ''),
    'enabled'              => env('MODEL_CACHE_ENABLED', true),
    'use-database-keying'  => env('MODEL_CACHE_USE_DATABASE_KEYING', true),
    'store'                => env('MODEL_CACHE_STORE'),
    'fallback-to-database' => env('MODEL_CACHE_FALLBACK_TO_DB', false),
];
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `MODEL_CACHE_ENABLED` | `true` | Enable or disable caching globally. |
| `MODEL_CACHE_STORE` | `null` | Cache store name from `config/cache.php`. Uses the default store when not set. |
| `MODEL_CACHE_USE_DATABASE_KEYING` | `true` | Include database connection and name in cache keys. Important for multi-tenant or multi-database apps. |
| `MODEL_CACHE_CACHE_PREFIX` | `''` | Global prefix applied to all cache keys. |
| `MODEL_CACHE_FALLBACK_TO_DB` | `false` | When `true`, falls back to direct database queries if the cache backend is unavailable (e.g. Redis is down) instead of throwing an exception. |

### Custom Cache Store
To use a dedicated cache store for model caching, define one in
`config/cache.php` and reference it:
```
MODEL_CACHE_STORE=model-cache
```

### Cache Key Prefix
For multi-tenant applications you can isolate cache entries per tenant. Set the
prefix globally in config:
```php
'cache-prefix' => 'tenant-123',
```

Or per-model via a property:
```php
class Post extends Model
{
    use Cachable;

    protected $cachePrefix = 'tenant-123';
}
```

### Multiple Database Connections
When `use-database-keying` is enabled (the default), cache keys automatically
include the database connection and name. This keeps cache entries separate
across connections without any extra configuration.

### Disabling Cache
There are three ways to bypass caching:

**1. Per-query:**
```php
$results = MyModel::disableCache()->where('active', true)->get();
```

**2. Globally via environment:**
```
MODEL_CACHE_ENABLED=false
```

**3. For a block of code:**
```php
$result = app('model-cache')->runDisabled(function () {
    return MyModel::get();
});

// or via the Facade
use GeneaLabs\LaravelModelCaching\Facades\ModelCache;

ModelCache::runDisabled(function () {
    return MyModel::get();
});
```

> **Tip:** Use option 1 in seeders to avoid pulling stale cached data during
> reseeds.

### Cache Cool-Down Period
In high-traffic scenarios (e.g. frequent comment submissions) you may want to
prevent every write from immediately flushing the cache. Set a cool-down period
on the model:

```php
class Comment extends Model
{
    use Cachable;

    protected $cacheCooldownSeconds = 300; // 5 minutes
}
```

Then use it in queries:
```php
// Use the model's default cool-down
Comment::withCacheCooldownSeconds()->get();

// Override with a specific duration
Comment::withCacheCooldownSeconds(30)->get();
```

During the cool-down window, cache is not flushed on every write. After it
expires, the next modification triggers a flush.

### Graceful Fallback
When enabled, if the cache backend (e.g. Redis) is unavailable the package logs
a warning and falls back to querying the database directly — your application
continues to function without caching rather than throwing an exception.

```
MODEL_CACHE_FALLBACK_TO_DB=true
```

### Cache Invalidation
Cache is automatically flushed when:

| Trigger | Behavior |
|---------|----------|
| Model created | Flush model cache |
| Model updated/saved | Flush model cache |
| Model deleted | Flush only if rows were actually deleted |
| Model force-deleted | Flush only if rows were actually deleted |
| Pivot `attach` / `detach` / `sync` / `updateExistingPivot` | Flush relationship cache |
| `increment` / `decrement` | Flush model cache |
| `insert` / `update` (builder) | Flush model cache |
| `truncate` | Flush model cache |

Cache tags are generated for the primary model, each eager-loaded relationship,
joined tables, and morph-to target types, so only the relevant entries are
invalidated.

### BelongsToMany with Custom Pivot Models
Cache invalidation works for `BelongsToMany` relationships using custom pivot
models (`->using(CustomPivot::class)`) as long as either the parent or the
related model uses the `Cachable` trait.

### Manual Cache Flushing

**Artisan command — single model:**
```sh
php artisan modelCache:clear --model=App\\Models\\Post
```

**Artisan command — all models:**
```sh
php artisan modelCache:clear
```

**Programmatic via Facade:**
```php
use GeneaLabs\LaravelModelCaching\Facades\ModelCache;

// Single model
ModelCache::invalidate(App\Models\Post::class);

// Multiple models
ModelCache::invalidate([
    App\Models\Post::class,
    App\Models\Comment::class,
]);
```

## Contributing
Contributions are welcome. Please review the
[Contribution Guidelines](https://github.com/GeneaLabs/laravel-model-caching/blob/master/CONTRIBUTING.md)
and observe the
[Code of Conduct](https://github.com/GeneaLabs/laravel-model-caching/blob/master/CODE_OF_CONDUCT.md)
before submitting a pull request.

## Security
Please review the [Security Policy](https://github.com/GeneaLabs/laravel-model-caching/blob/master/SECURITY.md)
for information on supported versions and how to report vulnerabilities.

---

This is an MIT-licensed open-source project. Its continued development is made
possible by the community. If you find it useful, please consider
[becoming a sponsor](https://github.com/sponsors/mikebronner).
