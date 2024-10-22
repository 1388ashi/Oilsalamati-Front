<?php

namespace Modules\Report\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Area\Entities\Province;
use Modules\Customer\Entities\Customer;
use Modules\Home\Entities\SiteView;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderItem;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;

class NewReportController extends Controller
{
    public function customers()
    {
        $customers = Customer::query()
            ->with('orders', fn($q) => $q->orderByDesc('id'))
            ->withCount('orders')
            ->filters()
            ->latest('id')
            ->paginate();

        $maxInvoiceAmount = Order::max('total_invoices_amount');
        $maxItemCount = Order::max('total_items_count');
        $provinces = Province::query()->active()->select('id', 'name')->with('cities')->get();

        return view('report::admin.customers.index', compact(['customers', 'maxInvoiceAmount', 'maxItemCount', 'provinces']));
    }

    public function products()
    {
        $products = Product::query()
            ->select(['id', 'title'])
            ->paginate(10);

        return view('report::admin.products.index', compact('products'));
    }

    public function orders()
    {
        $orders = Order::query()
            ->select(['id', 'discount_amount', 'shipping_amount', 'created_at', 'customer_id'])
            ->withCount('items')
            ->applyFilter()
            ->latest('id')
            ->paginate(50);

        $provinces = Province::query()->active()->select('id', 'name')->with('cities')->get();

        return view('report::admin.orders.index', compact(['orders', 'provinces']));
    }

    public function varieties()
    {
        $varieties = Variety::query()->active()->latest('id')->paginate(10);

        return view('report::admin.varieties.varieties', compact('varieties'));
    }

    public function varietiesBalance()
    {
        $varieties = Variety::query()
            ->when(request('product_id'), fn($q) => $q->where('product_id', request('product_id')))
            ->when(request('variety_id'), fn($q) => $q->where('id', request('variety_id')))
            ->when(request('start_date'), fn($q) => $q->whereDate('created_at', '>=', request('start_date')))
            ->when(request('end_date'), fn($q) => $q->whereDate('created_at', '<=', request('end_date')))
            ->active()
            ->latest('id')
            ->paginate();

        return view('report::admin.varieties.varieties-balance', compact('varieties'));
    }

    public function wallets()
    {
        $customers = Customer::query()
            ->select(['id', 'first_name', 'last_name', 'mobile', 'created_at'])
            ->filters()
            ->latest('id')
            ->paginate();

        return view('report::admin.wallets.index', compact('customers'));
    }

    public function siteviews()
    {
        $siteviews = SiteView::query()
            ->select('date')
            ->selectRaw('SUM(count) as total_count')
            ->where('date', '<=', now())
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->paginate();

        return view('report::admin.siteviews.index', compact('siteviews'));
    }

    public function loadSiteViews(Request $request)
    {
        $siteviews = SiteView::query()
            ->where('date', $request->input('date'))
            ->latest('hour')
            ->get(['date', 'hour', 'count']);

        return response()->success('', compact('siteviews'));
    }

    public function loadVarieties()
    {
        $product = Product::findOrFail(request('product_id'));

        if ($product->hasFakeVariety()) {
            return response()->success(['no_variety' => true]);
        }

        $varietyReports = [];
        $sumTotal = 0;
        $sumQuantity = 0;

        foreach ($product->varieties()->with(['attributes', 'color'])->get() as $variety) {

            $varietyReport = [];
            $varietyReport['variety'] = $variety;

            $orderItems = OrderItem::query()
                ->whereHas('order', fn($q) => $q->success())
                ->where('variety_id', $variety->id)
                ->active()
                ->get();

            $varietyReport['total'] = $orderItems->sum('amount');
            $varietyReport['quantity'] = $orderItems->sum('quantity');
            $sumTotal += $varietyReport['total'];
            $sumQuantity += $varietyReport['quantity'];
            $varietyReports[] = $varietyReport;
        }


        return response()->success('', [
            'variety_reports' => $varietyReports,
            'no_variety' => false,
            'sum_quantity' => $sumQuantity,
            'sum_total' => $sumTotal
        ]);
    }
}
