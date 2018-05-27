<?php namespace GeneaLabs\LaravelModelCaching\Tests;

use GeneaLabs\LaravelModelCaching\Providers\Service as LaravelModelCachingService;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use Orchestra\Database\ConsoleServiceProvider;

trait CreatesApplication
{
    protected $cache;

    protected function cache()
    {
        $cache = cache();

        if (config('laravel-model-caching.store')) {
            $cache = $cache->store(config('laravel-model-caching.store'));
        }

        return $cache;
    }

    public function setUp()
    {
        parent::setUp();

        require(__DIR__ . '/routes/web.php');

        $this->withFactories(__DIR__ . '/database/factories');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        view()->addLocation(__DIR__ . '/resources/views', 'laravel-model-caching');

        $this->cache = cache()
            ->store(config('laravel-model-caching.store'));

        $this->cache()->flush();
        $publishers = factory(Publisher::class, 10)->create();
        factory(Author::class, 10)->create()
            ->each(function ($author) use ($publishers) {
                factory(Book::class, random_int(5, 25))->make()
                    ->each(function ($book) use ($author, $publishers) {
                        $book->author()->associate($author);
                        $book->publisher()->associate($publishers[rand(0, 9)]);
                        $book->save();
                    });
                factory(Profile::class)->make([
                    'author_id' => $author->id,
                ]);
            });

        $bookIds = (new Book)->all()->pluck('id');
        factory(Store::class, 10)->create()
            ->each(function ($store) use ($bookIds) {
                $store->books()->sync(rand($bookIds->min(), $bookIds->max()));
            });
        $this->cache()->flush();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelModelCachingService::class,
            ConsoleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.redis.default', [
            'host' => env('REDIS_HOST', '192.168.10.10'),
        ]);
        $app['config']->set('database.redis.model-cache', [
            'host' => env('REDIS_HOST', '192.168.10.10'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 1,
        ]);
        $app['config']->set('cache.stores.model', [
            'driver' => 'redis',
            'connection' => 'model-cache',
        ]);
        $app['config']->set('laravel-model-caching.store', 'model');
    }
}
