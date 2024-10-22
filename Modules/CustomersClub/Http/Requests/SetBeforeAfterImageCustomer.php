<?php

namespace Modules\CustomersClub\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetBeforeAfterImageCustomer extends FormRequest
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
            'before_image' => 'required',
            'after_image' => 'required',
            'description' => 'required|string'
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
