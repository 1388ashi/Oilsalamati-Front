<?php

namespace Modules\Product\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Product\Http\Controllers\Admin\ProductSetController as BaseProductSetController;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\Entities\ProductSet;

class ProductSetController extends BaseController
{
    public function index()
    {
        $productSets = ProductSet::latest()->filters()->withCount('products')->paginate();

        return response()->success('', ['product_sets' => $productSets]);
    }

    public function show(ProductSet $productSet)
    {
        $productSet->loadCommonRelations();

        return response()->success('', ['product_set' => $productSet]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_id.*' => 'required|integer|exists:products,id',
            'name' =>  'required|string'
        ]);

        $productSet = ProductSet::storeAndUpdateModel($request);
        $productSet->loadCommonRelations();

        return response()->success('محصول با موفقیت ست شد', ['product_set' => $productSet]);
    }

    public function update(Request $request, ProductSet $productSet)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|integer|exists:products,id',
            'name' =>  'required|string'
        ]);

        $productSet = ProductSet::storeAndUpdateModel($request, $productSet);
        $productSet->loadCommonRelations();

        return response()->success('محصول با موفقیت ست شد', ['product_set' => $productSet]);
    }


    public function destroy(ProductSet $productSet)
    {
        $productSet->delete();
        $productSet->loadCommonRelations();

        return response()->success('محصول ست شده با موفقیت حذف شد', ['product_set' => $productSet]);
    }
}

