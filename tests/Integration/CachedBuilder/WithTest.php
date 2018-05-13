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

class WithTest extends IntegrationTestCase
{
    use RefreshDatabase;

    public function testWithQuery()
    {
        $author = (new Author)
            ->where("id", 1)
            ->with([
                'books' => function ($query) {
                    $query->where("id", "<", 100);
                }
            ])
            ->first();
        $uncachedAuthor = (new UncachedAuthor)->with([
                'books' => function ($query) {
                    $query->where("id", "<", 100);
                },
            ])
            ->where("id", 1)
            ->first();

        $this->assertEquals($uncachedAuthor->books()->count(), $author->books()->count());
        $this->assertEquals($uncachedAuthor->id, $author->id);
    }
}
