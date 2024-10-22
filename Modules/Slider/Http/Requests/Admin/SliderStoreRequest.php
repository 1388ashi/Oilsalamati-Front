<?php

namespace Modules\Slider\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Slider\Http\Requests\Admin\SliderStoreRequest as BaseSliderStoreRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
use Modules\Link\Services\LinkValidator;
use Modules\Advertise\Entities\Advertise;
use Modules\Blog\Entities\Post;
use Modules\Category\Entities\Category;
use Modules\Flash\Entities\Flash;
use Modules\Page\Entities\Page;
use Modules\Product\Entities\Product;
use Modules\Slider\Entities\Slider;

class SliderStoreRequest extends FormRequest
{
    public function rules()
    {
        $put = $this->isMethod('put') ? 'nullable' : 'required';

        return [
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'group' => 'required|string',
            'status' => 'required|boolean',
            'image' => "$put|image",
            'link' => 'nullable',
            'linkable_id' => 'nullable',
            'linkable_type' => 'nullable',
        ];
    }

    public function passedValidation()
    {
        $groupExists = in_array($this->group, config('slider.groups'));
        if (!$groupExists) {
            throw Helpers::makeValidationException('گروه مورد نظر یافت نشد');
        }
    }

    public function prepareForValidation()
    {
        if (filled($this->linkable_type) && $this->linkable_type != 'self_link' && $this->linkable_type != 'self_link2') {
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
        $this->merge([
            'status' => $this->input('status') ? true : false
        ]);
    }
}

