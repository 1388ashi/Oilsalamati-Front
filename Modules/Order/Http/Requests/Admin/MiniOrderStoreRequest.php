<?php

namespace Modules\Order\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Core\Rules\IranMobile;
use Modules\Admin\Entities\Admin;
use Modules\Core\Helpers\Helpers;

class MiniOrderStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'mobile' => ['nullable', 'digits:11', new IranMobile()],
            'discount_amount' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:65000',
            'varieties' => 'nullable|array',
            'varieties.*.id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::exists('varieties', 'id')
            ],
            'varieties.*.quantity' => ['required', 'integer', 'min:1'],
            'varieties.*.amount' => ['required', 'integer', 'min:0'],
            'refund_varieties' => 'nullable|array',
            'refund_varieties.*.id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::exists('varieties', 'id')
            ],
            'refund_varieties.*.quantity' => ['required', 'integer', 'min:1'],
            'refund_varieties.*.amount' => ['required', 'integer', 'min:0'],
            'from_wallet_amount' => 'nullable|integer'
        ];
    }

    public function prepareForValidation()
    {
        /** @var Admin $admin */
        $admin = Auth::user();
        if ($this->discount_amount &&
            (!$admin->hasPermissionTo('discount_mini_order') && !$admin->isSuperAdmin())) {
            throw Helpers::makeValidationException('دسترسی دادن تخفیف وجود ندارد');
        }
    }

    public function passedValidation()
    {
        $this->merge([
           'discount_amount' => $this->discount_amount ?: 0
        ]);
    }
}
