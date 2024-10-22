<?php

namespace Modules\Blog\Http\Requests\Admin\PostCategory;

use Illuminate\Foundation\Http\FormRequest;
//use Shetabit\Shopit\Modules\Blog\Http\Requests\Admin\PostCategory\PostCategoryStoreRequest as BasePostCategoryStoreRequest;

class PostCategoryStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:191|unique:post_categories',
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
