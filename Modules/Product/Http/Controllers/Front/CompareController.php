<?php

namespace Modules\Product\Http\Controllers\Front;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\Entities\Product;
use Shetabit\Shopit\Modules\Core\Helpers\Helpers;

class CompareController extends BaseController
{
    public function index()
    {
        $ids = request('compares', []);
        $products = Product::query()
            ->with(['categories', 'varieties', 'specifications.pivot.specificationValues',
                'specifications.pivot.specificationValue'])
            ->active()->findMany($ids);
        /** @var Product $product */
        foreach ($products as $product) {
            $product->dontHide[] = 'categories';
        }
        $products = Helpers::removeVarieties($products->toArray());

        return response()->success('', compact('products'));
    }

    public function search(Request $request)
    {
        $cats = $request->input('c');
        if (empty($cats)) {
            return response()->success('', ['products' => []]);
        }

        $q = $request->input('q');
        // Excluded ids
        $excludedIds = $request->input('exclude');

        $products = Product::query()->with('varieties')->when($q, function ($query) use ($q) {
            $query->where('title', 'LIKE', "%$q%");
        })->whereHas('categories', function ($query) use ($cats) {
            $query->whereIn('id', $cats);
        })->when(!empty($excludedIds), function ($query) use ($excludedIds) {
            $query->whereNotIn('id', $excludedIds);
        })->take(30)->latest()->active()->get();

        return response()->success('', [
            'products' => $products
        ]);
    }
}
