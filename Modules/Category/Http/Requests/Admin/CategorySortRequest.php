<?php

namespace Modules\Category\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Shetabit\Shopit\Modules\Category\Http\Requests\Admin\CategorySortRequest as BaseCategorySortRequest;

class CategorySortRequest extends FormRequest
{
    public function rules()
    {
        return [
            'categories' => 'required',
        ];
    }

    protected function passedValidation()
    {

    }
}
