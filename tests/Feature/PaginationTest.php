<?php namespace GeneaLabs\LaravelModelCaching\Tests\Feature;

use GeneaLabs\LaravelModelCaching\Tests\FeatureTestCase;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;

class PaginationTest extends FeatureTestCase
{
    public function testPaginationProvidesDifferentLinksOnDifferentPages()
    {
        $book = (new Book)
            ->take(11)
            ->get()
            ->last();
        $page1 = $this->visit("pagination-test");

        $page1->see('aria-current="page"');
        $page2 = $page1->click("2");
        $page2->see('aria-current="page"');
        $page2->see($book->title);
    }

    public function testAdvancedPagination()
    {
        $response = $this->visit("pagination-test?page[size]=1");

        $response->see('aria-current="page"');
    }

    public function testCustomPagination()
    {
        $response = $this->visit("pagination-test2?custom-page=2");

        $response->see('aria-current="page"');
    }
}
