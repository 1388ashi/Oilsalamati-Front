<?php

namespace Modules\Product\Http\Requests\Customer;

//use Shetabit\Shopit\Modules\Product\Http\Requests\Customer\ListenChargeStoreRequest as BaseListenChargeStoreRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Product\Entities\Product;

class ListenChargeStoreRequest extends FormRequest
{
    public ?Product $product;

    public function rules()
    {
        return [

        ];
    }

    public function passedValidation()
    {
        $this->product = Product::findOrFail($this->route('product'));
    }
}
