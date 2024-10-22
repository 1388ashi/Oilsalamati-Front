<?php

namespace Modules\Campaign\Http\Controllers\Front;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignQuestion;
use Modules\Campaign\Entities\CampaignUser;
use Modules\Campaign\Entities\CampaignUserAnswer;
use Symfony\Component\Console\Question\Question;

class CampaignController extends Controller
{

    public function showQuestions($id)
    {
        #دادن لیست سوالات به فرانت
        $campaign = Campaign::findOrFail($id);
        $campaign->load('questions');
        $campaign->makeHidden(['coupon_code','created_at','updated_at','status','start_date','end_date']);
        views($campaign)->collection('campaign')->record();

        return response()->success('',compact('campaign'));
    }

    public function getAnswersFromFront(Request $request)
    {
        $userExisis = CampaignUser::where('mobile',$request->mobile)
            ->where('campaign_id',$request->campaign_id)
            ->count();
        if ($userExisis){
            return response()->error('شما قبلا در کمپین ثبت نام کرده اید!');
        }
        $user = CampaignUser::create([
            'mobile' => $request->mobile,
            'campaign_id' => $request->campaign_id
        ]);

        $answers = $request->answers;
        foreach ($answers as $answer){
            CampaignUserAnswer::create([
                'question_id' => $answer['question_id'],
                'answer' => $answer['answer'],
                'user_id' => $user->id,
            ]);
        }

        $campaign = Campaign::findOrFail($request->campaign_id);
        $coupon = $campaign->coupon_code;

        return response()->success('درخواست شما با موفقیت ثبت شد',compact('coupon'));

    }

}
