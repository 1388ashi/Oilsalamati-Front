<?php

namespace Modules\Customer\Http\Requests\Customer;

//use Shetabit\Shopit\Modules\Customer\Http\Requests\Customer\CustomerUpdateRequest as BaseCustomerUpdateRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Rules\Base64Image;
use Modules\Customer\Entities\Customer;

class CustomerUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = Helpers::getModelIdOnPut('customer');

        return [
            'first_name' => 'nullable|string|min:3|max:120',
            'last_name' => 'nullable|string|min:3|max:120',
            'email' => 'nullable|unique:customers,email,'.$id,
            'password' => 'nullable|string|min:6',
            'mobile' => 'nullable',
            'national_code' => 'nullable|string|size:10|digits:10',
            'gender' => ['nullable', Rule::in(Customer::getAvailableGenders())],
            'card_number' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'newsletter' => 'nullable|boolean',
            'foreign_national' => 'nullable|boolean',
            'image' => ['string', new Base64Image()],
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
    protected function passedValidation()
    {
        if ($this->password == null) $this->request->remove('password');
        if ($this->mobile == null) $this->request->remove('mobile');
    }
}
