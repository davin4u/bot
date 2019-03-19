<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed_content', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('feed_id');
            $table->string('guid')->nullable();
            $table->string('title')->nullable();
            $table->string('link')->nullable();
            $table->dateTime('publish_date')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
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
        Schema::dropIfExists('feed_content');
    }
}
