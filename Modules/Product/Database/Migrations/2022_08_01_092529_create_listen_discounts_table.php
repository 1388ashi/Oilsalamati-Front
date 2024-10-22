<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListenDiscountsTable extends Migration
{

    public function up()
    {
        Schema::create('listen_discounts', function (Blueprint $table) {
            $this->default()($table);
        });
    }


    public function down()
    {
        Schema::dropIfExists('listen_discounts');
    }

    public function default()
    {
        return function ($table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedMediumInteger('total_sent')->default(0);
            $table->timestamps();
        };
    }
}
