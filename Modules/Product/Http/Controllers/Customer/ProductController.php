<?php

namespace Modules\Product\Http\Controllers\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Comment\Entities\Comment;
use Modules\Core\Classes\CoreSettings;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderItem;
use Modules\Product\Entities\Product;
use Modules\Product\Services\ProductService;
use Modules\ProductComment\Entities\ProductComment;
//use Shetabit\Shopit\Modules\Product\Http\Controllers\Customer\ProductController as BaseProductController;

class ProductController extends Controller
{
    public function getCustomerProducts()
    {
        $orders= Order::query()
            ->where('status', \Shetabit\Shopit\Modules\Order\Entities\Order::STATUS_DELIVERED)
            ->where('customer_id',auth()->user()->id)
            ->get();

        $productIds = array();

        foreach ($orders->where('status',Order::STATUS_DELIVERED) as $order){
            foreach ($order->activeItems as $item){
                if ($item->status == 1){
                    array_push($productIds,$item->product_id);
                }
            }
        }

        $comments = ProductComment::query()
            ->where('creator_id',auth()->user()->id)
            ->pluck('product_id');


        $productService = new ProductService();
        $products = $productService->filters();
        $products = $products->whereIn('id',$productIds)->whereNotIn('id',$comments)->with(['varieties.color'])->get();
        foreach ($products as $product) {
            $product->makeHidden('varieties');
            $product->makeHidden('activeFlash');
            $product->makeHidden('varietyOnlyDiscountsRelationship');
        }


        return response()->success('لیست تمامی محصولات',
            compact('products'));
    }






    // came from vendor ================================================================================================
    public function indexFavorites(): JsonResponse
    {
        $customer = \Auth::guard('customer-api')->user();
        /* @var $user Customer */
        $favorite = $customer->favorites()->select(Product::SELECTED_COLUMNS_FOR_FRONT)->get();

        foreach ($favorite as $item) {
            $item->setAppends(Product::APPENDS_LIST_FOR_FRONT);
            $item->makeHidden('varieties', 'pivot');
        }

        return response()->success('لیست مورد علاقه های شما .', compact('favorite'));
    }

    public function addToFavorites($productId): JsonResponse
    {
        $user = auth()->user();
        $product = Product::query()->select(['id', 'title'])
            ->findOrFail($productId)->makeHidden('images');
        /**
         * @var $user Customer
         */

        $response = $user->favorites()->where('product_id', $product->id);
        if ($response->exists()) {
            return response()->success('قبلا به لیست مورد علاقه های شما افزوده شده است.');
        }

        $response->save($product);

        return response()->success('به لیست مورد علاقه ها افزوده شد', compact('product'));
    }

    public function deleteFromFavorites($productId)
    {
        /** @var Customer $user */
        $user = auth()->user();
        $product = Product::query()->select(['id', 'title'])
            ->findOrFail($productId)->makeHidden('images');
        $user->favorites()->detach([$product->id]);

        return response()->success('از لیست مورد علاقه ها حذف شد');
    }
}
