<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\IntegerStatus;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\StringStatus;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class WhereEnumTest extends IntegrationTestCase
{
    public function testWhereWithIntegerBackedEnum()
    {
        $authors = (new Author)
            ->where('id', IntegerStatus::Active)
            ->get();

        $this->assertNotNull($authors);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $authors);
    }

    public function testWhereWithStringBackedEnum()
    {
        $authors = (new Author)
            ->where('name', StringStatus::Active)
            ->get();

        $this->assertNotNull($authors);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $authors);
    }

    public function testWhereInWithIntegerBackedEnums()
    {
        $authors = (new Author)
            ->whereIn('id', [IntegerStatus::Active, IntegerStatus::Inactive])
            ->get();

        $this->assertNotNull($authors);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $authors);
    }

    public function testWhereInWithStringBackedEnums()
    {
        $authors = (new Author)
            ->whereIn('name', [StringStatus::Active, StringStatus::Inactive])
            ->get();

        $this->assertNotNull($authors);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $authors);
    }
}
