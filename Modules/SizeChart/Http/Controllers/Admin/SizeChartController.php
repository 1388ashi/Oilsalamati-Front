<?php

namespace Modules\SizeChart\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\SizeChart\Http\Controllers\Admin\SizeChartController as BaseSizeChartController;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Intervention\Image\Size;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Http\Requests\Admin\StoreSizeChartRequest;
use Modules\SizeChart\Http\Requests\Admin\UpdateSizeChartRequest;

class SizeChartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sizeChart = SizeChart::latest()->withCommonRelations()->filters()->paginateOrAll();

        if (\request('accept') == 'application/json') {
          return response()->success('تمام سایز چارت ها', compact('sizeChart'));
        }
        // return view('sizechart::admin.sizeChart.index');
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreSizeChartRequest $request
     */
    public function store(StoreSizeChartRequest $request)
    {
        $sizeChart = new SizeChart($request->all());
        $sizeChart->type()->associate($request->type_id)->save();
        ActivityLogHelper::storeModel('سایز چارت ثبت شد', $sizeChart);

        return response()->success(' سایز چارت با موفقیت ایجاد شد.', compact('sizeChart'));
    }

    /**
     * Show the specified resource.
     * @param SizeChart $sizeChart
     */
    public function show(SizeChart $sizeChart)
    {
        return response()->success('', compact('sizeChart'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     */
    public function update(UpdateSizeChartRequest $request, SizeChart $sizeChart)
    {
        $data = $request->toArray();
        $request->merge([
            'chart' => is_string($data['chart']) ? $data['chart'] : json_encode($data['chart'])
        ]);
        $sizeChart->update($request->all());
        ActivityLogHelper::updatedModel('سایز چارت بروز شد', $sizeChart);

        return response()->success('سایز چارت با موفقیت بروزرسانی شد', compact('sizeChart'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     */
    public function destroy(SizeChart $sizeChart)
    {
        $sizeChart->delete();
        ActivityLogHelper::deletedModel('سایز چارت حذف شد', $sizeChart);

        return response()->success('سایز چارت با موفقیت حذف شد', compact('sizeChart'));
    }
}

