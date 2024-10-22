<?php

namespace Modules\Home\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BeforeAfterImageUpdate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_id' => 'required',
            'title_before' => 'required|min:3|max:100',
            'title_after' => 'required|min:3|max:100',
            'short_description' => 'nullable|min:3|max:255',
            'full_description' => 'nullable|min:3',
            'customer_name' => 'nullable|min:3|max:50',
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
