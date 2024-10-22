<?php

namespace Modules\Product\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Product\Http\Requests\Admin\GiftUpdateRequest as BaseGiftUpdateRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
use Shetabit\Shopit\Modules\Product\Entities\Variety;

class GiftUpdateRequest extends FormRequest
{

    public function rules()
    {
        $id = Helpers::getModelIdOnPut('gift');
        return [
            'name' => 'required|string|unique:gifts,name,' . $id,
            'status' => 'required|boolean',
            'image' => 'nullable|image',
            'start_date' => 'nullable|date_format:Y-m-d H:i',
            'end_date' => 'required|date_format:Y-m-d H:i',
        ];
    }
}
