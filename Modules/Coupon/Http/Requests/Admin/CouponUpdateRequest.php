<?php

namespace Modules\Coupon\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;
use Modules\Product\Services\Validations\DiscountValidationService;
use Modules\Product\Services\Validations\StoreCouponValidationService;
use Shetabit\Shopit\Modules\Coupon\Http\Requests\Admin\CouponUpdateRequest as BaseCouponUpdateRequest;

class CouponUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $code = Helpers::getModelIdOnPut('coupon');
        return [
            "title" => 'required|string',
            "code" => 'required|string|unique:coupons,code,'.$code,
            "start_date" => 'required|date_format:Y-m-d H:i',
            "end_date" => 'required|after_or_equal:start_date',
            "type" => ['nullable', Rule::in(Coupon::getAvailableTypes())],
            "amount" => 'nullable|integer',
            "usage_limit" => 'nullable|integer|min:1',
            "usage_per_user_limit" => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:1000',
            'categories' => 'nullable|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.amount' => 'required|integer|min:1',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    protected function prepareForValidation()
    {

        $this->merge([
            'amount' => str_replace(',', '', $this->input('amount')),
            'min_order_amount' => str_replace(',', '', $this->input('min_order_amount'))
        ]);


        if ($this->has('categories') && $this->categories) {
            $this->offsetUnset('type');
            $this->offsetUnset('amount');

            // prevent to store repetitive category
            $categoryIdsInRequest = [];
            foreach ($this->categories as $requestCategory) {
                if (in_array($requestCategory['id'], $categoryIdsInRequest)) {
                    throw Helpers::makeValidationException('دسته بندی تکراری انتخاب شده است');
                }
                $categoryIdsInRequest[] = $requestCategory['id'];
            }
        }
    }

    protected function passedValidation()
    {
        if (!$this->has('categories') && (!$this->amount || !$this->type)) {
            throw Helpers::makeValidationException('نوع و مقدار الزامی هستند');
        }
        (new StoreCouponValidationService($this))->check();
    }

}
