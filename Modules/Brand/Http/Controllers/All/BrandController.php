<?php

namespace Modules\Brand\Http\Controllers\All;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Brand\Entities\Brand;
//use Shetabit\Shopit\Modules\Brand\Http\Controllers\All\BrandController as BaseBrandController;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $brands = Brand::query()->filters()->paginateOrAll();

        return response()->success('لیست تمامی برند ها', compact('brands'));
    }

    /**
     * Show the specified resource.
     * @param int|Brand $id
     * @return JsonResponse
     */
    public function show(int|Brand $id): JsonResponse
    {
        $brand = Brand::query()->findOrFail($id);

        return response()->success('', compact('brand'));
    }
}
