<?php


namespace Modules\Invoice\Contracts;

//use Shetabit\Shopit\Modules\Invoice\Contracts\GatewayVerifyResponse as BaseGatewayVerifyResponse;

use JetBrains\PhpStorm\ArrayShape;

interface GatewayVerifyResponse
{


    #[ArrayShape([
        'success' => 'bool',
        'message' => 'string'
    ])]
    public function getResult(): array;
}
