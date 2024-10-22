<?php

namespace Modules\Shipping\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Shipping\Http\Requests\Admin\ShippingStoreRequest as BaseShippingStoreRequest;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;

class ShippingStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'bail|required|string|max:191|unique:shippings',
            'default_price' => 'nullable|integer|min:0',
            'free_threshold' => 'nullable|integer|min:1000',
            'minimum_delay' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:191',
            'logo' => 'required|image|max:10000',
            'status' => 'required|boolean',
            'provinces' => 'nullable|array',
            'provinces.*.id' => 'bail|required|integer|exists:provinces,id',
            'provinces.*.price' => 'bail|nullable|integer|min:0',
            'customer_roles.*.id' => 'bail|required|integer|exists:customer_roles,id',
            'customer_roles.*.amount' => 'bail|required|integer|min:0',
            'cities' => 'nullable|array',
            'cities.*.id' => 'bail|integer|exists:cities,id',
            'cities.*.price' => 'bail|nullable|integer',
            'packet_size' => 'required|integer',
            'more_packet_price' => 'required|integer|min:0',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->status ? 1 : 0,
            'default_price' => $this->filled('default_price') ? str_replace(',', '', $this->default_price) : null,
            'free_threshold' => $this->filled('free_threshold') ? str_replace(',', '', $this->free_threshold) : null,
            'more_packet_price' => str_replace(',', '', $this->more_packet_price),
        ]);
    }
}
