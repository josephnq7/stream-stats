<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->bigInteger('channel_id')->unsigned()->nullable();
            $table->bigInteger('game_id')->unsigned()->nullable();
            $table->bigInteger('viewer_count')->unsigned()->default(0)->index();
            $table->dateTime('start_at');
            $table->timestamps();

            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('streams');
    }
}
