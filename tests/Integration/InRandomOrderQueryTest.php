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

class InRandomOrderQueryTest extends IntegrationTestCase
{
    use RefreshDatabase;

    public function testInRandomOrderCachesResults()
    {
        $cachedBook1 = (new Book)
            ->inRandomOrder()
            ->first();
        $cachedBook2 = (new Book)
            ->inRandomOrder()
            ->first();
        $book1 = (new UncachedBook)
            ->inRandomOrder()
            ->first();
        $book2 = (new UncachedBook)
            ->inRandomOrder()
            ->first();

        $this->assertNotEquals($book1, $book2);
        $this->assertNotEquals($cachedBook1, $cachedBook2);
    }
}
