<?php

namespace Modules\Product\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Advertise\Entities\Advertise;
use Modules\Blog\Entities\Post;
use Modules\Category\Entities\Category;
use Modules\Flash\Entities\Flash;
use Modules\Page\Entities\Page;
use Modules\Product\Entities\Product;

class RecommendationUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'group_name' => ['required', 'regex:/^[a-zA-Z0-9\s]*$/'],
            'title' => 'nullable',
            'link' => "nullable|string",
            'linkable_id' => "nullable|string",
            'linkable_type' => "nullable|string",
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,png',
            'images_mobile' => 'nullable|array',
            'images_mobile.*' => 'nullable|image|mimes:jpg,png',
        ];
    }
    public function prepareForValidation()
    {
        if (filled($this->linkable_type) && $this->linkable_type != 'self_link2') {
            $array = [
                'IndexPost' => Post::class,
                'Post' => Post::class,
                'Category' => Category::class,
                'Product' => Product::class,
                'IndexProduct' => Product::class,
                'Flash' => Flash::class,
                'Page' => Page::class,
                'IndexAdvertise' => Advertise::class,
                'Advertise' => Advertise::class,
                'IndexAboutUs' => 'Custom\AboutUs',
            ];
            $this->merge([
                'linkable_type' => $array[$this->linkable_type]
            ]);
        }
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
