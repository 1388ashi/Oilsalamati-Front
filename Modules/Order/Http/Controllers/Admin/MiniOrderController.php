<?php

namespace Modules\Order\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\MiniOrder;
use Modules\Order\Entities\MiniOrderItem;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Variety;
use Modules\Report\Entities\OrderReport;
use Modules\Core\Helpers\Helpers;
use Modules\Order\Http\Requests\Admin\MiniOrderStoreRequest;

class MiniOrderController extends Controller
{
    public function index()
    {
        $mini_orders = MiniOrder::query()->withCount('miniOrderItems')->latest()->paginateOrAll(40);
        $mini_orders->each(function (MiniOrder $miniOrder) {
            $miniOrder->append('total');
        });

        return response()->success('', compact('mini_orders'));
    }

    public function show(MiniOrder $miniOrder)
    {
        $miniOrder->loadCount('miniOrderItems');
        $miniOrder->append('total');
        $miniOrder->miniOrderItems->each(function (MiniOrderItem $miniOrderItem) {
            $miniOrderItem->load(['variety' => function($query) {
                $query->with(['attributes', 'color', 'product']);
            }]);
        });

        return response()->success('', [
            'mini_order' => $miniOrder
        ]);
    }

    public function store(MiniOrderStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            if ($request->mobile) {
                $alreadyExists = false;
                /** @var Customer $customer */
                $customer = Customer::query()->where('mobile', $request->mobile)->first();
                if ($customer) {
                    $alreadyExists = true;
                } else {
                    $customer = Customer::query()->create([
                        'mobile' => $request->mobile
                    ]);
                }
                $request->merge([
                    'customer_id' => $customer->id
                ]);

                if ($alreadyExists) {
                    $balance = $customer->balance;
                    if ($balance < $request->from_wallet_amount) {
                        return response()->error('مبلغ وارد شده از کیف پول مشتری بیشتر است');
                    }
                } else {
                    if ($request->from_wallet_amount) {
                        return response()->error('مبلغ وارد شده از کیف پول مشتری بیشتر است');
                    }
                }
            }
            /** @var MiniOrder $miniOrder */
            $miniOrder = new MiniOrder($request->all());
            $hasSell = $request->has('varieties') && count($request->varieties);
            $hasRefund = $request->has('refund_varieties') && count($request->refund_varieties);
            $type = match (true) {
                $hasRefund && $hasSell => MiniOrder::TYPE_BOTH,
                $hasRefund => MiniOrder::TYPE_REFUND,
                $hasSell => MiniOrder::TYPE_SELL,
                default => throw new \Exception('23214140'),
            };
            $miniOrder->type = $type;
            $miniOrder->save();

            foreach ($request->varieties as $varietyFromRequest) {
                /** @var Variety $variety */
                $variety = Variety::withCommonRelations()->findOrFail($varietyFromRequest['id']);
                MiniOrderItem::store($variety, $varietyFromRequest['quantity'],
                    $miniOrder, MiniOrderItem::TYPE_SELL, $varietyFromRequest['amount']);
            }

            foreach ($request->refund_varieties as $refundVarietyFromRequest) {
                /** @var Variety $variety */
                $variety = Variety::withCommonRelations()->findOrFail($refundVarietyFromRequest['id']);
                MiniOrderItem::store($variety, $refundVarietyFromRequest['quantity'],
                    $miniOrder, MiniOrderItem::TYPE_REFUND, $refundVarietyFromRequest['amount']);
            }

            if ($request->from_wallet_amount) {
                $transaction = $customer->withdraw($request->from_wallet_amount, [
                    'description' => 'خرید سفارش حضوری به شماره #' . $miniOrder->id
                ]);
                $miniOrder->transaction()->associate($transaction);
            }

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();

            Log::error($throwable->getTraceAsString());
            return response()->error('عملیات به مشکل خورد: ' . $throwable->getMessage(),
                $throwable->getTrace());
        }

        $miniOrder->load('customer');

        return response()->success('سفارش با موفقیت ایجاد شد', ['mini_order' => $miniOrder]);
    }

    public function destroy(MiniOrder $miniOrder)
    {
        try {
            DB::beginTransaction();
            $miniOrder->delete();
            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();

            return response()->error('مشکلی رخ داد: ' . $throwable->getMessage(), ['data' => $throwable->getTrace()]);
        }

        return response()->success('سفارش با موفقیت حذف شد', ['mini_order' => $miniOrder]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'sku' => 'required'
        ]);

        $variety = Variety::query()->withCommonRelations()->where('barcode', $request->sku)->first();
        if (!$variety) {
            return response()->error('محصول مورد نظر یافت نشد', null, 404);
        }
        if (!$variety->store->balance && !$request->refund) {
            return response()->error('محصول مورد نظر موجودی ندارد');
        }

        return response()->success('', compact('variety'));
    }

    public function searchCustomer(Request $request)
    {
        $customer = Customer::query()->withCommonRelations()
            ->where('mobile', $request->mobile)->first();
        if (!$customer) {
            return response()->success('', compact('customer'));
        }

        /* Online */
        $online_orders_count = Order::query()->where('customer_id', $customer->id)->success()->count();
        $online_orders_total = OrderReport::query()
            ->where('customer_id', $customer->id)->success()->sum('total');
        $last_online_order_at = $online_orders_count === 0 ? null :
            Order::query()->where('customer_id', $customer->id)->success()->latest('id')
                ->select('created_at')->first()->created_at;

        /* Physical */
        $physical_orders_count = MiniOrder::query()
            ->where('customer_id', $customer->id)
            ->where('type', '!=', MiniOrder::TYPE_REFUND)->count();
        $physical_orders_total = 0;
        $miniOrders = MiniOrder::query()->latest()->where('customer_id', $customer->id)
            ->get();
        $miniOrders->each(fn ($m) => $physical_orders_total += $m->miniOrderItems()->sum('amount'));
        $last_physical_order_at = $miniOrders->count() ? $miniOrders->first()->created_at : null;

        $online = [
            'orders_count' => $online_orders_count,
            'orders_total' => $online_orders_total,
            'last_order_at' => $last_online_order_at
        ];

        $physical = [
            'orders_count' => $physical_orders_count,
            'orders_total' => $physical_orders_total,
            'last_order_at' => $last_physical_order_at
        ];

        return response()->success('', compact('customer', 'online', 'physical'));
    }
}
