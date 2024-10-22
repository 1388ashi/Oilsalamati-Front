<?php

namespace Modules\Brand\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
//use Shetabit\Shopit\Modules\Brand\Http\Requests\Admin\BrandUpdateRequest as BaseBrandUpdateRequest;

class BrandUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $brandId = Helpers::getModelIdOnPut('brand');

        return [
            'name' => 'required|string|unique:brands,name,'.$brandId,
            'status' => 'required|boolean',
            'show_index' => 'required|boolean',
            'description' => 'nullable|string',
            'image' =>'nullable|file|mimes:jpg,jpeg,png'
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
}
