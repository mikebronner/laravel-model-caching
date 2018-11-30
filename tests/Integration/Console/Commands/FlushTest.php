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
    public function setUp()
    {
        parent::setUp();

        if (starts_with($this->app->version(), '5.7')) {
            $this->withoutMockingConsoleOutput();
        }
    }

    public function testGivenModelIsFlushed()
    {
        $authors = (new Author)->all();
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = $this
            ->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this->artisan('modelCache:clear', ['--model' => Author::class]);
        $flushedResults = $this
            ->cache
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

        $key = sha1('genealabs:laravel-model-caching:testing::memory::test-prefix:authors:genealabslaravelmodelcachingtestsfixturesprefixedauthor');
        $tags = ['genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesprefixedauthor'];

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
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor-books');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook',
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

        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor'];
        $cachedAuthors = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:testing::memory::books:genealabslaravelmodelcachingtestsfixturesbook');
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook'];
        $cachedBooks = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:testing::memory::stores:genealabslaravelmodelcachingtestsfixturesstore');
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesstore'];
        $cachedStores = $this->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertNotEmpty($cachedAuthors);
        $this->assertNotEmpty($cachedBooks);
        $this->assertNotEmpty($cachedStores);

        $this->artisan('modelCache:clear');

        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor'];
        $cachedAuthors = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:testing::memory::books:genealabslaravelmodelcachingtestsfixturesbook');
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook'];
        $cachedBooks = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1('genealabs:laravel-model-caching:testing::memory::stores:genealabslaravelmodelcachingtestsfixturesstore');
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesstore'];
        $cachedStores = $this->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEmpty($cachedAuthors);
        $this->assertEmpty($cachedBooks);
        $this->assertEmpty($cachedStores);
    }
}
