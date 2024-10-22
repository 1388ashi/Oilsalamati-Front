<?php

namespace Modules\Advertise\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Advertise\Entities\PositionAdvertise;
use Modules\Advertise\Http\Requests\Position\PositionStoreRequest;
use Modules\Advertise\Http\Requests\Position\PositionUpdateRequest;
use Modules\Core\Helpers\Helpers;
// use Shetabit\Shopit\Modules\Advertise\Http\Controllers\Admin\PositionAdvertiseController as BasePositionAdvertiseController;

class PositionAdvertiseController extends Controller
{
    public function index()
    {
        $positionAdvertiseBuilder = PositionAdvertise::with('advertisements');
        Helpers::applyFilters($positionAdvertiseBuilder);
        $positions = Helpers::paginateOrAll($positionAdvertiseBuilder);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('' , compact('positions'));
        }
        return view('advertise::admin.position.index', compact('positions'));
    }

    public function show($id)
    {
        $positions = PositionAdvertise::with('advertisements')->findOrFail($id);

        return response()->success('' , compact('positions'));
    }

    public function store(PositionStoreRequest $request)
    {
        $positionAdvertise = PositionAdvertise::create($request->all());
        $positionAdvertise->load('advertisements');
        ActivityLogHelper::simple('جایگاه ثبت شد', 'store', $positionAdvertise);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('جایگاه با موفقیت اضافه شد', compact('positionAdvertise'));
        }
        return redirect()->route('admin.positions.index')
        ->with('success', 'جایگاه با موفقیت ثبت شد.');
    }

    public function update(PositionUpdateRequest $request, $positionAdvertiseId)
    {
        $positionAdvertise = PositionAdvertise::findOrFail($positionAdvertiseId);
        $positionAdvertise->update($request->all());
        $positionAdvertise->load('advertisements');
        ActivityLogHelper::updatedModel('جایگاه ویرایش شد', $positionAdvertise);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('جایگاه با موفقیت ویرایش شد', compact('positionAdvertise'));
        }
        return redirect()->route('admin.positions.index')
        ->with('success', 'جایگاه به روزرسانی شد.');
    }
}
