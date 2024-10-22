<?php

namespace Modules\Product\Http\Controllers\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\ListenDiscount;
use Modules\Product\Http\Requests\Customer\ListenDiscountStoreRequest;

class ListenDiscountController extends Controller
{
    public function store(ListenDiscountStoreRequest $request)
    {
        $product = $request->product;
        /**
         * @var $customer Customer
         */
        $customer = \Auth::user();
        $listenDiscount = ListenDiscount::store($customer, $product);

        return response()
            ->success(
                'عملیات با موفقیت انجام شد. در صورت پیشنهاد ویژه شدن محصول به شما اطلاع داده خواهد شد',
                compact('listenDiscount')
            );
    }

    public function destroy($productId)
    {
        /**
         * @var $customer Customer
         */
        $customer = \Auth::user();
        $listenDiscount = $customer->listenDiscounts()->where('product_id', $productId)->first();
        if ($listenDiscount) {
            $listenDiscount->delete();
        }

        return response()->success('عملیات لغو با موفقیت انجام شد');
    }

}
