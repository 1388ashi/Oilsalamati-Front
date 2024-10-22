<?php

namespace Modules\Area\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
//use Shetabit\Shopit\Modules\Area\Http\Requests\Admin\CityStoreRequest as BaseCityStoreRequest;

class CityStoreRequest extends FormRequest
{
    public function rules()
    {
        return  [
            'name' => ["required","string","unique:cities"],
            'province_id' => ["required","exists:provinces,id"],
            'status' => ["nullable","boolean"],

        ];
    }
}
