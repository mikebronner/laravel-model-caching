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

/**
* @SuppressWarnings(PHPMD.TooManyPublicMethods)
* @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CachedBuilderPaginationTest extends IntegrationTestCase
{
    use RefreshDatabase;

    public function testPaginationIsCached()
    {
        $authors = (new Author)
            ->paginate(3);

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-paginate_by_3_page_1');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->paginate(3);

        $this->assertEquals($cachedResults, $authors);
        $this->assertEquals($liveResults->pluck("email"), $authors->pluck("email"));
        $this->assertEquals($liveResults->pluck("name"), $authors->pluck("name"));
    }

    public function testPaginationReturnsCorrectLinks()
    {
        $page1ActiveLink = starts_with(app()->version(), "5.5")
            ? '<li class="active"><span>1</span></li>'
            : '<li class="page-item active" aria-current="page"><span class="page-link">1</span></li>';
        $page2ActiveLink = starts_with(app()->version(), "5.5")
            ? '<li class="active"><span>2</span></li>'
            : '<li class="page-item active" aria-current="page"><span class="page-link">2</span></li>';
        $page24ActiveLink = starts_with(app()->version(), "5.5")
            ? '<li class="active"><span>24</span></li>'
            : '<li class="page-item active" aria-current="page"><span class="page-link">24</span></li>';

        $booksPage1 = (new Book)
            ->paginate(2);
        $booksPage2 = (new Book)
            ->paginate(2, ['*'], null, 2);
        $booksPage24 = (new Book)
            ->paginate(2, ['*'], null, 24);

        $this->assertCount(2, $booksPage1);
        $this->assertCount(2, $booksPage2);
        $this->assertCount(2, $booksPage24);
        $this->assertContains($page1ActiveLink, (string) $booksPage1->links());
        $this->assertContains($page2ActiveLink, (string) $booksPage2->links());
        $this->assertContains($page24ActiveLink, (string) $booksPage24->links());
    }
}
