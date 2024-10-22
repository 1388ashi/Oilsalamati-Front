<?php

namespace Modules\Product\Services\Validations;

use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;

class StoreCouponValidationService
{
    protected $couponHasCategory;
    protected $request;

    public function __construct($request)
    {
        if ($request->has('categories') && $request->categories)
            $this->couponHasCategory = true;
        else $this->couponHasCategory = false;

        $this->request = $request;
    }

    public function check()
    {
        $request = $this->request;
        if (!$this->couponHasCategory) {
            // normal coupon
            $this->valuesChecker($request->type, $request->amount);
            if ($request->type == Coupon::DISCOUNT_TYPE_FLAT) {
                if (!$request->has('min_order_amount') && !$request->min_order_amount) {
                    throw Helpers::makeValidationException('هنگامی که نوع تخفیف را از نوع مبلغ تعریف میکنید حداقل مبلغ سبد خرید الزامی است');
                }
            }
        } else {
            // coupon with category
            foreach ($request->categories as $requestCategory) {
                $this->valuesChecker(Coupon::DISCOUNT_TYPE_PERCENTAGE, $requestCategory['amount']); /* all coupons with category are in percentage type */
//                if ($requestCategory['type'] == Coupon::DISCOUNT_TYPE_FLAT) $hasFlatType = true;
            }
        }
    }

    private function valuesChecker($type, $amount)
    {
        if ($type == Coupon::DISCOUNT_TYPE_PERCENTAGE) {
            if ($amount <= 0 || $amount > 100) {
                throw Helpers::makeValidationException('درصد تخفیف باید بین صفر تا صد باشد');
            }
        } elseif ($type == Coupon::DISCOUNT_TYPE_FLAT) {
            if ($amount <= 0) {
                throw Helpers::makeValidationException('مقدار تخفیف نمیتواند کمتر مساوی صفر باشد');
            }
        }
    }
}
