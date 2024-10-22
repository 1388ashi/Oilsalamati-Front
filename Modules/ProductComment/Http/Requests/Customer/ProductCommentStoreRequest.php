<?php

namespace Modules\ProductComment\Http\Requests\Customer;

//use Shetabit\Shopit\Modules\ProductComment\Http\Requests\Customer\ProductCommentStoreRequest as BaseProductCommentStoreRequest;

use Illuminate\Foundation\Http\FormRequest;

class ProductCommentStoreRequest extends FormRequest
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
            'rate' => 'required|integer|digits_between:1,10',
            'show_customer_name' => 'required|boolean',
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
