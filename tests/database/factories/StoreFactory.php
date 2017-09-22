<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;

$factory->define(Store::class, function (Faker $faker) {
    return [
        'address' => $faker->address,
        'name' => $faker->company,
    ];
});
