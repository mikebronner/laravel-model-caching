<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorBeginsWithA;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithInlineGlobalScope;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthorWithInlineGlobalScope;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScopeTest extends IntegrationTestCase
{
    public function testScopeClauseParsing()
    {
        $author = factory(Author::class, 1)
            ->create(['name' => 'Anton'])
            ->first();
        $authors = (new Author)
            ->startsWithA()
            ->get();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-name_like_A%-authors.deleted_at_null");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor"];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->startsWithA()
            ->get();

        $this->assertTrue($authors->contains($author));
        $this->assertTrue($cachedResults->contains($author));
        $this->assertTrue($liveResults->contains($author));
    }

    public function testScopeClauseWithParameter()
    {
        $author = factory(Author::class, 1)
            ->create(['name' => 'Boris'])
            ->first();
        $authors = (new Author)
            ->nameStartsWith("B")
            ->get();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-name_like_B%-authors.deleted_at_null");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor"];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->nameStartsWith("B")
            ->get();

        $this->assertTrue($authors->contains($author));
        $this->assertTrue($cachedResults->contains($author));
        $this->assertTrue($liveResults->contains($author));
    }

    public function testGlobalScopesAreCached()
    {
        $author = factory(UncachedAuthor::class, 1)
            ->create(['name' => 'Alois'])
            ->first();
        $authors = (new AuthorBeginsWithA)
            ->get();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthorbeginswitha-name_like_A%");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthorbeginswitha"];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->nameStartsWith("A")
            ->get();

        $this->assertTrue($authors->contains($author));
        $this->assertTrue($cachedResults->contains($author));
        $this->assertTrue($liveResults->contains($author));
    }

    public function testInlineGlobalScopesAreCached()
    {
        $author = factory(UncachedAuthor::class, 1)
            ->create(['name' => 'Alois'])
            ->first();
        $authors = (new AuthorWithInlineGlobalScope)
            ->get();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthorwithinlineglobalscope-authors.deleted_at_null-name_like_A%");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthorwithinlineglobalscope"];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthorWithInlineGlobalScope)
            ->get();

        $this->assertTrue($authors->contains($author));
        $this->assertTrue($cachedResults->contains($author));
        $this->assertTrue($liveResults->contains($author));
    }

    public function testLocalScopesInRelationship()
    {
        $first = "A";
        $second = "B";
        $authors1 = (new Author)
            ->with(['books' => static function (HasMany $model) use ($first) {
                $model->startsWith($first);
            }])
            ->get();
        $authors2 = (new Author)
            ->disableModelCaching()
            ->with(['books' => static function (HasMany $model) use ($second) {
                $model->startsWith($second);
            }])
            ->get();

        // $this->assertNotEquals($authors1, $authors2);
        $this->markTestSkipped();
    }
}
