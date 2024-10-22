<?php

namespace Modules\Advertise\Http\Requests\Advertise;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Link\Services\LinkValidator;
//use Shetabit\Shopit\Modules\Advertise\Http\Requests\Advertise\StoreRequest as BaseStoreRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image' => 'required|mimes:jpg,jpeg,png',
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
