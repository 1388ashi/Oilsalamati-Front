<?php

namespace Modules\Order\Http\Controllers\Admin;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Area\Entities\Province;
use Modules\Cart\Entities\Cart;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Customer;
use Modules\CustomersClub\Entities\CustomersClubScore;
use Modules\Invoice\Entities\Invoice;
use Modules\Invoice\Entities\Payment;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderScope;
use Modules\Order\Entities\OrderUpdater;
use Modules\Order\Http\Requests\Admin\AddItemsRequest;
use Modules\Order\Http\Requests\Admin\OrderStoreRequest;
use Modules\Order\Services\Order\OrderUpdaterService;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\Report\Entities\OrderReport;
use Modules\Shipping\Entities\Shipping;
use Modules\Store\Entities\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Order\Entities\OrderLog;
use Modules\Order\Entities\OrderItem;
use Modules\Order\Entities\OrderItemLog;
use Modules\Order\Jobs\ChangeStatusNotificationJob;
use Modules\Order\Http\Requests\Admin\OrderUpdateRequest;
use Modules\Order\Http\Requests\Admin\UpdateItemsRequest;
use mysql_xdevapi\Table;
use Shetabit\Shopit\Modules\Order\Events\OrderChangedEvent;
use Modules\Order\Http\Requests\Admin\UpdateItemStatusRequest;
use Modules\Order\Http\Requests\Admin\OrderUpdateStatusRequest as OrderUpdateStatusRequest;
use Modules\Order\Services\Statuses\ChangeStatus;
//use Shetabit\Shopit\Modules\Order\Http\Controllers\Admin\OrderController as BaseOrderController;
use stdClass;
use Throwable;
use function Composer\Autoload\includeFile;

class OrderController extends Controller
{
    public function index($todayOrders = false): JsonResponse|View
    {
        $ordersQuery = Order::query()
            ->applyFilter()
            ->parents()
            ->select(
                'id',
                'customer_id',
                'address',
                'status',
                'parent_id',
                'created_at',
                'total_items_count',
                'total_invoices_amount'
            )
            ->latest('id');

        if (request()->header('Accept') == 'application/json') {

            $orders = $ordersQuery
                ->paginate(25)
                ->withQueryString()
                ->each(fn($order) => $order->setAppends('receiver'));
                
            $copyOrderQuery = clone $ordersQuery;
            Helpers::removeWhere(clone $ordersQuery->getQuery(), 'status');
            $order_statuses = Order::getAllStatuses($copyOrderQuery);
            
            return response()->success('Get optimized orders list :)', compact(['orders', 'order_statuses']));
        }

        $orders = $ordersQuery->paginate()->withQueryString();
        $allOrderStatuses = Order::getAvailableStatuses();

        if ($todayOrders) {
            return view('order::admin.today-orders', compact(['orders', 'allOrderStatuses']));
        }

        return view('order::admin.index', compact(['orders', 'allOrderStatuses']));
    }

    public function show($id)
    {
        $order = Order::query()/*->withCommonRelations()*/
            ->with([
                // 'reservations' => function ($query) {
                //     $query->where('status', Order::STATUS_RESERVED);
                //     $query->with('invoices.payments');
                // },
                'orderLogs',
                'childs',
                'orderUpdaters' => function ($query) {
                    $query->done();
                },
                'invoices.payments',
                'items' => function ($query) {
                    $query->active();
                },
            ])->findOrFail($id);
        // $transactions = DB::table('transactions')->where('order_id',$order->id)->get();
// $scores = CustomersClubScore::query()->where('order_id',$order->id)->get();
        $child_items = [];
        foreach ($order->childs as $child) {
            if (in_array($child->status, Order::ACTIVE_STATUSES)) {
                foreach ($child->items->where('status', 1) as $ch_item) {
                    $child_items[] = $ch_item;
                }
            }
        }
        foreach ($child_items as $child_order_item) {
            if (in_array($child_order_item->variety_id, $order->items->pluck('variety_id')->toArray())) {
                foreach ($order->items as $item) {
                    if ($item->variety_id == $child_order_item->variety_id) {
                        if ($item->status == $child_order_item->status) {
                            $order->items->where('variety_id', $child_order_item->variety_id)->first()->quantity += $child_order_item->quantity;
                        } else {
                            $order->items->push($child_order_item);
                        }
                    }
                }
            } else {
                $order->items->push($child_order_item);
            }
        }
        // dd($order);
        if (\request()->header('Accept') == 'application/json') {
            return response()->success('جزئیات سفارش مشتری', compact('order'));
        }
        $orderStatuses = Order::getAvailableStatuses();
        $shippings = Shipping::query()->select('id', 'name')->where('id', '!=', $order->shipping_id)->get();
        $addresses = $order->customer->addresses->where('id', '!=', $order->address_id);

        return view('order::admin.show', compact('order', 'orderStatuses', 'shippings', 'addresses'));
    }

    public function edit(Order $order) 
    {
        $order->load([
            'childs',
            'orderUpdaters' => fn ($q) => $q->done(),
            'items' => fn ($q) => $q->active(),
        ]);

        $childItems = [];

        foreach ($order->childs as $child) {
            if (in_array($child->status, Order::ACTIVE_STATUSES)) {
                foreach ($child->items->where('status', 1) as $ch_item) {
                    $childItems[] = $ch_item;
                }
            }
        }

        foreach ($childItems as $childOrderItem) {
            if (in_array($childOrderItem->variety_id, $order->items->pluck('variety_id')->toArray())) {
                foreach ($order->items as $item) {
                    if ($item->variety_id == $childOrderItem->variety_id) {
                        if ($item->status == $childOrderItem->status) {
                            $order->items->where('variety_id', $childOrderItem->variety_id)->first()->quantity += $childOrderItem->quantity;
                        } else {
                            $order->items->push($childOrderItem);
                        }
                    }
                }
            } else {
                $order->items->push($childOrderItem);
            }
        }
    
        $orderStatuses = Order::getAvailableStatuses();
        $drivers = Payment::getAvailableDrivers();
        $shippings = Shipping::query()->select('id', 'name')->where('id', '!=', $order->shipping_id)->get();
        $addresses = $order->customer->addresses->where('id', '!=', $order->address_id);

        return view('order::admin.edit', compact('order', 'orderStatuses', 'shippings', 'addresses', 'drivers'));
    }

