<?php

use Illuminate\Http\Request;
use Modules\Invoice\Http\Controllers\All\PaymentController;
use Modules\Invoice\Http\Controllers\All\VirtualGatewayController;



\Illuminate\Support\Facades\Route::name('payment.verify')
    ->any('payment/{gateway}/verify', [PaymentController::class, 'verify']);
\Illuminate\Support\Facades\Route::name('virtual-gateway')
    ->get('virtual-gateway/{virtual_gateway}', [VirtualGatewayController::class, 'pay']);
