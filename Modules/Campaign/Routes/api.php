<?php



use Modules\Campaign\Http\Controllers\Admin\CampaignQuestionController;

Route::superGroup('admin', function () {
    Route::permissionResource('campaigns' , CampaignController::class);

    #سوالات کمپین CRUD
    //index
    route::get('campaignQuestions/{campaign}',[CampaignQuestionController::class,'index'])->name('campaignQuestions.index');

    //show
    route::get('campaignQuestions/{campaignQuestion}/show',[CampaignQuestionController::class,'show']);

    //store
    route::post('campaignQuestions',[CampaignQuestionController::class,'store'])->name('campaignQuestions.store');

    //update
    route::put('campaignQuestions/{campaignQuestion}/',[CampaignQuestionController::class,'update'])->name('campaignQuestions.update');

    //destroy
    route::delete('campaignQuestions/{campaignQuestion}/',[CampaignQuestionController::class,'destroy'])->name('campaignQuestions.destroy');

    #کاربران کمپین INDEX,SHOW,DELETE

    //index
    route::get('campaignUsers/{campaign}',[\Modules\Campaign\Http\Controllers\Admin\CampaignUserController::class,'index']);

    //show
    route::get('campaignUsers/{user}/show',[\Modules\Campaign\Http\Controllers\Admin\CampaignUserController::class,'show']);

    //destroy
    route::delete('campaignUsers/{user}/',[\Modules\Campaign\Http\Controllers\Admin\CampaignUserController::class,'destroy']);

});


Route::superGroup("front" ,function (){

    #دادن لیست سوالات به سعید
    route::get('get-campaign-questions/{question}',[\Modules\Campaign\Http\Controllers\Front\CampaignController::class,'showQuestions']);

    #گرفتن جواب ها و ثبت در دیتابیس
    route::post('save-campaign-answers',[\Modules\Campaign\Http\Controllers\Front\CampaignController::class,'getAnswersFromFront']);

},[]);

Route::superGroup('customer', function () {

});


Route::superGroup('all' ,  function() {

},[]);
