<?php

namespace Modules\Flash\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Helpers\Helpers;
use Modules\Flash\Entities\Flash;
use Modules\Flash\Services\Validations\FlashProductsValidationService;
use Modules\Product\Entities\Product;
use Shetabit\Shopit\Modules\Core\Rules\ColorCode;
use Shetabit\Shopit\Modules\Flash\Http\Requests\Admin\FlashUpdateRequest as BaseFlashUpdateRequest;

class FlashUpdateRequest extends FormRequest
{
    public function passedValidation()
    {

        foreach ($this->products as $key => $product) {
            if ($this->discount == null) {
                if (!($product['discount'] || $product['discount_type'])
                    || ($product['discount'] == null || $product['discount_type'] == null)
                ) {
                    throw Helpers::makeValidationException('فیلد تخفیف اجباری است');
                }
            }

            if (($product['discount_type'] == Flash::DISCOUNT_TYPE_FLAT)) {
                $baseProduct = Product::query()->findOrFail($product['id']);
                $varieties = $baseProduct->varieties;
                foreach ($varieties as $variety) {
                    if (($variety->price < $product['discount']) || ($variety->price < $this->discount)) {
                        throw Helpers::makeValidationException('مقدار تخفیف از تنوع های محصول بیتشر می باشد.');
                    }
                }
            }
            if (($product['discount_type'] == Flash::DISCOUNT_TYPE_PERCENTAGE) && !($product['discount'] < 100 && $product['discount'] > 1)) {
                $key += 1;
                throw Helpers::makeValidationException("تخفیف محصول شمار {$key} باید بین 1 تا 100 باشد");
            }
        }
    }







    // came from vendor ================================================================================================
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $discountRule = $this->discount_type === 'flat' ?
            'nullable|integer|min:1|' :
            'nullable|integer|min:1|max:100';

        $discountTypes = Flash::getAvailableDiscountTypes();

        return [
            'title' => ['required', 'string', 'max:191', Rule::unique('flashes')->ignore($this->route('flash'))],
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s',
            'image' => ['nullable', 'file', 'image'],
            'mobile_image' => ['nullable', 'file', 'image'],
            'bg_image' => ['nullable', 'file', 'image'],
            'color' => ['nullable', 'string' , new ColorCode()],
            'preview_count' => 'nullable|integer',
            'timer' => 'required|boolean',
            'status' => 'required|boolean',
            'discount_type' => ['nullable', Rule::in($discountTypes)],
            'discount' => $discountRule,
            //products
            'products' => 'required|array',
            'products.*.id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('products', 'id')
            ],
            'products.*.discount_type' => ['required', Rule::in($discountTypes)],
            'products.*.discount' => 'nullable|integer',
            'products.*.salable_max' => 'nullable|integer',
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
            'timer' => $this->timer ? 1 : 0,
            'status' => $this->status ? 1 : 0
        ]);
    }



}
