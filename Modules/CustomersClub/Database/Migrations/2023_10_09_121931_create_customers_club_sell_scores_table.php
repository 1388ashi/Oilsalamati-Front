<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersClubSellScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_club_sell_scores', function (Blueprint $table) {
            $table->id();
            $table->string('title',50);
            $table->integer('min_value')->unsigned();
            $table->integer('max_value')->unsigned();
            $table->integer('bon_value')->unsigned();
            $table->integer('score_value')->unsigned();
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
        Schema::dropIfExists('customers_club_sell_scores');
    }
}
