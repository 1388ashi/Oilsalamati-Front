<?php
use Modules\Campaign\Http\Controllers\Admin\CampaignQuestionController;
use Modules\Campaign\Http\Controllers\Admin\CampaignController;


Route::webSuperGroup('admin', function () {

    Route::resource('campaigns' ,'CampaignController');
    #سوالات کمپین CRUD
    //index
    route::get('campaignQuestions/{campaign}',[CampaignQuestionController::class,'index'])->name('campaignQuestions.index');
    Route::patch('/campaignQuestions/{campaign}/sort', [CampaignQuestionController::class, 'sort'])->name('campaignQuestions.sort');

    //show
    route::get('campaignQuestions/{campaign}/show',[CampaignController::class,'exportReport'])->name('campaign.exel');
    // exel
    route::get('campaigns/{campaign}/exel',[CampaignQuestionController::class,'show'])->name('campaigns.export');
    //store
    route::post('campaignQuestions',[CampaignQuestionController::class,'store'])->name('campaignQuestions.store');

    //update
    route::get('campaignQuestions/{id}/edit',[CampaignQuestionController::class,'edit'])->name('campaignQuestions.edit');
    route::put('campaignQuestions/{campaignQuestion}/',[CampaignQuestionController::class,'update'])->name('campaignQuestions.update');

    //destroy
    route::delete('campaignQuestions/{campaignQuestion}/',[CampaignQuestionController::class,'destroy'])->name('campaignQuestions.destroy');

    #کاربران کمپین INDEX,SHOW,DELETE

    //index
    route::get('campaignUsers/{campaign}',[\Modules\Campaign\Http\Controllers\Admin\CampaignUserController::class,'index'])->name('campaignUsers');

    //show
    route::get('campaignUsers/{user}/show',[\Modules\Campaign\Http\Controllers\Admin\CampaignUserController::class,'show']);

    //destroy
    route::delete('campaignUsers/{user}/',[\Modules\Campaign\Http\Controllers\Admin\CampaignUserController::class,'destroy']);

});
