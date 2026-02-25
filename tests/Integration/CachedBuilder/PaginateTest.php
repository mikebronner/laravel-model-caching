<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Str;

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

        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-paginate_by_3_page_1");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
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
        // Checking the version start with 11.0.
        if ($this->appVersionEleven()) {
            $page1ActiveLink = '<span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">1</span>';
            $page2ActiveLink = '<span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">2</span>';
            $page24ActiveLink = '<span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">24</span>';
        }

        // Checking the version start with 10.0.
        if ($this->appVersionTen()) {
            $page1ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">1</span>';
            $page2ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">2</span>';
            $page24ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">24</span>';
        }

        if ($this->appVersionEightAndNine()) {
            $page1ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">1</span>';
            $page2ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">2</span>';
            $page24ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">24</span>';
        }

        if ($this->appVersionFiveBetweenSeven()) {
            $page1ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">1</span></li>';
            $page2ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">2</span></li>';
            $page24ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">24</span></li>';
        }

        if ($this->appVersionOld()) {
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
        $this->assertStringContainsString($page1ActiveLink, (string) $booksPage1->links());
        $this->assertStringContainsString($page2ActiveLink, (string) $booksPage2->links());
        $this->assertStringContainsString($page24ActiveLink, (string) $booksPage24->links());
    }

    public function testPaginationWithOptionsReturnsCorrectLinks()
    {
        // Checking the version start with 11.0.
        if ($this->appVersionEleven()) {
            $page1ActiveLink = '<span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">1</span>';
            $page2ActiveLink = '<span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">2</span>';
            $page24ActiveLink = '<span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">24</span>';
        }

        // Checking the version start with 10.0.
        if ($this->appVersionTen()) {
            $page1ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">1</span>';
            $page2ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">2</span>';
            $page24ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">24</span>';
        }

        if ($this->appVersionEightAndNine()) {
            $page1ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">1</span>';
            $page2ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">2</span>';
            $page24ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">24</span>';
        }

        if ($this->appVersionFiveBetweenSeven()) {
            $page1ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">1</span></li>';
            $page2ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">2</span></li>';
            $page24ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">24</span></li>';
        }

        if ($this->appVersionOld()) {
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
        $this->assertStringContainsString($page1ActiveLink, (string) $booksPage1->links());
        $this->assertStringContainsString($page2ActiveLink, (string) $booksPage2->links());
        $this->assertStringContainsString($page24ActiveLink, (string) $booksPage24->links());
    }

    public function testPaginationWithCustomOptionsReturnsCorrectLinks()
    {
        // Checking the version start with 11.0.
        if ($this->appVersionEleven()) {
            $page1ActiveLink = '<span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">1</span>';
            $page2ActiveLink = '<span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">2</span>';
            $page24ActiveLink = '<span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">24</span>';
        }

        // Checking the version start with 10.0.
        if ($this->appVersionTen()) {
            $page1ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">1</span>';
            $page2ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">2</span>';
            $page24ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">24</span>';
        }

        if ($this->appVersionEightAndNine()) {
            $page1ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">1</span>';
            $page2ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">2</span>';
            $page24ActiveLink = '<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">24</span>';
        }

        if ($this->appVersionFiveBetweenSeven()) {
            $page1ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">1</span></li>';
            $page2ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">2</span></li>';
            $page24ActiveLink = '<li class="page-item active" aria-current="page"><span class="page-link">24</span></li>';
        }

        if ($this->appVersionOld()) {
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
        $this->assertStringContainsString($page1ActiveLink, (string) $booksPage1->links());
        $this->assertStringContainsString($page2ActiveLink, (string) $booksPage2->links());
        $this->assertStringContainsString($page24ActiveLink, (string) $booksPage24->links());
    }

    public function testCustomPageNamePagination()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-paginate_by_3_custom-page_1");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
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

    public function testPaginatorBaseUrlReflectsCurrentRequest()
    {
        // First request from domain A
        \Illuminate\Pagination\Paginator::currentPathResolver(function () {
            return "https://domain-a.com/authors";
        });

        $authorsFromDomainA = (new Author)->paginate(3);
        $this->assertStringContainsString(
            "domain-a.com",
            $authorsFromDomainA->url(1)
        );

        // Second request from domain B — should use domain B's URL, not cached domain A
        \Illuminate\Pagination\Paginator::currentPathResolver(function () {
            return "https://domain-b.com/authors";
        });

        $authorsFromDomainB = (new Author)->paginate(3);
        $this->assertStringContainsString(
            "domain-b.com",
            $authorsFromDomainB->url(1),
            "Cached paginator should use current request domain, not the domain that populated the cache"
        );
    }

    public function testCachedPaginatorPathIsReappliedFromCurrentRequest()
    {
        // Populate cache with a specific path
        \Illuminate\Pagination\Paginator::currentPathResolver(function () {
            return "https://original.com/users";
        });

        (new Author)->paginate(3);

        // Retrieve from cache with a different path
        \Illuminate\Pagination\Paginator::currentPathResolver(function () {
            return "https://different.com/users";
        });

        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-paginate_by_3_page_1");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $cachedRaw = $this->cache()->tags($tags)->get($key)['value'];

        // The cached raw paginator may have the old URL, but when retrieved
        // through the model caching layer, it should have the current URL
        $result = (new Author)->paginate(3);

        $this->assertStringContainsString(
            "different.com",
            $result->url(1),
            "Paginator path should be re-applied from current request after cache retrieval"
        );
        $this->assertStringNotContainsString(
            "original.com",
            $result->url(1),
            "Paginator should not retain the original cached domain"
        );
    }
}
