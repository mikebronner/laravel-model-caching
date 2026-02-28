<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class CreateTest extends IntegrationTestCase
{
    public function test_first_or_create_flushes_cache_for_model()
    {
        (new Author)->truncate();
        $noAuthors = (new Author)->get();
        (new Author)->create([
            'name' => 'foo',
            'email' => 'test1@noemail.com',
        ]);
        $authors = (new Author)->get();

        $this->assertEquals(0, $noAuthors->count());
        $this->assertEquals(1, $authors->count());
    }
}
