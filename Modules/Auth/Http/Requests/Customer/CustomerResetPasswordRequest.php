<?php

namespace Modules\Auth\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Modules\Core\Rules\IranMobile;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\SmsToken;
//use Shetabit\Shopit\Modules\Auth\Http\Requests\Customer\CustomerResetPasswordRequest as BaseCustomerResetPasswordRequest;

class CustomerResetPasswordRequest extends FormRequest
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
            'password' => ['required', Password::min(6), 'confirmed'],
            'sms_token' => 'nullable'
        ];
    }

    /**
     * @throws ValidationException
     */
    public function passedValidation()
    {
        if ($this->sms_token) {
            $customer = Customer::where('mobile', $this->mobile)->first();
            if (! $customer) {
                throw ValidationException::withMessages([
                    'mobile' => ['کاربری پیدا نشد!']
                ]);
            }

            //Check SMS token
            $smsToken = SmsToken::where('mobile', $this->mobile)->first();
            if (! $smsToken) {
                throw ValidationException::withMessages([
                    'mobile' => ['کاربری با این مشخصات پیدا نشد!']
                ]);
            } elseif (! $smsToken->verified_at) {
                throw ValidationException::withMessages([
                    'mobile' => ['شماره موبایل کاربر احراز نشده است.']
                ]);
            }

            $this->merge([
                'customer' => $customer
            ]);
        }
    }
}
