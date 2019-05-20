<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComments extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->unsignedInteger("commentable_id")->nullable();
            $table->string("commentable_type")->nullable();
            $table->text("description");
            $table->string("subject");
        });
    }
}
