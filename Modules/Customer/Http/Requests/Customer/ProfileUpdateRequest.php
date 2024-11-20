<?php

namespace Modules\Customer\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Customer\Entities\Customer;
//use Shetabit\Shopit\Modules\Customer\Http\Requests\Customer\ProfileUpdateRequest as BaseProfileUpdateRequest;

/**
 * @property mixed $first_name
 * @property mixed $last_name
 * @property mixed $email
 * @property mixed $national_code
 * @property mixed $gender
 * @property mixed $card_number
 * @property mixed $birth_date
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $customer = \Auth::guard('customer-api')->user();

        return [
            'first_name' => 'nullable|string|max:191',
            'last_name' => 'nullable|string|max:191',
            'email' => ['nullable', 'email', 'max:191'],
            'national_code' => [
                'nullable',
                'digits:10',
            ],
            'gender' => ['nullable', Rule::in(Customer::getAvailableGenders())],
            'card_number' => 'nullable|digits:16',
            'birth_date' => 'nullable|date_format:Y-m-d|before:'.now(),
            'newsletter' => 'required|boolean',
            'foreign_national' => 'required|boolean',
            'password' => 'nullable|string|min:6|max:50'
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

    protected function prepareForValidation()
    {
        $this->merge([
            'newsletter' => $this->newsletter ? 1 : 0,
            'foreign_national' => $this->foreign_national ? 1 : 0,
        ]);
    }
}
