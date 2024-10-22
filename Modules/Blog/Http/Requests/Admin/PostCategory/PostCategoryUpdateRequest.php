<?php

namespace Modules\Blog\Http\Requests\Admin\PostCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
//use Shetabit\Shopit\Modules\Blog\Http\Requests\Admin\PostCategory\PostCategoryUpdateRequest as BasePostCategoryUpdateRequest;

class PostCategoryUpdateRequest extends FormRequest
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
                'max:191',
                Rule::unique('post_categories')->ignore($this->route('post_category'))
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

    public function prepareForValidation()
    {
        $this->merge([
            'status' => $this->status ? 1 : 0
        ]);
    }
}
