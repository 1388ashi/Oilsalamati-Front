<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Campaign\Entities\Campaign;

class CreateCampaignQuestionsTable extends Migration
{

    public function up()
    {
        Schema::create('campaign_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->enum('type',Campaign::getAvailableTypes());#TODO : checkbox , options , text
            $table->text('data')->nullable();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->unsignedBigInteger('order')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('campaign_questions');
    }
}
