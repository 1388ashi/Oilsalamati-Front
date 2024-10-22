<?php

namespace Modules\Shipping\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Shipping\Http\Requests\Admin\ShippingCityAssignRequest as BaseShippingCityAssignRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingCityAssignRequest extends FormRequest
{
    public function rules()
    {
        return [
            'cities' => 'nullable|array',
            'cities.*.id' => 'bail|integer|exists:cities,id',
            'cities.*.price' => 'bail|nullable|integer',
        ];
    }
}
