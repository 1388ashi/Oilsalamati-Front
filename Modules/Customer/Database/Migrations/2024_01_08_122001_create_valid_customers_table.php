<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValidCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('valid_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('link')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('valid_customers');
    }
}
