<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedProfile;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class LazyLoadTest extends IntegrationTestCase
{
    public function testBelongsToRelationship()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.id_=_1-authors.deleted_at_null:http://localhost-first");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $result = (new Book)
            ->where("id", 1)
            ->first()
            ->author;
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $uncachedResult = (new UncachedBook)
            ->where("id", 1)
            ->first()
            ->author;

        $this->assertEquals($uncachedResult->id, $result->id);
        $this->assertEquals($uncachedResult->id, $cachedResult->id);
        $this->assertEquals(Author::class, get_class($result));
        $this->assertEquals(Author::class, get_class($cachedResult));
        $this->assertEquals(UncachedAuthor::class, get_class($uncachedResult));
        $this->assertNotNull($result);
        $this->assertNotNull($cachedResult);
        $this->assertNotNull($uncachedResult);
    }

    public function testHasManyRelationship()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook-books.author_id_=_1-books.author_id_notnull:http://localhost");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
        ];

        $result = (new Author)
            ->find(1)
            ->books;
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $uncachedResult = (new UncachedAuthor)
            ->find(1)
            ->books;

        $this->assertEquals($uncachedResult->pluck("id"), $result->pluck("id"));
        $this->assertEquals($uncachedResult->pluck("id"), $cachedResult->pluck("id"));
        $this->assertEquals(Book::class, get_class($result->first()));
        $this->assertEquals(Book::class, get_class($cachedResult->first()));
        $this->assertEquals(UncachedBook::class, get_class($uncachedResult->first()));
        $this->assertNotEmpty($result);
        $this->assertNotEmpty($cachedResult);
        $this->assertNotEmpty($uncachedResult);
    }

    public function testHasOneRelationship()
    {
        $authorId = (new UncachedProfile)
            ->first()
            ->author_id;
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:profiles:genealabslaravelmodelcachingtestsfixturesprofile-profiles.author_id_=_{$authorId}-profiles.author_id_notnull:http://localhost-first");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesprofile",
        ];

        $result = (new Author)
            ->find($authorId)
            ->profile;
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $uncachedResult = (new UncachedAuthor)
            ->find($authorId)
            ->profile;

        $this->assertEquals($uncachedResult->id, $result->id);
        $this->assertEquals($uncachedResult->id, $cachedResult->id);
        $this->assertEquals(Profile::class, get_class($result->first()));
        $this->assertEquals(Profile::class, get_class($cachedResult->first()));
        $this->assertEquals(UncachedProfile::class, get_class($uncachedResult->first()));
        $this->assertNotEmpty($result);
        $this->assertNotEmpty($cachedResult);
        $this->assertNotEmpty($uncachedResult);
    }
}
