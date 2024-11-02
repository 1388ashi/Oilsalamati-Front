<?php

namespace Modules\Auth\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Modules\Core\Rules\IranMobile;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\SmsToken;
//use Shetabit\Shopit\Modules\Auth\Http\Requests\Customer\CustomerRegisterRequest as BaseCustomerRegisterRequest;
use Modules\Core\Classes\CoreSettings;

class CustomerRegisterRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $passwordRequired = app(CoreSettings::class)->get('auth.password.required', true);
        $passwordValidation = $passwordRequired ? ['required', Password::min(6)] : ['nullable'];

        return [
            'mobile' => ['required', 'digits:11', new IranMobile()],
            'first_name' => 'nullable|string|max:191',
            'last_name' => 'nullable|string|max:191',
            'password' => $passwordValidation,
            'foreign_national' => 'nullable|boolean',
            'national_code' => 'nullable|string|size:10|unique:customers,national_code',
            'email' => 'nullable|email|max:191',
            'newsletter' => 'nullable|boolean',
            'sms_token' => 'required|string',
            'reprezentant_mobile' => 'nullable|exists:customers,mobile',
            'reprezentant_code' => 'nullable|exists:customers,invite_code'
        ];
    }

        protected function passedValidation()
    {
        //Check customer
        $registeredCustomer = Customer::where('mobile', $this->mobile)->whereNotNull('password')->count();
        if ($registeredCustomer >= 1) {
            throw ValidationException::withMessages([
                'mobile' => ['کاربر قبلا ثبت نام شده است.']
            ]);
        }

        //Check SMS token
        $smsToken = SmsToken::where('mobile', $this->mobile)->first();
        if (! $smsToken) {
            return redirect()->back()->with(['status' => 'danger','کاربری با این مشخصات پیدا نشد']);
        } elseif (! $smsToken->verified_at) {
            return redirect()->back()->with(['status' => 'danger','شماره موبایل کاربر احراز نشده است.']);
        }
        if ($this->reprezentant_mobile) {
            if ($this->mobile == $this->reprezentant_mobile ||
                DB::table('customers')->where('mobile',$this->reprezentant_mobile)->count() != 1)
            {
                throw ValidationException::withMessages([ 'reprezentant_mobile' => ['شماره همراه معرف معتبر نیست'] ]);
            }
        }

        // if there was reprezentant_code we convert it to the reprezentant_mobile. we didn't change controller codes
        if ($this->has('reprezentant_code') && $this->reprezentant_code && !$this->reprezentant_mobile) {
            $this->merge([
                'reprezentant_mobile' => DB::table('customers')->where('invite_code', $this->reprezentant_code)->pluck('mobile')->first()
            ]);
        }


        $this->merge(['invite_code' => Customer::getUniqueInviteCode()]);
    }

}
