<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\MaxCacheBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Facades\DB;

class MaxCacheTest extends IntegrationTestCase
{
    public function testWithQuery()
    {
        $maxcached_book = MaxCacheBook::query()->find(1);
        $original_maxcached_book_price = $maxcached_book->price;

        DB::update(DB::raw("UPDATE `books` SET `price` = (`price` + 1.25) WHERE `id` = 1"));

        // Re-pull maxcached book and verify cache hasn't changed despite raw update
        $maxcached_book = MaxCacheBook::query()->find(1);
        $this->assertEquals($original_maxcached_book_price, $maxcached_book->price);

        sleep(MaxCacheBook::maxCacheTimeout()); // Not great, is there a better way?

        $maxcached_book = MaxCacheBook::query()->find(1);

        //Cache should now have expired and the cached value should have changed
        $this->assertEquals($original_maxcached_book_price + 1.25, $maxcached_book->price);
    }

    public function testWithAll()
    {
        $maxcached_book = MaxCacheBook::all()->where('id', 1)->first();
        $original_maxcached_book_price = $maxcached_book->price;

        DB::update(DB::raw("UPDATE `books` SET `price` = (`price` + 1.25) WHERE `id` = 1"));

        // Re-pull maxcached book and verify cache hasn't changed despite raw update
        $maxcached_book = MaxCacheBook::all()->where('id', 1)->first();

        $this->assertEquals($original_maxcached_book_price, $maxcached_book->price);

        sleep(MaxCacheBook::maxCacheTimeout()); // Not great, is there a better way?

        $maxcached_book = MaxCacheBook::all()->where('id', 1)->first();

        //Cache should now have expired and the cached value should have changed
        $this->assertEquals($original_maxcached_book_price + 1.25, $maxcached_book->price);
    }
}