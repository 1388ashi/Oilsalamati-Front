<?php

namespace Modules\Invoice\Http\Controllers\All;

//use Shetabit\Shopit\Modules\Invoice\Http\Controllers\All\VirtualGatewayController as BaseVirtualGatewayController;


use Illuminate\Support\Facades\View;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Invoice\Entities\VirtualGateway;

class VirtualGatewayController extends BaseController
{
    public function pay($id)
    {
        $virtualGateway = VirtualGateway::where('id', $id)->first();
        if (!$virtualGateway) {
            abort(404);
        }

        return view('core::invoice.pay', compact('virtualGateway'));
    }
}
