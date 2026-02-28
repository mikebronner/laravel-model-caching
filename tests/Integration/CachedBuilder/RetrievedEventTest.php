<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class RetrievedEventTest extends IntegrationTestCase
{
    public function test_retrieved_event_fires_on_cache_miss()
    {
        $firedCount = 0;

        Author::retrieved(function () use (&$firedCount) {
            $firedCount++;
        });

        $this->cache()->flush();

        $authors = (new Author)->get();

        $this->assertGreaterThan(0, $authors->count());
        $this->assertGreaterThan(0, $firedCount, 'Retrieved event should fire on cache miss');
    }

    public function test_retrieved_event_fires_on_cache_hit()
    {
        // First call — cache miss, populates cache
        (new Author)->get();

        $firedCount = 0;

        Author::retrieved(function () use (&$firedCount) {
            $firedCount++;
        });

        // Second call — cache hit
        $authors = (new Author)->get();

        $this->assertGreaterThan(0, $authors->count());
        $this->assertGreaterThan(0, $firedCount, 'Retrieved event should fire on cache hit');
    }

    public function test_retrieved_event_fires_on_cache_hit_for_find()
    {
        $author = (new Author)->first();

        $firedCount = 0;

        Author::retrieved(function () use (&$firedCount) {
            $firedCount++;
        });

        // Cache hit
        $result = (new Author)->find($author->id);

        $this->assertNotNull($result);
        $this->assertGreaterThanOrEqual(1, $firedCount, 'Retrieved event should fire on cache hit for find()');
    }

    public function test_retrieved_event_fires_on_cache_hit_for_first()
    {
        // Cache miss
        (new Author)->first();

        $firedCount = 0;

        Author::retrieved(function () use (&$firedCount) {
            $firedCount++;
        });

        // Cache hit
        $result = (new Author)->first();

        $this->assertNotNull($result);
        $this->assertGreaterThanOrEqual(1, $firedCount, 'Retrieved event should fire on cache hit for first()');
    }

    public function test_retrieved_event_fires_on_cache_hit_for_paginate()
    {
        // Cache miss
        (new Author)->paginate(5);

        $firedCount = 0;

        Author::retrieved(function () use (&$firedCount) {
            $firedCount++;
        });

        // Cache hit
        $result = (new Author)->paginate(5);

        $this->assertGreaterThan(0, $result->count());
        $this->assertGreaterThan(0, $firedCount, 'Retrieved event should fire on cache hit for paginate()');
    }
}
