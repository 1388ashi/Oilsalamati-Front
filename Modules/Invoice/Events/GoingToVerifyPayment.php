<?php

namespace Modules\Invoice\Events;

//use Shetabit\Shopit\Modules\Invoice\Events\GoingToVerifyPayment as BaseGoingToVerifyPayment;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Invoice\Entities\Payment;

class GoingToVerifyPayment
{
    use Dispatchable, SerializesModels;
    /**
     * Listeners
     * @see CheckStoreOnVerified
     */
    public function __construct(public Payment $payment){}
}
