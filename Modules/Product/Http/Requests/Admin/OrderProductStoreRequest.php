<?php

namespace Modules\Product\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Book\Entities\Book;
use Modules\Product\Entities\Product;

class OrderProductStoreRequest extends FormRequest
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
