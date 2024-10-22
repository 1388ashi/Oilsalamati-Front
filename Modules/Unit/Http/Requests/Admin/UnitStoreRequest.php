<?php

namespace Modules\Unit\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Unit\Http\Requests\Admin\UnitStoreRequest as BaseUnitStoreRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Unit\Entities\Unit;

class UnitStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:191|unique:units',
            'symbol' => 'required|string|max:191',
            'precision' => ['required', 'numeric', Rule::in(Unit::PRECISION)],
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
