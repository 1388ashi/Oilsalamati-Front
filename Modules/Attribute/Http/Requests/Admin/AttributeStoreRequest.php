<?php

namespace Modules\Attribute\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shetabit\Shopit\Modules\Attribute\Http\Requests\Admin\AttributeStoreRequest as BaseAttributeStoreRequest;

class AttributeStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:191|unique:attributes',
            'label' => 'required|string|max:191',
            'type' => ['required', 'string', 'max:50', Rule::in(['text', 'select'])],
            'show_filter' => 'required|boolean',
            'style' => 'nullable|string|in:select,box,image',
            'public' => 'required|boolean',
            'status' => 'required|boolean',
            // 'values' => 'required_if:type,select|array',
            // 'values.*' => 'required_if:type,select|string|max:191'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'show_filter' => $this->show_filter ? 1 : 0,
            'status' => $this->status ? 1 : 0,
            'public' => $this->status ? 1 : 0,
        ]);
    }
}
