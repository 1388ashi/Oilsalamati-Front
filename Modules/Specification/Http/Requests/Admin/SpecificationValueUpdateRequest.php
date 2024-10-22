<?php

namespace Modules\Specification\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Specification\Http\Requests\Admin\SpecificationValueUpdateRequest as BaseSpecificationValueUpdateRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Specification\Entities\SpecificationValue;

class SpecificationValueUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $specificationValue = SpecificationValue::findOrFail($this->route('specification_value'));
        return [
            'value' => [
                'required',
                'string',
                'max:191',
                Rule::unique('specification_values')->where(function ($query) use ($specificationValue) {
                    return $query->where('specification_id', $specificationValue->specification_id);
                })
            ]
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

