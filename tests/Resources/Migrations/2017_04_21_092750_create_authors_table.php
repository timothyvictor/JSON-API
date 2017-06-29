<?php

use Illuminate\Database\Migrations\Migration;

class CreateAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authors', function ($table) {
            $table->increments('id');
            // $table->integer('category_id')->unsigned();
            // $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');
            $table->string('first_name');
            $table->string('surname');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('authors');
    }
}
