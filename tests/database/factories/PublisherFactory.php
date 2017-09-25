<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;

$factory->define(Publisher::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
