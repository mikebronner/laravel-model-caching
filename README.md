# Laravel-cached-models

## Features
- automatic relationship caching.
- automatic cache flushing when models are changed.
- automatic use of cache flags for cache providers that support them (will flush
  entire cache for providers that don't).
- provides simple caching methods for use in query methods for models that take
  advantage of the automatic cache management features mentioned.
