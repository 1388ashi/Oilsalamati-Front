<?php

namespace Modules\SizeChart\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\SizeChart\Http\Controllers\Admin\SizeChartTypeController as BaseSizeChartTypeValue;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Intervention\Image\Size;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Entities\SizeChartType;
use Modules\SizeChart\Entities\SizeChartTypeValue;
use Modules\SizeChart\Http\Requests\Admin\StoreSizeChartRequest;
use Modules\SizeChart\Http\Requests\Admin\UpdateSizeChartRequest;

class SizeChartTypeController extends Controller
{
    public function index()
    {
        $sizeChartType = SizeChartType::query()->filters()->latest()->get();

        return response()->success('تمام تایپ ها', ['size_chart_types' => $sizeChartType]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'string|unique:size_chart_types,name',
            'values' => 'required|array',
            'values.*' => 'required|string|min:1'
        ]);
        /** @var SizeChartType $sizeChart */
        $sizeChartType = SizeChartType::query()->create(['name' => $request->name]);
        foreach ($request->values as $value) {
            $sizeChartTypeValue = new SizeChartTypeValue([
                'name' => $value
            ]);
            $sizeChartTypeValue->type()->associate($sizeChartType)->save();
        }
        $sizeChartType->load(['values']);

        return response()->success(' سایز چارت با موفقیت ایجاد شد.', ['size_chart_type' => $sizeChartType]);
    }

    public function update(Request $request, $id)
    {
        /** @var SizeChartType $sizeChartType */
        $sizeChartType = SizeChartType::findOrFail($id);
        $request->validate([
            'name'  => 'string|unique:size_chart_types,name,' . $id,
            'values' => 'required|array',
            'values.*' => 'required|string|min:1'
        ]);
        /** @var SizeChartType $sizeChart */
        $sizeChartType->update(['name' => $request->name]);
        $sizeChartType->values()->delete();
        foreach ($request->values as $value) {
            $sizeChartTypeValue = new SizeChartTypeValue([
                'name' => $value
            ]);
            $sizeChartTypeValue->type()->associate($sizeChartType)->save();
        }
        $sizeChartType->load(['values']);

        return response()->success(' سایز چارت با موفقیت ویرایش شد.', ['size_chart_type' => $sizeChartType]);
    }

    public function show(SizeChartType $sizeChartType)
    {
        return response()->success('', ['size_chart_type' => $sizeChartType]);
    }

    public function destroy($id)
    {
        SizeChartType::query()->findOrFail($id)->delete();

        return response()->success('سایز چارت با موفقیت حذف شد');
    }
}

