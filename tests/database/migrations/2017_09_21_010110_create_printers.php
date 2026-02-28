<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
