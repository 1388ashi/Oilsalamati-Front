<?php

namespace Modules\Order\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OrderGiftRangeUpdateRequest extends FormRequest
{

    public function prepareForValidation()
    {
        $this->merge([
            'price' => str_replace(',', '', $this->input('price')),
            'min_order_amount' => str_replace(',', '', $this->input('min_order_amount'))
        ]);
    }

    public function rules()
    {
        return [
            'title' => 'required|min:3',
            'description' => 'nullable',
            'price' => 'required|integer|min:1000',
            'image' => 'nullable|image',
            'min_order_amount' => 'required|integer|min:1000',
        ];
    }


    public function authorize()
    {
        return true;
    }
}
