<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Comment;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'description' => $faker->paragraphs(3, true),
        'subject' => $faker->sentence,
    ];
});
