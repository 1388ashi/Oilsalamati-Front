<?php

namespace Modules\FAQ\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\FAQ\Http\Requests\Admin\FAQStoreRequest as BaseFAQStoreRequest;


use Illuminate\Foundation\Http\FormRequest;
use Modules\FAQ\Entities\FAQ;

class FAQStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'nullable|boolean'
        ];
    }
    public function prepareForValidation()
    {
        $this->merge([
            'status' => (bool) $this->input('status', 0),
        ]);
    }
}
