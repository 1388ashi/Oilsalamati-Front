<?php

namespace Modules\Campaign\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignUser;
use Modules\Campaign\Entities\CampaignUserAnswer;
use Modules\Campaign\Exports\CampaignReportExport;
use Modules\Campaign\Http\Requests\Admin\Campaign\CampaignStoreRequest;
use Modules\Campaign\Http\Requests\Admin\Campaign\CampaignUpdateRequest;

class CampaignController extends Controller
{
    #CRUD
    public function index()
    {
        $campaigns = Campaign::query()->searchkeywords()->latest('id')->paginate(50);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('لیست کمپین ها',compact('campaigns'));
        }
        return view('campaign::admin.campaign.index',compact('campaigns'));
    }
    public function create()
    {
        return view('campaign::admin.campaign.create');
    }
    public function store(CampaignStoreRequest $request)
    {
        $campaign = Campaign::create($request->validated());
        ActivityLogHelper::storeModel('کمپین ثبت شد', $campaign);

        $campaign->uploadFile($request);

      if (request()->header('Accept') == 'application/json') {
        return response()->success('ساخت کمپین',compact('campaign'));
      }
      return redirect()->route('admin.campaigns.index')
      ->with('success', 'کمپین با موفقیت ثبت شد.');
    }

    public function exportReport($id)
    {
        $campaign = Campaign::findOrFail($id);
            $ids = [];
            foreach ($campaign->questions as $question){
                $ids [] = $question->id;
            }

            $answers = CampaignUserAnswer::query()->whereIn('question_id',$ids)->orderBy('id')->get();

        return Excel::download(new CampaignReportExport($answers), 'گزارش-' . now()->toDateString() . '.xlsx');
    }
    public function show($id)
    {
        $campaign = Campaign::findOrFail($id);
        if (\request()->header('accept') == 'x-xlsx') {
            $ids = [];
            foreach ($campaign->questions as $question){
                $ids [] = $question->id;
            }

            $answers = CampaignUserAnswer::query()->whereIn('question_id',$ids)->orderBy('id')->get();

            return Excel::download(new CampaignReportExport($answers),
                __FUNCTION__ . '-' . now()->toDateString() . '.xlsx');
            }

        return response()->success('نمایش کمپین',compact('campaign'));
    }

    public function edit($id)
    {
      $campaign = Campaign::findOrFail($id);

      return view('campaign::admin.campaign.edit',compact('campaign'));
    }
    public function update(CampaignUpdateRequest $request, $id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->update($request->validated());
        $campaign->uploadFile($request);
        ActivityLogHelper::updatedModel('کمپین بروز شد', $campaign);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('کمپین با موفقیت ویرایش شد',compact('campaign'));
        }
        return redirect()->route('admin.campaigns.index')
        ->with('success', 'کمپین با موفقیت به روزرسانی شد.');
    }


    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();
        ActivityLogHelper::deletedModel('کمپین حذف شد', $campaign);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('کمپین با موفقیت حذف شد',compact('campaign'));
        }
        return redirect()->route('admin.campaigns.index')
        ->with('success', 'کمپین با موفقیت حذف شد.');
    }
}
