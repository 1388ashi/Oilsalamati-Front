<?php

namespace Modules\Coupon\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Coupon\Entities\Coupon;
//use Shetabit\Shopit\Modules\Coupon\Http\Requests\Customer\CouponVerifyRequest as BaseCouponVerifyRequest;

class CouponVerifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required|exists:coupons'
        ];
    }

//    public function passedValidation()
//    {
//        Coupon::dontAllowCouponAndDiscountTogether();
//    }
}
