# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [0.2.63] - 9 Jun 2018
### Fixed
- where clause binding resolution issue.

## [0.2.62] - 1 Jun 2018
### Fixed
- function name typo.

### Removed
- dump() used for debugging.

## [0.2.61] - 31 May 2018
### Fixed
- caching of paginated queries with page identifiers as arrays (`?page[size]=1`).

## [0.2.60] - 27 May 2018
### Added
- unit tests for multiple versions of Laravel simultaneously.
- backwards-compatibility to Laravel 5.4.

## [0.2.59] - 27 May 2018
### Fixed
- caching of queries with `whereNotIn` clauses.

### Updated
- readme to specify that lazy-loaded relationships are currently not cached.

## [0.2.58] - 24 May 2018
### Fixed
- caching of queries with `whereIn` clauses.

## [0.2.57] - 19 May 2018
### Added
- database name to cache keys and tags to help with multi-tenancy use-cases.

### Fixed
- `find()` using array parameter.

## [0.2.56] - 12 May 2018
### Fixed
- nested `whereNull` within `whereHas`.

## [0.2.55] - 6 May 2018
### Fixed
- caching of `between` where clauses.
- test cache keys and brought them back to green.

## [0.2.54] - 6 May 2018
### Fixed
- caching of query parameter bindings.

## [0.2.53] - 6 May 2018
### Fixed
- `->inRandomOrder()` to not cache the query.

## [0.2.52] - 21 Mar 2018
### Changed
- `flush` console command to be called `clear`, to match other laravel commands.

### Fixed
- implementation of `count()` method.

## [0.2.51] - 10 Mar 2018
### Added
- disabling of `all()` query.

## [0.2.50] - 10 Mar 2018
### Added
- cache invalidation when `destroy()`ing models.

### Fixed
- cache tag generation when calling `all()` queries that prevented proper
  cache invalidation.

## [0.2.49] - 9 Mar 2018
### Fixed
- caching of `->first()` queries.

## [0.2.48] - 9 Mar 2018
### Added
- database connection name to cache prefix.

## [0.2.47] - 5 Mar 2018
### Fixed
- exception when calling disableCache() when caching is already disabled via config.

## [0.2.46] - 5 Mar 2018
### Fixed
- package dependency version to work with Laravel 5.5.

## [0.2.45] - 3 Mar 2018
### Fixed
- pagination cache key generation; fixes #85.

## [0.2.44] - 3 Mar 2018
### Fixed
- disabling of caching using the query scope.

## [0.2.43] - 2 Mar 2018
### Fixed
- actions on belongsToMany relationships not flushing cache when needed.

## [0.2.42] - 28 Feb 2018
### Added
- additional integration tests for additional use cases.

### Fixed
- flushing a specific model from the command line that extended a base class and did not use the trait directly.

## [0.2.41] - 26 Feb 2018
### Fixes
- cache invalidation when using ->insert() method.
- cache invalidation when using ->update() method.

## [0.2.40] - 24 Feb 2018
### Updated
- code with some home-cleaning and refactoring.

## [0.2.39] - 24 Feb 2018
### Updated
- CachedBuilder class with some refactoring and cleanup.

## [0.2.38] - 24 Feb 2018
### Added
- cache-invalidation-cool-down functionality.

## [0.2.37] - 23 Feb 2018
### Added
- disabling of `->all()` method caching via config flag.

## [0.2.36] - 23 Feb 2018
### Added
- config setting to allow disabling of model-caching.

## [0.2.35] - 21 Feb 2018
### Fixed
- cache key generation for `find()` and `findOrFail()`.

### Added
- caching for `paginate()`;

## [0.2.34] - 21 Feb 2018
### Added
- implementation tests using redis.
- additional tests for some edge case scenarios.

### Fixed
- cache key prefix functionality.

### Updated
- tests through refactoring and cleaning up.

## [0.2.33] - 19 Feb 2018
### Added
- unit test to make sure `Model::all()` returns a collection when only only
  record is retrieved.
- console command to flush entire model-cache.

