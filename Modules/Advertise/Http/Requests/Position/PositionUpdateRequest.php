<?php

namespace Modules\Advertise\Http\Requests\Position;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
use Shetabit\Shopit\Modules\Advertise\Http\Requests\Position\PositionUpdateRequest as BasePositionUpdateRequest;

class PositionUpdateRequest extends FormRequest
{
    public function rules()
    {
        $positionAdvertisementId = Helpers::getModelIdOnPut('position');

        return [
            'label' => 'required|string',
            'key' => 'required|string|unique:advertisement_positions,key,' . $positionAdvertisementId,
            'description' => 'required|string',
            'status' => 'required|in:0,1',
            'height' => 'required|integer',
            'width' => 'required|integer',
        ];
    }
}
