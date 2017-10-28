![pexels-photo-325229](https://user-images.githubusercontent.com/1791050/30768358-0df9d0f2-9fbb-11e7-9f10-ad40b83bbf59.jpg)

# Model Caching for Laravel
[![Gitter](https://badges.gitter.im/GeneaLabs/laravel-model-caching.svg)](https://gitter.im/GeneaLabs/laravel-model-caching?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=body_badge)
[![Travis](https://img.shields.io/travis/GeneaLabs/laravel-model-caching.svg)](https://travis-ci.org/GeneaLabs/laravel-model-caching)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/fde269ac-c382-4d17-a647-c69ad6b9dd85.svg)](https://insight.sensiolabs.com/projects/fde269ac-c382-4d17-a647-c69ad6b9dd85)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/GeneaLabs/laravel-model-caching.svg)](https://scrutinizer-ci.com/g/GeneaLabs/laravel-model-caching)
[![Coveralls](https://img.shields.io/coveralls/GeneaLabs/laravel-model-caching.svg)](https://coveralls.io/github/GeneaLabs/laravel-model-caching)
[![GitHub (pre-)release](https://img.shields.io/github/release/GeneaLabs/laravel-model-caching/all.svg)](https://github.com/GeneaLabs/laravel-model-caching)
[![Packagist](https://img.shields.io/packagist/dt/GeneaLabs/laravel-model-caching.svg)](https://packagist.org/packages/genealabs/laravel-model-caching)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/GeneaLabs/laravel-model-caching/master/LICENSE)

## Impetus
I created this package in response to a client project that had complex, nested
forms with many `<select>`'s that resulted in over 700 database queries on one
page. I needed a package that abstracted the caching process out of the model
for me, and one that would let me cache custom queries, as well as cache model
relationships. This package is the attempt to address those requirements.

## Features
-   automatic, self-invalidating relationship (both eager- and lazy-loaded) caching.
-   automatic, self-invalidating model query caching.
-   automatic use of cache tags for cache providers that support them (will
    flush entire cache for providers that don't).

## Requirements
-   PHP >= 7.0.0
-   Laravel 5.5

## Usage
For best performance a taggable cache provider is recommended (redis,
memcached). While this is optional, using a non-taggable cache provider will
mean that the entire cache is cleared each time a model is created, saved,
updated, or deleted.

For ease of maintenance, I would recommend adding a `BaseModel` model that
extends `CachedModel`, from which all your other models are extended. If you
don't want to do that, simply extend your models directly from `CachedModel`.

Here's an example `BaseModel` class:

```php
<?php namespace App;

use GeneaLabs\LaravelModelCaching\CachedModel;

abstract class BaseModel extends CachedModel
{
    //
}
```

**That's all you need to do. All model queries and relationships are now
cached!**

In testing this has optimized performance on some pages up to 900%! Most often
you should see somewhere around 100% performance increase. (I will show some
concrete examples here soon, still working on optimizing things first.)

## Commitment to Quality
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
possible. Incomplete issues may be ignored or closed if there is not enough
information included to be actionable.

### Submitting Pull Requests
Please review the Contribution Guidelines <https://github.com/GeneaLabs/laravel-model-caching/blob/master/CONTRIBUTING.md>.
Only PRs that meet all criterium will be accepted.
