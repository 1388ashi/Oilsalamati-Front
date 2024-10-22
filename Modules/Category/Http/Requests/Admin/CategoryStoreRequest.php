<?php

namespace Modules\Category\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
//use Shetabit\Shopit\Modules\Category\Http\Requests\Admin\CategoryStoreRequest as BaseCategoryStoreRequest;

class CategoryStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title'             => 'required|string|min:1' ,
            'banner_link'       => 'nullable|string|min:1' ,
            'en_title'          => 'nullable|string|min:1' ,
            'description'       => 'nullable|string' ,
            'parent_id'         => 'nullable|exists:categories,id' ,
            'status'            => 'required|boolean' ,
            'special'           => 'required|boolean' ,
            'show_in_home'           => 'nullable|boolean' ,
            'meta_title'        => 'nullable|string' ,
            'meta_description'  => 'nullable|string' ,
            'attribute_ids'     => 'nullable|array',
            'attribute_ids.*'   => 'exists:attributes,id',
            'brand_ids'         => 'nullable|array',
            'brand_ids.*'       => 'exists:brands,id',
            'specification_ids' => 'nullable|array',
            'specification_ids.*' =>'exists:specifications,id',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'special' => $this->input('special') ?: false,
            'show_in_home' => $this->input('show_in_home') ?: false
        ]);
    }

}
