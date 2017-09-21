** DO NOT INSTALL -- STILL IN EXPERIMENTAL STAGE**

# Model Caching for Laravel
[![Travis](https://img.shields.io/travis/GeneaLabs/laravel-model-caching.svg)](https://travis-ci.org/GeneaLabs/laravel-model-caching)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/fde269ac-c382-4d17-a647-c69ad6b9dd85.svg)](https://insight.sensiolabs.com/projects/fde269ac-c382-4d17-a647-c69ad6b9dd85)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/GeneaLabs/laravel-model-caching.svg)](https://scrutinizer-ci.com/g/GeneaLabs/laravel-model-caching)
[![Coveralls](https://img.shields.io/coveralls/GeneaLabs/laravel-model-caching.svg)](https://coveralls.io/github/GeneaLabs/laravel-model-caching)
[![GitHub (pre-)release](https://img.shields.io/github/release/GeneaLabs/laravel-model-caching/all.svg)](https://github.com/GeneaLabs/laravel-model-caching)
[![Packagist](https://img.shields.io/packagist/dt/GeneaLabs/laravel-model-caching.svg)](https://packagist.org/packages/genealabs/laravel-model-caching)

## Features
- automatic relationship caching.
- automatic cache flushing when models are changed.
- automatic use of cache flags for cache providers that support them (will flush
  entire cache for providers that don't).
- provides simple caching methods for use in query methods for models that take
  advantage of the automatic cache management features mentioned.
