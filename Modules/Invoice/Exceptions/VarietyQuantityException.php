<?php

namespace Modules\Invoice\Exceptions;

//use Shetabit\Shopit\Modules\Invoice\Exceptions\VarietyQuantityException as BaseVarietyQuantityException;


use Exception;
use Modules\Setting\Entities\Setting;

class VarietyQuantityException extends Exception
{
    public function render($request)
    {
        return view('core::invoice.exceptionError',
            [
                'message' => $this->getMessage(),
                'description' => Setting::getFromName('transaction_message_failed'),
            ]);
    }
}
