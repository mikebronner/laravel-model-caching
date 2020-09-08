<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class FirstOrCreateTest extends IntegrationTestCase
{
    public function testFirstOrCreateFlushesCacheForModel()
    {
        (new Author)->truncate();

        $items = [
            ['name' => 'foo', 'email' => 'test1@noemail.com'],
            ['name' => 'foo', 'email' => 'test2@noemail.com'],
            ['name' => 'foo', 'email' => 'test3@noemail.com'],
            ['name' => 'foo', 'email' => 'test4@noemail.com'],
            ['name' => 'foo', 'email' => 'test5@noemail.com'],
        ];

        foreach ($items as $item) {
            (new Author)->firstOrCreate($item);
        }

        $authors = (new Author)->get();

        $this->assertEquals(5, $authors->count());
    }
}
