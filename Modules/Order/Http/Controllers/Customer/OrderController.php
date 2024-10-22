<?php

namespace Modules\Order\Http\Controllers\Customer;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Entities\Admin;
use Modules\Area\Entities\City;
use Modules\Cart\Entities\Cart;
use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Http\Requests\Admin\AddressStoreRequest;
use Modules\Invoice\Entities\Invoice;
use Modules\Invoice\Entities\InvoiceLog;
use Modules\Invoice\Entities\Payment;
use Modules\Order\Classes\OrderStoreProperties;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderGiftRange;
use Modules\Order\Entities\OrderItem;
use Modules\Order\Entities\OrderItemLog;
use Modules\Order\Entities\OrderLog;
use Modules\Order\Entities\OrderUpdater;
use Modules\Order\Http\Requests\Admin\AddItemsRequest;
use Modules\Order\Http\Requests\Admin\OrderUpdateRequest;
use Modules\Order\Http\Requests\Admin\OrderUpdateStatusRequest;
use Modules\Order\Http\Requests\User\OrderStoreRequest;
use Modules\Order\Jobs\ChangeStatusNotificationJob;
use Modules\Order\Jobs\NewOrderForAdminNotificationJob;
use Modules\Order\Services\Order\OrderCreatorService;
use Modules\Order\Services\Order\OrderUpdaterService;
use Modules\Order\Services\Statuses\ChangeStatus;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\Setting\Entities\Setting;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Services\ShippingCalculatorService;
use Modules\Shipping\Services\ShippingCollectionService;
use Modules\Store\Entities\Store;
use Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Order\Events\OrderChangedEvent;
//use Shetabit\Shopit\Modules\Order\Http\Controllers\Customer\OrderController as BaseOrderController;
use Illuminate\Support\Facades\Validator;
use Shetabit\Shopit\Modules\Sms\Sms;
use Illuminate\Http\Request;
use Exception;


class OrderController extends Controller
{

    public function store(OrderStoreRequest $request)
    {
        /* @var $orderCreatorServiceObject OrderCreatorService */
        $orderCreatorServiceObject = $request->orderCreatorServiceObject;

        /*if ($request->has('add_item') && $request->add_item) {
            $customer = \auth()->user();
            $orders = $customer->orders();
            // get order parent id
            $order = $orders
                ->where('address_id', $request->address_id)
                ->where('status', Order::STATUS_NEW)
                ->where('customer_id', $customer->id)
                ->whereNull('parent_id')
                ->latest()
                ->first();

            if (!$order) {
                return response()->error('سفارش پدر یافت نشد');
            }
            $pay_type = 'gateway';
            if ($request->pay_wallet) $pay_type = 'both';
            $payment_driver = $request->payment_driver;
            $carts = $customer->carts;

            $orderUpdaterService = new OrderUpdaterService($order, $pay_type, $payment_driver);
            $validatorResponse = $orderUpdaterService->validation_add_items($carts);
            $serviceResponse = $orderUpdaterService->add_items($carts);
            if ($serviceResponse['has_process_completed']) {
                $data = [
                    'order_id' => $order,
                    'invoice_id' => $order->invoices()->latest('id')->first()->id,
                    'need_pay' => 0
                ];
                return response()->success('با موفقیت به سفارش افزوده شد', compact('data'));
            } else {
                if ($serviceResponse['redirect_to_gateway']) {
                    $orderUpdaterObject = $serviceResponse['newOrderUpdater'];
                    return $orderUpdaterObject->pay();
                }
            }
        }*/

        $orderGift = OrderGiftRange::find($request->order_gift_id);

        /** @var $customer Customer */
        $customer = $request->customer;


        if(isset($orderGift)){
            $total_items_amount = $orderCreatorServiceObject->calculatorResponse['products_prices_with_discount'];
            if ($total_items_amount < $orderGift->min_order_amount){
                return response()->error('هدیه انتخاب شده معتبر نیست!');
            }
        }

        try {
            /** @var $order Order */
//            $order = Order::store($customer, $request);
            $order = $orderCreatorServiceObject->store(
                orderGift:  $orderGift,
                description:  $request->description,
                reserved_at: $request->reserved_at
            );
            if (in_array($request->pay_type, ['both', 'wallet'])) {
                return $order->payWithWallet($customer);
            } else {
                return $order->pay();
            }
        } catch (Exception $exception) {
            Log::error($exception->getTraceAsString());
            return response()->error('مشکلی در برنامه رخ داده است:' . $exception->getMessage(), $exception->getTrace(), 500);
        }
    }


