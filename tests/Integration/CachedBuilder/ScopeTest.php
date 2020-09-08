<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorBeginsWithScoped;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithInlineGlobalScope;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthorWithInlineGlobalScope;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $user = factory(User::class)->create(["name" => "Abernathy Kings"]);
        $this->actingAs($user);
        $author = factory(UncachedAuthor::class, 1)
            ->create(['name' => 'Alois'])
            ->first();
        $authors = (new AuthorBeginsWithScoped)
            ->get();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthorbeginswithscoped-name_like_A%0=A%25");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthorbeginswithscoped"];

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
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthorwithinlineglobalscope-authors.deleted_at_null-name_like_A%0=A%25");
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

    public function testGlobalScopesWhenSwitchingContextUsingAllMethod()
    {
        factory(Author::class, 200)->create();
        $user = factory(User::class)->create(["name" => "Andrew Junior"]);
        $this->actingAs($user);
        $authorsA = (new AuthorBeginsWithScoped)
            ->all()
            ->map(function ($author) {
                return (new Str)->substr($author->name, 0, 1);
            })
            ->unique();
        $user = factory(User::class)->create(["name" => "Barry Barry Barry"]);
        $this->actingAs($user);
        $authorsB = (new AuthorBeginsWithScoped)
            ->all()
            ->map(function ($author) {
                return (new Str)->substr($author->name, 0, 1);
            })
            ->unique();

        $this->assertCount(1, $authorsA);
        $this->assertCount(1, $authorsB);
        $this->assertEquals("A", $authorsA->first());
        $this->assertEquals("B", $authorsB->first());
    }

    public function testGlobalScopesWhenSwitchingContextUsingGetMethod()
    {
        factory(Author::class, 200)->create();
        $user = factory(User::class)->create(["name" => "Anton Junior"]);
        $this->actingAs($user);
        $authorsA = (new AuthorBeginsWithScoped)
            ->get()
            ->map(function ($author) {
                return (new Str)->substr($author->name, 0, 1);
            })
            ->unique();
        $user = factory(User::class)->create(["name" => "Burli Burli Burli"]);
        $this->actingAs($user);
        $authorsB = (new AuthorBeginsWithScoped)
            ->get()
            ->map(function ($author) {
                return (new Str)->substr($author->name, 0, 1);
            })
            ->unique();

        $this->assertCount(1, $authorsA);
        $this->assertCount(1, $authorsB);
        $this->assertEquals("A", $authorsA->first());
        $this->assertEquals("B", $authorsB->first());
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

    public function testScopeNotAppliedTwice()
    {
        $user = factory(User::class)
            ->create(["name" => "Anton Junior"]);
        $this->actingAs($user);
        DB::enableQueryLog();
        
        (new AuthorBeginsWithScoped)
            ->get();
        $queryLog = DB::getQueryLog();

        $this->assertCount(1, $queryLog);
        $this->assertCount(
            1,
            $queryLog[0]['bindings'],
            "There should only be 1 binding, scope is being applied more than once."
        );
    }
}
