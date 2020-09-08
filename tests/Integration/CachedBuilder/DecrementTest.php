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

class DecrementTest extends IntegrationTestCase
{
    public function testDecrementingInvalidatesCache()
    {
        $book = (new Book)
            ->find(1);
        $originalPrice = $book->price;
        $originalDescription = $book->description;

        $book->decrement("price", 1.25, ["description" => "test description update"]);
        $book = (new Book)
            ->find(1);

        $this->assertEquals($originalPrice - 1.25, $book->price);
        $this->assertNotEquals($originalDescription, $book->description);
        $this->assertEquals($book->description, "test description update");
    }
}
