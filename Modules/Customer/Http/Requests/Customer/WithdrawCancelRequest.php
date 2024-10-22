<?php

namespace Modules\Customer\Http\Requests\Customer;

//use Shetabit\Shopit\Modules\Customer\Http\Requests\Customer\WithdrawCancelRequest as BaseWithdrawCancelRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Withdraw;

class WithdrawCancelRequest extends FormRequest
{
    public function passedValidation()
    {
        $this->checkStatus();
    }
    public function rules(){
        return [

        ];
    }

    public function checkStatus()
    {
        /** @var Withdraw $withdraw */
        $withdraw = Withdraw::findOrFail($this->route('withdraw'));
        if (!$withdraw->isCancelableByCustomer()) {
            throw Helpers::makeValidationException('امکان کنسل کردن این درخواست وجود ندارد');
        }
    }
}
