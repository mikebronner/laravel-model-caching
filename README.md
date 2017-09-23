![pexels-photo-325229](https://user-images.githubusercontent.com/1791050/30768358-0df9d0f2-9fbb-11e7-9f10-ad40b83bbf59.jpg)

# Model Caching for Laravel
[![Travis](https://img.shields.io/travis/GeneaLabs/laravel-model-caching.svg)](https://travis-ci.org/GeneaLabs/laravel-model-caching)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/fde269ac-c382-4d17-a647-c69ad6b9dd85.svg)](https://insight.sensiolabs.com/projects/fde269ac-c382-4d17-a647-c69ad6b9dd85)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/GeneaLabs/laravel-model-caching.svg)](https://scrutinizer-ci.com/g/GeneaLabs/laravel-model-caching)
[![Coveralls](https://img.shields.io/coveralls/GeneaLabs/laravel-model-caching.svg)](https://coveralls.io/github/GeneaLabs/laravel-model-caching)
[![GitHub (pre-)release](https://img.shields.io/github/release/GeneaLabs/laravel-model-caching/all.svg)](https://github.com/GeneaLabs/laravel-model-caching)
[![Packagist](https://img.shields.io/packagist/dt/GeneaLabs/laravel-model-caching.svg)](https://packagist.org/packages/genealabs/laravel-model-caching)

## Impetus
I created this package in response to a client project that had complex, nested
forms with many `<select>`'s that resulted in over 700 database queries on one
page. I needed a package that abstracted the caching process out of the model
for me, and one that would let me cache custom queries, as well as cache model
relationships. This package is the attempt to address those requirements.

## Features
-   automatic, self-invalidating relationship caching.
-   automatic use of cache flags for cache providers that support them (will
    flush entire cache for providers that don't).
-   provides simple caching methods for use in query methods for models that
    take advantage of the automatic cache management features mentioned.

## Requirements
-   PHP >= 7.0.0
-   Laravel 5.5

## Usage
For best performance a taggable cache provider is recommended (redis,
memcached). While this is optional, using a non-taggable cache provider will
mean that the entire cache is cleared each time a model is created, saved,
updated, or deleted.

For best implementation results, I would recommend adding a `BaseModel` model
from which all your other models are extended. The BaseModel should extend from
`CachedModel`.

### Automatic Relationship Caching
When writing custom queries in your models, use `$this->cache()` instead of
`cache()` to automatically tag and cache the queries. These will also be auto-
invalidated.

```php
<?php namespace App;

use GeneaLabs\LaravelModelCaching\CachedModel;

abstract class BaseModel extends CachedModel
{
    //
}
```

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Venue extends BaseModel
{
    protected $fillable = [
        'name',
    ];

    public function address() : BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function getAll() : Collection
    {
        return $this->cache()
            ->rememberForever("venues-getAll", function () {
                return $this->orderBy('name')
                    ->get();
            });
    }
}
```

### Custom Query Caching
**`$this->cache(array $keys)`**
This method is available in any model that extends `CachedModel`, as well
as automatically invalidate them. Pass in respective additional models that are
represented in the query being cached. This is most often necessary when eager-
loading relationships.

When you create the cache key, be sure to build the key in such a way that it
uniquely represents the query and does not overwrite keys of other queries. The
best way to achieve this is to build the key as follows: `<model slug>-<model
method>-<unique key>`. The `unique key` portion is only necessary if you pass in
parameters for your query where clauses.

```php
public function getByTypes(array $types) : Collection
{
    $key = implode('-', $types);

    return $this->cache([ContactType::class])
        ->rememberForever("contacts-getByTypes-{$key}", function () use ($types) {
            return $this
                ->with(['contactTypes' => function ($query) use ($types) {
                    $query->whereIn('title', $types);
                }])
                ->orderBy('name')
                ->get();
        });
}
```

## Dedication to Quality
During package development I try as best as possible to embrace good design and
development practices to try to ensure that this package is as good as it can
be. My checklist for package development includes:

-   ✅ Achieve as close to 100% code coverage as possible using unit tests.
-   ✅ Eliminate any issues identified by SensioLabs Insight and Scrutinizer.
-   ✅ Be fully PSR1, PSR2, and PSR4 compliant.
-   ✅ Include comprehensive documentation in README.md.
-   ✅ Provide an up-to-date CHANGELOG.md which adheres to the format outlined
    at <http://keepachangelog.com>.
-   ✅ Have no PHPMD or PHPCS warnings throughout all code.

## Contributing
Please observe and respect all aspects of the included Code of Conduct <https://github.com/GeneaLabs/laravel-model-caching/blob/master/CODE_OF_CONDUCT.md>.

### Reporting Issues
When reporting issues, please fill out the included template as completely as
possible. Incomplete issues may be ignore or closed if there is not enough
information included to be actionable.

### Submitting Pull Requests
Please review the Contribution Guidelines <https://github.com/GeneaLabs/laravel-model-caching/blob/master/CONTRIBUTING.md>.
Only PRs that meet all criterium will be accepted.
