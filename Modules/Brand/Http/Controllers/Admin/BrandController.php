<?php

namespace Modules\Brand\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Brand\Entities\Brand;
use Modules\Brand\Http\Requests\Admin\BrandStoreRequest;
use Modules\Brand\Http\Requests\Admin\BrandUpdateRequest;
use Modules\Product\Entities\Recommendation;

//use Shetabit\Shopit\Modules\Brand\Http\Controllers\Admin\BrandController as BaseBrandController;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return  mixed
     */
    public function index()
    {
        $brands = Brand::latest()->filters()->paginateOrAll();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست تمامی برند ها', compact('brands'));
        }

        $totalBrands = $brands->total();

        return view('brand::admin.index', compact('brands', 'totalBrands'));
    }

    /**
     * Store a newly created resource in storage.
     * @param BrandStoreRequest $request
     * @param Brand $brand
     * @return mixed
     */
    public function store(BrandStoreRequest $request, Brand $brand)
    {
        $brand->fill($request->all());
        $brand->save();
        if ($request->hasFile('image')) {
            $brand->updateFiles($request->images, Brand::COLLECTION_NAME_IMAGES);
        }
        $brand->load('media');

        if (request()->header('Accept') == 'application/json') {
            return response()->success('برند شما با موفقیت ایجاد شد.', compact('brand'));
        }

        return redirect()->back()->with('success', 'برند جدید با موفقیت ثبت شد');
    }

    /**
     * Show the specified resource.
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $brand = Brand::query()->findOrFail($id);

        return response()->success('', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     * @param BrandUpdateRequest $request
     * @param Brand $brand
     */
    public function update(BrandUpdateRequest $request, $id)
    {
        $brand = Brand::query()->findOrFail($id);

        $brand->fill($request->all());
        if ($request->hasFile('image')) {
            $brand->updateFiles($request->images, Brand::COLLECTION_NAME_IMAGES);
        }
        $brand->save();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('برند مورد نظر بروزرسانی شد.', compact('brand'));
        }

        return redirect()->back()->with('success', 'برند با موفقیت ویرایش شد');
    }

    /**
     * Remove the specified resource from storage.
     * @param Brand $brand
     * @return Response|RedirectResponse
     * @throws Exception
     */
    public function destroy($id)
    {
        $brand = Brand::query()->findOrFail($id);

        $brand->delete();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('برند با موفقیت حذف شد.', compact('brand'));
        }

        return redirect()->back()->with('success', 'برند با موفقیت حذف شد');
    }
}