    public function index(): JsonResponse
    {
        /**
         * @var $user Customer
         */
        $status = request('status', false);
        $customer = \Auth::gurad('customer-api')->user();

        $orders = Order::query()
            ->when($status && $status != 'all', function ($query) use ($status){
                $query->where('status' , $status);
            })
            ->where('customer_id', '=', $customer->id)
            ->with([/*'statusLogs',*/ 'invoices.payments','orderUpdaters'])
            ->parents()
//            ->with('childs',function ($q){
//                $q->with(['statusLogs', 'invoices.payments']);
//            })
            ->whereNull('parent_id')
//            ->filters()
            ->latest('id')
            ->paginateOrAll();
        $statistics = Order::getOrderStatisticsForCustomer($customer);

        return response()->success('Get all orders list', compact('orders', 'statistics'));
    }
    //Customer مشتری

    public function show($id): JsonResponse
    {
        $order = Order::query()/*->withCommonRelations()*/->with('reservations')->findOrFail($id);
        //Authorization
        if ($order->customer_id !== auth()->user()->id) {
            return response()->error('درخواست غیرمجاز است.', [], 403);
        }

        $childs = Order::query()/*->withCommonRelations()*/->with('reservations')->where('parent_id',$order->id)->get();

        return response()->success('Get order detail', compact('order','childs'));
    }

    public function create()
    {
        $customer = \Auth::guard('customer-api')->user();
        // customers' addresses.  ===============
        $addresses = Address::query()
            ->select(['id','city_id','first_name','last_name','mobile','address','postal_code','telephone','latitude','longitude'])
            ->orderByDesc('id')
            ->where('customer_id', $customer->id)
            ->get();

        $chosenAddress = null;
        if ($addresses->count() > 0) {
            $chosenAddress = $addresses->first();
            if (\request()->has('address_id') && \request('address_id'))
                $chosenAddress = $addresses->where('id',\request('address_id'))->first();
        }
        if ($chosenAddress) {
            // get shipping
            $shippings = (new ShippingCollectionService())->getShippableShippingsForAddress($chosenAddress);
            // get carts of this customer
            $cacheName = 'customerCartsForOrderCreate_' . $customer->id;
            if (Cache::has($cacheName))
                $carts = Cache::get($cacheName);
            else {
                $carts = $customer->carts;
                Cache::put($cacheName, $carts, 10);
                $customer->unsetRelation('carts');
            }
            foreach ($shippings as $shipping) {
                $shipping->amount_showcase = (new ShippingCalculatorService($chosenAddress, $shipping, $customer, $carts))->calculate()['shipping_amount'];
            }
        } else {
            $shippings = (new ShippingCollectionService())->getActiveShippings();
            foreach ($shippings as $shipping)
                $shipping->amount_showcase = $shipping->default_price;
        }


        // gateways ===============
        if (Cache::has('availableGatewayDriversForFront'))
            $gateways = Cache::get('availableGatewayDriversForFront');
        else {
            $gateways = Payment::getAvailableDriversForFront();
            Cache::forever('availableGatewayDriversForFront', $gateways);
        }
        return response()->success('', compact('shippings','addresses','gateways'));
    }


    //used
    public function newUserOrders(Request $request)
    {
        $customer = \auth()->user();
        $orders = $customer->orders();

        $newOrders = $orders
            ->where('address_id', $request->address_id)
            ->where('status', Order::STATUS_NEW)
            ->where('customer_id',$customer->id)
            ->whereNull('parent_id')
            ->latest()
            ->first();

        return response()->success('Get new orders list', compact('newOrders'));
    }

    public function addItemShippingAmountForFront(Request $request)
    {
        $customer = \auth()->user();
        $orders = $customer->orders();

        $parentOrder = $orders
            ->where('address_id', $request->address_id)
            ->where('status', Order::STATUS_NEW)
            ->where('customer_id',$customer->id)
            ->whereNull('parent_id')
            ->latest()
            ->first();

        if (!$parentOrder){
            return response()->error('سفارش پدر یافت نشد');
        }

        $address = Address::find($parentOrder->address_id);
        $city = City::find($address->city_id);

        $shipping = Shipping::find($parentOrder->shipping_id) ;

        $shipping_price = $shipping->getNewShippingPrice($parentOrder,$city);

        //return
        return response()->success('',compact('shipping_price'));
    }


