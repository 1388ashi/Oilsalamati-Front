<?php

namespace Modules\Menu\Http\Requests;

//use Shetabit\Shopit\Modules\Menu\Http\Requests\MenuStoreRequest as BaseMenuStoreRequest;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Advertise\Entities\Advertise;
use Modules\Blog\Entities\Post;
use Modules\Category\Entities\Category;
use Modules\Core\Helpers\Helpers;
use Modules\Flash\Entities\Flash;
use Modules\Link\Services\LinkValidator;
use Modules\Menu\Entities\MenuItem;
use Modules\Page\Entities\Page;
use Modules\Product\Entities\Product;

class MenuStoreRequest extends FormRequest
{
    public $toggle = false;

    public function rules()
    {
        return [
            'title' => 'required',
            'link' => "nullable|string",
            'new_tab' => 'nullable',
            'parent_id' => 'nullable|exists:menu_items,id',
            'status' => 'nullable',
            'group_id' => 'required|exists:menu_groups,id',
            'icon' => 'nullable|image'
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
        $this->merge([
            'status' => (bool) $this->input('status', 0),
            'new_tab' => (bool) $this->input('new_tab', 0)
        ]);
    }
}
