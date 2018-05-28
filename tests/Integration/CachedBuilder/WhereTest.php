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

class WhereTest extends IntegrationTestCase
{


    public function testWithQuery()
    {
        $books = (new Book)
            ->where(function ($query) {
                $query->where("id", ">", "1")
                    ->where("id", "<", "5");
            })
            ->get();
        $uncachedBooks = (new UncachedBook)
            ->where(function ($query) {
                $query->where("id", ">", "1")
                    ->where("id", "<", "5");
            })
            ->get();

        $this->assertEquals($books->pluck("id"), $uncachedBooks->pluck("id"));
    }

    public function testColumnsRelationshipWhereClauseParsing()
    {
        $author = (new Author)
            ->orderBy('name')
            ->first();
        $authors = (new Author)
            ->where('name', '=', $author->name)
            ->get();
        $key = sha1('genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor-name_=_' .
            $author->name);
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->where('name', '=', $author->name)
            ->get();

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    private function processWhereClauseTestWithOperator(string $operator)
    {
        $author = (new Author)->first();
        $authors = (new Author)
            ->where('name', $operator, $author->name)
            ->get();
        $keyParts = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor-name',
            '_',
            str_replace(' ', '_', strtolower($operator)),
            '_',
            $author->name,
        ];
        $key = sha1(implode('', $keyParts));
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->where('name', $operator, $author->name)
            ->get();

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testWhereClauseParsingOfOperators()
    {
        $this->processWhereClauseTestWithOperator('=');
        $this->processWhereClauseTestWithOperator('!=');
        $this->processWhereClauseTestWithOperator('<>');
        $this->processWhereClauseTestWithOperator('>');
        $this->processWhereClauseTestWithOperator('<');
        $this->processWhereClauseTestWithOperator('LIKE');
        $this->processWhereClauseTestWithOperator('NOT LIKE');
    }
}
