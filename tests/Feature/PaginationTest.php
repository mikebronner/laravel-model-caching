<?php namespace GeneaLabs\LaravelModelCaching\Tests\Browser;

use GeneaLabs\LaravelModelCaching\Tests\FeatureTestCase;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;

class PaginationTest extends FeatureTestCase
{
    public function testPaginationProvidesDifferentLinksOnDifferentPages()
    {
        $page1ActiveLink = starts_with(app()->version(), "5.5")
            ? '<li class="active"><span>1</span></li>'
            : '<li class="page-item active"><span class="page-link">1</span></li>';
        $page2ActiveLink = starts_with(app()->version(), "5.5")
            ? '<li class="active"><span>2</span></li>'
            : '<li class="page-item active"><span class="page-link">2</span></li>';

        $book = (new Book)
            ->take(11)
            ->get()
            ->last();
        $page1 = $this->visit("pagination-test");

        $page1->see($page1ActiveLink);
        $page2 = $page1->click("2");
        $page2->see($page2ActiveLink);
        $page2->see($book->title);
    }
}
