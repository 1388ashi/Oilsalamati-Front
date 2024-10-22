<?php

namespace Modules\Invoice\Events;

//use Shetabit\Shopit\Modules\Invoice\Events\PaymentVerified as BasePaymentVerified;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Invoice\Contracts\GatewayVerifyResponse;
use Modules\Invoice\Entities\Invoice;
use Modules\Invoice\Entities\Payment;
use Modules\Invoice\Listeners\CheckStoreOnVerified;

class PaymentVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public GatewayVerifyResponse $gatewayVerifyResponse,
    ){}
}
