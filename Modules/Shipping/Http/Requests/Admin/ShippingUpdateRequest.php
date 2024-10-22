<?php

namespace Modules\Shipping\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Shipping\Http\Requests\Admin\ShippingUpdateRequest as BaseShippingUpdateRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:191',
                Rule::unique('shippings')->ignore($this->route('shipping'))
            ],
            'default_price' => 'nullable|integer|min:0',
            'free_threshold' => 'nullable|integer|min:1000',
            'minimum_delay' => 'nullable|integer|min:1',
            'logo' => 'nullable|image|max:10000',
            'status' => 'required|boolean',
            'description' => 'nullable|string|max:191',
            'provinces.*.id' => 'bail|required|integer|exists:provinces,id',
            'provinces.*.price' => 'bail|nullable|integer|min:0',
            'customer_roles.*.id' => 'bail|required|integer|exists:customer_roles,id',
            'customer_roles.*.amount' => 'bail|required|integer|min:0',
            'cities' => 'nullable|array',
            'cities.*.id' => 'bail|integer|exists:cities,id',
            'cities.*.price' => 'bail|nullable|integer',
            'packet_size' => 'required|integer|min:0',
            'more_packet_price' => 'required|integer|min:0',
        ];
    }


    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->status ? 1 : 0,
            'free_threshold' => str_replace(',', '', $this->input('free_threshold'))
        ]);
    }
}