    public function getOrders($paginate, $date = null, $apiType = 'old'): JsonResponse
    {
        $requestParams = [
            'id' => \request('id', false), // شناسه
            'customer_id' => \request('customer_id', false), // مشتری
            'status' => \request('status', false), // وضعیت
            'product_id' => \request('product_id', false), // محصول
            'variety_id' => \request('variety_id', false), // تنوع
            'tracking_code' => \request('tracking_code', false), // کد رهگیری
            'city' => \request('city', false), // شهر
            'province' => \request('province', false), // استان
            'first_name' => \request('first_name', false), // نام
            'last_name' => \request('last_name', false), // نام خانوادگی
            'start_date' => \request('start_date', false) ? Carbon::createFromTimestamp(\request('start_date'))->toDateString() : false, // از تاریخ
            'end_date' => \request('end_date', false) ? Carbon::createFromTimestamp(\request('end_date'))->toDateString() : false, // تا تاریخ
        ];

        $ordersQuery = Order::query()
            //            ->with('customer')
//            ->with([
//                'reservations' => function ($query) {
//                    $query->where('status', Order::STATUS_RESERVED)
//                        ->with('invoices.payments.invoice');
//                }
//            ])
            ->applyFilter($requestParams)
            ->parents()
            //            ->filters()
            ->select(
                'id',
                'customer_id',
                DB::raw("CONCAT(CAST(json_unquote(JSON_EXTRACT(address, '$.first_name')) as CHAR), ' ', CAST(json_unquote(JSON_EXTRACT(address, '$.last_name')) as CHAR)) AS receiver"),
                'shipping_amount',
                'discount_amount',
                'status',
                'parent_id',
                'created_at',
                //                'total_items_count'
//                DB::raw('(SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) as items_count')
            )
            ->latest('id');

        if ($date) {
            $ordersQuery = $ordersQuery->whereDate('created_at', '=', $date);
        }

        $orders = $ordersQuery->paginateOrAll($paginate);

        //        foreach ($orders as $order) {
//            $order->append('active_payment');
//            $order->append('active_payments');
//            $order->makeHidden('invoices');
//            $order->makeHidden('items');
//        }

        $copyOrderQuery = clone $ordersQuery;
        Helpers::removeWhere($copyOrderQuery->getQuery(), 'status');
        $order_statuses = Order::getAllStatuses($copyOrderQuery);

        if ($apiType == 'new') {

            $newOrders = [
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'current_Page' => $orders->currentPage(),
            ];

            $items = array();
            foreach ($orders as $order) {
                $outer_tracking_codes = array();
                foreach ($order->active_payments as $active_payment) {
                    $outer_tracking_codes[] = $active_payment->tracking_code;
                }
                $invoices = array();
                foreach ($order->invoices as $invoice) {
                    $tracking_codes = array();
                    foreach ($invoice->payments as $payment) {
                        $tracking_codes[] = $payment->tracking_code;
                    }
                    $invoices[] = [
                        'tracking_code' => $tracking_codes,
                        'wallet_amount' => $invoice->wallet_amount,
                    ];
                }

                $items[] = [
                    "id" => $order->id,
                    "customer_id" => $order->customer_id,
                    "receiver" => $order->receiver,
                    "shipping_amount" => $order->shipping_amount,
                    "discount_amount" => $order->discount_amount,
                    "status" => $order->status,
                    "created_at" => $order->created_at,
                    "items_count" => $order->items_count,
                    "parent_id" => $order->parent_id,
                    "total_amount" => $order->total_amount,
                    "customer_first_name" => $order->customer->first_name,
                    "customer_last_name" => $order->customer->last_name,
                    "customer_mobile" => $order->customer->mobile,
                    "tracking_codes" => $outer_tracking_codes,
                    "invoices" => $invoices,
                ];
            }

            $newOrders['items'] = $items;

            $result = [
                'success' => true,
                'message' => 'Get optimized orders list :',
                'data' => [
                    'orders' => $newOrders,
                    'order_statuses' => $order_statuses,
                ],
            ];

            return response()->json($result);
        } else {
            return response()->success('Get optimized orders list :)', compact('orders', 'order_statuses'));
        }
    }


    public function indexLight(): JsonResponse|View
    {
        $paginate = app(CoreSettings::class)->get('order.admin.pagination', 10);
        $result = $this->getOrdersLight($paginate, null, \request('apiType'));
        return $result;
    }


    /** @throws Throwable */
    public function store(OrderStoreRequest $request): JsonResponse|View
    {
        /* todo: of course we should can create a pay link for this. this is order store in admin panel */
        try {
            $customer = $request->orderStoreProperties->customer;
            DB::beginTransaction();
            $order = Order::store($customer, $request);
            $order->payWithWallet($customer);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getTraceAsString());
            return response()->error(
                'ثبت سفارش به مشکل خورده است:' . $exception->getMessage(),
                $exception->getTrace(),
                500
            );
        }

        ActivityLogHelper::storeModel(' سفارش مشتری ثبت شد', $order);

