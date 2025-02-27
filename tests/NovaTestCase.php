<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Nova\AuthorResource;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Nova\BookResource;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Nova\StoreResource;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Providers\NovaServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Inertia\ServiceProvider;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaCoreServiceProvider;

abstract class NovaTestCase extends FeatureTestCase
{
    protected $response;

    protected $authenticatedAs;

    public function setUp(): void
    {
        parent::setUp();

        Nova::$tools = [];
        Nova::$resources = [];

        Nova::resources([
            AuthorResource::class,
            BookResource::class,
            StoreResource::class,
        ]);

        Nova::auth(function () {
            return true;
        });

        $this->authenticate();
        $this->withoutMiddleware();

        Artisan::call("nova:publish");
    }

    protected function authenticate()
    {
        $this->actingAs($this->authenticatedAs = \Mockery::mock(Authenticatable::class));

        $this->authenticatedAs->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->authenticatedAs->shouldReceive('getKey')->andReturn(1);

        return $this;
    }

    protected function getPackageProviders($app)
    {
        return array_merge(
            parent::getPackageProviders($app),
            [
                NovaCoreServiceProvider::class,
                \Laravel\Nova\NovaServiceProvider::class,
                NovaServiceProvider::class,
                ServiceProvider::class,
            ]
        );
    }
}
