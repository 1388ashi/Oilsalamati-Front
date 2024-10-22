<?php

namespace Modules\Invoice\Classes;

//use Shetabit\Shopit\Modules\Invoice\Classes\PayDriver as BasePayDriver;

use Illuminate\Http\Request;
use Modules\Invoice\Entities\Payment;
use Modules\Invoice\Classes\GatewayMakeResponse;
use Modules\Invoice\Classes\GatewayVerifyResponse;

abstract class PayDriver
{
    protected Payment $payment;

    public function __construct(protected array $options, protected string $name) {}

    /**
     * @return GatewayMakeResponse
     */
    public abstract function make($amount, string $callback);

    /**
     * @return GatewayVerifyResponse
     */
    public abstract function verify(Request $request = null);

    public abstract function getTransactionId(Request $request = null): ?string;

    public function setPayment(Payment $payment) {
        $this->payment = $payment;

        return $this;
    }

    public function setOptions($arr)
    {
        foreach ($arr as $key => $value) {
            $this->options[$key] = $value;
        }
    }

    public function getPayment()
    {
        return $this->payment;
    }

    public function getName()
    {
        return $this->name;
    }
}
