<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedUser;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\User;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class PolymorphicOneToOneTest extends IntegrationTestCase
{
    public function testEagerloadedRelationship()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:images:genealabslaravelmodelcachingtestsfixturesimage-images.imagable_id_inraw_2-images.imagable_type_=_GeneaLabs\LaravelModelCaching\Tests\Fixtures\User:http://localhost");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesimage",
        ];

        $result = (new User)
            ->with("image")
            ->whereHas("image")
            ->first()
            ->image;
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value']
            ->first();
        $liveResults = (new UncachedUser)
            ->with("image")
            ->whereHas("image")
            ->first()
            ->image;

        $this->assertEquals($liveResults->path, $result->path);
        $this->assertEquals($liveResults->path, $cachedResults->path);
        $this->assertNotEmpty($result);
        $this->assertNotEmpty($cachedResults);
        $this->assertNotEmpty($liveResults);
    }

    public function testLazyloadedHasOneThrough()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:images:genealabslaravelmodelcachingtestsfixturesimage-images.imagable_id_=_2-images.imagable_id_notnull-images.imagable_type_=_GeneaLabs\LaravelModelCaching\Tests\Fixtures\User-limit_1:http://localhost");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesimage",
        ];

        $result = (new User)
            ->whereHas("image")
            ->first()
            ->image;
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value']
            ->first();
        $liveResults = (new UncachedUser)
            ->whereHas("image")
            ->first()
            ->image;

        $this->assertEquals($liveResults->path, $result->path);
        $this->assertEquals($liveResults->path, $cachedResults->path);
        $this->assertNotEmpty($result);
        $this->assertNotEmpty($cachedResults);
        $this->assertNotEmpty($liveResults);
    }
}
