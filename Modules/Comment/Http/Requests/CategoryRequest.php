<?php

namespace Modules\Comment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
//use Shetabit\Shopit\Modules\Comment\Http\Requests\CategoryRequest as BaseCategoryRequest;

class CategoryRequest extends FormRequest
{
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'parent_id' => 'nullable|exists:categories,id',
            'model' => 'required|string',
        ];
    }
}
