<?php

namespace Modules\Order\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SellerStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'full_name' => 'required|string',
            'national_code' => 'nullable|string',
            'description' => 'nullable|string',
            'active' => 'required|boolean'
        ];
    }
}
