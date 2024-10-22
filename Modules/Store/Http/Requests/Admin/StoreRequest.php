<?php

namespace Modules\Store\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Store\Entities\Store;
use Shetabit\Shopit\Modules\Store\Http\Requests\Admin\StoreRequest as BaseStoreRequest;

class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'variety_id' => 'required|integer|min:1|exists:varieties,id',
            'description' => 'nullable|string|max:1000',
            'quantity' => 'required|integer'
        ];
    }



    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }



}
