<?php

namespace Modules\Category\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Category\Entities\Category;
use Modules\Core\Helpers\Helpers;
//use Shetabit\Shopit\Modules\Category\Http\Requests\Admin\CategoryUpdateRequest as BaseCategoryUpdateRequest;

class CategoryUpdateRequest extends FormRequest
{

    public function rules()
    {
        return [
            'title'                => 'required|string|min:1',
            'banner_link'          => 'nullable|string|min:1' ,
            'en_title'             => 'nullable|string|min:1',
            'description'          => 'nullable|string' ,
            'parent_id'            => 'nullable|exists:categories,id' ,
            'status'               => 'required|boolean' ,
            'special'              => 'required|boolean' ,
            'show_in_home'           => 'nullable|boolean' ,
            'meta_title'           => 'nullable|string' ,
            'meta_description'     => 'nullable|string' ,
            'attribute_ids'        => 'nullable|array',
            'attribute_ids.*'      => 'exists:attributes,id',
            'brand_ids'            => 'nullable|array',
            'brand_ids.*'          => 'exists:brands,id',
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



    // came from vendor ================================================================================================
    public function passedValidation()
    {
        if ($this->input('parent_id')) {
            $parentCategory = Category::findOrFail($this->input('parent_id'));
            $categoryId = Helpers::getModelIdOnPut('category');
            if (!$categoryId) {
                return;
            }
            if ($parentCategory->id == $categoryId) {
                throw Helpers::makeValidationException('خودش نمیتونه پدر خودش باشه !');
            }
            if ($parentCategory->parent_id == $categoryId) {
                throw Helpers::makeValidationException('نمیشه هردو پدر هم باشن !');
            }
            // جلوگیری از لوپ بینهایت
            $parentId = $parentCategory->parent_id;
            while ($parentId != null) {
                $tempMenu = Category::find($parentId);
                if (!$tempMenu) {
                    continue;
                }
                if ($tempMenu->parent_id == $categoryId) {
                    throw Helpers::makeValidationException('انتخاب پدر از نوه نتیجه نبیره و ... مجاز نیست');
                }
                $parentId = $tempMenu->parent_id;
            }
        }
    }

}
