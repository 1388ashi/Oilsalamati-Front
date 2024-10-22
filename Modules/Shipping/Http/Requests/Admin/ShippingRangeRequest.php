<?php

namespace Modules\Shipping\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShippingRangeRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'lower' => str_replace(',', '', $this->lower) ,
            'higher' => str_replace(',', '', $this->higher),
            'amount' => str_replace(',', '', $this->amount),
        ]);
    }
    public function rules()
    {
        return [
            'lower' => 'required|integer|min:0',
            'higher' => 'required|integer',
            'shipping_id' => 'required|integer|exists:shippings,id',
            'amount' => 'required|integer|min:0'
        ];
    }
}
