<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Role;

$factory->define(Role::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word,
    ];
});