## [0.2.32] - 19 Feb 2018
### Fixed
- hash collision logic to not run query twice if not needed.

## [0.2.31] - 18 Feb 2018
### Added
- optional cache key prefixing.

## [0.2.30] - 18 Feb 2018
### Changed
- detection of Cachable trait to use `class_uses()` instead of looking for
  method.

## [0.2.29] - 18 Feb 2018
### Added
- hash collision detection and prevetion.

## [0.2.28] - 18 Feb 2018
### Changed
- disabling of cache from using session to use cache-key instead.

## [0.2.27] - 17 Feb 2018
### Fixed
- the erroneous use of `arrayEmpty()` function, changed to simple `count()`.

## [0.2.26] - 16 Feb 2018
### Added
- refactor functionality to trait (thanks @rs-sliske!).

## [0.2.25] - 16 Feb 2018
### Fixed
- readme spelling errors (thanks @fridzema!).

## [0.2.24] - 16 Feb 2018
### Fixed
- whereNotIn query caching.

## [0.2.23] - 13 Feb 2018
### Fixed
- whereBetween and value bindings parsing.

## [0.2.22] - 10 Feb 2018
### Fixed
- Laravel 5.5 dependencies.

## [0.2.21] - 9 Feb 2018
### Added
- Laravel 5.6 compatibility.

## [0.2.20] - 7 Feb 2018
### Fixed
- previously existing unit tests to properly consider changes made in 0.2.19.

## [0.2.19] - 7 Feb 2018
### Fixed
- parsing of where clause operators.

## [0.2.18] - 16 Jan 2018
### Added
- hashing of cache keys to prevent key length over-run issues.

### Updated
- dependency version constraint for "pretty test printer".

## [0.2.17] - 10 Jan 2018
###Added
- caching for value() querybuilder method.

### Updated
- tests to use Orchestral Testbench.

## [0.2.16] - 5 Jan 2018
### Added
- `thanks` package.

### Updated
- readme explaining `thanks` package.

## [0.2.15] - 30 Dec 2017
### Added
- sanity checks for artisan command with feedback as to what needs to be fixed.

## [0.2.14] - 30 Dec 2017
### Added
- ability to flush cache for a given model via Artisan command.

## [0.2.13] - 28 Dec 2017
### Added
- ability to define custom cache store in `.env` file.

## [0.2.12] - 14 Dec 2017
### Added
- chainable method to disable caching of queries.

## [0.2.11] - 13 Dec 2017
### Added
- functionality to clear corresponding cache tags when model is deleted.

## [0.2.10] - 5 Dec 2017
### Fixed
- caching when using `orderByRaw()`.

## [0.2.9] - 19 Nov 2017
### Added
- test for query scopes.
- test for relationship query.

### Updated
- readme file.
- travis configuration.

## [0.2.8] - 2017-10-17
### Updated
- code with optimizations and refactoring.

## [0.2.7] - 2017-10-16
### Added
- remaining unit tests that were incomplete, thanks everyone who participated!
- added parsing of where `doesnthave()` condition.

## [0.2.6] - 2017-10-12
### Added
- orderBy clause to cache key. Thanks @RobMKR for the PR!

## [0.2.5] - 2017-10-04
### Fixed
- parsing of nested, exists, raw, and column where clauses.

## [0.2.4] - 2017-10-03
### Added
- .gitignore file to reduce download size for production environment.

### Fixed
- parsing of where clauses with null and notnull.

## [0.2.3] - 2017-10-03
### Fixed
- parsing of where clauses to account for some edge cases.

## [0.2.2] - 2017-09-29
### Added
- additional unit tests for checking caching of lazy-loaded relationships.

### Fixed
- generation of cache key for queries with where clauses.

## [0.2.1] - 2017-09-25
### Fixed
- where clause parsing when where clause is empty.

## [0.2.0] - 2017-09-24
### Changed
- approach to caching things. Completely rewrote the CachedBuilder class.

### Added
- many, many more tests.

## [0.1.0] - 2017-09-22
### Added
- initial development of package.
- unit tests with 100% code coverage.
