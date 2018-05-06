<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

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

class CachedBuilderRelationshipsTest extends IntegrationTestCase
{
    use RefreshDatabase;

    public function testHasRelationshipResults()
    {
        $booksWithStores = (new Book)
            ->with("stores")
            ->has("stores")
            ->get();
        $key = "genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook_exists_and_books.id_=_book_store.book_id-stores";
        $tags = [
            "genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook",
            "genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesstore",
        ];
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get(sha1($key))["value"];

        $this->assertNotEmpty($booksWithStores);
        $this->assertEquals($booksWithStores, $cachedResults);
    }
}
