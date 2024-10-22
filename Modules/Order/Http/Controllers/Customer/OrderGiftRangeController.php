<?php

namespace Modules\Order\Http\Controllers\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\OrderGiftRange;
use Modules\Order\Http\Requests\Admin\OrderGiftRangeStoreRequest;
use Modules\Order\Http\Requests\Admin\OrderGiftRangeUpdateRequest;
use Modules\Product\Entities\Gift;

class OrderGiftRangeController extends Controller
{

    public function index()
    {
        if(!auth()->user()->id){
            return response()->error('لطفا لاگین کنید');
        }

        $gifts = OrderGiftRange::query()->latest('id')->get();

        foreach ($gifts as $gift){
            $customer = Customer::findOrFail(auth()->user()->id);
            $total_amount = 0;
            foreach ($customer->carts as $cart){
                $total_amount+=$cart->price - $cart->discount_price;
            }

            if ($total_amount >= $gift->min_order_amount){
                $gift->is_buyable= 1;
            }else{
                $gift->is_buyable= 0;
            }
        }

        return response()->success('',compact('gifts'));
    }
}
