<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class DecrementTest extends IntegrationTestCase
{
    public function test_decrementing_invalidates_cache()
    {
        $book = (new Book)
            ->find(1);
        $originalPrice = $book->price;
        $originalDescription = $book->description;

        $book->decrement('price', 1.25, ['description' => 'test description update']);
        $book = (new Book)
            ->find(1);

        $this->assertEquals($originalPrice - 1.25, $book->price);
        $this->assertNotEquals($originalDescription, $book->description);
        $this->assertEquals($book->description, 'test description update');
    }
}
