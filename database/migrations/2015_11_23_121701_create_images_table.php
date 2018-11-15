<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->integer('album_id')->unsigned();
            $table->string('name');
            $table->string('image');
            $table->string('description');
            $table->integer('order')->unsigned()->default(0);
            $table->timestamps();
        });

        /* SQLSTATE[HY000]: General error: 1215 Cannot add foreign key constraint
        * (SQL: alter table `images` add constraint images_album_id_foreign foreign key (`
  album_id`) references `albums` (`id`) on delete cascade on update cascade)
        Schema::table('images', function(Blueprint $table)
        {
            $table->foreign('album_id')
                ->references('id')->on('albums')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('images');
    }
}
