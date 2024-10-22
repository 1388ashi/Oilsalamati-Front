<?php

namespace Modules\Coupon\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Cart\Entities\Cart;
use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Customer;
use Shetabit\Shopit\Modules\Core\Classes\CoreSettings;
//use Shetabit\Shopit\Modules\Coupon\Services\CalculateCouponDiscountService as BaseCalculateCouponDiscountService;


/* todo: ATTENTION -- THIS CLASS DOES NOT USED IN ALL OVER THE PROJECT */
class CalculateCouponDiscountService
{
    protected ?\Illuminate\Contracts\Auth\Authenticatable $user;
    protected Coupon $model;

    /**
     * CalculateCouponDiscountService constructor.
     * @param $code
     * @param $totalPrice
     */
    public function __construct(
        protected $code,
        protected $totalPrice = null,
        $user = null,
        protected $carts = null,
    ){

        $this->user = $user ?? \Auth::guard('customer-api')->user();
        $this->model = new Coupon();
        if (!$carts)
            $this->carts = $this->user->carts()->with(['variety'])->get();

        /* todo: totalPriceCalculator need to be from one reference */
        $this->totalPrice = $this->totalPrice ?? $this->totalPriceCalculator($this->carts);

    }

    public function getCouponModel()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function calculate()
    {
        $coupon = $this->model->where('code' , $this->code)->firstOrFail();
        $this->validation($coupon, $this->model, $this->user);

        return $this->calculationDiscount($coupon , $this->totalPrice, $this->carts);
    }

    /**
     * @param $coupon
     * @param $totalPrice
     * @return mixed
     */
    public function calculationDiscount($coupon , $totalPrice, $carts = null): mixed
    {
        $type = $coupon->type; // نوع تخفیف عددی یا درصدی
        $amount = $coupon->amount; // مبلغ یا درصد تخفیف
        $customer = $this->user;
        $carts = $carts ?? $this->carts;


        if ($coupon->categories()->count() != 0) {
            // so this coupon has categories ===========================================================================
            $discount = 0;
            $amount = 100;
            foreach ($carts as $cart) {
                $product = $cart->variety->product;
                $productCategoriesIds = $product->categories()->pluck('id')->toArray();
                $jointCategoryIds = $coupon->categories()->whereIn('categories.id', $productCategoriesIds)->get();

                if ($jointCategoryIds->count() == 0) {
                    // now we should search on parent categories of product categories.
                    $productCategoriesParentsIds = [];
                    foreach ($product->categories as $category) {
                        if ($parent = $category->parent()->first()) {
                            $productCategoriesParentsIds[] = $parent->id;
                        }
                    }
                    $jointCategoryIds = $coupon->categories()->whereIn('categories.id', $productCategoriesParentsIds)->get();

                    if ($jointCategoryIds->count() == 0) {
                        // we search in parents categories in 2 levels. just. I don't want to use of recursive functions
                        $productCategoriesParentParentIds = [];
                        foreach ($product->categories as $category) {
                            if ($parent = $category->parent) {
                                if ($parent->parent) {
                                    $productCategoriesParentParentIds[] = $parent->parent->id;
                                }
                            }
                        }
                        $jointCategoryIds = $coupon->categories()->whereIn('categories.id', $productCategoriesParentParentIds)->get();
                        if ($jointCategoryIds->count() == 0) {
                            continue;
                        }
                    }
                }

                // we select the least amounts in pivot.
                $selectedCategoryForThisVariety = $jointCategoryIds->sortBy(function ($item, $key) {
                    return $item->pivot->amount;
                })->first();

                $coreSettings = app(CoreSettings::class);
                if (!$coreSettings->get('order.allow_coupon_with_discount') && $coreSettings->get('order.allow_coupon_mixed')) {
                    // we don't accept product with discount
                    if ($cart->variety->final_price['discount'] == 0) {
                        $discount += ((($cart->variety->final_price['amount'] * $cart->quantity) * $selectedCategoryForThisVariety->pivot->amount) / 100);
                    }
                } else {
                    // we accept product with discount
                    $discount += ((($cart->variety->final_price['amount'] * $cart->quantity) * $selectedCategoryForThisVariety->pivot->amount) / 100);
                }
                if ($selectedCategoryForThisVariety->pivot->amount < $amount) {
                    $amount = $selectedCategoryForThisVariety->pivot->amount;
                }

            }
            return $this->returnFormat($discount, 'percentage', $amount.'%');
        } else {
            // so this coupon hasn't categories. and this is a normal coupon. ==========================================
            $coreSettings = app(CoreSettings::class);
            if (!$coreSettings->get('order.allow_coupon_with_discount') && $coreSettings->get('order.allow_coupon_mixed')) {
                /** @var Customer $customer */
                if ($customer = Auth::user()) {
                    $totalPrice = 0;
                    /** @var Cart $cart */
                    foreach ($carts as $cart) {
                        if (!$cart->variety->final_price['discount_price']) {
                            $totalPrice += $cart->variety->final_price['amount'] * $cart->quantity;
                        }
                    }
                }
            }

            if ($type == $this->model::DISCOUNT_TYPE_FLAT){
                return $this->returnFormat($amount, 'flat');
            }
            $discount = (int)round(($amount * $totalPrice) / 100);
            #if type == static::DISCOUNT_TYPE_PERCENTAGE
            return $this->returnFormat($discount, 'percentage', $amount.'%');
        }
    }

    public function returnFormat($amount, $type, $percentage = null)
    {
        return [
            'discount' => $amount,
            'type' => $type,
            'percentage' => $percentage,
        ];
    }


    /**
     * @param $coupon
     * @param $model
     * @param $user
     */
    public function validation($coupon, $model, $user)
    {
        $now = Carbon::now()->toDateString();
        $start_date = Carbon::parse($coupon->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($coupon->end_date)->format('Y-m-d');

        if (!(($start_date <= $now) && ($now <= $end_date))){
            throw Helpers::makeValidationException('تاریخ استفاده از کد تخفیف به پایان رسیده است.');
        }
        if ($coupon->usage_limit <= $model->countCouponUsed($coupon->id)){
            throw Helpers::makeValidationException('تعداد استفاده از این کد تخفیف به اتمام رسیده است');
        }
        if ($coupon->usage_per_user_limit <= $model->countCouponUsedByCustomer($user , $coupon->id)){
            throw Helpers::makeValidationException('تعداد استفاده شما از این کد تخفیف به اتمام رسیده است');
        }
        if ($coupon->min_order_amount !== null && ($this->totalPrice < $coupon->min_order_amount)) {
            throw Helpers::makeValidationException('حداقل مبلغ سبد خرید برای استفاده از این کد تخفیف '
                . $coupon->min_order_amount . ' تومان است');
        }
    }


    private function totalPriceCalculator($carts)
    {
        $total = 0;
        foreach ($carts as $item) {
            $total += ($item->quantity * $item->price);
        }
        return $total;
    }
}
