<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Collection;

class FirstCacheInvalidationTest extends IntegrationTestCase
{
    /**
     * AC 1: `Model::where(...)->first()` returns a non-stale result consistent
     *       with `->get()->first()` when the cache is warm.
     */
    public function test_first_returns_non_stale_result_after_model_saved()
    {
        // Warm the cache via first()
        $cachedAuthor = (new Author)->first();

        // Confirm get()->first() and first() agree on initial data
        $viaGet = (new Author)->get()->first();
        $this->assertEquals($cachedAuthor->id, $viaGet->id);

        // Mutate the model
        $newName = 'Updated Name '.uniqid();
        $cachedAuthor->name = $newName;
        $cachedAuthor->save();

        // After save, first() must return the fresh (updated) record — not stale cache
        $freshAuthor = (new Author)->first();
        $this->assertEquals($newName, $freshAuthor->name);
    }

    public function test_first_returns_non_stale_result_after_model_created()
    {
        // Truncate then cache the "no match" result
        (new Author)->truncate();

        $uniqueName = 'Unique Author X9Y8Z7';
        $noAuthor = (new Author)->where('name', $uniqueName)->first();
        $this->assertNull($noAuthor);

        // Create a matching author — created event must flush the cache
        $author = Author::create([
            'name' => $uniqueName,
            'email' => 'x9y8z7@noemail.com',
        ]);

        // first() must now return the newly created author, not null
        $freshAuthor = (new Author)->where('name', $uniqueName)->first();
        $this->assertNotNull($freshAuthor);
        $this->assertEquals($author->id, $freshAuthor->id);
    }

    /**
     * AC 2: Cache key for ->first() is distinct from ->get() when results differ.
     */
    public function test_first_cache_key_is_distinct_from_get()
    {
        // Warm both caches
        $collection = (new Author)->get();
        $model = (new Author)->first();

        // first() must return a single Model instance, not a Collection
        $this->assertInstanceOf(Author::class, $model);
        $this->assertNotInstanceOf(Collection::class, $model);

        // The cache keys must be different
        $firstKey = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite".
            ':authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-first'
        );
        $getKey = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite".
            ':authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null'
        );
        $this->assertNotEquals($firstKey, $getKey);

        // The cached value under the -first key must be a single model, not a collection
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite".
            ':genealabslaravelmodelcachingtestsfixturesauthor',
        ];
        $cached = $this->cache()->tags($tags)->get($firstKey);
        $this->assertNotNull($cached, 'No cache entry found for ->first()');
        $this->assertInstanceOf(Author::class, $cached['value']);

        // The cached get() collection must be distinct from the first() value
        $cachedCollection = $this->cache()->tags($tags)->get($getKey);
        $this->assertNotNull($cachedCollection, 'No cache entry found for ->get()');
        $this->assertInstanceOf(Collection::class, $cachedCollection['value']);
    }

    /**
     * AC 3: Regression — ->first() on composite where conditions (unique-key pattern)
     *       returns fresh data after model updates.
     */
    public function test_first_on_composite_where_returns_non_stale_result_after_update()
    {
        $email = 'composite-'.uniqid().'@noemail.com';
        $name = 'Composite Test Author';

        // Create an author matched by the composite where
        $author = Author::create(['name' => $name, 'email' => $email]);

        // Using array-style where (the syntax originally reported as problematic)
        $found = (new Author)->where(['email' => $email, 'name' => $name])->first();
        $this->assertNotNull($found);
        $this->assertEquals($author->id, $found->id);

        // Update the author's name — the original composite where no longer matches
        $newName = 'Renamed Composite Author';
        $author->name = $newName;
        $author->save();

        // Cache must be invalidated: old conditions should return null
        $staleCheck = (new Author)->where(['email' => $email, 'name' => $name])->first();
        $this->assertNull(
            $staleCheck,
            'first() returned a stale result — cache was not properly invalidated after save'
        );

        // New conditions should return the updated model
        $updated = (new Author)->where(['email' => $email, 'name' => $newName])->first();
        $this->assertNotNull($updated);
        $this->assertEquals($newName, $updated->name);
    }
}
