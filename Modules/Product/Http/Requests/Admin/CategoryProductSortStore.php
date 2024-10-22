<?php

namespace Modules\Product\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryProductSortStore extends FormRequest
{
    public function rules()
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'product_id' => 'required|exists:products,id',
//            'order'=> 'required|integer'
        ];
    }


    public function authorize()
    {
        return true;
    }
}
