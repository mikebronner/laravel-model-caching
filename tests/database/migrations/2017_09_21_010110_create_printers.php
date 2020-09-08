<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrinters extends Migration
{
    public function up()
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('book_id');
            $table->timestamps();

            $table->text('name');
        });
    }
}
