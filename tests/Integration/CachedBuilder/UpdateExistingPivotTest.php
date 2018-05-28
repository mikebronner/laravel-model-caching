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

class UpdateExistingPivotTest extends IntegrationTestCase
{
    

    public function testInRandomOrderCachesResults()
    {
        $book = (new Book)
            ->with("stores")
            ->whereHas("stores")
            ->first();
        $book->stores()
            ->updateExistingPivot(
                $book->stores->first()->id,
                ["test" => "value"]
            );
        $updatedCount = (new Book)
            ->with("stores")
            ->whereHas("stores")
            ->first()
            ->stores()
            ->wherePivot("test", "value")
            ->count();

        $this->assertEquals(1, $updatedCount);
    }
}
