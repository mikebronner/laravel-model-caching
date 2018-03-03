<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Console\Commands;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\PrefixedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlushTest extends IntegrationTestCase
{
    use RefreshDatabase;

    public function testGivenModelIsFlushed()
    {
        $authors = (new Author)->all();
        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this->artisan('modelCache:flush', ['--model' => Author::class]);
        $flushedResults = $this->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($flushedResults);
        $this->assertEquals($result, 0);
    }

    public function testExtendedModelIsFlushed()
    {
        $authors = (new PrefixedAuthor)
            ->get();

        $key = sha1('genealabs:laravel-model-caching:test-prefix:genealabslaravelmodelcachingtestsfixturesprefixedauthor');
        $tags = ['genealabs:laravel-model-caching:test-prefix:genealabslaravelmodelcachingtestsfixturesprefixedauthor'];

        $cachedResults = $this
            ->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this->artisan('modelCache:flush', ['--model' => PrefixedAuthor::class]);
        $flushedResults = $this
            ->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($flushedResults);
        $this->assertEquals($result, 0);
    }

    public function testGivenModelWithRelationshipIsFlushed()
    {
        $authors = (new Author)->with('books')->get();
        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor-books');
        $tags = [
            'genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this->artisan(
            'modelCache:flush',
            ['--model' => Author::class]
        );
        $flushedResults = $this->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($flushedResults);
        $this->assertEquals($result, 0);
    }

    public function testNonCachedModelsCannotBeFlushed()
    {
        $result = $this->artisan(
            'modelCache:flush',
            ['--model' => UncachedAuthor::class]
        );

        $this->assertEquals($result, 1);
    }

    public function testAllModelsAreFlushed()
    {
        (new Author)->all();
        (new Book)->all();
        (new Store)->all();

        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor'];
        $cachedAuthors = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesbook');
        $tags = ['genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesbook'];
        $cachedBooks = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesstore');
        $tags = ['genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesstore'];
        $cachedStores = $this->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertNotEmpty($cachedAuthors);
        $this->assertNotEmpty($cachedBooks);
        $this->assertNotEmpty($cachedStores);

        $this->artisan('modelCache:flush');

        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor'];
        $cachedAuthors = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesbook');
        $tags = ['genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesbook'];
        $cachedBooks = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesstore');
        $tags = ['genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesstore'];
        $cachedStores = $this->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEmpty($cachedAuthors);
        $this->assertEmpty($cachedBooks);
        $this->assertEmpty($cachedStores);
    }
}
