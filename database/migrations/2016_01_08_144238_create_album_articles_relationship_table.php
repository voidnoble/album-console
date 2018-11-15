<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlbumArticlesRelationshipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('album_articles_relationship', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('album_id')->unsigned();
            $table->integer('article_id')->unsigned();
            $table->string('title')->default('');
            $table->string('url')->default('');
            $table->integer('order')->unsigned()->default(0);
            $table->timestamps();
        });

        Schema::table('album_articles_relationship', function($table) {
            $table->foreign('album_id')
                ->references('id')->on('album')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('album_articles_relationship');
    }
}
