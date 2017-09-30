# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [0.2.1] - 2017-09-29
### Added
- additional unit tests for checking caching of lazy-loaded relationships.

### Fixed
- generation of cache key for queries with where clauses.

## [0.2.0] - 2017-09-24
### Changed
- approach to caching things. Completely rewrote the CachedBuilder class.

### Added
- many, many more tests.

## [0.1.0] - 2017-09-22
### Added
- initial development of package.
- unit tests with 100% code coverage.
