<?php

namespace Modules\Customer\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use Modules\Setting\Entities\Setting;
use Modules\Core\Helpers\Helpers;
//use \Shetabit\Shopit\Modules\Customer\Http\Requests\Customer\WithdrawStoreRequest as BaseWithdrawStoreRequest;

class WithdrawStoreRequest extends FormRequest
{
    public function rules()
    {
        $min_amount =Setting::getFromName('min_withdrew_wallet') ?:1000;
        $min_gift =Setting::getFromName('min_withdrew_wallet_gift') ?:1000;
        $balance = auth()->user()->balance;

        $gift_balance = DB::table('wallets')
            ->where('holder_type','Modules\Customer\Entities\Customer')
            ->where('holder_id',auth()->user()->id)
            ->latest('id')
            ->first()
            ->gift_balance;

        $max_amount = $balance-$gift_balance;


        return [
            'amount' => 'required|integer|min:'.$min_amount,
        ];
    }

    public function passedValidation()
    {
        $balance = auth()->user()->balance;

        $gift_balance = DB::table('wallets')
            ->where('holder_type','Modules\Customer\Entities\Customer')
            ->where('holder_id',auth()->user()->id)
            ->latest('id')
            ->first()
            ->gift_balance;

        $real_amount = $balance - $gift_balance;
        $request_amount = $this->amount;
        if ($request_amount > $real_amount) {
            if ($request_amount <= $balance) {
                throw Helpers::makeValidationException($gift_balance . ' تومان موجودی هدیه شماست و قابل برداشت نمی باشد. موجودی قابل برداشت: ' . $real_amount);
            } else {
                throw Helpers::makeValidationException('موجودی کافی نیست');
            }
        }

        $this->checkBalance();
        $this->checkProfile();
    }





    // came from vendor ================================================================================================
    public function checkBalance()
    {
        /** @var Customer $customer */
        $customer = Auth::user();
        if ($customer->balance < $this->amount) {
            throw \Shetabit\Shopit\Modules\Core\Helpers\Helpers::makeValidationException('مبلغ مورد نظر از شارژ کیف پول بیشتر است');
        }
    }

    public function checkProfile()
    {
        /** @var Customer $customer */
        $customer = Auth::user();
        $fields = [
            'bank_account_number',
            'card_number',
            'shaba_code'
        ];

        $anyExists = false;
        foreach ($fields as $field) {
            if ($customer->$field) {
                $anyExists = true;
            }
        }
        if (!$anyExists) {
            throw new HttpResponseException(response()->error(
                'وارد کردن شماره کارت یا شماره حساب در پروفایل الزامی است',
                ['unknown' => ['وارد کردن شماره کارت یا شماره حساب در پروفایل الزامی است']],
                433)); // 433 means front should redirect to profile
        }
    }

    public function all($keys = null)
    {
        /** @var Customer $customer */
        $customer = Auth::user();
        $all = parent::all($keys);

        $excepts = ['tracking_code'];

        foreach ($excepts as $except) {
            unset($all[$except]);
        }

        $fields = [
            'bank_account_number' => $customer->bank_account_number,
            'card_number' => $customer->card_number,
            'shaba_code' => $customer->shaba_code
        ];

        return array_merge($all, $fields);
    }
}
