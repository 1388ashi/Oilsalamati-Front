<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Entities\Admin;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\OrderUpdater;

class OrderUpdaterController extends Controller
{
    public function index($customer_id = null)
    {
        if ($customer_id && auth()->user() instanceof Admin) {
            $customer = Customer::findOrFail($customer_id);
        }
        elseif (!$customer_id && auth()->user() instanceof Customer) {
            $customer = \Auth::guard('customer-api')->user();
        }
        else return response()->error('مشکلی رخ داد');
        $orderUpdaters = $customer->orderUpdaters()->active()->orderBy('id','desc')->get();
        return response()->success('', compact('orderUpdaters'));
    }


    public function destroy($customer_id, $id)
    {
        if (auth()->user() instanceof Admin) {
            $orderUpdater = OrderUpdater::findOrFail($id);
        }
        elseif (auth()->user() instanceof Customer) {
            $customer = \Auth::guard('customer-api')->user();
            $orderUpdater = $customer->orderUpdaters()->active()->findOrFail($id);
        }
        else return response()->error('مشکلی رخ داد');

        $orderUpdater->delete();
        return response()->success('با موفقیت غیرفعال شد');
    }


    public function pay($id)
    {
        $customer = \Auth::guard('customer-api')->user();
        $orderUpdater = $customer->orderUpdaters()
            ->payable()
            ->findOrFail($id);

        /** @var $orderUpdater OrderUpdater */
        return $orderUpdater->pay();
    }



}
