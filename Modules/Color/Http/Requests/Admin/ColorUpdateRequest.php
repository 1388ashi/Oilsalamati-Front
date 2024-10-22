<?php

namespace Modules\Color\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Rules\ColorCode;
//use Shetabit\Shopit\Modules\Color\Http\Requests\Admin\ColorUpdateRequest as BaseColorUpdateRequest;

class ColorUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('colors')->ignore($this->route('color'))
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('colors')->ignore($this->route('color')),
                new ColorCode()
            ],
            'status' => 'required|boolean'
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

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->status ? 1 : 0
        ]);
    }
}
