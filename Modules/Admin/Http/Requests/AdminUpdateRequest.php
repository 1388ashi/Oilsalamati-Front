<?php

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Rules\IranMobile;
use Shetabit\Shopit\Modules\Admin\Http\Requests\AdminUpdateRequest as BaseAdminUpdateRequest;

class AdminUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $adminId = Helpers::getModelIdOnPut('admin');
        return [
            'name' => 'nullable|string|min:2',
            'username' => 'required|string|min:2|unique:admins,username,' . $adminId,
            'password' => 'nullable|string|min:6',
            'email' => 'nullable|email|min:2|unique:admins,email,' . $adminId,
            'mobile' => ['nullable','string',new IranMobile(), Rule::unique('admins', 'mobile')->ignore($adminId)],
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
