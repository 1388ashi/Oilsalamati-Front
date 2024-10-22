<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignUsersTable extends Migration
{

    public function up()
    {
        Schema::create('campaign_users', function (Blueprint $table) {
            $table->id();
            $table->string('mobile');
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_users');
    }
}
