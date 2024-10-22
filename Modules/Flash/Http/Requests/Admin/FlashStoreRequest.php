<?php

namespace Modules\Flash\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Helpers\Helpers;
use Modules\Flash\Entities\Flash;
use Modules\Product\Entities\Product;
use Modules\Flash\Services\Validations\FlashProductsValidationService;
use Shetabit\Shopit\Modules\Core\Rules\ColorCode;
//use Shetabit\Shopit\Modules\Flash\Http\Requests\Admin\FlashStoreRequest as BaseFlashStoreRequest;

class FlashStoreRequest extends FormRequest
{
    public function passedValidation()
    {
        if($this->missing('discount') || !$this->get('discount',null)){
            // try to get set discount
            $defaultDiscount = Flash::getDefaultDiscount($this->get('discount_type'),0);
            $this->merge([
                'discount' => $defaultDiscount
            ]);
        }
        new FlashProductsValidationService($this->input('products'));
        foreach ($this->products as $key => $product) {
            if(($product['discount_type'] == Flash::DISCOUNT_TYPE_FLAT)){
                $baseProduct = Product::query()->findOrFail($product['id']);
                $varieties = $baseProduct->varieties;
                foreach ($varieties as $variety) {
                   if (($variety->price < $product['discount']) || ($variety->price < $this->discount)){
                       throw Helpers::makeValidationException('مقدار تخفیف از تنوع های محصول بیتشر می باشد.');
                   }
                }
            }
            if (($product['discount_type'] == Flash::DISCOUNT_TYPE_PERCENTAGE) && !($product['discount'] < 100 && $product['discount'] > 1)){
                $key  +=1;
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
            'nullable|integer|min:1' :
            'nullable|integer|min:1|max:100';


        $discountTypes = Flash::getAvailableDiscountTypes();

        return [
            'title' => 'bail|required|string|max:191|unique:flashes',
            'start_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:today',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
            'image' => ['required', 'file', 'image'],
            'mobile_image' => ['nullable', 'file', 'image'],
            'bg_image' => ['nullable', 'file', 'image'],
            'color' => ['nullable', 'string' , new ColorCode()],
            'preview_count' => 'nullable|integer',
            'timer' => 'required|boolean',
            'status' => 'required|boolean',
            'discount_type' => ['required', Rule::in($discountTypes)],
            'discount' => $discountRule,
            //products
            'products' => 'required|array',
            'products.*.id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    return $query->where('status', Product::STATUS_AVAILABLE);
                })
            ],
            'products.*.discount_type' => ['nullable', Rule::in($discountTypes)],
            'products.*.discount' => 'nullable|integer',
            'products.*.salable_max' => 'nullable|integer',
        ];
    }


    protected function prepareForValidation()
    {
        $products = [];
        $counter = 0;

        foreach ($this->products as $product) {
            if (!is_null($product['discount_type'])) {

                $products[$counter] = [
                    'id' => $product['id'],
                    'discount_type' => $product['discount_type'],
                    'discount' => str_replace(',', '', $product['discount'])
                ];

                $counter++;
            }
        }

        $this->merge([
            'timer' => $this->timer ? 1 : 0,
            'status' => $this->status ? 1 : 0,
            'products' => $products
        ]);
    }
}
