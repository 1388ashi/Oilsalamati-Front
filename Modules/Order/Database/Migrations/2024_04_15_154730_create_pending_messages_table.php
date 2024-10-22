<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePendingMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_messages', function (Blueprint $table) {
            $table->id();

            $table->string('template');
            $table->string('mobile');
            $table->timestamp('hold_to');

            $table->string('token')->nullable();
            $table->string('token2')->nullable();
            $table->string('token3')->nullable();
            $table->string('token10')->nullable();
            $table->string('token20')->nullable();


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
        Schema::dropIfExists('pending_messages');
    }
}
