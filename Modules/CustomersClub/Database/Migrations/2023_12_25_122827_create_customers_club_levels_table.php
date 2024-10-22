<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersClubLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_club_levels', function (Blueprint $table) {
            $table->id();
            $table->string('title',50);
            $table->string('key',50);
            $table->unsignedBigInteger('min_score');
            $table->unsignedBigInteger('max_score');
            $table->string('color',7);
            $table->integer('permanent_purchase_discount');
            $table->integer('birthdate_discount');
            $table->unsignedBigInteger('free_shipping');
            $table->string('image',100)->nullable();
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
        Schema::dropIfExists('customers_club_levels');
    }
}
