<?php


namespace Modules\Invoice\Contracts;

//use Shetabit\Shopit\Modules\Invoice\Contracts\GatewayMakeResponse as BaseGatewayMakeResponse;
use JetBrains\PhpStorm\ArrayShape;

interface GatewayMakeResponse
{
    #[ArrayShape([
        'success' => 'bool',
        'transaction_id' => 'string',
        'message' => 'string',
        'url' => 'string',
        'inputs' => 'array',
        'method' => 'string'
    ])]
    public function getResult(): array;
}
