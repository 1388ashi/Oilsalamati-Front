<?php

namespace Modules\Unit\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Unit\Http\Controllers\Admin\UnitController as BaseUnitController;

use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Unit\Entities\Unit;
use Modules\Unit\Http\Requests\Admin\UnitStoreRequest;
use Modules\Unit\Http\Requests\Admin\UnitUpdateRequest;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $units = Unit::query()->latest('id')->paginate();

         if (\request()->header('Accept') == 'application/json') {
          return response()->success('دریافت لیست واحدها', compact('units'));
        }

        return view('unit::admin.index', compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UnitStoreRequest $request
     */
    public function store(UnitStoreRequest $request)
    {
        $unit = Unit::create($request->all());
		ActivityLogHelper::storeModel('واحد جدید ساخته شد.', $unit);

         if (\request()->header('Accept') == 'application/json') {
          return response()->success('واحد با موفقیت ثبت شد.', compact('unit'));
        }
          return redirect()->route('admin.units.index')
          ->with('success', 'واحد با موفقیت ثبت شد.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     */
    public function show($id)
    {
        $unit = Unit::findOrfail($id);

        if (\request()->header('Accept') == 'application/json') {
			$unit->loadCommonRelations();
          return response()->success('واحد با موفقیت دریافت شد', compact('unit'));
        }
        return redirect()->route('admin.units.index')
        ->with('success', 'واحد با موفقیت ثبت شد.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UnitUpdateRequest $request
     * @param int $id
     */
    public function update(UnitUpdateRequest $request, $id)
    {
        $unit = Unit::findOrfail($id);
        $unit->update($request->all());
		ActivityLogHelper::updatedModel('واحد بروزرسانی شد.', $unit);

        if (\request()->header('Accept') == 'application/json') {
          return response()->success('واحد با موفقیت به روزرسانی شد', compact('unit'));
        }
        return redirect()->route('admin.units.index')
        ->with('success', 'واحد با موفقیت به روزرسانی شد.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     */
    public function destroy($id)
    {
        $unit = Unit::findOrfail($id);
        $unit->delete();
		ActivityLogHelper::deletedModel('واحد حذف شد.', $unit);

         if (\request()->header('Accept') == 'application/json') {
          return response()->success('واحد با موفقیت حذف شد', compact('unit'));
        }
        return redirect()->route('admin.units.index')
        ->with('success', 'واحد با موفقیت حذف شد.');
    }
}
