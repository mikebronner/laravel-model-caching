<?php namespace GeneaLabs\LaravelModelCaching\Tests\Database\Factories;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Printer;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrinterFactory extends Factory
{
    protected $model = Printer::class;

    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'name'    => $this->faker->sentence(),
        ];
    }
}