<?php

namespace Modules\Color\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Color\Entities\Color;
use Modules\Color\Http\Requests\Admin\ColorStoreRequest;
use Modules\Color\Http\Requests\Admin\ColorUpdateRequest;
//use Shetabit\Shopit\Modules\Color\Http\Controllers\Admin\ColorController as BaseColorController;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $colors = Color::latest('id')->filters()->paginate(15);

        if (\request()->header('Accept') == 'application/json') {
            return response()->success('دریافت لیست همه رنگ ها', compact('colors'));
        }
        return view('color::admin.index', compact('colors'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ColorStoreRequest $request
     */
    public function store(ColorStoreRequest $request)
    {
        $color = Color::create($request->all());
        ActivityLogHelper::storeModel('رنگ ثبت شد', $color);

         if (\request()->header('Accept') == 'application/json') {
          return response()->success('رنگ با موفقیت ثبت شد.', compact('color'));
        }
        return redirect()->route('admin.colors.index')
        ->with('success', 'رنگ با موفقیت ثبت شد.');
    }

    /**
     * Show the specified resource.
     *
     * @param int $id
     */
    public function show($id)
    {
        $color = Color::findOrFail($id);

        return response()->success('رنگ با موفقیت دریافت شد.', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ColorUpdateRequest $request
     * @param int $id
     */
    public function update(ColorUpdateRequest $request, $id)
    {
        $color = Color::findOrFail($id);

        $color->update($request->all());
        ActivityLogHelper::updatedModel('رنگ بروز شد', $color);

         if (\request()->header('Accept') == 'application/json') {
          return response()->success('رنگ با موفقیت به روزرسانی شد.', compact('color'));
        }
        return redirect()->route('admin.colors.index')
        ->with('success', 'رنگ با موفقیت به روزرسانی شد.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @throws \Exception
     */
    public function destroy($id)
    {
        $color = Color::findOrFail($id);

        $color->delete();
        ActivityLogHelper::deletedModel('رنگ حذف شد', $color);

         if (\request()->header('Accept') == 'application/json') {
          return response()->success('رنگ با موفقیت حذف شد.', compact('color'));
        }
        return redirect()->route('admin.colors.index')
          ->with('success', 'رنگ با موفقیت حذف شد.');
    }
}
