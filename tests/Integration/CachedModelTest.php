<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithCooldown;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\PrefixedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use ReflectionClass;

class CachedModelTest extends IntegrationTestCase
{
    public function test_all_model_results_creates_cache()
    {
        $authors = (new Author)->all();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->all();

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function test_scope_disables_caching()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor"];
        $authors = (new Author)
            ->where('name', 'Bruno')
            ->disableCache()
            ->get();

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertNull($cachedResults);
        $this->assertNotEquals($authors, $cachedResults);
    }

    public function test_scope_disables_caching_when_called_on_model()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:test-prefix:genealabslaravelmodelcachingtestsfixturesauthor"];
        $authors = (new PrefixedAuthor)
            ->disableCache()
            ->where('name', 'Bruno')
            ->get();

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertNull($cachedResults);
        $this->assertNotEquals($authors, $cachedResults);
    }

    public function test_scope_disable_cache_doesnt_crash_when_caching_is_disabled_in_config()
    {
        config(['laravel-model-caching.enabled' => false]);
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:test-prefix:genealabslaravelmodelcachingtestsfixturesauthor"];
        $authors = (new PrefixedAuthor)
            ->where('name', 'Bruno')
            ->disableCache()
            ->get();

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertNull($cachedResults);
        $this->assertNotEquals($authors, $cachedResults);
    }

    public function test_all_method_caching_can_be_disabled_via_config()
    {
        config(['laravel-model-caching.enabled' => false]);
        $authors = (new Author)
            ->all();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];
        config(['laravel-model-caching.enabled' => true]);

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertEmpty($cachedResults);
        $this->assertNotEmpty($authors);
        $this->assertCount(10, $authors);
    }

    public function test_where_has_is_being_cached()
    {
        $books = (new Book)
            ->with('author')
            ->whereHas('author', function ($query) {
                $query->whereId('1');
            })
            ->get();

        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook-exists-and_books.author_id_=_authors.id-id_=_1-authors.deleted_at_null-testing:{$this->testingSqlitePath}testing.sqlite:author");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEquals(1, $books->first()->author->id);
        $this->assertEquals(1, $cachedResults->first()->author->id);
    }

    public function test_where_has_with_closure_is_being_cached()
    {
        $books1 = (new Book)
            ->with('author')
            ->whereHas('author', function ($query) {
                $query->whereId(1);
            })
            ->get()
            ->keyBy('id');
        $books2 = (new Book)
            ->with('author')
            ->whereHas('author', function ($query) {
                $query->whereId(2);
            })
            ->get()
            ->keyBy('id');

        $this->assertNotEmpty($books1->diffKeys($books2));
    }

    public function test_cooldown_is_not_queried_for_normal_cached_models()
    {
        $class = new ReflectionClass(Author::class);
        $method = $class->getMethod('getModelCacheCooldown');
        $method->setAccessible(true);
        $author = (new Author)
            ->first();

        $this->assertEquals([null, null, null], $method->invokeArgs($author, [$author]));
    }

    public function test_cooldown_is_queried_for_cooldown_models()
    {
        $class = new ReflectionClass(AuthorWithCooldown::class);
        $method = $class->getMethod('getModelCacheCooldown');
        $method->setAccessible(true);
        $author = (new AuthorWithCooldown)
            ->withCacheCooldownSeconds(1)
            ->first();

        [$usesCacheCooldown, $expiresAt, $savedAt] = $method->invokeArgs($author, [$author]);

        $this->assertEquals($usesCacheCooldown, 1);
        $this->assertEquals("Illuminate\Support\Carbon", get_class($expiresAt));
        $this->assertNull($savedAt);
    }

    public function test_model_cache_doesnt_invalidate_during_cooldown_period()
    {
        $authors = (new AuthorWithCooldown)
            ->withCacheCooldownSeconds(1)
            ->get();

        Author::factory()->count(1)->create();
        $authorsDuringCooldown = (new AuthorWithCooldown)
            ->get();
        $uncachedAuthors = (new UncachedAuthor)
            ->get();
        sleep(3);
        $authorsAfterCooldown = (new AuthorWithCooldown)
            ->get();

        $this->assertCount(10, $authors);
        $this->assertCount(10, $authorsDuringCooldown);
        $this->assertCount(11, $uncachedAuthors);
        $this->assertCount(11, $authorsAfterCooldown);
    }

    public function test_model_cache_does_invalidate_when_no_cooldown_period()
    {
        $authors = (new AuthorWithCooldown)
            ->get();

        Author::factory()->count(1)->create();
        $authorsAfterCreate = (new Author)
            ->get();
        $uncachedAuthors = (new UncachedAuthor)
            ->get();

        $this->assertCount(10, $authors);
        $this->assertCount(11, $authorsAfterCreate);
        $this->assertCount(11, $uncachedAuthors);
    }
}
