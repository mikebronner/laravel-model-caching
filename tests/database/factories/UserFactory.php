<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Supplier;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        "supplier_id" => factory(Supplier::class)->create()->id,
    ];
});
