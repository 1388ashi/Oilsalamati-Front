<?php

namespace Modules\Order\Entities;

use Bavix\Wallet\Interfaces\Customer as CustomerWallet;
use Bavix\Wallet\Interfaces\Product as ProductWallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Cart\Entities\Cart;
//use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Entities\HasFilters;
//use Modules\Core\Helpers\Helpers;
use Modules\Core\Traits\HasMorphAuthors;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Notifications\InvoicePaid;
use Modules\Invoice\Classes\Payable;
use Modules\Invoice\Entities\Invoice;
use Modules\Invoice\Entities\Payment;
use Modules\Order\Classes\OrderStoreProperties;
use Modules\Order\Jobs\NewOrderForCustomerNotificationJob;
use Modules\Product\Entities\Variety;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Setting\Entities\Setting;
use Modules\Shipping\Entities\Shipping;
use Modules\Store\Entities\Store;
//use Shetabit\Shopit\Modules\Order\Entities\Order as BaseOrder;
use Modules\Store\Services\StoreBalanceService;
use Shetabit\Shopit\Modules\Sms\Sms;
//use Spatie\Activitylog\LogOptions;
//use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Payable implements ProductWallet
{
    protected $fillable = [
        'shipping_id',
        'coupon_id',
        'address',
        'shipping_amount',
        'discount_amount',
        'description',
        'status',
        'status_detail',
        'reserved_at',
        'shipping_packet_amount',
        'shipping_more_packet_price',
        'shipping_first_packet_size',
        'parent_id',
        'weight',
        'address_id',
        'gift_range_id',
        'gift_title',
        'gift_price',

        'children_count',
        'total_quantity',
        'total_items_count',
        'discount_on_products',
        'total_products_prices_with_discount',
        'total_shipping_amount',
        'paid_by_wallet_gift_balance',
        'paid_by_wallet_main_balance',
        'total_invoices_amount',
        'total_discount_on_orders'
    ];

//    protected $appends = [/*'total_amount','real_total_amount',*/ /*'showcase'*/];

    const STATUS_CANCELED_BY_USER = 'canceled_by_user';

    public static function booted()
    {
//        static::updated(function (\Modules\Order\Entities\Order $order) {
//            self::updateTotalFields($order->id);
//        });

        static::created(function (\Modules\Order\Entities\Order $order) {
            self::updateTotalFields($order->id);
        });


        static::deleting(function (\Modules\Order\Entities\Order $order) {
            $order->items->each(fn($item) => $item->delete());
            $order->reservations->each(fn($_order) => $_order->delete());
            $order->orderLogs->each(fn($_orderLog) => $_orderLog->delete());
        });
    }

    public static function getAvailableStatuses(): array
    {
        return [
            static::STATUS_WAIT_FOR_PAYMENT,
            static::STATUS_IN_PROGRESS,
            static::STATUS_DELIVERED,
            static::STATUS_NEW,
            static::STATUS_CANCELED,
            static::STATUS_FAILED,
            static::STATUS_RESERVED,
            static::STATUS_CANCELED_BY_USER,
        ];
    }


    public function childs()
    {
        return $this->hasMany(Order::class, 'parent_id');
    }



    public static function store(Customer $customer, $request)
    {
        try {
            \DB::beginTransaction();
            $ORDER = new static;
            /**
             * @var Cart $fakeCart
             * @var $properties OrderStoreProperties
             */
            $properties = $request->orderStoreProperties;
            $order = new static();
            $orderGift = OrderGiftRange::find($request->order_gift_id);

            $order->fill([
                'shipping_id' => $properties->shipping->id,
                'shipping_more_packet_price' => $properties->shipping->more_packet_price, // of course, we should get it from ShippingCalculatorService. but this is not important now
                'shipping_first_packet_size' => $properties->shipping->first_packet_size, // of course, we should get it from ShippingCalculatorService. but this is not important now
                'shipping_packet_amount' => $properties->shipping_packet_amount,
                'address' => $properties->address->toJson(),
                'coupon_id' => $properties->coupon ? $properties->coupon->id : null,
                'shipping_amount' => $properties->shipping_amount,
                'discount_amount' => $properties->discount_amount,
                'delivered_at' => $request->delivered_at,
                'status' => static::STATUS_WAIT_FOR_PAYMENT,
                'reserved' => $request->reserved ?? 0,
                'description' => $request->description,
                'weight' => $properties->orderWeight,

                'gift_title'=>$orderGift->title ?? null,
                'gift_price'=>$orderGift->price ?? null,
            ]);

            $order->customer()->associate($customer);
            $order->address()->associate($properties->address);

            $order->save();


            //Create status log
            $order->statusLogs()->create([
                'status' => static::STATUS_WAIT_FOR_PAYMENT
            ]);

            // store items for this order
            foreach ($properties->carts as $cart) {
                $ORDER->addItemsInOrder($order, $cart);
            }
            /**
             * کم کردن از انبار توی ایونت موقع پرداخت صورت میگیره
             * @see  CheckStoreOnVerified::store Listener
             * @see  GoingToVerifyPayment::__construct Event
             */
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            throw $exception;
        }
        $order->load('items');

        return $order;
    }


    public function depositCustomer($amount, $newStatus, $customer)
    {
        $customer->deposit($amount, [
            'causer_id' => auth()->user()->id,
            'causer_mobile' => auth()->user()->mobile,
            'description' => "برگشت مبلغ سفارش در اثر تغییر وضعیت به {$newStatus}"
        ]);
        $this->orderLogs()->create([

            'amount' => $this->getTotalAmount(),
            'status' => 'canceled'
        ]);
    }

    public function depositStore($orderItems)
    {
        foreach ($orderItems as $item) {
            Store::insertModel((object)
            [
                'variety_id' => $item->variety_id,
                'description' => "با تغییر وضغیت سفارش با شناسه {$item->order_id} به انبار اضافه شد",
                'type' => Store::TYPE_INCREMENT,
                'quantity' => $item->quantity
            ]);
        }
    }

    // محاسبه قیمت نهایی
    public function getTotalAmount($no_discount= false): int
    {
        $activeItems = $this->items->where('status', 1);

        $totalItemsAmount = $activeItems
            ->reduce(function ($total, $item) {
                return $total + ($item->amount * $item->quantity);
            });

        $discount_amount = $no_discount ? 0 : $this->attributes['discount_amount'];

        return ($totalItemsAmount + $this->attributes['shipping_amount']) - $discount_amount;
    }
//    public function getTotalAmountWithoutDiscount(): int
//    {
//        $activeItems = $this->items->where('status', 1);
//
//        $totalItemsAmount = $activeItems
//            ->reduce(function ($total, $item) {
//                return $total + ($item->amount * $item->quantity);
//            });
//
//        return ($totalItemsAmount + $this->attributes['shipping_amount']);
//    }

//    public function getRealTotalAmountAttribute()
//    {
//        return (new \Modules\Core\Helpers\Helpers)->calculateOrderFields($this,true);
//    }

    //
    public function getTotalAmountWithoutShipping(): int
    {
        $activeItems = $this->items->where('status', 1);

        $totalItemsAmount = $activeItems
            ->reduce(function ($total, $item) {
                return $total + ($item->amount * $item->quantity);
            });
//        Log::info('Total Items Amount : ' . $totalItemsAmount);
//        Log::info('Discount Amount : ' . $this->attributes['discount_amount']);
        return $totalItemsAmount - $this->attributes['discount_amount'];
    }


    public function onSuccessPayment(Invoice $invoice)
    {
        $this->status = $this->reserved ? static::STATUS_RESERVED : static::STATUS_NEW;
        $this->save();
        $wallet = $invoice->type == 'wallet' ? 1 : 0;
        $type = $wallet ? 'از کیف پول' : 'از درگاه پرداخت';

        /** @var OrderItem $item */
        foreach ($this->items()->active()->get() as $item) {
            if ($item->flash_id) {
                DB::table('flash_product')
                    ->where('product_id', $item->product_id)
                    ->where('flash_id', $item->flash_id)
                    ->update([
                        'sales_count' => DB::raw("sales_count + {$item->quantity}")
                    ]);
            }

            (new StoreBalanceService($item->variety_id))->getFromStore($item->quantity,'sdflkj',$this->id);

//            Store::insertModel((object)[
//                'variety_id' => $item->variety->id,
//                'description' => "محصول {$item->variety->title} {$type} توسط مشتری با شناسه {$this->customer_id} در سفارش {$this->id} خریداری شد ",
//                'type' => Store::TYPE_DECREMENT,
//                'quantity' => $item->quantity,
//                'order_id' => $this->id
//            ]);
        }

        // ذخیره سازی کوپن
        if ($this->coupon_id) {
            Coupon::useCoupon($this->customer_id, $this->coupon_id);
        }


//        if ($this->reserved_id) {
//            static::query()->findOrFail($this->reserved_id)
//                ->increment('shipping_amount', $this->shipping_amount);
//            $this->shipping_amount = 0;
//            $this->save();
//        }
        /** Clear customer basket for new purchases  */
        $this->customer->carts()->delete();


        // Send sms
        NewOrderForCustomerNotificationJob::dispatch($this)->delay(now()->addseconds(10));
        try {
            $this->customer->notify(new InvoicePaid($this));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
        if ($wallet) {
            $data = [
                'order_id' => $invoice->payable_id,
                'invoice_id' => $invoice->id,
                'need_pay' => 0
            ];

            return response()->success('خرید با موفقیت انجام شد.', $data);
        }

        return $this->callBackViewPayment($invoice);
    }

    public static function getAllStatuses($query)
    {
        $orderStatuses = [];
        foreach (static::getAvailableStatuses() as $status) {
            $orderStatuses[$status] = (clone $query->whereNull('parent_id'))->where('status', $status)->count();
        }
        return $orderStatuses;
    }

    public static function getStartEndMonth($month, $offsetYears): array
    {
        $startMonth = verta()->subYears($offsetYears)->month($month)->startMonth()->datetime();
        $endMonth = verta()->subYears($offsetYears)->month($month)->endMonth()->datetime();

        return [
            'start' => Carbon::instance($startMonth),
            'end' =>  Carbon::instance($endMonth),
        ];
    }

    public function scopeApplyFilter($query)
    {
        $id = request('id');
        $trackingCode = request('tracking_code');
        $city = request('city');
        $province = request('province');
        $firstName = request('first_name');
        $lastName = request('last_name');
        $customerId = request('customer_id');
        $status = request('status');
        $productId = request('product_id');
        $varietyId = request('variety_id');
        $startDate = request('start_date');
        $endDate = request('end_date');

        return $query->when($productId || $varietyId, function ($query) use ($productId, $varietyId) {
            $query->whereHas('items', function ($query) use ($productId, $varietyId) {
                $query
                    ->when($productId && !$varietyId, fn($q) => $q->where('product_id', $productId))
                    ->when($varietyId, fn($q) => $q->where('variety_id', $varietyId));
            });
        })
        ->when($trackingCode, function ($query) use ($trackingCode) {
            $invoiceIds = Payment::query()->where('tracking_code', 'LIKE', "%".$trackingCode."%")->pluck('invoice_id');
            $orderIds = Invoice::query()->whereIn('id', $invoiceIds)->where('payable_type', Order::class)->pluck('payable_id');
            $query->whereIn('id', $orderIds);
        })
        ->when($city, fn($q) => $q->where('address->city->name', 'LIKE', '%'.$city.'%'))
        ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
        ->when($firstName, fn($q) => $q->where('address->first_name', 'LIKE', '%'.$firstName.'%'))
        ->when($lastName, fn($q) => $q->where('address->last_name', 'LIKE', '%'.$lastName.'%'))
        ->when($province, fn($q) => $q->where('address->city->province->name', 'LIKE', '%'.$province.'%'))
        ->when($id, fn($q) => $q->where('id', $id))
        ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
        ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
        ->when(isset($status), fn($q) => $q->where('status', $status));

        /* extract($requestParams);
        $query->when($city, function ($query) use ($city) {
            $query->where('address->city->name', 'LIKE', '%'.$city.'%');
            $addressIds = Address::query()->where('city_id', $city_id)->pluck('id')->toArray();
           $query->whereIn('address_id', $addressIds);
        })
            ->when($first_name, function ($query) use ($first_name) {
                $query->where('address->first_name', 'LIKE', '%'.$first_name.'%');
            })
            ->when($last_name, function ($query) use ($last_name) {
                $query->where('address->last_name', 'LIKE', '%'.$last_name.'%');
            })
            ->when($province, function ($query) use ($province) {
                $query->where('address->city->province->name', 'LIKE', '%'.$province.'%');
            })
            ->when($tracking_code, function ($query) use ($tracking_code) {
                $invoiceIds = Payment::query()
                    ->where('tracking_code', 'LIKE', "%$tracking_code%")
                    ->pluck('invoice_id');

                $orderIds = Invoice::query()
                    ->whereIn('id', $invoiceIds)
                    ->where('payable_type', Order::class)
                    ->pluck('payable_id');
                $query->whereIn('id', $orderIds);
            });

        $query->when($id, function ($query) use ($id){
            $query->where('id',$id);
        });

        $query->when($start_date, function ($query) use ($start_date){
            $query->whereDate('created_at', '>=', $start_date);
            $query->whereRaw("date(created_at) >= '$start_date'");
        });

        $query->when($end_date, function ($query) use ($end_date){
            $query->whereDate('created_at', '<=', $end_date);
            $query->whereRaw("date(created_at) <= '$end_date'");
        });

        $query->when($status, function ($query) use ($status){
            $query->where('status', $status);
        });

        dd($requestParams, $query->toSql());
        return $query; */
    }

    public function activeItemsCount()
    {
        return $this->items()->where('status',1)->count();
    }

    public function gifts()
    {
        return $this->belongsTo(OrderGiftRange::class,'gift_range_id');
    }

    public function gift_transaction()
    {
        return $this->belongsTo(Transaction::class, 'gift_transaction_id');
    }


    public function getShowcaseAttribute()
    {
        return [
            'children_count' => $this->children_count,
            'total_quantity' => $this->total_quantity,
            'items_count' => $this->total_items_count,
            'discount_on_products' => $this->discount_on_products,
            'discount_on_order' => $this->total_discount_on_orders,
            'total_all_discounts' => $this->discount_on_products + $this->total_discount_on_orders,
            'total_products_prices_with_discount' => $this->total_products_prices_with_discount,
            'total_products_prices_without_discount' => $this->total_products_prices_with_discount + $this->discount_on_products,
            'shipping_amount' => $this->total_shipping_amount,
            'payed_by_wallet' => $this->paid_by_wallet_main_balance,
            'total_price' => $this->total_invoices_amount
        ];


        // ==========================================================================================

        $totals = [
            'children_count' => $this->childs()->whereIn('status', Order::ACTIVE_STATUSES)->count(),
            'total_quantity' => 0,
            'items_count' => 0,
            'discount_on_products' => 0,
            'discount_on_order' => 0,
            'total_all_discounts' => 0,
            'total_products_prices_with_discount' => 0,
            'total_products_prices_without_discount' => 0,
            'shipping_amount' => 0,
            'payed_by_wallet' => 0,
            'total_price' => 0
        ];

        $items = $this->items()->where('status', '=', true)->get();
        foreach ($items as $item) {
            $totals['total_quantity'] += $item->quantity;
            $totals['discount_on_products'] += $item->discount_amount * $item->quantity;
            $totals['total_products_prices_with_discount'] += $item->amount * $item->quantity;
            $totals['total_products_prices_without_discount'] += (($item->amount + $item->discount_amount) * $item->quantity);
        }
        $totals['items_count'] += $items->count();
        $totals['discount_on_order'] += $this->discount_amount;
        $totals['total_all_discounts'] += ($totals['discount_on_products'] + $this->discount_amount);
        $totals['shipping_amount'] += $this->shipping_amount;
        $totals['total_price'] += (($totals['total_products_prices_with_discount'] + $this->shipping_amount) - $totals['discount_on_order']);
        // invoices
        $invoices = $this->invoices()->where('status', 'success')->get();
        if ($invoices) {
            foreach ($invoices as $invoice) {
                $totals['payed_by_wallet'] += $invoice->wallet_amount;
            }
        }

        // for child
        $children = $this->childs()->whereIn('status', Order::ACTIVE_STATUSES)->get();
        foreach ($children as $child) {
            $products_prices_with_discount_for_this_child = 0;
            $discounts_on_products_for_this_child = 0;
            $items = $child->items()->where('status', '=', true)->get();
            foreach ($items as $item) {
                $totals['total_quantity'] += $item->quantity;
                $discounts_on_products_for_this_child += $item->discount_amount * $item->quantity;
                $products_prices_with_discount_for_this_child += $item->amount * $item->quantity;
                $totals['total_products_prices_without_discount'] += (($item->amount + $item->discount_amount) * $item->quantity);
            }
            $totals['discount_on_products'] += $discounts_on_products_for_this_child;
            $totals['total_products_prices_with_discount'] += $products_prices_with_discount_for_this_child;

            $totals['items_count'] += $items->count();
            $totals['discount_on_order'] += $child->discount_amount;
            $totals['total_all_discounts'] += ($discounts_on_products_for_this_child + $child->discount_amount);
            $totals['shipping_amount'] += $child->shipping_amount;
            $totals['total_price'] += (($products_prices_with_discount_for_this_child + $child->shipping_amount) - $child->discount_amount);

            // invoices
            $invoices = $child->invoices()->where('status', 'success')->get();
            if ($invoices) {
                foreach ($invoices as $invoice) {
                    $totals['payed_by_wallet'] += $invoice->wallet_amount;
                }
            }

        }

        return $totals;
    }


    public static function updateTotalFields($orderId)
    {
        $order = DB::table('orders')
            ->select(['id','customer_id','created_at','address_id','shipping_amount','discount_amount','status','parent_id'])
            ->where('id',$orderId)->whereIn('status',Order::ACTIVE_STATUSES)->first();
        if (!$order) {return;}
        if ($order->parent_id != null) {
            $order = DB::table('orders')->where('id',$order->parent_id)->whereIn('status',Order::ACTIVE_STATUSES)->first();
        }

        $parent_children_ids = [$order->id];

        $children_count = 0;
        $total_quantity = 0;
        $items_count = 0;
        $discount_on_products = 0;
        $discount_on_order = $order->discount_amount;
        $total_products_prices_with_discount = 0;
        $total_products_prices_without_discount = 0;
        $shipping_amount = $order->shipping_amount;
        $paid_by_wallet_gift_balance = 0;
        $paid_by_wallet_main_balance = 0;
        $paid_by_wallet = 0;
        $total_invoice_amount = 0;

        // children
        $children = DB::table('orders')
            ->whereIn('status', Order::ACTIVE_STATUSES)
            ->select(['id','customer_id','created_at','address_id','shipping_amount','discount_amount','status','parent_id'])
            ->where('parent_id', $order->id)
            ->get();
        if ($children) {
            foreach ($children as $child) {
                $parent_children_ids[] = $child->id;
                $children_count += 1;
                $shipping_amount += $child->shipping_amount;
                $discount_on_order += $child->discount_amount;
            }
        }
        // invoices
        $invoices = DB::table('invoices')
            ->select(['id','amount','wallet_amount','gift_wallet_amount','payable_id','payable_type','status'])
            ->where('status', Invoice::STATUS_SUCCESS)->where('payable_type', 'Modules\\Order\\Entities\\Order')->whereIn('payable_id', $parent_children_ids)->get();
        foreach ($invoices as $invoice) {
            $total_invoice_amount += $invoice->amount;
            $paid_by_wallet += $invoice->wallet_amount;
            $paid_by_wallet_main_balance += ($invoice->wallet_amount - $invoice->gift_wallet_amount);
            $paid_by_wallet_gift_balance += $invoice->gift_wallet_amount;
        }

        // all items
        $order_items = DB::table('order_items')
            ->select(['id','amount','order_id','quantity','discount_amount','status'])
            ->where('status', 1)->whereIn('order_id', $parent_children_ids)->get();
        foreach ($order_items as $item) {
            $total_quantity += $item->quantity;
            $items_count += 1;
            $discount_on_products += ($item->discount_amount * $item->quantity);
            $total_products_prices_with_discount += ($item->amount * $item->quantity);
            $total_products_prices_without_discount += (($item->amount + $item->discount_amount) * $item->quantity);
        }


        DB::table('orders')->where('id', $order->id)->update([
            'children_count' => $children_count,
            'total_quantity' => $total_quantity,
            'total_items_count' => $items_count,
            'discount_on_products' => $discount_on_products,
            'total_discount_on_orders' => $discount_on_order,
            'total_products_prices_with_discount' => $total_products_prices_with_discount,
            'total_shipping_amount' => $shipping_amount,
            'paid_by_wallet_gift_balance' => $paid_by_wallet_gift_balance,
            'paid_by_wallet_main_balance' => $paid_by_wallet_main_balance,
            'total_invoices_amount' => $total_invoice_amount,
        ]);
    }


    public function orderUpdaters(): \Illuminate\Database\Eloquent\Relations\HasMany  {
        return $this->hasMany(OrderUpdater::class, 'order_id');
    }
    // came from vendor ================================================================================================
    use HasFactory/*, HasCommonRelations*/, HasMorphAuthors, HasFilters, HasWallet/*, LogsActivity*/;

    const STATUS_NEW = 'new';
    const STATUS_WAIT_FOR_PAYMENT = 'wait_for_payment';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELED = 'canceled';
    const STATUS_FAILED = 'failed';
    const STATUS_RESERVED = 'reserved';

    const ACTIVE_STATUSES = [
        Order::STATUS_DELIVERED, Order::STATUS_IN_PROGRESS, Order::STATUS_RESERVED,Order::STATUS_NEW
    ];

    public static $commonRelations = [
        /*'customer', 'statusLogs', 'items', 'invoices.payments', 'shipping', 'orderLogs'*/
    ];

    protected $casts = [
        'shipping_amount' => 'integer',
        'discount_amount' => 'integer'
    ];

    protected $payDescription = null;


    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function address(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function associateReserved($order, $customer, $properties)
    {
        $oldOrder = $customer->orders()
            ->where('address_id', $properties->address->id)
            ->where('status', static::STATUS_RESERVED)
            ->isReserved()
            ->latest()->first();
        if ($oldOrder) {
            $order->reserved()->associate($oldOrder);
        }

        return $order;
    }
    //Wallet
    public function statusLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }
    public function addItemsInOrder($order, $cart)
    {
        /**
         * @var $varietyItem Variety
         */
        $varietyItem = $cart->variety;
        $gifts = $varietyItem->final_gift;
        $productCache = (new ProductsCollectionService())->getProductObjectFromVarietyId($cart->variety_id);
        $activeFlash = $productCache->activeFlash->first();

        /** @var OrderItem $item */
        $item = $order->items()->create([
            'product_id' => $productCache->id,
            'variety_id' => $cart->variety_id,
            'quantity' => $cart->quantity,
            'amount' => $cart->price,
            'flash_id' => $activeFlash->id ?? null,
            'discount_amount' => $cart->discount_price,
            'extra' => collect([
                'attributes' => $varietyItem->attributes()->get(['name', 'label', 'value']),
                'color' => $varietyItem->color()->exists() ? $varietyItem->color->name : null
            ])->toJson()
        ]);
        $item->gifts()->attach($gifts, ['gift' => $gifts?->toJson()]);
    }
    public static function getOrderStatisticsForCustomer(Customer $customer)
    {
        $statistics = [];
        foreach (static::getAvailableStatuses() as $status) {
            $statistics[$status] = $customer->orders()->where('status', $status)->count();
        }

        return $statistics;
    }
    public function getActivePaymentsAttribute()
    {
        $payments = [];
        $activePayment = $this->getActivePaymentAttribute();
        if ($activePayment) {
            $payments[] = $activePayment;
            foreach ($this->reservations as $reservation) {
                $activePayment = $reservation->getActivePaymentAttribute();
                if ($activePayment) {
                    $payments[] = $activePayment;
                }
            }
        }

        return $payments;
    }
    // فاکتور هایی که یا کلا با کیف پول پرداخت شدن یا بخشیشون از کیف پول پرداخت شده
    public function getWalletInvoicesAttribute()
    {
        $final = [...$this->getSuccessWalletInvoices()];
        foreach ($this->reservations as $reservation) {
            $walletInvoices = $reservation->getSuccessWalletInvoices();
            if (count($walletInvoices)) {
                $final = [...$final, ...$walletInvoices];
            }
        }

        // Fake invoice from order logs (زمانی که فاکتور توسط ادمین ویرایش شده باشد)
        $orderLogs = $this->orderLogs;
        $changeAmount = 0;
        foreach ($orderLogs as $orderLog) {
            $changeAmount += $orderLog->amount;
        }
        if ($changeAmount !== 0) {
            $invoice = new Invoice();
            $invoice->forceFill([
                'amount' => $changeAmount,
                'status' => Invoice::STATUS_SUCCESS,
                'type' => Invoice::PAY_TYPE_WALLET
            ]);
            $final = [...$final, $invoice];
        }

        return $final;
    }
    public function getStatusAttribute($status)
    {
        /**
         * مهم است، پاک نشود در جاب استفاده میشود
         */
        if ($status == static::STATUS_RESERVED && $this->reserved_id == null && $this->isReservedExpired()) {
            static::withoutEvents(function () {
                $this->update(['status' => static::STATUS_NEW]);
            });
            OrderStatusLog::store($this, $status);
        }

        return $status;
    }
//    public function getActivitylogOptions(): LogOptions
//    {
//        $user = auth()->user();
//
//        return LogOptions::defaults()
//            ->useLogName('Order')
//            ->logAll()
//            ->logOnlyDirty()
//            ->setDescriptionForEvent(function ($eventName) use ($user) {
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "سفارش با شناسه {$this->id} توسط '' {$eventName} شد.";
//            });
//    }
    #end of wallet
    public function setPayDescription($value)
    {
        $this->payDescription = $value;
    }
    //Scopes
    public function orderLogs()
    {
        return $this->hasMany(OrderLog::class, 'order_id')->latest('id');
    }
    //Custom functions
    public function canBuy(CustomerWallet $customer, int $quantity = 1, bool $force = null): bool
    {
        /**
         * If the service can be purchased once, then
         * @see OrderValidationService::checkAvailableVariety
         */

        return !$customer->paid($this);
    }
    //Relations
    public function getAmountProduct(CustomerWallet $customer): int
    {
        return $this->getTotalAmount();
    }
    // محاسبه قیمت نهایی
    public function getTotalAmountAttribute()
    {
        return $this->getTotalAmount();
    }
    public function getMetaProduct(): ?array
    {
        return [
            'customer_name' => $this->customer->full_name,
            'customer_mobile' => $this->customer->mobile,
            'description' => $this->payDescription ?: 'خرید سفارش به شماره #' . $this->id
        ];
    }
    public function getUniqueId(): string
    {
        return (string)$this->getKey();
    }
    public function scopeMyOrders($query)
    {
        return $query->where('customer_id', '=', auth()->user()->id);
    }

    public function shipping(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

    public function coupon(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function reserved(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(static::class, 'reserved_id');
    }

    public function scopeParents($query)
    {
        $query->whereNull('parent_id');
    }

    public function isPayable()
    {
        return $this->status === static::STATUS_WAIT_FOR_PAYMENT;
    }

    public function getPayableAmount()
    {
        return $this->getTotalAmount();
    }

    public function scopeArrayIdsByDate($query, $startDay, $endDay): array
    {
        return static::query()
            ->whereBetween('created_at', [$startDay, $endDay])
            ->get('id')->pluck('id')->toArray();
    }

    public function scopeIsReserved($query)
    {
//        $reservesDay = Setting::getFromName('reserved_day') ?: 0;
//        $reservesDayStart = \Carbon\Carbon::now()->subDays($reservesDay)->toDateTimeString();
        $query
            ->where('status', Order::STATUS_RESERVED)
            ->whereNotNull('reserved_at');
//            ->whereNull('reserved_id')
//            ->whereBetween('created_at', [$reservesDayStart, Carbon::now()->toDateTimeString()]);
    }

    public function scopeIsActiveReserved($query)
    {

        $reservesDay = Setting::getFromName('reserved_day') ?: 0;
        $reservesDayStart = Carbon::now()->subDays($reservesDay)->toDateTimeString();
        $query->where('reserved', true)
            ->where('status', static::STATUS_RESERVED)
            ->whereNull('reserved_id')
            ->whereBetween('created_at', [$reservesDayStart, Carbon::now()->toDateTimeString()]);
    }

    public function recalculateShippingAmount()
    {
        $this->shipping_amount = $this->calculateShippingAmount();
        $this->save();
        return $this->shipping_amount;
    }

    // Parent

    public function calculateShippingAmount()
    {
        $totalTotalQuantity = $this->getTotalTotalQuantity();
        return static::getPacketHelper(
            $totalTotalQuantity,$this->shipping->packet_size, $this->shipping_packet_amount,
            $this->shipping_more_packet_price, $this->shipping_first_packet_size);
    }

    // Childeren

    public function getTotalTotalQuantity()
    {
        return $this->activeItems()->sum('quantity') + $this->activeReserved()
                ->withSum('activeItems', 'quantity')
                ->get()->sum('active_items_sum_quantity');
    }

    public function activeItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->items()->where('status', '=', 1);
    }

    public function isReservedExpired()
    {
        $reservesDay = Setting::getFromName('reserved_day') ?: 0;
        $reservesDayStart = Carbon::now()->subDays($reservesDay);

        return $this->created_at < $reservesDayStart;
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id')
            ->with(['variety']);
//        ->with(['variety' => function ($query) {
//        $query->withCommonRelations();
//    }]);
    }



    public function activeReserved()
    {
        $reservesDay = Setting::getFromName('reserved_day') ?: 0;
        $reservesDayStart = Carbon::now()->subDays($reservesDay)->toDateTimeString();
        return $this->reservations()
            ->where('status', static::STATUS_RESERVED)
            ->whereNotNull('reserved_id')
            ->whereBetween('created_at', [$reservesDayStart, Carbon::now()->toDateTimeString()]);
    }

    public function reservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(static::class, 'reserved_id', 'id')/*->withCommonRelations()*/;
    }

    public function onFailedPayment(Invoice $invoice): View|Factory|JsonResponse|Application
    {
        $this->status = static::STATUS_FAILED;
        $this->status_detail = $invoice->status_detail;
        $this->save();

        NewOrderForCustomerNotificationJob::dispatch($this);

        return $this->callBackViewPayment($invoice);
    }

    public static function getPacketHelper($quantity, $packetSize, $price, $morePrice, $firstPacketSize, $oldQuantity = 0, $oldShippingAmountPaid = 0)
    {
        $allQuantity = $quantity + $oldQuantity;
        if ($allQuantity <= $firstPacketSize){
            return $price - $oldShippingAmountPaid;
        }

        $totalPackets = (int)ceil((($allQuantity) - $firstPacketSize) / $packetSize);

        return (int)($price + $totalPackets * $morePrice) - $oldShippingAmountPaid;
    }

    public function callBackViewPayment($invoice)
    {
        return (\Illuminate\Support\Facades\View::exists('basecore::invoice.callback')) ?
            view('basecore::invoice.callback', ['invoice' => $invoice, 'type' => 'order'])
            :
            view('core::invoice.callback', ['invoice' => $invoice, 'type' => 'order']);
    }

    public function scopeSuccess($query)
    {
        $query->whereIn('status', static::ACTIVE_STATUSES);
    }

    public function getReceiverAttribute()
    {
        $addressArray = json_decode($this->attributes['address'], true);

        return $addressArray['first_name'] .' '. $addressArray['last_name'];
    }
}
