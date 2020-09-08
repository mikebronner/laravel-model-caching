<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImages extends Migration
{
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->unsignedInteger("imagable_id")->nullable();
            $table->string("imagable_type")->nullable();
            $table->text("path");
        });
    }
}
