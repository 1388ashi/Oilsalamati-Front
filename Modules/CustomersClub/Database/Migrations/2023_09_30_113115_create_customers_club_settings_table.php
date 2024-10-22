<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersClubSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_club_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title',100);
            $table->string('key',50);
            $table->string('value',255)->nullable();
            $table->enum('type',['text','number'])->default('text');
            $table->boolean('status')->default(true);
            $table->date('date')->nullable();

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
        Schema::dropIfExists('customers_club_settings');
    }
}
