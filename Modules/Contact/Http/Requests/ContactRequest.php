<?php

namespace Modules\Contact\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Rules\IranMobile;
//use Shetabit\Shopit\Modules\Contact\Http\Requests\ContactRequest as BaseContactRequest;

class ContactRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'phone_number' => ['required', new IranMobile()],
            'subject' => 'required|string',
            'body' => 'required|string'
        ];
    }
}