    //Cancel Order | لغو سفارش
    public function cancelOrder(OrderUpdateStatusRequest $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->customer_id != auth()->user()->id){
            return response()->error('این سفارش متعلق به شما نیست !');
        }
        if ($order->parent_id){
            return response()->error('امکان لغو این سفارش وجود ندارد !');
        }

        //کاربر فقط بتونه وضعیت سفارش خودشو به کنسل تغییر بده

        $validator = Validator::make($request->all(), [
            'status' => 'in:canceled_by_user',
        ]);

        if ($validator->fails()) {
            return response()->error('لطفا وضعیت صحیح را انتخاب نمایید ');
        }

        event(new OrderChangedEvent($request->order, $request));
        //No need deleting
        DB::beginTransaction();
//        try {

        /** @var Order $order */
        $order = $request->order;
        (new ChangeStatus($order, $request))->checkStatus();
        ChangeStatusNotificationJob::dispatch($order);

        DB::commit();

        // send SMS
        $customerPhone = auth()->user()->mobile;

        if (!app(CoreSettings::class)->get('sms.patterns.cancel-order', false)) {
            return response()->error('الگوی پیامکی شما موجود نیست');
        }

        $pattern = app(CoreSettings::class)->get('sms.patterns.cancel-order');

        if (env('APP_ENV') == 'production') {
            Sms::pattern($pattern)->data([
                '1' => 'دوست',
            ])->to([$customerPhone])->send();
        }


        return response()->success('سفارش شما با موفقیت لغو شد.');

