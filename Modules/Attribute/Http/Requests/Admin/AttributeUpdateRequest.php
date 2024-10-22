<?php

namespace Modules\Attribute\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Attribute\Entities\Attribute;
//use Shetabit\Shopit\Modules\Attribute\Http\Requests\Admin\AttributeUpdateRequest as BaseAttributeUpdateRequest;
use Shetabit\Shopit\Modules\Core\Helpers\Helpers;

class AttributeUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * در ویرایش نوع نمیتواند تغییر کند
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('attributes')->ignore($this->route('attribute'))],
            'label' => 'required|string|max:191',
            'show_filter' => 'required|boolean',
            'public' => 'required|boolean',
            'status' => 'required|boolean',
            'style' => 'nullable|string|in:select,box,image',
            // برای دادن مقادیر جدید ولیوز میدیم در غیر اینصورت برای ویرایش ادیتد ولیو
            'values' => 'nullable|array',
            'values.*' => 'nullable|string|max:191',
            'edited_values' => 'nullable|array',
            'edited_values.*' => 'nullable',
            'edited_values.*.id' => 'required_if:type,select',
            'edited_values.*.value' => 'required_if:type,select|string|max:191',
        ];
    }


    protected function prepareForValidation()
    {
        $this->merge([
            'show_filter' => $this->show_filter ? 1 : 0,
            'status' => $this->status ? 1 : 0,
            'public' => $this->public ? 1 : 0
        ]);
    }

    public function passedValidation()
    {
        $attribute = $this->route('attribute');
        // مقادیری که حذف شده
        foreach ($this->input('deleted_values', []) as $editedValue) {
            $attributeValue = $attribute->values()->find($editedValue['id']);
            if (!$attributeValue) {
                return;
            }
            if (DB::table('attribute_variety')->where('attribute_value_id', $attributeValue->id)->exists()) {
                throw Helpers::makeValidationException('به علت وصل بودن مقدار ویژگی ' . $attributeValue->value .
                    ' امکان حذف آن وجود ندازد');
            }
        }
    }
}
