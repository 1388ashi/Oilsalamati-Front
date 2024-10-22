<?php

namespace Modules\Customer\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Customer\Http\Requests\Admin\CustomerDepositRequest as BaseCustomerDepositRequest;

use Illuminate\Foundation\Http\FormRequest;

class CustomerDepositRequest extends FormRequest
{
  public function prepareForValidation(){
    $this->merge([
        'amount' => str_replace(',', '', $this->input('amount')),
    ]);
  }
    public function rules()
    {
        return array_merge([
            'customer_id' => 'required|integer|exists:customers,id',
            'amount' => 'required|integer|min:1000',
            'description' => 'nullable|string',
        ], $this->customRules());
    }

    public function customRules()
    {
        return [];
    }
}

