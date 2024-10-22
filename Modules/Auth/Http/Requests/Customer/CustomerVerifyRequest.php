<?php

namespace Modules\Auth\Http\Requests\Customer;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Core\Rules\IranMobile;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\SmsToken;
use Shetabit\Shopit\Modules\Auth\Http\Requests\Customer\CustomerVerifyRequest as BaseCustomerVerifyRequest;

class CustomerVerifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //from unregistered user to register
        //from forgot password to reset password
        //form login with code to logged-in

        return [
            'mobile' => ['required', 'digits:11', new IranMobile()],
            'sms_token' => 'required',
            'type' => ['required', Rule::in(['register', 'forget', 'login'])]
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


    public function passedValidation()
    {
        $smsToken = SmsToken::where('mobile', $this->mobile)->first();
        if (env('APP_ENV') != 'local') {
            // فقط در صورتی که لوکال نباشد این شروط تست می شود و در صورتیکه لوکال باشد بدون نمایش خطا از این مرحله رد شده و ثبت نام انجام می شود
            if (!$smsToken) {
                throw ValidationException::withMessages([
                    'mobile' => ['کاربری با این مشخصات پیدا نشد!']
                ]);
            } elseif ($smsToken->token !== $this->sms_token) {
                throw ValidationException::withMessages([
                    'sms_token' => ['کد وارد شده نادرست است!']
                ]);
            } elseif (Carbon::now()->gt($smsToken->expired_at)) {
                throw ValidationException::withMessages([
                    'sms_token' => ['کد وارد شده منقضی شده است!']
                ]);
            }
        }

        if ($this->type !== 'register') {
            $customer = Customer::where('mobile', $this->mobile)->first();
        }

        $this->merge([
            'smsToken' => $smsToken,
            'customer' => $customer ?? null
        ]);
    }
}
