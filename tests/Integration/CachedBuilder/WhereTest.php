<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

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
        $key = sha1('genealabs:laravel-model-caching:testing::memory::test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor-name_=_' .
            $author->name);
        $tags = ['genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesauthor'];

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
            'genealabs:laravel-model-caching:testing::memory::test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor-name',
            '_',
            str_replace(' ', '_', strtolower($operator)),
            '_',
            $author->name,
        ];
        $key = sha1(implode('', $keyParts));
        $tags = ['genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesauthor'];

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

    public function testTwoWhereClausesAfterEachOther()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor-id_>_0-id_<_100');
        $tags = ['genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesauthor'];

        $authors = (new Author)
            ->where("id", ">", 0)
            ->where("id", "<", 100)
            ->get();
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->where("id", ">", 0)
            ->where("id", "<", 100)
            ->get();

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testWhereUsesCorrectBinding()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor-id_>_5-name_like_B%');
        $tags = ['genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesauthor'];

        $authors = (new Author)
            ->where("id", ">", 2)
            ->where("name", "LIKE", "A%")
            ->get();
        $authors = (new Author)
            ->where("id", ">", 5)
            ->where("name", "LIKE", "B%")
            ->get();
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->where("id", ">", 5)
            ->where("name", "LIKE", "B%")
            ->get();

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }
}
