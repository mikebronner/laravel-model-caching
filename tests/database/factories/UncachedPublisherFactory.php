<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;

$factory->define(UncachedPublisher::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
