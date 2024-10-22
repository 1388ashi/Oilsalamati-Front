<?php

namespace Modules\Coupon\Http\Controllers\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Cart\Entities\Cart;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Http\Requests\Customer\CouponVerifyRequest;
//use Modules\Coupon\Services\CalculateCouponDiscountService;
use Modules\Coupon\Services\CouponCalculatorService;

//use Shetabit\Shopit\Modules\Coupon\Http\Controllers\Customer\CouponController as BaseCouponController;

class CouponController extends Controller
{
    public function verify(CouponVerifyRequest $request): JsonResponse
    {
        $coupon = Coupon::query()->where('code', $request->code)->firstOrFail();
        $customer = \Auth::guard('customer-api')->user();
        $carts = $customer->carts;
        $total_items_amount = Cart::calculate_sum_carts($carts);

//        $discount =  (new CalculateCouponDiscountService($code, $totalPrice))->calculate();
        $couponCalculatorServiceObject = new CouponCalculatorService($coupon,$carts,$total_items_amount,$customer);
        $couponCalculatorServiceObject->validator();
        $discount = $couponCalculatorServiceObject->calculator();

        return response()->success('تخفیف شما', compact('discount'));
    }
}
