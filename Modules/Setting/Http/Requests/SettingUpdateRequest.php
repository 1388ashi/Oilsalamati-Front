<?php

namespace Modules\Setting\Http\Requests;

//use Shetabit\Shopit\Modules\Setting\Http\Requests\SettingUpdateRequest as BaseSettingUpdateRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SettingUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '*' => 'nullable'
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

    protected function failedValidation(Validator $validator)
    {
        $data = [
            'messages' => $validator->errors()
        ];

        throw new HttpResponseException(response()->error('اطلاعات ورودی نامعتبر است', $data, 422));
    }
}
