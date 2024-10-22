<?php

namespace Modules\Customer\Http\Requests\Customer;

//use Shetabit\Shopit\Modules\Customer\Http\Requests\Customer\ChangePasswordRequest as BaseChangePasswordRequest;

use Hash;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'old_password' => [
                'required',
                'string',
                'min:6',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, $this->user()->password)) {
                        $fail("کلمه عبور قبلی اشتباه است.");
                    }
                }
            ],
            'password' => 'required|string|min:6|max:50|different:old_password|confirmed',
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
