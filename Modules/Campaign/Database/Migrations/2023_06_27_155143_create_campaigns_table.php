<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignsTable extends Migration
{

    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('status');
            $table->text('customer_title');
            $table->text('customer_text');
            $table->string('coupon_code');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}
