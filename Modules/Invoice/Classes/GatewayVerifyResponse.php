<?php

namespace Modules\Invoice\Classes;

//use Shetabit\Shopit\Modules\Invoice\Classes\GatewayVerifyResponse as BaseGatewayVerifyResponse;


class GatewayVerifyResponse implements \Modules\Invoice\Contracts\GatewayVerifyResponse
{
    public function __construct(public bool $success, public string $message = '') {}


    public function getResult(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message
        ];
    }
}
