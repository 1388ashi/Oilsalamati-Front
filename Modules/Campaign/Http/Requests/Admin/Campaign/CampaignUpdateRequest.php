<?php

namespace Modules\Campaign\Http\Requests\Admin\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class CampaignUpdateRequest extends FormRequest
{

    public function rules()
    {
        return [
            'title' => 'required|string|min:1',
            'start_date' => 'required   ',
            'end_date' => 'required ',
            'status' => 'required|boolean',
            'customer_title' => 'required|string|min:1',
            'customer_text' => 'required|string|min:1',
            'coupon_code' => 'nullable|string|min:1',
        ];
    }
    public function prepareForValidation()
    {
        $this->merge([
            'status' => (bool) $this->input('status', 0),
        ]);
    }

    public function authorize()
    {
        return true;
    }
}
