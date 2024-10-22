<?php

namespace Modules\ProductQuestion\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class ProductQuestionStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'nullable|string|min:5|max:195',
            'body' => 'required|string|min:10',
            'product_id' => 'required|integer|exists:products,id',
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
