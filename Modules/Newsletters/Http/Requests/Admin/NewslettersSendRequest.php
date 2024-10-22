<?php

namespace Modules\Newsletters\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Newsletters\Http\Requests\Admin\NewslettersSendRequest as BaseNewslettersSendRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Newsletters\Entities\Newsletters;

class NewslettersSendRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|min:2',
            'body' => 'required|string|min:5',
            'users' => 'nullable|array',
            'users.*' =>'nullable|integer|exists:users_newsletters,id',
            'send_all' => 'required_if:users.*,!=,null,boolean',
            'send_at' => 'nullable|date|after_or_equal:'.now()
        ];
    }

    protected function passedValidation()
    {
        $this->request->set('status', Newsletters::STATUS_SUCCESS);
    }
}
