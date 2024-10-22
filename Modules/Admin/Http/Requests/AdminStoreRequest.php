<?php

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Rules\IranMobile;
use Shetabit\Shopit\Modules\Admin\Http\Requests\AdminStoreRequest as BaseAdminStoreRequest;

class AdminStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string|min:2',
            'username' => 'required|string|min:2|unique:admins',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|min:2|unique:admins,username',
            'mobile' => ['nullable','string',new IranMobile(), Rule::unique('admins', 'mobile')],
            'role' => 'required|integer|exists:roles,id',
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
