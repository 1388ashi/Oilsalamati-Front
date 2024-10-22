<?php

namespace Modules\Contact\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerContactRequest extends FormRequest
{

    public function rules()
    {
        return [
            'answer' => 'nullable',
        ];
    }


    public function authorize()
    {
        return true;
    }
}
