<?php

namespace Modules\Store\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Store\Entities\StoreTransaction;

class StoreTransactionController extends Controller
{
    public function index(): JsonResponse|View
    {
        $storeTransactions = StoreTransaction::query()
            ->filters()
            ->orderByDesc('id')
            ->paginate(15);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست تراکنش های انبار', compact('storeTransactions'));
        }

        $products = (new ProductsCollectionService)->getProductsCollection();

        $productBalance = null;
        $varietyBalance = null;

        // if (request('product_id')) {

        //     $product = $products->where('id', request('product_id'))->first();
            
        //     if (request('variety_id')) {
        //         $variety = $product->varieties->where('id', request('variety_id'))->first();
        //         $varietyBalance = $variety->store_balance;
        //     }else {
        //         $productBalance = $product->store_balance;
        //     }
        // }
        
        return view('store::admin.index', compact([
            'storeTransactions', 
            'products',
            'varietyBalance',
            'productBalance'
        ]));
    }
}

