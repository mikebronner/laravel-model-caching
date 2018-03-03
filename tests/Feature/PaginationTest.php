<?php namespace GeneaLabs\LaravelModelCaching\Tests\Browser;

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

        $this->assertTrue(str_contains($page1->response->getContent(), '<li class="page-item active"><span class="page-link">1</span></li>'));

        $page2 = $page1->click("2");

        $this->assertTrue(str_contains($page2->response->getContent(), '<li class="page-item active"><span class="page-link">2</span></li>'));
        $page2->see($book->title);
    }
}
