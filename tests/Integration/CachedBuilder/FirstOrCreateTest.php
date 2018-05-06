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

class FirstOrCreateTest extends IntegrationTestCase
{
    use RefreshDatabase;

    public function testFirstOrCreateFlushesCacheForModel()
    {
        (new Author)->truncate();

        $items = [
            ['name' => 'foo', 'email' => 'test1@noemail.com'],
            ['name' => 'foo', 'email' => 'test2@noemail.com'],
            ['name' => 'foo', 'email' => 'test3@noemail.com'],
            ['name' => 'foo', 'email' => 'test4@noemail.com'],
            ['name' => 'foo', 'email' => 'test5@noemail.com'],
        ];

        foreach ($items as $item) {
            (new Author)->firstOrCreate($item);
        }

        $authors = (new Author)->get();

        $this->assertEquals(5, $authors->count());
    }
}
