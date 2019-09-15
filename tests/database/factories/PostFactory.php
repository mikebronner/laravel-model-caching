<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Post;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'body' => $faker->paragraph,
    ];
});
