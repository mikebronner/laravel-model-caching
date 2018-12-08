<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Observers\AuthorObserver;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\PrefixedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedProfile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedStore;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelFactoryTest extends IntegrationTestCase
{
    public function testAllModelResultsCreatesCache()
    {
        $author = factory(Author::class)->create();
        $assignedBookIds = [];
        $numberOfBooks = 5;

        foreach (range(1, $numberOfBooks) as $i) {
            $book = factory(Book::class)
                ->make()
                ->author()->associate($author)
                ->save();
            $author->load("books");
            dump(count($author->books));

            $assignedBookIds[] = $author
                ->latestBook
                ->id;

            dump($assignedBookIds);
        }

        $this->assertEquals($numberOfBooks, count($assignedBookIds));
    }
}
