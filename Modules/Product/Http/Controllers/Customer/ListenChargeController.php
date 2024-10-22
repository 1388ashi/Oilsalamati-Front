<?php

namespace Modules\Product\Http\Controllers\Customer;

//use Shetabit\Shopit\Modules\Product\Http\Controllers\Customer\ListenChargeController as BaseListenChargeController;

use App\Http\Controllers\Controller;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\ListenCharge;
use Modules\Product\Entities\Product;
use Modules\Product\Http\Requests\Customer\ListenChargeStoreRequest;

class ListenChargeController extends Controller
{
    public function store(ListenChargeStoreRequest $request)
    {
        $product = $request->product;
        /**
         * @var $customer Customer
         */
        $customer = \Auth::user();
        $listenCharge = ListenCharge::store($customer, $product);

        return response()
            ->success(
                'عملیات با موفقیت انجام شد. در صورت موجود شدن محصول به شما اطلاع داده خواهد شد',
                compact('listenCharge')
            );
    }

    public function destroy($productId)
    {
        /**
         * @var $customer Customer
         */
        $customer = \Auth::user();
        $listenCharge = $customer->listenCharges()->where('product_id', $productId)->first();
        if ($listenCharge) {
            $listenCharge->delete();
        }

        return response()->success('عملیات لغو با موفقیت انجام شد');
    }
}
