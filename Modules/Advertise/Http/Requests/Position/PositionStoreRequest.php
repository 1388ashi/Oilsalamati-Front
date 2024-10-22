<?php

namespace Modules\Advertise\Http\Requests\Position;

use Illuminate\Foundation\Http\FormRequest;
use Shetabit\Shopit\Modules\Advertise\Http\Requests\Position\PositionStoreRequest as BasePositionStoreRequest;

class PositionStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'label' => 'required|string',
            'key' => 'required|string|unique:advertisement_positions,key',
            'description' => 'required|string',
            'status' => 'required|in:0,1',
            'height' => 'required|integer',
            'width' => 'required|integer',
        ];
    }
}
