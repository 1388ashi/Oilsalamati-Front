<?php

namespace Modules\Customer\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Customer\Entities\Withdraw;
use Shetabit\Shopit\Modules\Core\Helpers\Helpers;

class WithdrawStoreRequest extends FormRequest
{
    public function prepareForValidation()
    {
        $this->merge([
            'amount' => str_replace(',', '', $this->input('amount'))
        ]);
    }

    public function rules()
    {
        return [
            'amount' => 'required|integer',
            'card_number' => 'nullable|string',
            'tracking_code' => 'nullable|string',
            'status' => 'required|string|in:' . implode(',', Withdraw::getAvailableStatuses())
        ];
    }

    public function passedValidation()
    {
        $withdraw = $this->route('withdraw');
        if ((int)$withdraw->amount !== (int)$this->amount) {
            throw Helpers::makeValidationException('امکان تغییر مبلغ وجود ندارد');
        }
    }
}
