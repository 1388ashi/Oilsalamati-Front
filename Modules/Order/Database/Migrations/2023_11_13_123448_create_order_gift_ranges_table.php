<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderGiftRangesTable extends Migration
{

    public function up()
    {
        Schema::create('order_gift_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('min_order_amount');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('order_gift_ranges');
    }
}
