<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shetabit\Shopit\Database\CreateShippingsTable as BaseCreateShippingsTable;

class CreateShippingRangesTable extends \Illuminate\Database\Migrations\Migration {
    public function up()
    {
        Schema::create('shipping_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_id')->constrained();
            $table->unsignedBigInteger('lower');
            $table->unsignedBigInteger('higher');
            $table->unsignedBigInteger('amount');
            $table->timestamps();
        });
    }
}
