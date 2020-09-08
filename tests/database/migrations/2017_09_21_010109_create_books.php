<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBooks extends Migration
{
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('author_id');
            $table->unsignedInteger('publisher_id');
            $table->timestamps();

            $table->text('description')->nullable();
            $table->dateTime('published_at');
            $table->string('title');
            $table->decimal('price')->default(0);

            $table->foreign('author_id')
                ->references('id')
                ->on('authors')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('publisher_id')
                ->references('id')
                ->on('publishers')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }
}
