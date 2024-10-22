<?php

namespace Modules\Coupon\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Cart\Entities\Cart;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Customer;

class CouponCalculatorService
{
    public function __construct(
        protected $coupon,
        protected $carts,
        protected $total_items_amount,
        public    $customer,
        protected $onUpdateMode = false, /* in OrderUpdater you don't need to check usage limit of coupon again. it might had finish */
//        protected $wantError = true,
    ) {}


    public function validator()
    {
        $now = Carbon::now()->toDateString();
        $start_date = Carbon::parse($this->coupon->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($this->coupon->end_date)->format('Y-m-d');

        if (!app(CoreSettings::class)->get('order.coupon.calculate_coupon_with_discount_on_items')) {
            foreach ($this->carts as $cart) {
                if ($cart->discount_price > 0)
                    throw Helpers::makeValidationException('در سبد شما محصول تخفیف دار وجود دارد و نمیتوانید از این کد تخفیف استفاده کنید.');
            }
        }


        if (!$this->onUpdateMode){
            if (!(($start_date <= $now) && ($now <= $end_date)))
                throw Helpers::makeValidationException('تاریخ استفاده از کد تخفیف به پایان رسیده است.');
            if ($this->coupon->usage_limit <= $this->coupon->countCouponUsed($this->coupon->id))
                throw Helpers::makeValidationException('تعداد استفاده از این کد تخفیف به اتمام رسیده است');
            if ($this->coupon->usage_per_user_limit <= $this->coupon->countCouponUsedByCustomer($this->customer , $this->coupon->id))
                throw Helpers::makeValidationException('تعداد استفاده شما از این کد تخفیف به اتمام رسیده است');
        }

        if ($this->coupon->min_order_amount !== null && ($this->total_items_amount < $this->coupon->min_order_amount))
            throw Helpers::makeValidationException('حداقل مبلغ سبد خرید برای استفاده از این کد تخفیف '
                . $this->coupon->min_order_amount . ' تومان است');
    }



    public function calculator()
    {
        $type = $this->coupon->type; // نوع تخفیف عددی یا درصدی
        $amount = $this->coupon->amount; // مبلغ یا درصد تخفیف


        if ($this->coupon->categories()->count() != 0) {
            // so this coupon has categories ===========================================================================
            $discount = 0;
            $amount = 100;
            foreach ($this->carts as $cart) {
                $product = $cart->variety->product;
                $productCategoriesIds = $product->categories()->pluck('id')->toArray();
                $jointCategoryIds = $this->coupon->categories()->whereIn('categories.id', $productCategoriesIds)->get();

                if ($jointCategoryIds->count() == 0) {
                    // now we should search on parent categories of product categories.
                    $productCategoriesParentsIds = [];
                    foreach ($product->categories as $category) {
                        if ($parent = $category->parent()->first()) {
                            $productCategoriesParentsIds[] = $parent->id;
                        }
                    }
                    $jointCategoryIds = $this->coupon->categories()->whereIn('categories.id', $productCategoriesParentsIds)->get();

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
                        $jointCategoryIds = $this->coupon->categories()->whereIn('categories.id', $productCategoriesParentParentIds)->get();
                        if ($jointCategoryIds->count() == 0) {
                            continue;
                        }
                    }
                }

                // we select the least amounts in pivot.
                $selectedCategoryForThisVariety = $jointCategoryIds->sortBy(function ($item, $key) {
                    return $item->pivot->amount;
                })->first();

                $coreSettings = app(\Shetabit\Shopit\Modules\Core\Classes\CoreSettings::class);
                if (!$coreSettings->get('order.allow_coupon_with_discount') && $coreSettings->get('order.allow_coupon_mixed')) {
                    // we don't accept product with discount
                    if ($cart->discount_price == 0) {
                        $discount += ((($cart->price * $cart->quantity) * $selectedCategoryForThisVariety->pivot->amount) / 100);
                    }
                } else {
                    // we accept product with discount
                    $discount += ((($cart->price * $cart->quantity) * $selectedCategoryForThisVariety->pivot->amount) / 100);
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
                $totalPrice = 0;
                foreach ($this->carts as $cart) {
                    if (!$cart->discount_price) {
                        $totalPrice += $cart->price * $cart->quantity;
                    }
                }
            }

            if ($type == Coupon::DISCOUNT_TYPE_FLAT){
                return $this->returnFormat($amount, 'flat');
            }
            $discount = (int)round(($amount * $totalPrice) / 100);
            #if type == static::DISCOUNT_TYPE_PERCENTAGE
            return $this->returnFormat($discount, 'percentage', $amount.'%');
        }
    }


    private function returnFormat($amount, $type, $percentage = null)
    {
        return [
            'discount' => $amount,
            'type' => $type,
            'percentage' => $percentage,
        ];
    }


}
