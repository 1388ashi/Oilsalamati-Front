<?php

namespace Modules\Prize\Http\Requests;

//use Shetabit\Shopit\Modules\Prize\Http\Requests\StorePrizeRequest as BaseStorePrizeRequest;
use Illuminate\Foundation\Http\FormRequest;

class StorePrizeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'required|integer|min:0', // از انقدر بیشتر خرید کرده باشن
            'prize_amount' => 'required|integer|min:1000'
        ];
    }
}
