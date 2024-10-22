<?php

namespace Modules\Product\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Product\Entities\CategoryProductSort;

class CategoryProductrtUpdate extends FormRequest
{

    public function rules()
    {
        $max = CategoryProductSort::query()->where('category_id',$this->category_id)->max('order');
        return [
            'category_id' => 'required|exists:categories,id',
            'product_id' => 'required|exists:products,id',
            'orders' => 'required',
        ];
    }

    public function authorize()
    {
        return true;
    }

    // public function messages()
    // {
    //     return [
    //         'order.between' => 'ترتیب وارد شده صحیح نمیباشد'
    //     ];
    // }
}
