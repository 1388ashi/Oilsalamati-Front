<?php

namespace Modules\Area\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
//use Shetabit\Shopit\Modules\Area\Http\Requests\Admin\CityUpdateRequest as BaseCityUpdateRequest;

class CityUpdateRequest extends FormRequest
{
    public function rules()
    {
        $cityId = Helpers::getModelIdOnPut('city');

        return  [
            'name' => ["required","string","unique:cities,id," . $cityId],
            'province_id' => ["required","exists:provinces,id"],
            'status' => ["nullable","boolean"],

        ];
    }
}
