<?php

namespace Modules\Campaign\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignUser;

class CampaignUserController extends Controller
{
    public function index($id)
    {
        $campaign = Campaign::query()->find($id);
        $users = CampaignUser::query()->where('campaign_id',$id)->paginate();

        if (request()->header('Accept') == 'application/json') {
          return response()->success('campaigns',compact('users'));
        }
        return view('campaign::admin.users.index',compact('users','campaign'));
    }


    public function show($id)
    {
        $user = CampaignUser::findOrFail($id);
        #TODO LOAD DATA

        return response()->success('campaigns',compact('user'));
    }

    public function destroy($id)
    {
        $user = CampaignUser::findOrFail($id);
        $user->delete();
        #TODO : DELETE DEPENDENCIES

        return response()->success('کاربر با موفقیت حذف شد',compact('user'));
    }
}
