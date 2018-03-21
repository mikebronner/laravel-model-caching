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
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this->artisan('modelCache:clear', ['--model' => Author::class]);
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

        $key = sha1('genealabs:laravel-model-caching:testing:test-prefix:genealabslaravelmodelcachingtestsfixturesprefixedauthor');
        $tags = ['genealabs:laravel-model-caching:testing:test-prefix:genealabslaravelmodelcachingtestsfixturesprefixedauthor'];

        $cachedResults = $this
            ->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this->artisan('modelCache:clear', ['--model' => PrefixedAuthor::class]);
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
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this->artisan(
            'modelCache:clear',
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
            'modelCache:clear',
            ['--model' => UncachedAuthor::class]
        );

        $this->assertEquals($result, 1);
    }

    public function testAllModelsAreFlushed()
    {
        (new Author)->all();
        (new Book)->all();
        (new Store)->all();

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];
        $cachedAuthors = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'];
        $cachedBooks = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesstore');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesstore'];
        $cachedStores = $this->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertNotEmpty($cachedAuthors);
        $this->assertNotEmpty($cachedBooks);
        $this->assertNotEmpty($cachedStores);

        $this->artisan('modelCache:clear');

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];
        $cachedAuthors = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'];
        $cachedBooks = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesstore');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesstore'];
        $cachedStores = $this->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEmpty($cachedAuthors);
        $this->assertEmpty($cachedBooks);
        $this->assertEmpty($cachedStores);
    }
}
