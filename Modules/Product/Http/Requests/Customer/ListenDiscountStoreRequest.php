<?php

namespace Modules\Product\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Product\Entities\Product;

class ListenDiscountStoreRequest extends FormRequest
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
