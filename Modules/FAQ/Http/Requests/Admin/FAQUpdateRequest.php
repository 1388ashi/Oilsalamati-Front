<?php

namespace Modules\FAQ\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\FAQ\Http\Requests\Admin\FAQUpdateRequest as BaseFAQUpdateRequest;


use Illuminate\Foundation\Http\FormRequest;
use Modules\FAQ\Entities\FAQ;

class FAQUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'nullable|boolean|in:0,1'
        ];
    }
    public function prepareForValidation()
    {
        $this->merge([
            'status' => (bool) $this->input('status', 0),
        ]);
    }
}
