<?php

namespace Modules\Area\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Shetabit\Shopit\Modules\Area\Http\Requests\Admin\ProvinceStoreRequest as BaseProvinceStoreRequest;

class ProvinceStoreRequest extends FormRequest
{
    public function rules()
    {
        return  [
            'name' => ["required","string","unique:provinces"],
            'status' => ["nullable","boolean"],
        ];
    }
}
