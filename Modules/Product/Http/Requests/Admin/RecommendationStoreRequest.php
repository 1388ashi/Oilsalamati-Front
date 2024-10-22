<?php

namespace Modules\Product\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Product\Http\Requests\Admin\RecommendationStoreRequest as BaseRecommendationStoreRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Advertise\Entities\Advertise;
use Modules\Category\Entities\Category;
use Modules\Flash\Entities\Flash;
use Modules\Page\Entities\Page;
use Modules\Product\Entities\Product;
use Modules\Blog\Entities\Post;
use Modules\Core\Helpers\Helpers;
use Modules\Product\Entities\Recommendation;

class RecommendationStoreRequest extends FormRequest
{

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
        if (filled($this->linkable_type) && $this->linkable_type != 'self_link') {
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
                'IndexContactUs' => 'Custom\ContactUs',
            ];
            $this->merge([
                'linkable_type' => $array[$this->linkable_type]
            ]);
        }
    }
}
