<?php namespace GeneaLabs\LaravelModelCaching\Tests\Feature;

use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;

class PaginationTest extends IntegrationTestCase
{
    public function testPaginationProvidesDifferentLinksOnDifferentPages()
    {
        $book = (new Book)
            ->take(11)
            ->get()
            ->last();
        $page1 = $this->get("pagination-test");

        $page1->assertSee('aria-current="page"', false);
        $page2 = $this->get("pagination-test?page=2");
        $page2->assertSee('aria-current="page"', false);
        $page2->assertSee($book->title, false);
    }

    public function testAdvancedPagination()
    {
        $response = $this->get("pagination-test?page[size]=1");

        $response->assertSee('aria-current="page"', false);
    }

    public function testCustomPagination()
    {
        $response = $this->get("pagination-test2?custom-page=2");

        $response->assertSee('aria-current="page"', false);
    }
}
