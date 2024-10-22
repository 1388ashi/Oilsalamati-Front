<?php

namespace Modules\Product\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Product\Http\Requests\Admin\GiftStoreRequest as BaseGiftStoreRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shetabit\Shopit\Modules\Core\Helpers\Helpers;
use Shetabit\Shopit\Modules\Product\Entities\Variety;

class GiftStoreRequest extends FormRequest
{

    public function rules()
    {
        return [
            'name' => 'required|string|unique:gifts,name',
            'status' => 'required|boolean',
            'image' => 'required|image',
            'start_date' => 'nullable|date_format:Y-m-d H:i|after_or_equal:'.now()->subMinutes(2),
            'end_date' => 'required|date_format:Y-m-d H:i|after:start_date',
        ];
    }
}
