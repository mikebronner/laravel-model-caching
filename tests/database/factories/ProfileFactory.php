<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;

$factory->define(Profile::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
    ];
});
