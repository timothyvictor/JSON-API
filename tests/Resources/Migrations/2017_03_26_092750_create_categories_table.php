<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function ($table) {
            $table->increments('id');
            $table->integer('author_id')->unsigned()->nullable();
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->string('title');
            $table->string('description')->nullable();
            $table->timestamps();
        });
        // $now = Carbon::now();
        // DB::table('users')->insert([
        //     'email'      => 'hello@orchestraplatform.com',
        //     'password'   => Hash::make('123'),
        //     'created_at' => $now,
        //     'updated_at' => $now,
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('categories');
    }
}
