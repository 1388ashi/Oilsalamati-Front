<?php

namespace Modules\Area\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
//use Shetabit\Shopit\Modules\Area\Http\Requests\Admin\ProvinceUpdateRequest as BaseProvinceUpdateRequest;

class ProvinceUpdateRequest extends FormRequest
{
    public function rules()
    {
        $provinceId = Helpers::getModelIdOnPut('province');

        return  [
            'name' => ["required","string","unique:provinces,id," . $provinceId],
            'status' => ["nullable","boolean"],
        ];
    }
}
