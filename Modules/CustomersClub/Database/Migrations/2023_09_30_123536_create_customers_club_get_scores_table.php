<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersClubGetScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_club_get_scores', function (Blueprint $table) {
            $table->id();
            $table->string('title',100);
            $table->string('key',100);
            $table->integer('bon_value')->default(0);
            $table->integer('score_value')->default(0);

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
        Schema::dropIfExists('customers_club_get_scores');
    }
}
