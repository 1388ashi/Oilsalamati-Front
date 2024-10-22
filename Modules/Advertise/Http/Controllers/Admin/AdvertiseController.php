<?php

namespace Modules\Advertise\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Advertise\Entities\Advertise;
use Modules\Advertise\Entities\PositionAdvertise;
use Modules\Advertise\Http\Requests\Advertise\StoreRequest;
use Modules\Advertise\Http\Requests\Advertise\UpdatePossibilityRequest;
use Modules\Advertise\Http\Requests\Advertise\UpdateRequest;
use Modules\Core\Helpers\Helpers;
//use Shetabit\Shopit\Modules\Advertise\Http\Controllers\Admin\AdvertiseController as BaseAdvertiseController;

class AdvertiseController extends Controller
{

    public function index()
    {

        $AdvertiseBuilder = Advertise::withCommonRelations();
        Helpers::applyFilters($AdvertiseBuilder);
        $advertise = Helpers::paginateOrAll($AdvertiseBuilder);

        return response()->success('' , compact('advertise'));
    }


    public function store(StoreRequest $request)
    {
        $advertise = new Advertise;
        $advertise->positionAdvertise()->associate($request->position_id);
        Helpers::toCarbonRequest(['start' , 'end'] , $request);
        $advertise->fill($request->except(['possibility']))->save();
        $advertise->refresh();
        if (Advertise::wherePositionId($request->position_id)->count() === 1) {
            $advertise->possibility = 100;
            $advertise->save();
        }
        $advertise->addPicture($request->file('image'));
        ActivityLogHelper::simple('تبلیغ ساخته شد', 'store', $advertise);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('تبلیغ شما با موفقیت ایجاد شد' , compact('advertise'));
        }
        return redirect()->route('admin.advertisements.edit_possibility',$request->position_id)
        ->with('success', 'تبلیغ با موفقیت ثبت شد.');
    }


    public function show($id)
    {
        $advertise = Advertise::findOrFail($id);

        return response()->success('' , compact('advertise'));
    }

    public function update(UpdateRequest $request, $id)
    {
        $advertise = Advertise::findOrFail($id);
        Helpers::toCarbonRequest(['start' , 'end'] , $request);
        $advertise->update($request->except(['possibility' , 'position_id']));
        if ($request->hasFile('image')) {
            $advertise->addPicture($request->file('image'));
        }
        $advertise->first();
        ActivityLogHelper::updatedModel('تبلیغ ساخته شد', $advertise);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('تبلیغ شما با موفقیت بروزرسانی شد' , compact('advertise'));
          }
          return redirect()->route('admin.advertisements.edit_possibility',$request->position_id)
          ->with('success', 'تبلیغ با موفقیت به روزرسانی شد.');
    }
    public function editPossibility($id){
      $position = PositionAdvertise::with('advertisements')->findOrFail($id);

      return view('advertise::admin.advertise.edit',compact('position'));
    }
    public function updatePossibility(UpdatePossibilityRequest $request)
    {
        $positionId = $request->route('position');
        $positionAdvertise = PositionAdvertise::findOrFail($positionId);
        $countAds = $positionAdvertise->advertisements()->count();
        for ($i = 0; $countAds > $i; $i++ ) {
            Advertise::find($request->banner_ids[$i])
                ->update(['possibility' => $request->banner_possibility[$i]]);
        }
        $positionAdvertise->advertisements()->get();
        ActivityLogHelper::updatedModel('احتمالات ویرایش شد', $positionAdvertise);

        if (request()->header('Accept') == 'application/json') {
          return response()->success(
              'احتمالات با موفقیت بروزرسانی شد',
              compact('positionAdvertise')
          );
        }
        return redirect()->route('admin.positions.index')
        ->with('success', 'احتمالات با موفقیت ثبت شد.');
    }

    public function destroy($id,Request $request)
    {
        /**
         * @var $advertise Advertise
         */
        $advertise = Advertise::findOrFail($id);
        $advertise->delete();
        $positionAdvertise = $advertise->positionAdvertise()->first();
        $firstAdvertise = $positionAdvertise->advertisements()->where('id', '!=', $advertise->id)->first();
        ActivityLogHelper::deletedModel('تبلیغ حذف شد', $advertise);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('تبلیغ با موفقیت حذف شد', ['edited' => $firstAdvertise]);
          }
          return redirect()->route('admin.advertisements.edit_possibility',$request->position_id)
          ->with('success', 'تبلیغ با موفقیت حذف شد.');
    }
}
