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
            $table->timestamps();

            $table->text('description');
            $table->dateTime('published_at');
            $table->string('title');
        });
    }

    public function down()
    {
        //
    }
}
