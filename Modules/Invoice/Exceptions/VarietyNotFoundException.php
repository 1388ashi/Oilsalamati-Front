<?php

namespace Modules\Invoice\Exceptions;

//use Shetabit\Shopit\Modules\Invoice\Exceptions\VarietyNotFoundException as BaseVarietyNotFoundException;


use Exception;
use Modules\Setting\Entities\Setting;

class VarietyNotFoundException extends Exception
{
    public function render($request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('core::invoice.exceptionError',
            [
                'message' => $this->getMessage(),
                'description' => Setting::getFromName('transaction_message_failed'),
            ]);
    }
}
