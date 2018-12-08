<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;

$factory->define(UncachedBook::class, function (Faker $faker) {
    return [
        'title' => $faker->title,
        'description' => $faker->optional()->paragraphs(3, true),
        'published_at' => $faker->dateTime,
        'price' => $faker->randomFloat(2, 0, 999999),
        "publisher_id" => factory(UncachedPublisher::class)->create()->id,
    ];
});
