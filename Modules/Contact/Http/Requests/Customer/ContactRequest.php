<?php

namespace Modules\Contact\Http\Requests\Customer;

use Modules\Core\Helpers\Helpers;
use Shetabit\Shopit\Modules\Contact\Http\Requests\ContactRequest as BaseContactRequest;

class ContactRequest extends BaseContactRequest
{
    public function rules()
    {
        return [
            'subject' => 'required|string|min:3|max:192',
            'body' => 'required',
            'customer_id' => 'required|exists:customers,id',
            'answer' => 'nullable',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->input('_wreixcf14135vq2av54') !== 'تهران'
            && $this->input('cn8dsada032') !== 'ایران') {
            throw Helpers::makeValidationException('پاسخ امنیتی وارد شده اشتباه است', 'captcha');
        }
    }


}