        return response()->success('سفارش مشتری با موفقیت ثبت شد', compact('order'));
    }




    public function updateItemStatus(UpdateItemStatusRequest $request, OrderItem $orderItem): JsonResponse|View
    {
        $fakeCarts = [];
        $newFakeCart = new Cart([
            'variety_id' => $orderItem->variety_id,
            'quantity' => $orderItem->quantity,
            'discount_price' => $orderItem->discount_amount,
            'price' => $orderItem->amount,
        ]);
        $newFakeCart->load([
            'variety' => function ($query) {
                $query->with('product');
            }
        ]); /* todo: because of DontAppend method in final_price method in Variety, we have to load product to have final_price attribute here. */
        $fakeCarts[] = $newFakeCart;
        $carts = collect($fakeCarts);

        $pay_type = 'wallet';
        if ($request->status) {
            // activating
            $orderUpdaterService = new OrderUpdaterService($orderItem->order, $pay_type, /*$payment_driver*/);
            $orderUpdaterService->validation_add_items($carts);
            $serviceResponse = $orderUpdaterService->add_items($carts);
            ActivityLogHelper::updatedModel('سفارش با موفقیت به سفارش افزوده شد', $serviceResponse);
            if ($serviceResponse['has_process_completed']) {
                return response()->success('با موفقیت به سفارش افزوده شد', compact('carts'));
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
        } else {
            // deleting
            $orderUpdaterService = new OrderUpdaterService($orderItem->order, $pay_type, /*$payment_driver*/);
            $orderUpdaterService->validation_delete_items($carts);
            $serviceResponse = $orderUpdaterService->delete_items($carts);
            if ($serviceResponse['has_process_completed']) {
                return response()->success('با موفقیت از سفارش حذف شد', compact('carts'));
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
        ///////////////////////////////////////////////////////////////////////////////////// OLD CODES

        //        event(new OrderChangedEvent($orderItem->order, $request));
//        $order = $orderItem->order;
//        $oldTotalAmount = $order->getTotalAmount();
//        $parentOrder = $order->reserved_id == null ? $order : Order::findOrFail($order->reserved_id);
//        try {
//            $variety = $request->variety;
//            $oldAddress = $order->address;
//            $oldShipping = $order->shipping_id;
//            $oldDiscountAmount = $order->discount_amount;
//
//            DB::beginTransaction();
//            if ($orderItem->status == $request->status) {
//                return response()->success('وضعیت با موفقیت تغییر کرد', null);
//            }
//            if ($request->status == 1) {
//                $quantity = $orderItem->quantity;
//                $amount = (int)$orderItem->quantity * $orderItem->amount;
//                $wallet = ['type' => 'decrement', 'amount' => $amount];
//                $store = ['type' => 'decrement', 'quantity' => $quantity];
//            } elseif ($request->status == 0) {
//                $quantity = $orderItem->quantity;
//                $amount = (int)$orderItem->quantity * $orderItem->amount;
//                $wallet = ['type' => 'increment', 'amount' => $amount];
//                $store = ['type' => 'increment', 'quantity' => $quantity];
//            }
//            /** @var Customer $customer */
//            $customer = $orderItem->order->customer()->first();
//            if ($wallet['type'] == 'decrement') {
//                $customer->withdraw($wallet['amount'], [
//                    'name' => $customer->getFullNameAttribute(),
//                    'mobile' => $customer->mobile,
//                    'description' => "با تغییر وضعیت آیتم سفارش  به تعداد {$quantity} عدد به محصول {$variety->title} اضافه شد"
//                ]);
//
//                Store::insertModel((object)[
//                    'type' => $store['type'],
//                    'description' => "با تغییر وضعیت آیتم سفارش  به تعداد {$quantity} عدد به محصول {$variety->title} اضافه شد",
//                    'quantity' => $store['quantity'],
//                    'variety_id' => $variety->id
//                ]);
//            }
//
//            if ($wallet['type'] == 'increment') {
//                $customer->deposit($wallet['amount'], [
//                    'name' => $customer->getFullNameAttribute(),
//                    'mobile' => $customer->mobile,
//                    'description' => "با تغییر وضعیت آیتم سفارش به تعداد {$quantity} عدد از محصول {$variety->title} کم شد"
//                ]);
//
//                Store::insertModel((object)[
//                    'type' => $store['type'],
//                    'description' => "با تغییر وضعیت آیتم سفارش  به تعداد {$quantity} عدد از محصول {$variety->title} کم شد",
//                    'quantity' => $store['quantity'],
//                    'variety_id' => $variety->id
//                ]);
//            }
//            $orderItem->update(['status' => $request->status]);
//            $parentOrder->recalculateShippingAmount();
//            $order->load('items');
//            $orderLog = OrderLog::addLog($order,
//                ($order->getTotalAmount() - $oldTotalAmount),
//                $order->discount_amount - $oldDiscountAmount,
//                $order->address != $oldAddress ? $order->address : null,
//                $order->shipping_id != $oldShipping ? $order->shipping_id : null
//            );
//
//            if ($request->status == 0) {
//                $status = OrderItemLog::TYPE_DELETE;
//            } else {
//                $status = OrderItemLog::TYPE_NEW;
//            }
//
//            $orderItemLog = OrderItemLog::addLog($orderLog, $orderItem, $status, $orderItem->quantity);
//
//            DB::commit();
//        } catch (Exception $e) {
//            DB::rollBack();
//            Log::error($e->getTraceAsString());
//            return response()->error('عملیات ناموفق ' . $e->getMessage(), $e->getTrace());
//        }
//        return response()->success('وضعیت با موفقیت تغییر کرد', compact('orderItemLog'));
    }


    public function updateQuantityItem(UpdateItemsRequest $request, OrderItem $orderItem): JsonResponse|View
    {
        if ($request->quantity == $orderItem->quantity)
            throw Helpers::makeValidationException('تعداد جدید تغییری نکرده است.');


        $fakeCarts = [];
        $newFakeCart = new Cart([
            'variety_id' => $orderItem->variety_id,
            'quantity' => $request->quantity,
            'discount_price' => $orderItem->discount_amount,
            'price' => $orderItem->amount,
        ]);
        $newFakeCart->load([
            'variety' => function ($query) {
                $query->with('product');
            }
        ]); /* todo: because of DontAppend method in final_price method in Variety, we have to load product to have final_price attribute here. */
        $fakeCarts[] = $newFakeCart;
        $carts = collect($fakeCarts);



        $pay_type = 'wallet';
        if ($request->quantity > $orderItem->quantity) {
            // so this is increasing the quantity of orderItem
            $orderUpdaterService = new OrderUpdaterService($orderItem->order, $pay_type, /*$payment_driver*/);
            $orderUpdaterService->validation_add_items($carts);
            $serviceResponse = $orderUpdaterService->add_items($carts);
            if ($serviceResponse['has_process_completed']) {
                return response()->success('با موفقیت به سفارش افزوده شد', compact('carts'));
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
        } else {
            // so this is decreasing the quantity of orderItem
            $orderUpdaterService = new OrderUpdaterService($orderItem->order, $pay_type, /*$payment_driver*/);
            $orderUpdaterService->validation_delete_items($carts);
            $serviceResponse = $orderUpdaterService->delete_items($carts);
            if ($serviceResponse['has_process_completed']) {
                return response()->success('با موفقیت از سفارش حذف شد', compact('carts'));
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


        // ==================================================    OLD CODES


        //        event(new OrderChangedEvent($orderItem->order, $request));
//        /** @var Order $order */
//        $order = $orderItem->order;
//        $parentOrder = $order->reserved_id == null ? $order : Order::findOrFail($order->reserved_id);
//
//        $oldTotalAmount = $order->getTotalAmount();
//        $oldQuantity = $orderItem->quantity;
//        $oldShippingAmount = $parentOrder->shipping_amount;
//        try {
//            DB::beginTransaction();
//            $diffQuantity = 0;
//            /**
//             * @var $orderItem OrderItem
//             */
//            $variety = $request->variety;
//
//            if ($orderItem->quantity > $request->quantity) {
//                $newQuantity = $request->quantity; // - 2 = 5 - 3 for orderItem
//                $diffQuantity = $orderItem->quantity - $request->quantity; // - 2 = 5 - 3
//                $diffAmount = ($diffQuantity * $orderItem->amount);
//
//                $orderItem->update([
//                    'quantity' => $newQuantity,
//                ]);
//                /**
//                 * Store Increment
//                 * Wallet Increment
//                 */
//                $wallet = ['type' => 'increment', 'amount' => ($diffAmount + ($parentOrder->recalculateShippingAmount() - $oldShippingAmount))];
//            } elseif ($orderItem->quantity < $request->quantity) {
//                $newQuantity = $request->quantity;
//
//                $diffQuantity = $request->quantity - $orderItem->quantity; // +2 = 4 - 2
//                $diffAmount = ($diffQuantity * $orderItem->amount);
//                if ($variety->quantity < $diffQuantity) {
//                    throw Helpers::makeValidationException("تعداد سفارش این تنوع بیشتر از موجودی است. موجودی این تنوع : {$variety->quantity}");
//                }
//                $orderItem->update([
//                    'quantity' => $newQuantity,
//                ]);
//                /**
//                 * Store Decrement
//                 * Wallet Decrement
//                 * Variety quantity Increment
//                 */
//
//                $wallet = ['type' => 'decrement', 'amount' => ($diffAmount + ($parentOrder->recalculateShippingAmount() - $oldShippingAmount))];
//            }
//            /**
//             * @var $customer Customer
//             */
//            $customer = $orderItem->order->customer()->first();
//            if ($wallet['type'] == 'increment') {
//
//                $customer->deposit($wallet['amount'], [
//                    'name' => $customer->getFullNameAttribute(),
//                    'mobile' => $customer->mobile,
//                    'description' => "از محصول {$variety->title} به تعداد {$request->quantity} از سفارش کم شد"
//                ]);
//
//                Store::insertModel((object)[
//                    'type' => $wallet['type'],
//                    'description' => "از محصول {$variety->title} به تعداد {$diffQuantity} از سفارش کم شد",
//                    'quantity' => $diffQuantity,
//                    'variety_id' => $variety->id
//                ]);
//            } elseif ($wallet['type'] == 'decrement') {
//                $customer->withdraw($wallet['amount'], [
//                    'name' => $customer->getFullNameAttribute(),
//                    'mobile' => $customer->mobile,
//                    'description' => "از محصول {$variety->title} به تعداد {$request->quantity} به سفارش اضافه شد"
//                ]);
//
//                Store::insertModel((object)[
//                    'type' => $wallet['type'],
//                    'description' => "از محصول {$variety->title} به تعداد {$diffQuantity} به سفارش اضافه شد",
//                    'quantity' => $diffQuantity,
//                    'variety_id' => $variety->id
//                ]);
//            }
//
//
//            $order->load('items');
//            $orderLog = OrderLog::addLog($order,
//                $order->getTotalAmount() - $oldTotalAmount, 0, null, null);
//
//            $type = $wallet['type'] === 'increment' ? 'decrement' : 'increment';
//
//            OrderItemLog::addLog($orderLog, $orderItem, $type, abs($oldQuantity - $request->quantity));
//
//            $order->load(['orderLogs.logItems']);
//            DB::commit();
//        } catch (Exception $e) {
//            DB::rollBack();
//            Log::error($e->getTraceAsString());
//            return response()->error(' عملیات ناموفق ' . $e->getMessage(), $e->getTrace());
//        }
//
//        return response()->success('محصول مورد نظر با موفقیت بروزرسانی شد', compact('orderItem'));
    }





    public function addItem(AddItemsRequest $request, Order $order): JsonResponse|View
    {
        $variety = Variety::findOrFail($request->variety_id);
        $variety->load('product');
        $fakeCarts = [];
        $newFakeCart = new Cart([
            'variety_id' => $variety->id,
            'quantity' => $request->quantity,
            'discount_price' => $variety->final_price['discount_price'],
            'price' => $variety->final_price['amount'],
        ]);
        $newFakeCart->load([
            'variety' => function ($query) {
                $query->with('product');
            }
        ]); /* todo: because of DontAppend method in final_price method in Variety, we have to load product to have final_price attribute here. */
        $fakeCarts[] = $newFakeCart;
        $carts = collect($fakeCarts);

        $pay_type = 'wallet';

        $orderUpdaterService = new OrderUpdaterService($order, $pay_type, /*$payment_driver*/);
        $orderUpdaterService->validation_add_items($carts);
        $serviceResponse = $orderUpdaterService->add_items($carts);
        if ($serviceResponse['has_process_completed']) {
            return response()->success('با موفقیت به سفارش افزوده شد', compact('carts'));
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

        // ///////////////////////////////////////////////////////     OLD CODES
//        event(new OrderChangedEvent($order, $request));
//        $variety = $request->variety;
//        $oldTotalAmount = $order->getTotalAmount();
//        /**
//         * @var $product Product
//         */
//        $product = Product::query()->find($request->product_id);
//        $activeFlash = $product->activeFlash->first();
//        $parentOrder = $order->reserved_id == null ? $order : Order::findOrFail($order->reserved_id);
//        $oldShippingAmount = $parentOrder->shipping_amount;
//
//        try {
//            DB::beginTransaction();
//            $orderItem = $order->items()->create([
//                'product_id' => $request->product_id,
//                'variety_id' => $variety->id,
//                'quantity' => $request->quantity,
//                'amount' => $variety->final_price['amount'],
//                'flash_id' => $activeFlash->id ?? null,
//                'discount_amount' => $variety->final_price['discount_price'],
//                'extra' => collect([
//                    'attributes' => $variety->attributes()->get(['name', 'label', 'value']),
//                    'color' => $variety->color()->exists() ? $variety->color->name : null
//                ])
//            ]);
//            $newShippingAmount = $parentOrder->recalculateShippingAmount();
//
//            $calculateAmount = $orderItem->amount * $orderItem->quantity + ($newShippingAmount - $oldShippingAmount);
//            /**
//             * @var $customer Customer
//             */
//            $customer = $order->customer;
//            $customer->withdraw($calculateAmount, [
//                'name' => $customer->getFullNameAttribute(),
//                'mobile' => $customer->mobile,
//                'description' => "اضافه کردن محصول {$variety->title} به سفارش " . $order->id
//            ]);
//
//            Store::insertModel((object)[
//                'type' => Store::TYPE_DECREMENT,
//                'description' => "اضافه کردن محصول {$variety->title}  به سفارش " . $order->id,
//                'quantity' => $orderItem->quantity,
//                'variety_id' => $orderItem->variety_id
//            ]);
//            $order->load('items');
//
//            $orderLog = OrderLog::addLog($order,
//                ($order->getTotalAmount() - $oldTotalAmount), 0, null, null);
//
//            OrderItemLog::addLog($orderLog, $orderItem, 'new', $orderItem->quantity);
//
//            DB::commit();
//        } catch (Exception $e) {
//            DB::rollBack();
//            Log::error($e->getTraceAsString());
//            return response()->error('عملیات ناموفق ' . $e->getMessage(), $e->getTrace());
//        }
//        $orderItem->load('product.varieties');
//
//        return response()->success('تنوع مورد نظر با موفقیت به لیست خرید اضافه شد', compact('orderItem'));
    }











    public function getOrdersLight($paginate, $date = null): JsonResponse
    {
        $requestParams = [
            'id' => \request('id', false), // شناسه
            'customer_id' => \request('customer_id', false), // مشتری
            'status' => \request('status', false), // وضعیت
            'product_id' => \request('variety_id', false) ? false : \request('product_id', false), // محصول - در صورتی که کد تنوع ارسال شده باشد کد محصل درنظر گرفته نمی شود
            'variety_id' => \request('variety_id', false), // تنوع
//            'tracking_code' => \request('tracking_code', false), // کد رهگیری
            'city' => \request('city', false), // شهر
            'province' => \request('province', false), // استان
            'first_name' => \request('first_name', false), // نام
            'last_name' => \request('last_name', false), // نام خانوادگی
            'start_date' => \request('start_date', false) ? Carbon::createFromTimestamp(\request('start_date'))->toDateString() : false, // از تاریخ
            'end_date' => \request('end_date', false) ? Carbon::createFromTimestamp(\request('end_date'))->toDateString() : false, // تا تاریخ
        ];

        //        (new \Modules\Core\Helpers\Helpers)->updateOrdersUsefulData();
//        (new \Modules\Core\Helpers\Helpers)->updateOrdersCalculateData();

        $ordersBase = DB::table('orders as o')
            //            ->join('invoices as i','i.payable_id','=','o.id')
            ->select(
                'o.id',
                'o.customer_id',
                //                'shipping_id',
//                'coupon_id',
//                'address_id',
//                'address',
//                'receiver',
//                'city',
//                'province',
//                'receiver',
                DB::raw("CONCAT(CAST(json_unquote(JSON_EXTRACT(address, '$.first_name')) as CHAR), ' ', CAST(json_unquote(JSON_EXTRACT(address, '$.last_name')) as CHAR)) AS receiver"),
                //                DB::raw("CONCAT(o.first_name,' ',o.last_name) AS receiver"),
//                DB::raw("CONCAT(CAST(json_unquote(JSON_EXTRACT(address, '$.first_name')) as CHAR), ' ', CAST(json_unquote(JSON_EXTRACT(address, '$.last_name')) as CHAR)) AS receiver"),
//                DB::raw("trim(CAST(json_unquote(JSON_EXTRACT(address, '$.city.name')) as CHAR)) AS city"),
//                DB::raw("trim(CAST(json_unquote(JSON_EXTRACT(address, '$.city.province.name')) as CHAR)) AS province"),
                'o.shipping_amount',
                'o.discount_amount',
                //                'description',
                'o.status',
                //                'weight',
//                'gift_wallet_amount',
//                'status_detail',
//                'delivered_at',
//                'reserved',
//                'reserved_id',
                'o.parent_id',
                //                'shipping_packet_amount',
//                'shipping_more_packet_price',
//                'shipping_first_packet_size',
//                'creatorable_type',
//                'creatorable_id',
//                'updaterable_type',
//                'updaterable_id',
                'o.created_at',
                'o.total_amount',
                //                'updated_at',
//                'order',
//                DB::raw('(SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) as items_count'),
//                'o.items_count'
                'o.total_items_count AS items_count'
            )
            ->whereNull('o.parent_id')
            ->latest('o.id');

        foreach ($requestParams as $key => $requestParam) {
            if ($requestParam) {
                switch ($key) {
                    case 'id':
                        $ordersBase = $ordersBase->where('o.id', $requestParam);
                        break;

                    case 'customer_id':
                        $ordersBase = $ordersBase->where('o.customer_id', $requestParam);
                        break;

                    case 'start_date':
                        $ordersBase = $ordersBase->whereDate('o.created_at', '>=', $requestParam);
                        break;

                    case 'end_date':
                        $ordersBase = $ordersBase->whereDate('o.created_at', '<=', $requestParam);
                        break;

                    case 'status':
                        $ordersBase = $ordersBase->where('o.status', $requestParam);
                        break;

                    case 'first_name':
                        $ordersBase = $ordersBase->whereRaw("o.first_name like '%$requestParam%'");
                        break;

                    case 'last_name':
                        $ordersBase = $ordersBase->whereRaw("o.last_name like '%$requestParam%'");
                        break;

                    case 'city':
                        $ordersBase = $ordersBase->where('o.city', $requestParam);
                        break;

                    case 'province':
                        $ordersBase = $ordersBase->where('o.province', $requestParam);
                        break;

                    case 'product_id':
                        $ordersBase = $ordersBase
                            ->join('order_items as oip', 'o.id', '=', 'oip.order_id')
                            ->where('oip.product_id', $requestParam);
                        break;

                    case 'variety_id':
                        $ordersBase = $ordersBase
                            ->join('order_items as oiv', 'o.id', '=', 'oiv.order_id')
                            ->where('oiv.variety_id', $requestParam);
                        break;
                }
            }
        }

        if ($date) {
            $ordersBase = $ordersBase->whereDate('o.created_at', '=', $date);
        }

        $all_order_statuses = [
            "wait_for_payment",
            "in_progress",
            "delivered",
            "new",
            "canceled",
            "failed",
            "reserved",
            "canceled_by_user"
        ];

        $order_statuses_total = clone $ordersBase;
        $order_statuses_total = $order_statuses_total
            ->select('o.status', DB::raw('count(o.status) as total'))
            ->groupBy('o.status')
            ->pluck('total', 'o.status')
            ->toArray();

        foreach ($all_order_statuses as $os) {
            if (!isset($order_statuses_total[$os])) {
                $order_statuses_total[$os] = 0;
            }
        }

        $orders = $ordersBase->paginate($paginate);

        //        $orders->getCollection()->map(function($item) {
//            // Append additional data to each item
//
////            $item->active_payments = Helpers::getActivePayments($item->id);
//
//            if (!$item->receiver){
//                $order_address = DB::table('orders')->select('address')->where('id', $item->id)->first();
//                $address = json_decode($order_address->address);
//                $full_name = $address->first_name.' '.$address->last_name;
//                DB::table('orders')->select('address')->where('id', $item->id)->update(['receiver'=>$full_name]);
//                $item->receiver = $full_name;
//            }
//
//            return $item;
//        });

        $order_statuses = Order::getAllStatuses($orders);

        $orders = $orders->toArray();

        return response()->success('Get optimized orders list :)', compact('orders', 'order_statuses', 'order_statuses_total'));
    }




    public function detailsOrderForPrint(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:orders,id'
        ]);
        $orders = Order::withCommonRelations()->with('childs')->with([
            'reservations' => function ($query) {
                $query->where('status', Order::STATUS_RESERVED);
            }
        ])->parents()->whereIn('id', $request->ids)
            ->orderByRaw(DB::raw("FIELD(id, " . implode(',', $request->ids) . ")"))
            ->get();

        foreach ($orders as $order) {
            $child_items = [];
            foreach ($order->childs as $child) {
                if (in_array($child->status, Order::ACTIVE_STATUSES)) {
                    foreach ($child->items->where('status', 1) as $ch_item) {
                        $child_items[] = $ch_item;

                    }
                }
            }
            foreach ($child_items as $child_order_item) {
                if (in_array($child_order_item->variety_id, $order->items->pluck('variety_id')->toArray())) {
                    foreach ($order->items as $item) {
                        if ($item->variety_id == $child_order_item->variety_id) {
                            if ($item->status == $child_order_item->status) {
                                $order->items->where('variety_id', $child_order_item->variety_id)->first()->quantity += $child_order_item->quantity;
                            } else {
                                $order->items->push($child_order_item);
                            }
                        }
                    }
                } else {
                    $order->items->push($child_order_item);
                }
            }
        }

        if(request()->header('Accept') == 'application/json') {
            return response()->success('جزئیات سفارشات انتخاب شده', compact('orders'));
        }
        dd($orders);
    }

    /** @throws Throwable */
    public function updateStatus(OrderUpdateStatusRequest $request, $id)
    {
        $order = Order::find($id);
        if ($order->parent_id) {
            return response()->error('امکان تغییر وضعیت این سفارش وجود ندارد !');
        }

        //        event(new OrderChangedEvent($request->order, $request));
        //No need deleting
//        try {
        DB::beginTransaction();
        /** @var Order $order */
        $order = $request->order;
        (new ChangeStatus($order, $request))->checkStatus();
        ChangeStatusNotificationJob::dispatch($order);
        DB::commit();
        //        } catch (Exception $exception) {
//            DB::rollBack();
//            if (!($exception instanceof BalanceIsEmpty) && !($exception instanceof InsufficientFunds)) {
//                Log::error($exception->getTraceAsString());
//            }
//            return response()->error('عملیات به مشکل خورده است: ' . $exception->getMessage(), $exception->getTrace());
//        }

        return response()->success("وضعیت سفارش با موفقیت تغییر کرد");
    }

    public function changeStatusSelectedOrders(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:orders,id',
            'status' => ['required', Rule::in([Order::STATUS_NEW, Order::STATUS_DELIVERED, Order::STATUS_IN_PROGRESS])]
        ]);
        //TODO از کنسل و در انتظار پرداخت نمی تواند به این وضعیت ها تغییر کند
        $orders = Order::with(relations: 'childs')->whereIn('id', $request->ids)->whereNull('parent_id')->get();

        foreach ($orders as $order) {
            try {
                \Illuminate\Support\Facades\DB::beginTransaction();
                (new ChangeStatus($order, $request))->checkStatus(true);
                \Illuminate\Support\Facades\DB::commit();
                // ChangeStatusNotificationJob::dispatch($order);
            } catch (Throwable $exception) {
                \Illuminate\Support\Facades\DB::rollBack();
                Log::error($exception->getMessage() . $exception->getTraceAsString());
                return redirect()->back()->with('error','');
            }
            // if (in_array($request->status, [Order::STATUS_DELIVERED, Order::STATUS_IN_PROGRESS])) {
            //     $this->updateOrderCalculations($order->id);
            // }
        }
        // if (request()->header('Accept') == 'application/json') {
        //     return response()->success('تغییر وضعیت با موفقیت انجام شد.', null);
        // }
        return redirect()->back()->with('success', 'تغییر وضعیت با موفقیت انجام شد.');
    }

    public function updateOrderCalculations($order_id)
    {
        $total_amount = DB::table('order_items')->select(DB::raw('SUM(quantity*amount) AS total'))->where('order_id', $order_id)->first()->total;
        $items_count = DB::table('order_items')->where('order_id', $order_id)->count();
        $items_quantity = DB::table('order_items')->select('quantity')->where('order_id', $order_id)->sum('quantity');

        $sub_orders = DB::table('orders')->where('parent_id', $order_id)->whereIn('status', ['delivered', 'in_progress'])->get();
        foreach ($sub_orders as $sub_order) {
            $total_amount += DB::table('order_items')->select(DB::raw('sum(quantity*amount) as s'))->where('order_id', $sub_order->id)->first()->s;
            $items_count += DB::table('order_items')->where('order_id', $sub_order->id)->count();
            $items_quantity += DB::table('order_items')->select(DB::raw('sum(quantity) as q'))->where('order_id', $sub_order->id)->first()->q;
        }

        //        $order->total_amount = $total_amount;
//        $order->items_count = $items_count;
//        $order->items_quantity = $items_quantity;
        DB::table('orders')
            ->where('id', $order_id)
            ->update([
                'total_amount' => $total_amount,
                'items_count' => $items_count,
                'items_quantity' => $items_quantity,
            ]);
    }

    public function allNewOrders()
    {
        $startDate = \request('start_date', false) ? ' از ' . verta(Carbon::createFromTimestamp(\request('start_date'))->toDateString())->format('l d F') : null;
        $endDate = \request('end_date', false) ? ' تا ' . verta(Carbon::createFromTimestamp(\request('end_date'))->toDateString())->format('l d F') : null;
        $dateText = $startDate && $endDate ? $startDate . $endDate : null;
        $orders = [];
        $ordersCount = 0;
        $order_statuses = [];
        if ($dateText) {
            $productId = \request('product_id', false);
            $varietyId = \request('variety_id', false);
            $trackingCode = \request('tracking_code', false);
            $city = \request('city', false);
            $province = \request('province', false);
            $orderQuery = OrderReport::query()
                ->with('customer', 'invoices.payments.invoice')->withCount('items')
                ->with([
                    'reservations' => function ($query) {
                        $query->where('status', Order::STATUS_RESERVED)
                            ->with('invoices.payments.invoice');
                    }
                ])->when($productId || $varietyId, function (Builder $query) use ($productId, $varietyId) {
                    $query->whereHas('items', function ($query) use ($productId, $varietyId) {
                        if ($productId && !$varietyId) {
                            $query->where('product_id', $productId);
                        } else {
                            $query->where('variety_id', $varietyId);
                        }
                    });
                })->when($city, function (Builder $query) use ($city) {
                    $query->where('address->city->name', 'LIKE', '%' . $city . '%');
                })->when(\request('first_name'), function (Builder $query) {
                    $query->where('address->first_name', 'LIKE', '%' . \request('first_name') . '%');
                })->when(\request('last_name'), function (Builder $query) {
                    $query->where('address->last_name', 'LIKE', '%' . \request('last_name') . '%');
                })->when($province, function (Builder $query) use ($province) {
                    $query->where('address->city->province->name', 'LIKE', '%' . $province . '%');
                })->when($trackingCode, function ($query) use ($trackingCode) {
                    $invoiceIds = Payment::query()->where('tracking_code', 'LIKE', "%$trackingCode%")
                        ->get(['invoice_id'])->pluck('invoice_id');
                    $orderIds = Invoice::query()->whereIn('id', $invoiceIds)->
                        where('payable_type', Order::class)->get(['payable_id'])->pluck('payable_id');
                    $query->whereIn('id', $orderIds);
                })->parents()->filters()->latest('id');
            $orders = $orderQuery->paginateOrAll(999999);

            foreach ($orders as $order) {
                if ($order instanceof OrderReport) {
                    $order->setAppends([]);
                }
                $order->append('wallet_invoices');
                $order->append('active_payment');
                $order->append('active_payments');
                $order->makeHidden('invoices');
            }

            /** @var $orderQuery Builder */
            $copyOrderQuery = clone $orderQuery;
            Helpers::removeWhere($copyOrderQuery->getQuery(), 'status');
            $order_statuses = Order::getAllStatuses($copyOrderQuery);
            $ordersCount = $orders->count();
        }

        return response()->success('New Orders', compact('orders', 'order_statuses', 'ordersCount', 'dateText'));
    }

    public function todayOrders()
    {
        if (request()->header('Accept') == 'application/json') {
            //مشتری سفارشات دیروز رو میخاد
            $date = date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d'))));
            $start_date = \request('start_date', false);
            $end_date = \request('end_date', false);

            $paginate = 1000;

            if ($start_date && $end_date) {
                return $this->getOrders($paginate, null, \request('apiType'));
            } else {
                return $this->getOrders($paginate, $date, \request('apiType'));
            }
        }
        return $this->index(todayOrders: true);
        
    }

    public function todayOrdersLight()
    {
        //        $startTime = microtime(true);
        //مشتری سفارشات دیروز رو میخاد
        $date = date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d'))));
        $start_date = \request('start_date', false);
        $end_date = \request('end_date', false);

        $paginate = 1000;

        if ($start_date && $end_date) {
            $result = $this->getOrdersLight($paginate, null, \request('apiType'));
        } else {
            $result = $this->getOrdersLight($paginate, $date, \request('apiType'));
        }

        //        $endTime = microtime(true);
//        $elapsedTime = $endTime - $startTime;
//        Log::info('New Orders Light: ', ['elapsed_time' => $elapsedTime]);

        return $result;
    }

    public function oldIndex(): JsonResponse
    {
        list($startDate, $endDate) = array_values(Order::getStartEndMonth(
            (int) request('month', verta()->format('m')),
            \request('offset_year', 0)
        ));

        $productId = \request('product_id', false);
        $varietyId = \request('variety_id', false);
        $trackingCode = \request('tracking_code', false);
        $city = \request('city', false);
        $province = \request('province', false);

        $orderQuery = OrderReport::query()->with('customer', 'invoices.payments.invoice')->withCount('items')
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('created_at', '>', $startDate);
            })->when($endDate, function ($query) use ($endDate) {
                $query->where('created_at', '<', $endDate);
            })
            ->with([
                'reservations' => function ($query) {
                    $query->where('status', Order::STATUS_RESERVED)
                        ->with('invoices.payments.invoice');
                }
            ])
            ->when($productId || $varietyId, function (Builder $query) use ($productId, $varietyId) {
                $query->whereHas('items', function ($query) use ($productId, $varietyId) {
                    if ($productId && !$varietyId) {
                        $query->where('product_id', $productId);
                    } else {
                        $query->where('variety_id', $varietyId);
                    }
                });
            })->when($city, function (Builder $query) use ($city) {
                $query->where('address->city->name', 'LIKE', '%' . $city . '%');
            })->when(\request('first_name'), function (Builder $query) {
                $query->where('address->first_name', 'LIKE', '%' . \request('first_name') . '%');
            })->when(\request('last_name'), function (Builder $query) {
                $query->where('address->last_name', 'LIKE', '%' . \request('last_name') . '%');
            })->when($province, function (Builder $query) use ($province) {
                $query->where('address->city->province->name', 'LIKE', '%' . $province . '%');
            })->when($trackingCode, function ($query) use ($trackingCode) {
                $invoiceIds = Payment::query()->where('tracking_code', 'LIKE', "%$trackingCode%")
                    ->get(['invoice_id'])->pluck('invoice_id');
                $orderIds = Invoice::query()->whereIn('id', $invoiceIds)->
                    where('payable_type', Order::class)->get(['payable_id'])->pluck('payable_id');
                $query->whereIn('id', $orderIds);
            })
            ->parents()->filters()->latest('id');

        $orders = $orderQuery->paginateOrAll(app(CoreSettings::class)
            ->get('order.admin.pagination', 10));

        foreach ($orders as $order) {
            if ($order instanceof OrderReport) {
                $order->setAppends([]);
            }
            $order->append('wallet_invoices');
            $order->append('active_payment');
            $order->append('active_payments');
            $order->makeHidden('invoices');
        }

        /** @var $orderQuery Builder */
        $copyOrderQuery = clone $orderQuery;
        Helpers::removeWhere($copyOrderQuery->getQuery(), 'status');
        $order_statuses = Order::getAllStatuses($copyOrderQuery);

        return response()->success('Get all orders list', compact('orders', 'order_statuses'));
    }

    public function searchById(Request $request)
    {
        $order = Order::query()
            ->with('customer', 'invoices.payments.invoice')
            ->withCount('items')
            ->with([
                'reservations' => function ($query) {
                    $query->where('status', Order::STATUS_RESERVED)
                        ->with('invoices.payments.invoice');
                }
            ])
            ->parents()->filters()
            ->findOrFail($request->order_id);

        if ($order instanceof OrderReport) {
            $order->setAppends([]);
        }
        $order->append('wallet_invoices');
        $order->append('active_payment');
        $order->append('active_payments');
        $order->makeHidden('invoices');

        return response()->success('order detail', compact('order'));
    }








    // came from vendor ================================================================================================
    public function update(OrderUpdateRequest $request, $id): JsonResponse
    {
        event(new OrderChangedEvent($request->order, $request));
        DB::beginTransaction();
        try {
            /** @var Order $order */
            $order = $request->order;
            $parentOrder = $order->reserved_id == null ? $order : Order::findOrFail($order->reserved_id);

            /** @var Customer $customer */
            $customer = $request->customer;
            $oldTotalAmount = $order->getTotalAmount(); // before Update
            $oldAddress = $order->address;
            $oldShipping = $order->shipping_id;
            $oldDiscountAmount = $order->discount_amount;
            $order->update([
                'shipping_id' => $request->shipping_id,
                'address' => $request->address->toJson(),
                'discount_amount' => $request->discount_amount,
                'description' => $request->description,
            ]);

            $parentOrder->recalculateShippingAmount();

            //Create status logs
            if ($order->statusLogs->count() < 1) {
                $order->statusLogs()->createMany([
                    ['status' => Order::STATUS_WAIT_FOR_PAYMENT],
                    ['status' => Order::STATUS_IN_PROGRESS]
                ]);
            }

            $diffTotalAmount = $order->getTotalAmount() - $oldTotalAmount;
            //withdraw customer wallet
            if ($diffTotalAmount > 0) {
                $order->setPayDescription('کسر مبلغ اضافه شده سفارش از کیف پول بعد از به روزرسانی مدیریت #' . $order->id);
                $customer->withdraw($diffTotalAmount, $order->getMetaProduct());
            } elseif ($diffTotalAmount < 0) {
                $order->setPayDescription('افزایش مبلغ کم شده سفارش به کیف پول بعد از به روزرسانی مدیریت #' . $order->id);
                $customer->deposit(-$diffTotalAmount, $order->getMetaProduct());
            }
            $order->load('shipping', 'orderLogs');

            OrderLog::addLog(
                $order,
                $order->getTotalAmount() - $oldTotalAmount,
                $order->discount_amount - $oldDiscountAmount,
                $order->address != $oldAddress ? $order->address : null,
                $order->shipping_id != $oldShipping ? $order->shipping_id : null
            );

            ActivityLogHelper::updatedModel('سفارش با موفقیت به روزرسانی شد', $order);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getTraceAsString());
            return response()->error('به روزرسانی سفارش به مشکل خورده است:' . $exception->getMessage(), $exception->getTrace(), 500);
        }

        return response()->success('سفارش مشتری با موفقیت به روزرسانی شد', compact('order'));
    }

    /** @throws Throwable */
    public function create(): JsonResponse
    {
        if (!\request('without_customers')) {
            $customers = Customer::query()->with('wallet:id,balance,holder_id,holder_type')->latest('id')->get(['id', 'first_name', 'last_name', 'mobile']);
        } else {
            $customers = null;
        }
        $provinces = Province::query()->with([
            'cities' => function ($query) {
                $query->select(['id', 'name', 'province_id']);
            }
        ])->get(['id', 'name']);
        $shippings = Shipping::active()->withCommonRelations()->get();

        return response()->success('', compact('customers', 'provinces', 'shippings'));
    }

}
