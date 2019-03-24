<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\DisableCooldownAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Support\Facades\Event;

/**
* @SuppressWarnings(PHPMD.TooManyPublicMethods)
* @SuppressWarnings(PHPMD.TooManyMethods)
 */
class DisabledCooldownTest extends IntegrationTestCase
{

    public function testCooldownCacheIsNotSearchedWhenSetInConfig()
    {
    	$config = config('laravel-model-cache');

    	$config['cooldown-disable'][] = 'App\Author';

    	config($config);

	    Event::fake();

    	Author::first();

    	Event::assertNotDispatched(CacheMissed::class, function(CacheMissed $event){
			return $event->key === 'genealabs:laravel-model-caching::GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author-cooldown:seconds';
	    });
    }

    public function testCooldownCacheIsNotSearchedWhenNotEnabled()
    {
	    $config = config('laravel-model-cache');

	    $config['enable-cooldown'] = false;

	    config($config);

	    Event::fake();

	    Author::first();

	    Event::assertNotDispatched(CacheMissed::class, function(CacheMissed $event){
		    return $event->key === 'genealabs:laravel-model-caching::GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author-cooldown:seconds';
	    });
    }

    public function testCooldownCacheIsNotSearchedWithTrait()
    {
	    Event::fake();

	    DisableCooldownAuthor::first();

	    Event::assertNotDispatched(CacheMissed::class, function(CacheMissed $event){
		    return $event->key === 'genealabs:laravel-model-caching::GeneaLabs\LaravelModelCaching\Tests\Fixtures\DisableCooldownAuthor-cooldown:seconds';
	    });
    }


}
