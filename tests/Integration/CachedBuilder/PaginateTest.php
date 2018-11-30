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
class PaginateTest extends IntegrationTestCase
{
    public function testPaginationIsCached()
    {
        $authors = (new Author)
            ->paginate(3);

        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor-paginate_by_3_page_1');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
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
        if (starts_with(app()->version(), "5.6")
            || starts_with(app()->version(), "5.7")
        ) {
            $page1ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">1</span></li>';
            $page2ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">2</span></li>';
            $page24ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">24</span></li>';
        }

        if (starts_with(app()->version(), "5.5")) {
            $page1ActiveLink = '<li class="active"><span>1</span></li>';
            $page2ActiveLink = '<li class="active"><span>2</span></li>';
            $page24ActiveLink = '<li class="active"><span>24</span></li>';
        }

        if (starts_with(app()->version(), "5.4")) {
            $page1ActiveLink = '<li class="active"><span>1</span></li>';
            $page2ActiveLink = '<li class="active"><span>2</span></li>';
            $page24ActiveLink = '<li class="active"><span>24</span></li>';
        }

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

    public function testPaginationWithOptionsReturnsCorrectLinks()
    {
        if (starts_with(app()->version(), "5.6")
            || starts_with(app()->version(), "5.7")
        ) {
            $page1ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">1</span></li>';
            $page2ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">2</span></li>';
            $page24ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">24</span></li>';
        }

        if (starts_with(app()->version(), "5.5")) {
            $page1ActiveLink = '<li class="active"><span>1</span></li>';
            $page2ActiveLink = '<li class="active"><span>2</span></li>';
            $page24ActiveLink = '<li class="active"><span>24</span></li>';
        }

        if (starts_with(app()->version(), "5.4")) {
            $page1ActiveLink = '<li class="active"><span>1</span></li>';
            $page2ActiveLink = '<li class="active"><span>2</span></li>';
            $page24ActiveLink = '<li class="active"><span>24</span></li>';
        }

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

    public function testPaginationWithCustomOptionsReturnsCorrectLinks()
    {
        if (starts_with(app()->version(), "5.6")
            || starts_with(app()->version(), "5.7")
        ) {
            $page1ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">1</span></li>';
            $page2ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">2</span></li>';
            $page24ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">24</span></li>';
        }

        if (starts_with(app()->version(), "5.5")) {
            $page1ActiveLink = '<li class="active"><span>1</span></li>';
            $page2ActiveLink = '<li class="active"><span>2</span></li>';
            $page24ActiveLink = '<li class="active"><span>24</span></li>';
        }

        if (starts_with(app()->version(), "5.4")) {
            $page1ActiveLink = '<li class="active"><span>1</span></li>';
            $page2ActiveLink = '<li class="active"><span>2</span></li>';
            $page24ActiveLink = '<li class="active"><span>24</span></li>';
        }

        $booksPage1 = (new Book)
            ->paginate('2');
        $booksPage2 = (new Book)
            ->paginate('2', ['*'], 'pages', 2);
        $booksPage24 = (new Book)
            ->paginate('2', ['*'], 'pages', 24);

        $this->assertCount(2, $booksPage1);
        $this->assertCount(2, $booksPage2);
        $this->assertCount(2, $booksPage24);
        $this->assertContains($page1ActiveLink, (string) $booksPage1->links());
        $this->assertContains($page2ActiveLink, (string) $booksPage2->links());
        $this->assertContains($page24ActiveLink, (string) $booksPage24->links());
    }

    public function testCustomPageNamePagination()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor-paginate_by_3_custom-page_1');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $authors = (new Author)
            ->paginate(3, ["*"], "custom-page");
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->paginate(3, ["*"], "custom-page");

        $this->assertEquals($cachedResults, $authors);
        $this->assertEquals($liveResults->pluck("email"), $authors->pluck("email"));
        $this->assertEquals($liveResults->pluck("name"), $authors->pluck("name"));
    }

    public function testCustomPageNamePaginationFetchesCorrectPages()
    {
        $authors1 = (new Author)
            ->paginate(3, ["*"], "custom-page", 1);
        $authors2 = (new Author)
            ->paginate(3, ["*"], "custom-page", 2);

        $this->assertNotEquals($authors1->pluck("id"), $authors2->pluck("id"));
    }
}
