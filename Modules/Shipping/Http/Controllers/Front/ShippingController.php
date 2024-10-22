<?php

namespace Modules\Shipping\Http\Controllers\Front;

//use Shetabit\Shopit\Modules\Shipping\Http\Controllers\Front\ShippingController as BaseShippingController;


use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Area\Entities\City;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Http\Requests\Admin\ShippingCityAssignRequest;
use Modules\Shipping\Http\Requests\Admin\ShippingStoreRequest;
use Modules\Shipping\Http\Requests\Admin\ShippingUpdateRequest;

class ShippingController extends Controller
{
    public function index(Request $request, Shipping $shipping)
    {
        $shipping->getShippingInAddress(1);

//        return response()->success('', compact('shippings'));
    }
}
