<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Supplier;

$factory->define(Supplier::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
