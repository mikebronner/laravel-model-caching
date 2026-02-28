<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBookStore extends Migration
{
    public function up()
    {
        Schema::create('book_store', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('book_id');
            $table->unsignedInteger('store_id');
            $table->timestamps();

            $table->string('test')->nullable();

            $table->foreign('book_id')
                ->references('id')
                ->on('books')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }
}