        /*} catch (Exception $exception) {
            DB::rollBack();
            if (!($exception instanceof BalanceIsEmpty) && !($exception instanceof InsufficientFunds)) {
                Log::error($exception->getTraceAsString());
            }
            return response()->error('عملیات به مشکل خورده است: ' . $exception->getMessage(), $exception->getTrace());
        }*/
    }
    //-------------------------------------------------------------//


    //Delete Item From Order | حذف ایتم از سفارش
    public function deleteItem($id)
    {
        // order item Id
        $orderItem= OrderItem::find($id);

        if (!$orderItem){
            return response()->error('همچین سفارشی یافت نشد!');
        }

        if ($orderItem->status == 0 ){
            return response()->error( 'این سفارش قبلا لغو شده است !');
        }

        if (!$orderItem->order){
            return response()->error('این سفارش پدر ندارد');
        }

        if ($orderItem->order->status != 'new'){
            return response()->error('فقط سفارش هایی با وضعیت جدید قابل حذف هستند');
        }

        if ($orderItem->order->customer_id != auth()->user()->id){
            return response()->error('این سفارش متعلق به شما نیست !');
        }


        // use orderUpdaterService without frontEnd developer
        $pay_type = 'wallet';
        $payment_driver = 'virtual';

        // create fakeCart for this orderItem
        $fakeCarts = [];
        $newFakeCart = new Cart([
            'variety_id' => $orderItem->variety_id,
            'quantity' => $orderItem->quantity,
//                'discount_price' => $requestFakeCart['discount_price'],
//                'price' => $requestFakeCart['price'],
        ]);
        $newFakeCart->load(['variety' => function ($query) {$query->with('product');}]); /* todo: because of DontAppend method in final_price method in Variety, we have to load product to have final_price attribute here. */
        $fakeCarts[] = $newFakeCart;

        $carts = collect($fakeCarts);

        $orderUpdaterService = new OrderUpdaterService($orderItem->order, $pay_type, $payment_driver);
        $orderUpdaterService->validation_delete_items($carts);
        $serviceResponse = $orderUpdaterService->delete_items($carts);
        if ($serviceResponse['has_process_completed']) {
            return response()->success('با موفقیت از سفارش حذف شد', compact('orderItem'));
        } else {
            if ($serviceResponse['redirect_to_gateway']) {
                $orderUpdaterObject = $serviceResponse['newOrderUpdater'];
                /** @var $orderUpdaterObject OrderUpdater */
                return $orderUpdaterObject->pay();
            } /*else {
                $link = $serviceResponse['newOrderUpdaterLink'];
                return response()->success('با استفاده از لینک زیر میتوانید فاکتور را پرداخت کنید', compact('link'));
            }*/
        }







        // ================================================================== PAST




        #پول بیشتری به کیف پولشون میره
        if ($orderItem->order->coupon_id || $orderItem->discount_amount || $orderItem->order->discount_amount){
            return response()->error('بعلت خرید محصول در جشنواره امکان لغو آن وجود ندارد');
        }



        $thisOrderWeight = 0;
        $parentOrderWeight =0;
        $childOrderWeight =0;

        //delete order item
        $newStatus = 'canceled';
        $customer = Auth::user();

        $xweight = 0;
        //////////////////////

        //this order weight
        if ($orderItem->order){
            $order= Order::find($orderItem->order->id);

            foreach ($order->items as $iorderItem){
                if ($iorderItem->status == 1){
                    $variety = Variety::find($iorderItem->variety_id);

                    if($variety?->weight){
                        $xweight = $variety->weight;
                    }elseif($variety->product?->weight){
                        $xweight = $variety->product->weight;
                    }else{
                        $xweight = Setting::getFromName('defualt_product_weight') ? : 120; #todo : check
                    }
                }

                $thisOrderWeight = $thisOrderWeight + ($iorderItem->quantity * $xweight);
            }
        }

        //parent order weight
        if ($orderItem->order->parent_id){
            //shipping amount
            $parent = Order::find($orderItem->order->parent_id);
            foreach ($parent->items as $iorderItem){
                if ($iorderItem->status == 1){
                    $variety = Variety::find($iorderItem->variety_id);

                    if($variety?->weight){
                        $xweight = $variety->weight;
                    }elseif($variety->product?->weight){
                        $xweight = $variety->product->weight;
                    }else{
                        $xweight = Setting::getFromName('defualt_product_weight') ? : 120; #todo : check
                    }
                }

                $parentOrderWeight = $parentOrderWeight + ($iorderItem->quantity * $xweight);
            }
        }

        //child orders weight
        $childrens = Order::query()
            ->where('parent_id',$orderItem->order->id)
            ->where('status','new')
            ->get();

        if ($childrens){
            foreach ($childrens as $children){
                foreach ($children->items as $iorderItem){
                    if ($iorderItem->status == 1){
                        $variety = Variety::find($iorderItem->variety_id);

                        if($variety?->weight){
                            $xweight = $variety->weight;
                        }elseif($variety->product?->weight){
                            $xweight = $variety->product->weight;
                        }else{
                            $xweight = Setting::getFromName('defualt_product_weight') ? : 120; #todo : check
                        }
                    }
                    $childOrderWeight = $childOrderWeight + ($iorderItem->quantity * $xweight);
                }
            }
        }
        /////////////////////

        $OldOrderWeight = $childOrderWeight + $thisOrderWeight + $parentOrderWeight;

        $orderItem->status = 0;
        $orderItem->save();

        $shipping= Shipping::find($orderItem->order->shipping_id);
        $address= Address::find($orderItem->order->address_id);
        $city = City::find($address->city_id);

        $oldShippingAmount = $shipping->getPrice($city,$OldOrderWeight);
        /////////////////////////////////////////////////

        $deletedItemPrice = $orderItem->quantity * ($orderItem->amount);

        //change order weight
        $OrderItemVariety = Variety::find($orderItem->variety_id);
        $itemWeight = $OrderItemVariety->weight;

        $orderItem->order->weight = $orderItem->order->weight - $itemWeight;
        $city = City::find($orderItem->order->address()->first()->city_id);
        $shipping = Shipping::find($orderItem->order->shipping_id);

        $newOrderWeight = $OldOrderWeight - $itemWeight;

        $newShippingAmount = $shipping->getPrice($city,$newOrderWeight);
        $backShippingAmount = $oldShippingAmount - $newShippingAmount;

        $orderItem->order->shipping_amount = $orderItem->order->shipping_amount - $backShippingAmount;
        $orderItem->order->save();

        //edit invoice
        $invoice = Invoice::where('payable_id',$orderItem->order->id)->first();

        $backPrice = $deletedItemPrice + $backShippingAmount;

        $invoice->amount = $invoice->amount - $backPrice;
        $invoice->save();

        InvoiceLog::create([
            'description' => 'کاهش صورتحساب بدلیل حذف ایتم از سفارش توسط کاربر',
            'back_price' => $backPrice,
            'shipping_amount' => $backShippingAmount,
            'item_amount' =>$deletedItemPrice,
            'customer_id' => $customer->id,
            'item_id' => $id,
            'invoice_id' => $invoice->id,
        ]);

        //back price to wallet
        //Back Variety To Store
        $orderItem->depositStore($orderItem);
        $orderItem->depositCustomer($orderItem,$customer,$backPrice,$newStatus);

        //send sms
        $customerPhone = auth()->user()->mobile;
        if (!app(CoreSettings::class)->get('sms.patterns.cancel-order-item', false)) {
            return response()->error('الگوی پیامکی شما موجود نیست');
        }

        if($orderItem->order->activeItemsCount() == 0 ) {
            $orderItem->order->status = 'canceled_by_user';
            $orderItem->order->save();
        }


        $pattern = app(CoreSettings::class)->get('sms.patterns.cancel-order-item');
        Sms::pattern($pattern)->data([
            '1' => 'مشتری',
        ])->to([$customerPhone])->send();

        return response()->success('آیتم مورد نظر شما با موفقیت از سفارش حذف شد');

    }


    public function userAddresses()
    {
        $customer = auth()->user();
        $address = $customer->addresses()->get();

        return response()->success($address);
    }

    public function updateAddress($id,Request $request)
    {
        $order = Order::query()->findOrFail($id);
        $address = Address::findOrFail($request->address_id);

        if ($order->customer_id != auth()->user()->id) {
            return response()->error('این سفارش متعلق به شما نیست.');
        }

        if ($address->customer_id != auth()->user()->id) {
            return response()->error('این ادرس متعلق به شما نیست.');
        }

        if ($order && $order->status == Order::STATUS_NEW) {
            // update order address using OrderUpdaterService without frontEnd developer
            $pay_type = 'wallet';
            $payment_driver = 'virtual';
            // we should prepare above things

            $orderUpdaterService = new OrderUpdaterService($order, $pay_type, $payment_driver);
            $orderUpdaterService->validation_update_order_address($address);
            $serviceResponse = $orderUpdaterService->update_order_address($address);
            if ($serviceResponse['has_process_completed']) {
                return response()->success('آدرس سفارش با موفقیت ویرایش شد', compact('address'));
            } else {
                if ($serviceResponse['redirect_to_gateway']) {
                    $orderUpdaterObject = $serviceResponse['newOrderUpdater'];
                    /** @var $orderUpdaterObject OrderUpdater */
                    return $orderUpdaterObject->pay();
                } /*else {
                    $link = $serviceResponse['newOrderUpdaterLink'];
                    return response()->success('با استفاده از لینک زیر میتوانید فاکتور را پرداخت کنید', compact('link'));
                }*/
            }


            /*$order->update([
                'address_id' => $address->id,
                'address' => $address->toJson(),
            ]);

            $childs = $order->childs()->get();
            foreach ($childs as $child){
                $child->update([
                    'address_id' => $address->id,
                    'address' => $address->toJson(),
                ]);
            }*/
        } else {
            return response()->error('مهلت ویرایش ادرس این سفارش تمام شده است.');

        }

        $address->load('city.province');

        return response()->success('آدرس با موفقیت به روزرسانی شد.', compact('address'));
    }

    public function updateItemStatus(Request $request, $order_id)
    {
        $order = Order::findOrFail($order_id);
        $order_item = $order->items()->where('id', $request->order_item_id)->firstOrFail();

        $orderUpdaterService = new OrderUpdaterService($order, $request->pay_type, $request->payment_driver);
        $validatorResponse = $orderUpdaterService->validation_edit_item_update_status($order_item, $request->newStatus);
//        dd($validatorResponse);
        $serviceResponse = $orderUpdaterService->edit_item_update_status($order_item, $request->newStatus);

        if ($serviceResponse['has_process_completed']) {
            return response()->success('آیتم با موفقیت ویرایش شد', compact('order_item'));
        } else {
            if ($serviceResponse['redirect_to_gateway']) {
                return $serviceResponse['newOrderUpdater']->pay();
            } else {
                $link = $serviceResponse['newOrderUpdaterLink'];
                return response()->success('با استفاده از لینک زیر میتوانید فاکتور را پرداخت کنید', compact('link'));
            }
        }
    }








    // came from vendor ================================================================================================
    public function exitTheReservationMode(Order $order)
    {
        /** @var Order $order */
        if ($order->status != Order::STATUS_RESERVED){
            throw Helpers::makeValidationException('سفارش در حالت رزو نمی باشد');
        }
        $order->update(['status' => Order::STATUS_NEW]);

        return response()->success('سفارش از حالت رزو خارج شد', compact('order'));
    }



}
