<?php

namespace Modules\Blog\Http\Requests\Admin\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Blog\Entities\Post;
//use Shetabit\Shopit\Modules\Blog\Http\Requests\Admin\Post\PostStoreRequest as BasePostStoreRequest;

class PostStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:191',
            'post_category_id' => 'bail|required|integer|exists:post_categories,id',
            'summary' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'image' => 'nullable|image|max:8000',
            'meta_description' => 'nullable|string|max:1000',
            'status' => ['required',
                Rule::in([Post::STATUS_DRAFT, Post::STATUS_PENDING, Post::STATUS_PUBLISHED, Post::STATUS_UNPUBLISHED])],
            'special' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:191',
            'read_time' => 'nullable|integer|min:0',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'bail|nullable|integer|exists:products,id'
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

    protected function prepareForValidation()
    {
        $this->merge([
            'special' => $this->special ? 1 : 0
        ]);
    }
}
