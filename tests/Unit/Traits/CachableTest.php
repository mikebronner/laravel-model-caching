<?php namespace GeneaLabs\LaravelModelCaching\Tests\Unit;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedProfile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedStore;
use GeneaLabs\LaravelModelCaching\Tests\UnitTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CachableTest extends UnitTestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        cache()->flush();
        $publishers = factory(Publisher::class, 10)->create();
        factory(Author::class, 10)->create()
            ->each(function ($author) use ($publishers) {
                factory(Book::class, random_int(2, 10))->make()
                    ->each(function ($book) use ($author, $publishers) {
                        $book->author()->associate($author);
                        $book->publisher()->associate($publishers[rand(0, 9)]);
                        $book->save();
                    });
                factory(Profile::class)->make([
                    'author_id' => $author->id,
                ]);
            });

        $bookIds = (new Book)->all()->pluck('id');
        factory(Store::class, 10)->create()
            ->each(function ($store) use ($bookIds) {
                $store->books()->sync(rand($bookIds->min(), $bookIds->max()));
            });
        cache()->flush();
    }

    public function testSpecifyingAlternateCacheDriver()
    {
        $configCacheStores = config('cache.stores');
        $configCacheStores['customCache'] = ['driver' => 'array'];
        config(['cache.stores' => $configCacheStores]);
        config(['laravel-model-caching.store' => 'customCache']);
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $authors = (new Author)
            ->all();
        $defaultcacheResults = cache()
            ->tags($tags)
            ->get($key);
        $customCacheResults = cache()
            ->store('customCache')
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->all();

        $this->assertEquals($customCacheResults, $authors);
        $this->assertNull($defaultcacheResults);
        $this->assertEmpty($liveResults->diffAssoc($customCacheResults));
    }
}
