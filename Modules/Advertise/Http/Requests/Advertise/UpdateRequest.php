<?php

namespace Modules\Advertise\Http\Requests\Advertise;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Link\Services\LinkValidator;
use Shetabit\Shopit\Modules\Advertise\Http\Requests\Advertise\UpdateRequest as BaseUpdateRequest;

class UpdateRequest extends FormRequest
{


    public function rules()
    {
        return [
            'image' => 'nullable',
            'link' => "nullable|string",
            'new_tab' => 'required|in:0,1',
            'start' => 'nullable',
            'end' => 'nullable',
        ];
    }

    public function passedValidation()
    {
        (new LinkValidator($this))->validate();
    }
}
