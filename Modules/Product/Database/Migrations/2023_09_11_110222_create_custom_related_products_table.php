<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomRelatedProductsTable extends Migration
{

    public function up()
    {
        Schema::create('custom_related_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('related_id');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('custom_related_products');
    }
}
