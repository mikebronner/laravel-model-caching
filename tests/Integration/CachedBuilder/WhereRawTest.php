<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

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
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Http\Resources\Author as AuthorResource;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class WhereRawTest extends IntegrationTestCase
{
    use RefreshDatabase;

    public function testRawWhereClauseParsing()
    {
        $authors = collect([(new Author)
            ->whereRaw('name <> \'\'')
            ->first()]);

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_and_name-first');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = collect([$this->cache()->tags($tags)->get($key)['value']]);

        $liveResults = collect([(new UncachedAuthor)
            ->whereRaw('name <> \'\'')->first()]);

        $this->assertTrue($authors->diffKeys($cachedResults)->isEmpty());
        $this->assertTrue($liveResults->diffKeys($cachedResults)->isEmpty());
    }

    public function testWhereRawWithQueryParameters()
    {
        $authorName = (new Author)->first()->name;
        $authors = (new Author)
            ->where("name", "!=", "test")
            ->whereRaw("name != 'test3'")
            ->whereRaw('name = ? AND name != ?', [$authorName, "test2"])
            ->get();
        $key = sha1("genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthorname_!=_test_and_name_!=_'test3'_and_name_=_Guido_Feest__AND_name_!=_test2");
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = collect([$this->cache()->tags($tags)->get($key)['value']]);
        $liveResults = (new UncachedAuthor)
            ->where("name", "!=", "test")
            ->whereRaw("name != 'test3'")
            ->whereRaw('name = ? AND name != ?', [$authorName, "test2"])
            ->get();

        $this->assertTrue($authors->diffKeys($cachedResults)->isEmpty());
        $this->assertTrue($liveResults->diffKeys($cachedResults)->isEmpty());
    }

    public function testMultipleWhereRawCacheUniquely()
    {
        $book1 = (new UncachedBook)->first();
        $book2 = (new UncachedBook)->orderBy("id", "DESC")->first();
        $cachedBook1 = (new Book)->whereRaw('title = ?', [$book1->title])->first();
        $cachedBook2 = (new Book)->whereRaw('title = ?', [$book2->title])->first();

        $this->assertEquals($cachedBook1->title, $book1->title);
        $this->assertEquals($cachedBook2->title, $book2->title);
    }
}
