<?php

namespace Modules\Invoice\Http\Controllers\All;

//use Shetabit\Shopit\Modules\Invoice\Http\Controllers\All\GatewayController as BaseGatewayController;

use App\Http\Controllers\Controller;
use Modules\Invoice\Entities\Payment;

class GatewayController extends Controller
{
    public function index()
    {
        $gateways = Payment::getAvailableDriversForFront();

        return response()->success('', compact('gateways'));
    }
}
