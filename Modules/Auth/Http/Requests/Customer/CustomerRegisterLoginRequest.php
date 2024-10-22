<?php

namespace Modules\Auth\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Rules\IranMobile;
//use Shetabit\Shopit\Modules\Auth\Http\Requests\Customer\CustomerRegisterLoginRequest as BaseCustomerRegisterLoginRequest;

class CustomerRegisterLoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile' => ['required', 'digits:11', new IranMobile()],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
