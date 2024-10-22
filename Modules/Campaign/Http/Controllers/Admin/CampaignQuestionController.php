<?php

namespace Modules\Campaign\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignQuestion;
use Modules\Campaign\Http\Requests\Admin\CampaignQuestion\CampaignQuestionStoreRequest;
use Modules\Campaign\Http\Requests\Admin\CampaignQuestion\CampaignQuestionUpdateRequest;
use Symfony\Component\Console\Question\Question;

class CampaignQuestionController extends Controller
{

    public function index($id)//campaign id
    {
        $campaign = Campaign::query()->find($id);
        $questions = CampaignQuestion::query()
            ->where('campaign_id',$id)
            ->orderBy('order','desc')
            ->get();

        if (request()->header('Accept') == 'application/json') {
          return response()->success('campaigns',compact('questions'));
        }
        return view('campaign::admin.questions.index',compact('questions','campaign'));
    }

    public function store(CampaignQuestionStoreRequest $request)
    {
        $campaignQuestion = CampaignQuestion::create($request->validated());
        ActivityLogHelper::storeModel(' کمپین پرسشی ثبت شد', $campaignQuestion);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('سوال با موفقیت ساخته شد');
        }
        return redirect()->route('admin.campaignQuestions.index',$request->campaign_id)->with([
          'success' => 'سوال با موفقیت ثبت شد']);
    }


    public function show($id)
    {
        $question = CampaignQuestion::findOrFail($id);
        $question->load('campaign');

        return response()->success('campaigns',compact('question'));
    }
    public function edit($id)
    {
        $question = CampaignQuestion::find($id);

        return view('campaign::admin.questions.edit', compact('question'));
    }

    public function update(CampaignQuestionUpdateRequest $request, $id)
    {
        $question = CampaignQuestion::query()->findOrFail($id);
        $question->update($request->validated());
        ActivityLogHelper::updatedModel(' کمپین پرسشی بروز شد', $question);


        if (request()->header('Accept') == 'application/json') {
          return response()->success('سوال با موفقیت ویرایش شد',compact('question'));
        }
        return redirect()->route('admin.campaignQuestions.index',$question->campaign_id)->with([
          'success' => 'سوال با موفقیت به روزرسانی شد']);
    }
    public function sort(Request $request,$id)
    {
        $campaign = Campaign::query()->find($id);
        CampaignQuestion::setNewOrder($request->orders);

        return redirect()->route('admin.campaignQuestions.index',$campaign)
        ->with('success', 'سوال با موفقیت مرتب سازی شد.');
    }

    public function destroy($id)
    {
        $question = CampaignQuestion::findOrFail($id);
        $question->delete();
        ActivityLogHelper::deletedModel(' کمپین پرسشی حذف شد', $question);


        if (request()->header('Accept') == 'application/json') {
          return response()->success('campaigns',compact('question'));
        }
        return redirect()->route('admin.campaignQuestions.index',$question->campaign_id)
        ->with('success', 'سوال با موفقیت حذف شد.');
    }
}
