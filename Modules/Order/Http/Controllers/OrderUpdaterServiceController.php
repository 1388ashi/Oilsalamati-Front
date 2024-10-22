<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Admin\Entities\Admin;
use Modules\Cart\Entities\Cart;
use Modules\Customer\Entities\Customer;
use Modules\Invoice\Entities\Payment;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderUpdater;
use Modules\Order\Services\Order\OrderUpdaterService;
use Modules\Product\Entities\Variety;
use Modules\Shipping\Entities\Shipping;

class OrderUpdaterServiceController extends Controller
{
    private function isRequestFromBlade(): bool
    {
        return auth()->user() instanceof Admin && 
            request()->header('Accept') !== 'application/json' && 
            request()->has('_token');
    }

    public function showcase(Request $request, $order_id)
    {
        if ($this->isRequestFromBlade()) {

            $processCarts = function ($request, $key, $mergedKey) {
                if ($request->filled($key)) {
                    $request->merge([
                        $mergedKey => json_decode($request->input($key), true),
                    ]);
                    $request->request->remove($key);
                }
            };

            $processCarts($request, 'OrderItemsAddCarts', 'addCarts');
            $processCarts($request, 'OrderItemsDeleteCarts', 'deleteCarts');
        }

        dd($request->all());

        $request->validate([
            'addCarts.*.variety_id' => 'nullable|exists:varieties,id',
            'addCarts.*.quantity' => 'nullable|numeric|min:1',
            'deleteCarts.*.variety_id' => 'nullable|exists:varieties,id',
            'deleteCarts.*.quantity' => 'nullable|numeric|min:1',
            'newAddress_id' => 'nullable|exists:addresses,id',
            'newShipping_id' => 'nullable|exists:shippings,id',
            'payment_driver' => ['nullable:pay_type,both|gateway', Rule::in(Payment::getAvailableDrivers())],
            'pay_type' => ['required', Rule::in(['wallet','both','gateway'])]
        ]);
        if (in_array($request->pay_type, ['both','gateway'])) {
            $request->validate(['payment_driver' => ['required', Rule::in(Payment::getAvailableDrivers())]]);
        }

        if (auth()->user() instanceof Admin) {
            $order = Order::findOrFail($order_id);
            $payment_driver = null; /* in admin panel side we don't redirect to the gateway. we get link */
        } elseif (auth()->user() instanceof Customer) {
            $order = \Auth::guard('customer-api')->user()->orders()->findOrFail($order_id);
            $payment_driver = $request->payment_driver;
        } else return response()->error('مشکلی رخ داد');
        // addCarts section ================
        $fakeCarts = [];
        foreach ($request->addCarts ?? [] as $requestCart) {
            $variety = Variety::findOrFail($requestCart['variety_id']);
            $fakeCarts[] = Cart::fakeCartMaker(
                $requestCart['variety_id'],
                $requestCart['quantity'],
                $variety->final_price['discount_price'],
                $variety->final_price['amount']
            );
        }
        $addCarts = collect($fakeCarts);
        // deleteCarts section ================
        $fakeCarts = [];
        foreach ($request->deleteCarts ?? [] as $requestCart) {
            $variety = Variety::findOrFail($requestCart['variety_id']);
            $fakeCarts[] = Cart::fakeCartMaker(
                $requestCart['variety_id'],
                $requestCart['quantity'],
                $variety->final_price['discount_price'],
                $variety->final_price['amount']
            );
        }
        $deleteCarts = collect($fakeCarts);
        // address and shipping update section ================
        $newAddress = $order->customer->addresses()->where('id', $request->address_id)->first();
        $newShipping = Shipping::query()->active()->where('id',$request->shipping_id)->first();

        $orderUpdaterService = new OrderUpdaterService($order, $request->pay_type, $payment_driver, wantError: false);
        $validatorResponse = $orderUpdaterService->validator($addCarts,$deleteCarts,$newAddress,$newShipping);
        return response()->success('', compact('validatorResponse'));
    }

    public function applier(Request $request, $order_id)
    {
        $request->validate([
            'addCarts.*.variety_id' => 'nullable|exists:varieties,id',
            'addCarts.*.quantity' => 'nullable|numeric|min:1',
            'deleteCarts.*.variety_id' => 'nullable|exists:varieties,id',
            'deleteCarts.*.quantity' => 'nullable|numeric|min:1',
            'newAddress_id' => 'nullable|exists:addresses,id',
            'newShipping_id' => 'nullable|exists:shippings,id',
            'payment_driver' => ['nullable:pay_type,both|gateway', Rule::in(Payment::getAvailableDrivers())],
            'pay_type' => ['required', Rule::in(['wallet','both','gateway'])]
        ]);
        if (in_array($request->pay_type, ['both','gateway'])) {
            $request->validate(['payment_driver' => ['required', Rule::in(Payment::getAvailableDrivers())]]);
        }

        if (auth()->user() instanceof Admin) {
            $order = Order::findOrFail($order_id);
            $payment_driver = null; /* in admin panel side we don't redirect to the gateway. we get link */
        } elseif (auth()->user() instanceof Customer) {
            $order = \Auth::guard('customer-api')->user()->orders()->findOrFail($order_id);
            $payment_driver = $request->payment_driver;
        } else return response()->error('مشکلی رخ داد');
        // addCarts section ================
        $fakeCarts = [];
        foreach ($request->addCarts ?? [] as $requestCart) {
            $variety = Variety::findOrFail($requestCart['variety_id']);
            $fakeCarts[] = Cart::fakeCartMaker(
                $requestCart['variety_id'],
                $requestCart['quantity'],
                $variety->final_price['discount_price'],
                $variety->final_price['amount']
            );
        }
        $addCarts = collect($fakeCarts);
        // deleteCarts section ================
        $fakeCarts = [];
        foreach ($request->deleteCarts ?? [] as $requestCart) {
            $variety = Variety::findOrFail($requestCart['variety_id']);
            $fakeCarts[] = Cart::fakeCartMaker(
                $requestCart['variety_id'],
                $requestCart['quantity'],
                $variety->final_price['discount_price'],
                $variety->final_price['amount']
            );
        }
        $deleteCarts = collect($fakeCarts);
        // address and shipping update section ================
        $newAddress = $order->customer->addresses()->where('id', $request->address_id)->first();
        $newShipping = Shipping::query()->active()->where('id',$request->shipping_id)->first();

        $orderUpdaterService = new OrderUpdaterService($order, $request->pay_type, $payment_driver, wantError: true);
        $orderUpdaterService->validator($addCarts,$deleteCarts,$newAddress,$newShipping);
        $serviceResponse = $orderUpdaterService->applier($addCarts,$deleteCarts,$newAddress,$newShipping);
        $order = $serviceResponse['order'];
        if ($serviceResponse['has_process_completed']) {
            return response()->success('سفارش با موفقیت ویرایش شد', compact('order'));
        } else {
            if ($serviceResponse['redirect_to_gateway']) {
                $orderUpdaterObject = $serviceResponse['newOrderUpdater'];
                /** @var $orderUpdaterObject OrderUpdater */
                return $orderUpdaterObject->pay();
            } else {
                $link = $serviceResponse['newOrderUpdaterLink'];
                return response()->success('با استفاده از لینک زیر میتوانید فاکتور را پرداخت کنید', compact('link'));
            }
        }







    }

}
