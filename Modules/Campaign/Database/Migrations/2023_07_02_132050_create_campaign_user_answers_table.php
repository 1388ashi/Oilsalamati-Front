<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignUserAnswersTable extends Migration
{

    public function up()
    {
        Schema::create('campaign_user_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('campaign_questions')->cascadeOnDelete();
            $table->text('answer');
            $table->foreignId('user_id')->constrained('campaign_users')->cascadeOnDelete();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('campaign_user_answers');
    }
}
