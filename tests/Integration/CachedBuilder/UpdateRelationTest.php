<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class UpdateRelationTest extends IntegrationTestCase
{
    public function testInRandomOrderCachesResults()
    {
        $book = (new Book)
            ->with("stores")
            ->whereHas("stores")
            ->first();
        $book->stores()
            ->update(["name" => "test store name change"]);
        $updatedCount = (new Book)
            ->with("stores")
            ->whereHas("stores")
            ->first()
            ->stores()
            ->where("name", "test store name change")
            ->count();

        $this->assertEquals(1, $updatedCount);
    }
}
