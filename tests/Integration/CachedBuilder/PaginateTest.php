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

    public function testPaginatedResultsReturnCorrectDataOnPage2()
    {
        $page1 = (new Author)
            ->paginate(3, ["*"], "page", 1);
        $page2 = (new Author)
            ->paginate(3, ["*"], "page", 2);

        $uncachedPage1 = (new UncachedAuthor)
            ->paginate(3, ["*"], "page", 1);
        $uncachedPage2 = (new UncachedAuthor)
            ->paginate(3, ["*"], "page", 2);

        $this->assertEquals($uncachedPage1->pluck("id"), $page1->pluck("id"));
        $this->assertEquals($uncachedPage2->pluck("id"), $page2->pluck("id"));
        $this->assertNotEquals($page1->pluck("id"), $page2->pluck("id"));
    }

    public function testPaginatorPathIsNotPersistedFromCache()
    {
        // First call: cache the paginated result with the current path
        $page1First = (new Author)->paginate(3);
        $originalPath = $page1First->path();

        // Simulate a subsequent request from a different path (e.g. Livewire AJAX)
        $livewirePath = 'http://localhost/livewire/message/component';
        \Illuminate\Pagination\Paginator::currentPathResolver(function () use ($livewirePath) {
            return $livewirePath;
        });

        $page1Second = (new Author)->paginate(3);

        // The paginator path should reflect the CURRENT request, not the cached one
        $this->assertEquals($livewirePath, $page1Second->path());
        $this->assertNotEquals($originalPath, $page1Second->path());
    }
}
