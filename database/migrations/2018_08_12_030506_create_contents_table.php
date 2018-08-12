<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->integer('id')->unsigned()->unique()->primary();
            $table->string('isbn')->index()->nullable();

            $table->string('name')->index();
            $table->string('name2')->index()->nullable();

            $table->string('description')->nullable();
            $table->string('description2')->nullable();

            $table->integer('author_id')->unsigned()->index()->nullable();
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');

            $table->integer('series_id')->unsigned()->index()->nullable();
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');

            $table->smallInteger('type')->index();
            $table->smallInteger('format')->index();

            $table->dateTimeTz('updated_at')->index();
            $table->dateTimeTz('deliver_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contents');
    }
}
