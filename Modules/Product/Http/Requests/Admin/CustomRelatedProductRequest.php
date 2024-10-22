<?php

namespace Modules\Product\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CustomRelatedProductRequest extends FormRequest
{

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'related_id' => 'required|exists:products,id',
        ];
    }


    public function authorize()
    {
        return true;
    }
}
