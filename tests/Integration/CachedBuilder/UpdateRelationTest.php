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

        // Count actual stores for this book before the update (may be >1 due to random seeding).
        $storeCount = $book->stores()->count();

        $book->stores()
            ->update(["name" => "test store name change"]);

        $updatedCount = (new Book)
            ->with("stores")
            ->whereHas("stores")
            ->first()
            ->stores()
            ->where("name", "test store name change")
            ->count();

        // All stores belonging to the book should have been updated.
        $this->assertEquals($storeCount, $updatedCount);
    }
}
