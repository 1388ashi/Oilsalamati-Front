<?php

namespace Modules\Product\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Product\Entities\Product;

class OrderProductUpdateRequest extends FormRequest
{

    public function rules()
    {
        return [
            'order' => 'required|integer|between:1,' . Product::getMaxOrder(),
        ];
    }


    public function authorize()
    {
        return true;
    }
}
