<?php

namespace Modules\Product\Http\Controllers\Front;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\ApiResources\ProductEmallsPaginationResource;
use Modules\Product\ApiResources\ProductTorobPaginationResource;
use Modules\Product\Entities\Product;

class PPCController extends BaseController
{
    public function torob(Request $request)
    {
        $products = static::getBase();
        if ($request->filled('page_unique')) {
            $products = $products->whereId($request->page_unique);
        } else if ($request->filled('page_url')) {
            $id = explode('/', $request->page_url)[4];

            $products->whereId($id);
        }

        $products = new ProductTorobPaginationResource($products->paginate(100));

        return response()->json($products);
    }

    public function emalls(Request $request)
    {
        $products = static::getBase();
        $products = new ProductEmallsPaginationResource($products->paginate($request->size ?: 25));

        return response()->json($products);
    }

    public static function getBase()
    {
        return Product::query()->withCommonRelations()
            ->with(['varieties' => function($query) {
                $query->withCommonRelations();
            }])->latest('updated_at');
    }
}
