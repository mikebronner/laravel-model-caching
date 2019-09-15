<?php

use Faker\Generator as Faker;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Printer;

$factory->define(Printer::class, function (Faker $faker) {
    return [
        "book_id" => factory(Book::class)->create()->id,
        'name' => $faker->realText(),
    ];
});
