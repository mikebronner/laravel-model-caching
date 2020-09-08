<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\History;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\User;

$factory->define(History::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        "user_id" => factory(User::class)->create()->id,
    ];
});
