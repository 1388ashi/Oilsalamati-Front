<?php

namespace Modules\Customer\Http\Requests\Customer;

//use Shetabit\Shopit\Modules\Customer\Http\Requests\Customer\CustomerStoreRequest as BaseCustomerStoreRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Admin\Entities\Admin;
use Modules\Customer\Entities\Customer;

class CustomerStoreRequest extends FormRequest
{
    public function rules()
    {
        $passRequired = Auth::user() instanceof Admin ? 'nullable' : 'required';

        return [
            'first_name' => 'nullable|string|min:3|max:120',
            'last_name' => 'nullable|string|min:3|max:120',
            'email' => 'nullable|email|unique:customers,email',
            'password' => "$passRequired|string|min:6",
            'mobile' => 'required|unique:customers,mobile',
            'national_code' => 'nullable|string|digits:10',
            'gender' => ['nullable', Rule::in(Customer::getAvailableGenders())],
            'card_number' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'newsletter' => 'nullable|boolean',
            'foreign_national' => 'nullable|boolean',
        ];
    }
}
