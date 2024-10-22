<?php

namespace Modules\Shipping\Http\Controllers\Customer;

//use Shetabit\Shopit\Modules\Shipping\Http\Controllers\Customer\ShippingController as BaseShippingController;

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
    public function index()
    {
        $shippings = Shipping::query()
            ->with('cities:id,name,province_id')
            ->with('provinces:id,name')
            ->with('customerRoles:id')
            ->active()->latest('order')->get();

        return response()->success('', compact('shippings'));
    }
}
